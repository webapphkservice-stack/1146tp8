<?php
namespace app\store\model\user;

use app\common\model\user\Grade as GradeModel;
use think\facade\Db;

/**
 * 商家后台会员等级模型（B3）
 *
 * 继承公共核心模型，补充商家端 CRUD 与表单校验。
 */
class Grade extends GradeModel
{
    /**
     * 列表（分页）
     */
    public function getList()
    {
        return $this->where('is_delete', '=', 0)
            ->order(['weight' => 'asc', 'create_time' => 'desc'])
            ->paginate(15);
    }

    /**
     * 新增
     */
    public function add($data)
    {
        // 先补 wxapp_id（全局作用域依赖），再做权重校验
        $data['wxapp_id'] = self::getWxappId();
        if (!$this->validateForm($data)) {
            return false;
        }
        if (isset($data['upgrade'])) {
            $data['upgrade'] = json_encode($data['upgrade'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['equity'])) {
            $data['equity'] = json_encode($data['equity'], JSON_UNESCAPED_UNICODE);
        }
        return $this->save($data);
    }

    /**
     * 编辑
     */
    public function edit($data)
    {
        if (!$this->validateForm($data, 'edit')) {
            return false;
        }
        if (isset($data['upgrade'])) {
            $data['upgrade'] = json_encode($data['upgrade'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['equity'])) {
            $data['equity'] = json_encode($data['equity'], JSON_UNESCAPED_UNICODE);
        }
        return $this->save($data) !== false;
    }

    /**
     * 软删除（删除前校验该等级下是否有会员）
     */
    public function setDelete()
    {
        $count = Db::name('user')
            ->where('grade_id', $this['grade_id'])
            ->where('is_delete', 0)
            ->count();
        if ($count > 0) {
            $this->error = '该会员等级下存在用户，不允许删除';
            return false;
        }
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 表单校验（权重唯一）
     */
    private function validateForm($data, $scene = 'add')
    {
        if ($scene === 'add') {
            if (self::checkExistByWeight($data['weight'])) {
                $this->error = '等级权重已存在';
                return false;
            }
        } elseif ($scene === 'edit') {
            if (self::checkExistByWeight($data['weight'], $this['grade_id'])) {
                $this->error = '等级权重已存在';
                return false;
            }
        }
        return true;
    }
}
