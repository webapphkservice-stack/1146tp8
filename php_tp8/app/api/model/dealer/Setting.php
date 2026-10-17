<?php
namespace app\api\model\dealer;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 分销设置模型（轻量版，对齐 RN dealer.ts 的 center/apply/withdraw/team/order）
 *  - 直接读取 dealer_setting 表（按 wxapp_id），与 php_my Setting::getAll 行为一致
 *  - 默认数据与数据库值做递归合并，数据库优先
 */
class Setting
{
    /** @var array 默认设置（与 php_my 保持一致，数据库无记录时兜底） */
    private static $defaultData = [
        'basic'       => ['describe' => '基础设置', 'values' => ['is_open' => '0', 'level' => '2', 'self_buy' => '1']],
        'commission'  => ['describe' => '佣金设置', 'values' => ['first_money' => '0', 'second_money' => '0', 'third_money' => '0']],
        'condition'   => ['describe' => '分销商条件', 'values' => ['become' => '20', 'become__buy_goods' => '0', 'downline' => '10']],
        'license'     => ['describe' => '申请协议', 'values' => ['license' => '']],
        'settlement'  => [
            'describe' => '结算',
            'values'   => [
                'pay_type'        => ['10', '20'],
                'min_money'       => '1',
                'settle_days'     => '7',
                'alipay'          => ['name' => '', 'account' => ''],
                'bank_name'       => '',
                'bank_account'    => '',
                'bank_open'       => '',
                'bank_real_name'  => '',
            ],
        ],
        'words'       => ['describe' => '自定义文字', 'values' => []],
        'background'  => ['describe' => '背景图', 'values' => ['__' => '', 'index' => '', 'apply' => '', 'withdraw_apply' => '']],
    ];

    /**
     * 全量设置（与 php_my Setting::getAll 返回结构一致）
     * @return array [key => ['key'=>, 'describe'=>, 'values'=>]]
     */
    public static function getAll()
    {
        $wxappId = BaseModel::getWxappId();
        $rows = Db::name('dealer_setting')->where('wxapp_id', $wxappId)->select()->toArray();
        $db = [];
        foreach ($rows as $r) {
            $values = !empty($r['values']) ? json_decode($r['values'], true) : [];
            $db[$r['key']] = is_array($values) ? $values : [];
        }
        $merged = [];
        foreach (self::$defaultData as $key => $def) {
            $mergedValues = array_replace_recursive($def['values'], $db[$key] ?? []);
            $merged[$key] = ['key' => $key, 'describe' => $def['describe'], 'values' => $mergedValues];
        }
        return $merged;
    }

    /** 取某项 values */
    public static function getItem($key)
    {
        $all = self::getAll();
        return isset($all[$key]['values']) ? $all[$key]['values'] : [];
    }

    public static function getBasic()
    {
        return self::getItem('basic');
    }

    public static function getWords()
    {
        return self::getItem('words');
    }

    public static function getCondition()
    {
        return self::getItem('condition');
    }

    public static function getSettlement()
    {
        return self::getItem('settlement');
    }
}
