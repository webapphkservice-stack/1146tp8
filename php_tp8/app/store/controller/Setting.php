<?php

namespace app\store\controller;

use app\store\model\Printer as PrinterModel;
use app\store\model\Setting as SettingModel;
use app\common\library\sms\Driver as SmsDriver;

/**
 * 系统设置（商城/交易/短信/订阅消息/上传/打印）
 *
 * 移植自 php/source/application/store/controller/Setting.php。
 * 菜单入口：setting/store、setting/trade、setting/sms、setting/tplMsg、
 *          setting/storage、setting/printer、setting/sms、setting/storage
 *
 * 全部走 SettingModel::getItem/edit（与满额包邮同款模式）。
 * 关键点：设置项未初始化时 getItem 返回空数组，视图里 $values['xxx'] 在 PHP8 下会
 * 报未定义索引，因此统一用 settingValues() 合并默认值后再渲染。
 */
class Setting extends Controller
{
    /** 商城设置 */
    public function store()
    {
        return $this->updateEvent('store');
    }

    /** 交易设置 */
    public function trade()
    {
        return $this->updateEvent('trade');
    }

    /** 短信通知 */
    public function sms()
    {
        return $this->updateEvent('sms');
    }

    /** 订阅消息 */
    public function tplMsg()
    {
        return $this->updateEvent('tplMsg');
    }

    /** 上传设置 */
    public function storage()
    {
        return $this->updateEvent('storage');
    }

    /** 小票打印设置（额外需要打印机列表做下拉） */
    public function printer()
    {
        return $this->updateEvent('printer', [
            'printerList' => PrinterModel::getAll(),
        ]);
    }

    /**
     * 发送短信测试
     * @param string $AccessKeyId
     * @param string $AccessKeySecret
     * @param string $sign
     * @param string $msg_type
     * @param string $template_code
     * @param string $accept_phone
     * @return mixed
     */
    public function smsTest($AccessKeyId, $AccessKeySecret, $sign, $msg_type, $template_code, $accept_phone)
    {
        try {
            $SmsDriver = new SmsDriver([
                'default' => 'aliyun',
                'engine'  => [
                    'aliyun' => [
                        'AccessKeyId'     => $AccessKeyId,
                        'AccessKeySecret' => $AccessKeySecret,
                        'sign'            => $sign,
                        $msg_type         => compact('template_code', 'accept_phone'),
                    ],
                ],
            ]);
            if ($SmsDriver->sendSms($msg_type, [], true)) {
                return $this->renderSuccess('发送成功');
            }
            return $this->renderError('发送失败 ' . $SmsDriver->getError());
        } catch (\Throwable $e) {
            return $this->renderError('发送失败：' . $e->getMessage());
        }
    }

    /**
     * 更新设置事件
     * @param string $key
     * @param array  $vars
     * @return array|mixed
     */
    private function updateEvent($key, $vars = [])
    {
        if (!$this->request->isAjax()) {
            $vars['values'] = $this->settingValues($key);
            return $this->fetch($key, $vars);
        }
        $model = new SettingModel;
        $data  = $this->postData($key);
        if ($model->edit($key, $data)) {
            // store（商城设置）保存后，同步更新 wxapp.app_name，
            // 使 admin 后台"商城管理"列表能读到最新商城名称。
            if ($key === 'store' && !empty($data['name'])) {
                \think\facade\Db::name('wxapp')
                    ->where('wxapp_id', $this->getWxappId())
                    ->update([
                        'app_name'    => trim((string)$data['name']),
                        'update_time' => time(),
                    ]);
            }
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 读取设置并与默认值合并（避免视图出现未定义索引）
     * @param string $key
     * @return array
     */
    private function settingValues($key)
    {
        $defaults = [
            'store'   => [
                'name'          => '',
                'delivery_type' => [],
                'icp_text'      => '',
            ],
            // 天数默认值与 order:cron 及 PayConfig::trade() 保持一致：3/10/7
            'trade'   => [
                'freight_rule' => 0,
                'order'        => [
                    'close_days'   => 3,
                    'receive_days' => 10,
                    'refund_days'  => 7,
                ],
            ],
            'sms'     => [
                'engine'  => [
                    'aliyun' => [
                        'AccessKeyId'     => '',
                        'AccessKeySecret' => '',
                        'sign'            => '',
                        'register'        => [
                            'is_enable'     => 0,
                            'template_code' => '',
                            'accept_phone'  => '',
                        ],
                    ],
                ],
                'chatapp' => [
                    'accessKeyId'        => '',
                    'accessKeySecret'    => '',
                    'endpoint'           => '',
                    'regionId'           => '',
                    'custSpaceId'        => '',
                    'fromPhone'          => '',
                    'verifyTemplateCode' => '',
                    'verifyExpire'       => 0,
                    'verifyInterval'     => 0,
                    'language'           => '',
                ],
            ],
            'storage' => [
                'default' => 'local',
                'engine'  => [
                    'aliyun' => ['access_key_id' => '', 'access_key_secret' => '', 'bucket' => '', 'domain' => ''],
                    'qiniu'  => ['access_key' => '', 'secret_key' => '', 'bucket' => '', 'domain' => ''],
                    'qcloud' => ['secret_id' => '', 'secret_key' => '', 'bucket' => '', 'region' => '', 'domain' => ''],
                ],
            ],
            'tplMsg'  => [
                'payment'  => ['is_enable' => 0, 'template_id' => ''],
                'delivery' => ['is_enable' => 0, 'template_id' => ''],
                'refund'   => ['is_enable' => 0, 'template_id' => ''],
            ],
            'printer' => [
                'is_open'      => 0,
                'order_status' => [],
                'printer_id'   => 0,
            ],
        ];

        $values = SettingModel::getItem($key);
        return array_replace_recursive($defaults[$key] ?? [], is_array($values) ? $values : []);
    }
}
