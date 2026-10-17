<?php
namespace app\api\service;

use think\facade\Db;
use think\facade\Log;

/**
 * 账号关联(合并)服务（移植自 php_my api/service/UserMerge，纯 DB 操作，TP8 适配）
 */
class UserMerge
{
    private static $excludeTables = [
        'user', 'admin_user', 'store_user', 'dealer_user',
    ];

    private static $sumFields = [
        'balance', 'points', 'expend_money', 'pay_money', 'total_recharge',
    ];

    private $error = '';

    public function getError()
    {
        return $this->error;
    }

    public function canMerge($from, $to)
    {
        if (!empty($from['open_id']) && !empty($to['open_id'])) {
            $this->error = '该手机号已绑定另一个微信账号，如需合并请联系客服';
            return false;
        }
        if (!empty($from['alipay_user_id']) && !empty($to['alipay_user_id'])) {
            $this->error = '该手机号已绑定另一个支付宝账号，如需合并请联系客服';
            return false;
        }
        return true;
    }

    public function merge($fromUserId, $toUserId)
    {
        $fromUserId = (int)$fromUserId;
        $toUserId = (int)$toUserId;
        if ($fromUserId <= 0 || $toUserId <= 0 || $fromUserId === $toUserId) {
            $this->error = '账号信息有误';
            return false;
        }
        $from = Db::name('user')->where('user_id', $fromUserId)->find();
        $to = Db::name('user')->where('user_id', $toUserId)->find();
        if (empty($from) || empty($to)) {
            $this->error = '账号不存在';
            return false;
        }
        if (!$this->canMerge($from, $to)) {
            return false;
        }
        Db::startTrans();
        try {
            $this->migrateBusinessTables($fromUserId, $toUserId);
            $this->mergeDealer($fromUserId, $toUserId);
            $this->mergeUserRow($from, $to);
            Db::name('user')->where('user_id', $fromUserId)->update([
                'open_id'       => '',
                'alipay_user_id' => '',
                'mobile'        => '',
                'whatsapp'      => '',
                'is_delete'     => 1,
            ]);
            Db::commit();
            Log::write("账号合并成功：{$fromUserId} -> {$toUserId}", 'info');
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            Log::write("账号合并失败：{$fromUserId} -> {$toUserId}，原因：" . $e->getMessage(), 'error');
            $this->error = '账号关联失败，请稍后重试';
            return false;
        }
    }

    private function migrateBusinessTables($fromUserId, $toUserId)
    {
        $prefix = Db::getConfig('prefix');
        $tables = $this->getTablesWithUserId();
        foreach ($tables as $table) {
            $shortName = $prefix !== '' && strpos($table, $prefix) === 0
                ? substr($table, strlen($prefix)) : $table;
            if (in_array($shortName, self::$excludeTables)) {
                continue;
            }
            Db::execute(
                "UPDATE IGNORE `{$table}` SET `user_id` = :to WHERE `user_id` = :from",
                ['to' => $toUserId, 'from' => $fromUserId]
            );
        }
    }

    private function getTablesWithUserId()
    {
        $database = Db::getConfig('database');
        $rows = Db::query(
            'SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND COLUMN_NAME = :col',
            ['db' => $database, 'col' => 'user_id']
        );
        $tables = [];
        foreach ($rows as $row) {
            $name = isset($row['TABLE_NAME']) ? $row['TABLE_NAME'] : reset($row);
            if (!empty($name) && preg_match('/^[A-Za-z0-9_]+$/', $name)) {
                $tables[] = $name;
            }
        }
        return $tables;
    }

    private function mergeDealer($fromUserId, $toUserId)
    {
        $table = Db::getConfig('prefix') . 'dealer_user';
        if (!$this->tableExists($table)) {
            return;
        }
        $fromDealer = Db::table($table)->where('user_id', $fromUserId)->find();
        if (empty($fromDealer)) {
            return;
        }
        $toDealer = Db::table($table)->where('user_id', $toUserId)->find();
        if (empty($toDealer)) {
            Db::table($table)->where('user_id', $fromUserId)->update(['user_id' => $toUserId]);
            return;
        }
        $incFields = ['money', 'freeze_money', 'total_money'];
        $update = [];
        foreach ($incFields as $field) {
            if (array_key_exists($field, $fromDealer) && array_key_exists($field, $toDealer)) {
                $update[$field] = $toDealer[$field] + $fromDealer[$field];
            }
        }
        if (!empty($update)) {
            Db::table($table)->where('user_id', $toUserId)->update($update);
        }
        Db::table($table)->where('user_id', $fromUserId)->delete();
    }

    private function mergeUserRow($from, $to)
    {
        $update = [];
        foreach (['open_id', 'alipay_user_id', 'whatsapp'] as $field) {
            if (array_key_exists($field, $from) && empty($to[$field]) && !empty($from[$field])) {
                $update[$field] = $from[$field];
            }
        }
        foreach (self::$sumFields as $field) {
            if (array_key_exists($field, $from) && array_key_exists($field, $to)) {
                $update[$field] = $to[$field] + $from[$field];
            }
        }
        foreach (['nickName', 'avatarUrl'] as $field) {
            if (array_key_exists($field, $from) && empty($to[$field]) && !empty($from[$field])) {
                $update[$field] = $from[$field];
            }
        }
        if (!empty($update)) {
            Db::name('user')->where('user_id', $to['user_id'])->update($update);
        }
    }

    private function tableExists($table)
    {
        try {
            $rows = Db::query('SHOW TABLES LIKE :name', ['name' => $table]);
            return !empty($rows);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function findRivalByMobile($mobile, $excludeUserId)
    {
        return Db::name('user')
            ->where('mobile', $mobile)
            ->where('user_id', '<>', $excludeUserId)
            ->where('is_delete', 0)
            ->find();
    }

    public static function tryAutoBindMobile($userId, $mobile, $allowPlaceholder = false)
    {
        $mobile = trim((string)$mobile);
        if (!preg_match('/^1\d{10}$/', $mobile)) {
            return ['status' => 'skip', 'msg' => ''];
        }
        $user = Db::name('user')->where('user_id', $userId)->find();
        if (empty($user)) {
            return ['status' => 'skip', 'msg' => ''];
        }
        if (!empty($user['mobile'])) {
            return ['status' => 'exists', 'msg' => ''];
        }
        $rival = self::findRivalByMobile($mobile, $userId);
        if ($rival) {
            return ['status' => 'need_verify', 'msg' => '该手机号已注册，请在【我的】页面通过短信验证绑定，即可关联微信与支付宝账号'];
        }
        if (!$allowPlaceholder) {
            return ['status' => 'unverified', 'msg' => '该手机号可用于关联微信与支付宝账号，请在【我的】页面通过短信验证完成绑定'];
        }
        Db::name('user')->where('user_id', $userId)->update(['mobile' => $mobile]);
        return ['status' => 'bound', 'msg' => '已将该手机号绑定至您的账号，用于关联微信与支付宝账号'];
    }
}
