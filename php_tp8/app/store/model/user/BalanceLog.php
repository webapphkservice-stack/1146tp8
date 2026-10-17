<?php
namespace app\store\model\user;

use app\common\model\BaseModel;
use think\facade\Db;
use think\Collection;

/**
 * 用户余额变动明细（服务端渲染版，移植自 TP5 store/model/user/BalanceLog.php）
 */
class BalanceLog extends BaseModel
{
    protected $name = 'user_balance_log';
    protected $pk = 'log_id';
    protected $updateTime = false;

    /** 变动场景（对齐 TP5 common\enum\user\balanceLog\Scene） */
    protected static $sceneText = [
        10 => '用户充值',
        20 => '用户消费',
        30 => '管理员操作',
        40 => '订单退款',
        50 => '开通VIP',
    ];

    /**
     * 余额明细列表
     * @param array $query
     * @return \think\Paginator
     */
    public function getList($query = [])
    {
        $q = Db::name('user_balance_log')->alias('m')
            ->where('m.wxapp_id', self::getWxappId());
        if (!empty($query['search'])) {
            $q->whereLike('m.describe', '%' . trim($query['search']) . '%');
        }
        if (isset($query['scene']) && is_numeric($query['scene']) && (int)$query['scene'] > 0) {
            $q->where('m.scene', (int)$query['scene']);
        }
        if (!empty($query['user_id'])) {
            $q->where('m.user_id', (int)$query['user_id']);
        }
        $list = $q->order('m.create_time', 'desc')
            ->paginate(15, false, ['query' => request()->param()]);
        $items = $list->items();
        foreach ($items as &$item) {
            $item['scene'] = [
                'text'  => self::$sceneText[$item['scene']] ?? '未知',
                'value' => (int)$item['scene'],
            ];
            $item['user'] = Db::name('user')->where('user_id', $item['user_id'])
                ->field('user_id, nickName, avatarUrl')->find()
                ?: ['user_id' => $item['user_id'], 'nickName' => '', 'avatarUrl' => ''];
        }
        $list->setCollection(Collection::make($items));
        return $list;
    }

    /**
     * 属性集（供筛选下拉）
     * 视图按 foreach($attributes['scene'] as $attr) 使用 $attr['value']/$attr['name']
     * @return array
     */
    public static function getAttributes()
    {
        $scene = [];
        foreach (self::$sceneText as $value => $name) {
            $scene[$value] = ['value' => $value, 'name' => $name];
        }
        return ['scene' => $scene];
    }
}
