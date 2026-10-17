<?php
namespace app\common\model;

use think\Model;
use think\facade\Db;

/**
 * TP8 移植要点（对比 TP5 BaseModel）：
 * 1. TP5 的「全局作用域」写法 protected function base($query) 在 think-orm v4 已不存在。
 *    改为：声明 protected $globalScope = ['wxappId']; 并定义 public function scopeWxappId($query)。
 * 2. TP5 的 init() 静态钩子在 TP8 中为 protected static function init()（仅类级初始化一次），
 *    运行时注入 wxapp_id 仍用 setApiWxappId() 手动设置。
 */
class BaseModel extends Model
{
    // think-orm v4 全局作用域：数组元素对应 scopeXxx 方法名
    protected $globalScope = ['wxappId'];

    protected static $wxapp_id;

    /** @var string 业务错误信息（供控制器 $model->error / getError() 读取） */
    protected $error = '';

    /**
     * 全局作用域：自动拼接 wxapp_id 过滤（等价于 TP5 的 base()）
     */
    public function scopeWxappId($query)
    {
        if (static::$wxapp_id) {
            $query->where('wxapp_id', static::$wxapp_id);
        }
    }

    public static function setApiWxappId($wxappId)
    {
        static::$wxapp_id = $wxappId;
    }

    public static function getWxappId()
    {
        return static::$wxapp_id;
    }

    public function setError($msg = '')
    {
        $this->error = $msg;
        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * 基础查询（别名 + 字段 + join），移植自 TP5 BaseModel::setBaseQuery
     * @param string $alias
     * @param array  $join  [ [joinTable, joinField, thisField?] ]
     * @return \think\db\Query
     */
    public function setBaseQuery($alias = '', $join = [])
    {
        $aliasValue = $alias ?: $this->alias;
        $model = $this->alias($aliasValue)->field("{$aliasValue}.*");
        foreach ($join as $item) {
            $model->join($item[0], "{$item[0]}.{$item[1]}={$aliasValue}."
                . (isset($item[2]) ? $item[2] : $item[1]));
        }
        return $model;
    }

    /**
     * 批量更新（支持每条带 where），移植自 TP5 BaseModel::updateAll
     * @param array $data [ [ 'data' => [], 'where' => [] ], ... ]
     * @return \think\Collection
     */
    public function updateAll($data)
    {
        return $this->transaction(function () use ($data) {
            $result = [];
            foreach ($data as $key => $item) {
                $result[$key] = static::update($item['data'], $item['where']);
            }
            return collect($result);
        });
    }

    /**
     * 图片 file_id -> 完整 URL。
     * 规则（与 UploadFile::getFilePathAttr 对齐）：
     *   - file_name 已是 http(s):// 开头 → 直接返回
     *   - storage=local → /uploads/ 前缀
     *   - 其他 → 走 file_url 字段
     * @param int|string $fileId
     * @return string
     */
    public static function getFileUrl($fileId)
    {
        if (empty($fileId)) {
            return '';
        }
        $row = Db::name('upload_file')->where('file_id', $fileId)->find();
        if (empty($row)) {
            return '';
        }
        $fileName = $row['file_name'];
        if (empty($fileName)) {
            return '';
        }
        // 完整 URL 直接返回
        if (stripos($fileName, 'http://') === 0 || stripos($fileName, 'https://') === 0) {
            return $fileName;
        }
        if (($row['storage'] ?? 'local') === 'local') {
            return rtrim(base_url(), '/') . '/uploads/' . ltrim($fileName, '/');
        }
        return rtrim($row['file_url'] ?? '', '/') . '/' . ltrim($fileName, '/');
    }
}
