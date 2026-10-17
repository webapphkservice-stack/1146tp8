<?php

namespace app\store\model;

use app\common\model\BaseModel;
use app\common\enum\PrinterType as PrinterTypeEnum;
use think\facade\Db;

/**
 * 打印机模型
 *
 * 移植自 php/source/application/store/model/Printer.php。
 * 说明：原实现继承 app\common\model\Printer，php_tp8 未迁移该 common 模型，
 * 这里改为直接操作 printer 表（getAll 供「小票打印设置」下拉使用）。
 */
class Printer extends BaseModel
{
    protected $name = 'printer';

    /**
     * 全部未删除的打印机（按排序、ID 升序）
     * @return \think\Collection
     */
    public static function getAll()
    {
        return Db::name('printer')
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId())
            ->order('sort', 'asc')
            ->order('printer_id', 'asc')
            ->select();
    }

    /**
     * 打印机详情
     * @param int $printerId
     * @return static|null
     */
    public static function detail($printerId)
    {
        return self::find($printerId);
    }

    /**
     * 打印机类型列表（供 add / edit 表单下拉）
     * @return array
     */
    public static function getPrinterTypeList()
    {
        return PrinterTypeEnum::getTypeName();
    }

    /**
     * 打印机列表（后台分页）
     * 注意：think-orm v4 的 paginate 只接受数组配置，不再支持 TP5 的 (15, false, [...])
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', $this->getPk() => 'desc'])
            ->paginate(['list_rows' => 15, 'query' => request()->param()]);
    }

    /**
     * 获取器：打印机类型（返回 [value, text]，供视图直接取 text）
     * @param string $value
     * @return array
     */
    public function getPrinterTypeAttr($value)
    {
        $printerType = self::getPrinterTypeList();
        return ['value' => $value, 'text' => $printerType[$value] ?? $value];
    }

    /**
     * 获取器：printer_config 自动转数组
     * @param string $value
     * @return array
     */
    public function getPrinterConfigAttr($value)
    {
        if (empty($value)) {
            return [];
        }
        return json_decode($value, true) ?: [];
    }

    /**
     * 新增打印机
     * 注意：think-orm v4 的 allowField() 只接受数组，不再支持 allowField(true)，
     * 这里按表字段显式过滤后再写入。
     * @param array $data
     * @return bool
     */
    public function add(array $data)
    {
        // printer_config 是 text 字段，表单提交的是数组，需序列化后写入
        $data['printer_config'] = json_encode($data[$data['printer_type']] ?? [], JSON_UNESCAPED_UNICODE);
        $data['wxapp_id']       = self::getWxappId();
        return $this->save(self::filterFields($data)) !== false;
    }

    /**
     * 编辑打印机
     * @param array $data
     * @return bool
     */
    public function edit(array $data)
    {
        if (!empty($data['printer_type'])) {
            $data['printer_config'] = json_encode($data[$data['printer_type']] ?? [], JSON_UNESCAPED_UNICODE);
        }
        return $this->save(self::filterFields($data)) !== false;
    }

    /**
     * 逻辑删除
     * @return bool
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }

    /**
     * 只保留 printer 表实际存在的字段
     * @param array $data
     * @return array
     */
    private static function filterFields(array $data)
    {
        $fields = [
            'printer_name', 'printer_type', 'printer_config',
            'print_times', 'sort', 'is_delete', 'wxapp_id',
        ];
        return array_intersect_key($data, array_flip($fields));
    }
}
