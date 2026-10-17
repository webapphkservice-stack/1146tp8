<?php
namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户会员等级变更记录（B3 会员等级折扣）
 *
 * change_type 参考：10=后台调整，20=消费自动升级，30=降级
 */
class GradeLog extends BaseModel
{
    protected $name = 'user_grade_log';
    protected $updateTime = false;

    /**
     * 批量新增变更记录
     * @param array $data 每项可含 change_type / old_grade_id / new_grade_id / remark 等
     */
    public function records($data)
    {
        $saveData = [];
        foreach ($data as $item) {
            $saveData[] = array_merge([
                'change_type' => 10,
                'wxapp_id'   => static::$wxapp_id,
            ], $item);
        }
        return $this->saveAll($saveData);
    }
}
