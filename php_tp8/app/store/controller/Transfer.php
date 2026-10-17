<?php

namespace app\store\controller;

use app\store\model\Setting;
use think\facade\Db;

/**
 * 商家转账管理（商户后台）
 * 参照微信支付商家转账到零钱 V3 接口文档
 * @link https://pay.weixin.qq.com/doc/v3/merchant/4012711988
 */
class Transfer extends Controller
{
    /**
     * 微信支付商家转账场景定义（8 种场景）
     */
    private static $scenes = [
        ['id' => 1000, 'name' => '现金营销', 'identifier' => 'SCENE_CASH_MARKETING', 'sub_scene' => '活动奖励/现金奖励'],
        ['id' => 1001, 'name' => '保险理赔', 'identifier' => 'SCENE_INSURANCE_CLAIM', 'sub_scene' => '保险理赔款'],
        ['id' => 1002, 'name' => '企业赔付', 'identifier' => 'SCENE_ENTERPRISE_COMPENSATION', 'sub_scene' => '退款/商家赔付'],
        ['id' => 1003, 'name' => '采购货款', 'identifier' => 'SCENE_PROCUREMENT', 'sub_scene' => '货款'],
        ['id' => 1004, 'name' => '公益补助', 'identifier' => 'SCENE_PUBLIC_WELFARE', 'sub_scene' => '公益补助金'],
        ['id' => 1005, 'name' => '佣金报酬', 'identifier' => 'SCENE_COMMISSION', 'sub_scene' => '劳务报酬/报销款/企业补贴/开工利是'],
        ['id' => 1006, 'name' => '二手回收', 'identifier' => 'SCENE_SECOND_HAND', 'sub_scene' => '二手回收货款'],
        ['id' => 1007, 'name' => '行政补贴', 'identifier' => 'SCENE_ADMINISTRATIVE_SUBSIDY', 'sub_scene' => '行政补贴/行政奖励'],
    ];

    /**
     * 转账场景管理
     */
    public function scenes()
    {
        $settings = Setting::getItem('transfer_scenes', $this->getWxappId());
        $scenes = [];
        foreach (self::$scenes as $scene) {
            $scene['is_enable'] = isset($settings[$scene['id']]['is_enable']) ? (int)$settings[$scene['id']]['is_enable'] : 1;
            $scene['remark'] = isset($settings[$scene['id']]['remark']) ? $settings[$scene['id']]['remark'] : '';
            $scenes[] = $scene;
        }
        return $this->fetch('scene_display', compact('scenes'));
    }

    /**
     * 场景编辑
     */
    public function scene_edit()
    {
        $scene_id = input('scene_id', 0, 'intval');
        $scene = null;
        foreach (self::$scenes as $s) {
            if ($s['id'] === $scene_id) {
                $scene = $s;
                break;
            }
        }
        if (!$scene) {
            $this->error('场景不存在');
        }

        $settings = Setting::getItem('transfer_scenes', $this->getWxappId());
        $scene['is_enable'] = isset($settings[$scene_id]['is_enable']) ? (int)$settings[$scene_id]['is_enable'] : 1;
        $scene['remark'] = isset($settings[$scene_id]['remark']) ? $settings[$scene_id]['remark'] : '';

        if ($this->request->isAjax()) {
            $post = input('post.');
            $allSettings = Setting::getItem('transfer_scenes', $this->getWxappId());
            $allSettings[$scene_id] = [
                'is_enable' => isset($post['is_enable']) ? (int)$post['is_enable'] : 1,
                'remark' => $post['remark'] ?? '',
            ];
            $model = Setting::detail('transfer_scenes');
            if ($model) {
                $model['values'] = $allSettings;
                if (!$model->save()) {
                    return $this->renderError('保存失败');
                }
            } else {
                $model = new Setting;
                $model->save([
                    'key' => 'transfer_scenes',
                    'describe' => '商家转账场景设置',
                    'values' => $allSettings,
                    'wxapp_id' => $this->getWxappId(),
                ]);
            }
            return $this->renderSuccess('保存成功');
        }
        return $this->fetch('scene_edit', compact('scene'));
    }

    /**
     * 转账 API 接口配置
     */
    public function setting()
    {
        $values = Setting::getItem('transfer', $this->getWxappId());

        if ($this->request->isAjax()) {
            $post = input('post.');
            $model = Setting::detail('transfer');
            if ($model) {
                $model['values'] = $post;
                if (!$model->save()) {
                    return $this->renderError('保存失败');
                }
            } else {
                $model = new Setting;
                $model->save([
                    'key' => 'transfer',
                    'describe' => '商家转账接口配置',
                    'values' => $post,
                    'wxapp_id' => $this->getWxappId(),
                ]);
            }
            return $this->renderSuccess('保存成功');
        }
        return $this->fetch('setting_form', compact('values'));
    }

    /**
     * 转账记录列表（当前商户）
     */
    public function records()
    {
        $params = input('param.');
        $wxapp_id = $this->getWxappId();

        $query = Db::name('dealer_withdraw')
            ->alias('w')
            ->join('user u', 'u.user_id = w.user_id', 'left')
            ->field([
                'w.id', 'w.user_id', 'w.money', 'w.pay_type', 'w.apply_status',
                'w.audit_time', 'w.reject_reason', 'w.wxapp_id',
                'w.wx_bill_id', 'w.wx_out_bill_no', 'w.wx_transfer_state',
                'w.create_time', 'w.update_time',
                'u.nickName', 'u.avatarUrl'
            ])
            ->where('w.wxapp_id', $wxapp_id)
            ->order('w.id desc');

        // 按微信转账状态筛选
        if (!empty($params['transfer_state'])) {
            $query->where('w.wx_transfer_state', $params['transfer_state']);
        }
        // 按审核状态筛选
        if (!empty($params['apply_status']) && $params['apply_status'] !== '') {
            $query->where('w.apply_status', (int)$params['apply_status']);
        }
        // 按转账单号搜索
        if (!empty($params['bill_no'])) {
            $query->where(function ($q) use ($params) {
                $q->where('w.wx_out_bill_no', 'like', "%{$params['bill_no']}%")
                  ->whereOr('w.wx_bill_id', 'like', "%{$params['bill_no']}%");
            });
        }
        // 按日期范围
        if (!empty($params['start_time'])) {
            $query->where('w.create_time', '>=', strtotime($params['start_time']));
        }
        if (!empty($params['end_time'])) {
            $query->where('w.create_time', '<=', strtotime($params['end_time']) + 86399);
        }

        $list = $query->paginate(15, false, ['query' => $params]);
        return $this->fetch('record_list', compact('list'));
    }
}
