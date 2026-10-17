<?php

namespace app\store\model\dealer;

use app\common\model\BaseModel;

/**
 * 分销商入驻申请模型(后台，自包含)
 * Class Apply
 * @package app\store\model\dealer
 */
class Apply extends BaseModel
{
    protected $name = 'dealer_apply';

    // 审核状态
    const AUDIT_WAIT = 10;
    const AUDIT_PASS = 20;
    const AUDIT_REJECT = 30;

    /**
     * 推荐人
     * @return \think\model\relation\BelongsTo
     */
    public function referee()
    {
        return $this->belongsTo('app\store\model\User', 'referee_id', 'user_id');
    }

    /**
     * 获取申请列表
     * @param string $search
     * @return \think\Paginator
     */
    public function getList($search = '')
    {
        $this->withoutGlobalScope()
            ->alias('apply')
            ->field('apply.*, user.nickName, user.avatarUrl')
            ->join('user', 'user.user_id = apply.user_id')
            ->where('apply.wxapp_id', '=', self::getWxappId())
            ->where('apply.is_delete', '=', 0)
            ->order(['apply.create_time' => 'desc']);
        !empty($search) && $this->where('user.nickName|apply.real_name|apply.mobile', 'like', "%$search%");
        return $this->paginate(15, false, [
            'query' => \request()->request()
        ]);
    }

    /**
     * 详情
     * @param $applyId
     * @return null|\think\Model
     */
    public static function detail($applyId)
    {
        return static::with(['referee'])->find($applyId);
    }

    /**
     * 审核
     * @param $data
     * @return bool
     */
    public function submit($data)
    {
        if ($data['apply_status'] == self::AUDIT_REJECT && empty($data['reject_reason'])) {
            $this->error = '请填写驳回原因';
            return false;
        }
        $this->transaction(function () use ($data) {
            if ($data['apply_status'] == self::AUDIT_PASS) {
                // 审核通过：新增分销商用户
                User::add($this['user_id'], [
                    'real_name' => $this['real_name'],
                    'mobile' => $this['mobile'],
                    'referee_id' => $this['referee_id'],
                ]);
            }
            // 更新申请记录
            $data['audit_time'] = time();
            $this->save($data);
        });
        return true;
    }

}
