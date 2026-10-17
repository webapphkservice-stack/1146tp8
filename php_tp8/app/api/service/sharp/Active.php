<?php
namespace app\api\service\sharp;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 整点秒杀-活动服务（迁移自 php_my api/service/sharp/Active）
 *
 * 数据模型（yoshop_ 前缀由 config/database.php 的 prefix 提供）：
 *  sharp_active       活动（按天）   active_id / active_date / status / is_delete
 *  sharp_active_time  场次（按小时） active_time_id / active_id / active_time(0-23) / status
 *  sharp_active_goods 活动商品关联   id / active_id / active_time_id / sharp_goods_id / sales_actual
 *  sharp_goods        秒杀商品       sharp_goods_id / goods_id / limit_num / seckill_stock / status
 *  sharp_goods_sku    秒杀商品SKU    goods_sku_id / sharp_goods_id / spec_sku_id / seckill_price / seckill_stock
 *
 * 时间语义（与 php_my 完全一致）：
 *   startTime = active_date + active_time × 3600，每场持续 1 小时（DURATION）。
 *
 * 状态枚举（对齐 php_my common/enum/sharp/{ActiveStatus,GoodsStatus}）：
 *   ActiveStatus：BEGIN=10 / SOON=20 / NOTICE=30
 *   GoodsStatus ：STATE_BEGIN=10 / STATE_SOON=20 / STATE_END=30
 *
 * 采用与既有批次一致的「Db 直查」风格，规避 think-orm v4 的模型/获取器差异。
 */
class Active
{
    // 活动状态
    const ACTIVE_STATE_BEGIN  = 10;
    const ACTIVE_STATE_SOON   = 20;
    const ACTIVE_STATE_NOTICE = 30;

    // 活动商品状态
    const STATE_BEGIN = 10;
    const STATE_SOON  = 20;
    const STATE_END   = 30;

    /** 每场持续时长（秒） */
    const DURATION = 3600;

    /** @var int wxapp_id */
    private $wxappId;

    /** @var string 错误信息 */
    private $error = '';

    public function __construct($wxappId)
    {
        $this->wxappId = (int)$wxappId;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * 秒杀会场首页：tabbar（场次导航）+ 首个场次的商品
     * @return array ['tabbar' => [], 'goodsList' => []]
     */
    public function getHallIndex()
    {
        $tabbar = $this->getActiveTabbar();
        if (empty($tabbar)) {
            return ['tabbar' => [], 'goodsList' => []];
        }
        $goodsList = $this->getGoodsListByActiveTimeId($tabbar[0]['active_time_id']);
        return ['tabbar' => $tabbar, 'goodsList' => $goodsList];
    }

    /**
     * 按场次取秒杀商品列表
     * RN SharpScreen 消费字段：sharp_goods_id / goods_name / image / seckill_price /
     *                          original_price / sales_actual / active_time_id
     */
    public function getGoodsListByActiveTimeId($activeTimeId)
    {
        $rows = Db::name('sharp_active_goods')
            ->alias('ag')
            ->join('sharp_goods sg', 'sg.sharp_goods_id = ag.sharp_goods_id')
            ->where('ag.active_time_id', '=', (int)$activeTimeId)
            ->where('ag.wxapp_id', '=', $this->wxappId)
            ->where('sg.status', 'in', [1, 10])
            ->where('sg.is_delete', '=', 0)
            ->field('ag.sharp_goods_id,ag.active_time_id,ag.sales_actual,'
                . 'sg.goods_id,sg.limit_num,sg.seckill_stock,sg.total_sales,sg.sort')
            ->order('sg.sort', 'asc')
            ->select()->toArray();

        $list = [];
        foreach ($rows as $row) {
            $item = $this->buildGoodsItem($row);
            if ($item) {
                $list[] = $item;
            }
        }
        return $list;
    }

    /**
     * 秒杀商品详情（活动信息 + 商品信息 + SKU）
     * @return array|false ['active' => [], 'goods' => []]
     */
    public function getActiveGoodsDetail($activeTimeId, $sharpGoodsId)
    {
        $active = $this->getGoodsActive($activeTimeId, $sharpGoodsId);
        if (empty($active)) {
            return false;
        }
        $goods = $this->getGoodsDetail($sharpGoodsId);
        if (empty($goods)) {
            return false;
        }
        // 活动内销量与抢购进度
        $goods['sales_actual'] = $active['sales_actual'];
        $goods['progress']     = $this->getProgress($active['sales_actual'], $goods['seckill_stock']);
        return ['active' => $active, 'goods' => $goods];
    }

    // ==================== 场次导航（tabbar） ====================

    /**
     * 秒杀首页顶部场次导航：进行中 + 即将开始 + 下一场预告
     */
    private function getActiveTabbar()
    {
        $todyActive = $this->getTodayActive();
        $data = [];
        if (!empty($todyActive)) {
            $data[] = $this->getBeginActive($todyActive);
            $data   = array_merge($data, $this->getSoonActive($todyActive));
        }
        $data[] = $this->getNoticeActive();
        return array_values(array_filter($data));
    }

    /** 当天活动（active_date = 今天零点） */
    private function getTodayActive()
    {
        return Db::name('sharp_active')
            ->where('active_date', '=', strtotime(date('Y-m-d')))
            ->where('status', '=', 10)
            ->where('is_delete', '=', 0)
            ->where('wxapp_id', '=', $this->wxappId)
            ->find();
    }

    /** 下一场活动（active_date > 今天零点） */
    private function getNextActive()
    {
        return Db::name('sharp_active')
            ->where('active_date', '>', strtotime(date('Y-m-d')))
            ->where('status', '=', 10)
            ->where('is_delete', '=', 0)
            ->where('wxapp_id', '=', $this->wxappId)
            ->order('active_date', 'asc')
            ->find();
    }

    /** 当前进行中的场次（active_time = 当前小时） */
    private function getBeginActive($todyActive)
    {
        $model = Db::name('sharp_active_time')
            ->where('active_id', '=', $todyActive['active_id'])
            ->where('active_time', '=', (int)date('H'))
            ->where('status', '=', 10)
            ->where('wxapp_id', '=', $this->wxappId)
            ->find();
        if (empty($model)) {
            return [];
        }
        $startTime = $todyActive['active_date'] + ($model['active_time'] * 3600);
        $endTime   = $startTime + self::DURATION;
        return [
            'active_id'          => $todyActive['active_id'],
            'active_time_id'     => $model['active_time_id'],
            'active_time'        => $this->formatActiveTime($model['active_time']),
            'start_time'         => $this->onFormatTime($startTime),
            'end_time'           => $this->onFormatTime($endTime),
            'count_down_time'    => $this->onFormatTime($endTime),
            'status'             => self::ACTIVE_STATE_BEGIN,
            'status_text'        => '已开抢',
            'status_text2'       => '正在疯抢',
            'sharp_modular_text' => '正在疯抢',
        ];
    }

    /** 今天剩余场次（active_time > 当前小时） */
    private function getSoonActive($todyActive)
    {
        $list = Db::name('sharp_active_time')
            ->where('active_id', '=', $todyActive['active_id'])
            ->where('active_time', '>', (int)date('H'))
            ->where('status', '=', 10)
            ->where('wxapp_id', '=', $this->wxappId)
            ->order('active_time', 'asc')
            ->select()->toArray();
        if (empty($list)) {
            return [];
        }
        $data = [];
        foreach ($list as $item) {
            $startTime = $todyActive['active_date'] + ($item['active_time'] * 3600);
            $endTime   = $startTime + self::DURATION;
            $data[] = [
                'active_id'          => $todyActive['active_id'],
                'active_time_id'     => $item['active_time_id'],
                'active_time'        => $this->formatActiveTime($item['active_time']),
                'start_time'         => $this->onFormatTime($startTime),
                'end_time'           => $this->onFormatTime($endTime),
                'count_down_time'    => $this->onFormatTime($startTime),
                'status'             => self::ACTIVE_STATE_SOON,
                'status_text'        => '即将开抢',
                'status_text2'       => '即将开抢',
                'sharp_modular_text' => $item['active_time'] . ' 场预告',
            ];
        }
        return $data;
    }

    /** 预告：下一场活动的最早场次 */
    private function getNoticeActive()
    {
        $nextActive = $this->getNextActive();
        if (empty($nextActive)) {
            return [];
        }
        $model = Db::name('sharp_active_time')
            ->where('active_id', '=', $nextActive['active_id'])
            ->where('status', '=', 10)
            ->where('wxapp_id', '=', $this->wxappId)
            ->order('active_time', 'asc')
            ->find();
        if (empty($model)) {
            return [];
        }
        $startTime = $nextActive['active_date'] + ($model['active_time'] * 3600);
        $endTime   = $startTime + self::DURATION;
        return [
            'active_id'          => $nextActive['active_id'],
            'active_time_id'     => $model['active_time_id'],
            'active_time'        => $this->formatActiveTime($model['active_time']),
            'start_time'         => $this->onFormatTime($startTime),
            'end_time'           => $this->onFormatTime($endTime),
            'count_down_time'    => $this->onFormatTime($startTime),
            'status'             => self::ACTIVE_STATE_NOTICE,
            'status_text'        => '预告',
            'status_text2'       => $this->onFormatTime($startTime) . ' 开始',
            'sharp_modular_text' => $this->onFormatTime($startTime) . ' 开始',
        ];
    }

    // ==================== 商品 ====================

    /**
     * 组装列表项：补齐主商品名称 / 主图 / 秒杀价 / 原价
     */
    private function buildGoodsItem($row)
    {
        $goodsId = (int)$row['goods_id'];
        $goods   = Db::name('goods')
            ->where('goods_id', '=', $goodsId)
            ->where('goods_status', '=', 10)
            ->where('is_delete', '=', 0)
            ->field('goods_id,goods_name')
            ->find();
        if (empty($goods)) {
            return null;
        }
        // 秒杀价取该商品最低的 SKU 秒杀价（与商品列表 goods_min_price 语义一致）
        $seckillPrice = Db::name('sharp_goods_sku')
            ->where('sharp_goods_id', '=', (int)$row['sharp_goods_id'])
            ->order('seckill_price', 'asc')
            ->value('seckill_price');
        // 原价取主商品 SKU 的最低价
        $originalPrice = Db::name('goods_sku')
            ->where('goods_id', '=', $goodsId)
            ->order('goods_price', 'asc')
            ->value('goods_price');

        return [
            'sharp_goods_id' => (int)$row['sharp_goods_id'],
            'active_time_id' => (int)$row['active_time_id'],
            'goods_id'       => $goodsId,
            'goods_name'     => $goods['goods_name'],
            'image'          => $this->getGoodsImage($goodsId),
            'seckill_price'  => $seckillPrice === null ? 0 : (float)$seckillPrice,
            'original_price' => $originalPrice === null ? 0 : (float)$originalPrice,
            'sales_actual'   => (int)$row['sales_actual'],
            'seckill_stock'  => (int)$row['seckill_stock'],
            'limit_num'      => (int)$row['limit_num'],
            'total_sales'    => (int)$row['total_sales'],
        ];
    }

    /**
     * 秒杀商品详情（秒杀商品 + 主商品 + SKU 列表）
     */
    private function getGoodsDetail($sharpGoodsId)
    {
        $sharpGoods = Db::name('sharp_goods')
            ->where('sharp_goods_id', '=', (int)$sharpGoodsId)
            ->where('is_delete', '=', 0)
            ->find();
        if (empty($sharpGoods)) {
            $this->error = '秒杀商品信息不存在';
            return [];
        }
        if (!$sharpGoods['status']) {
            $this->error = '很抱歉，秒杀商品不存在或已下架';
            return [];
        }
        $goodsId = (int)$sharpGoods['goods_id'];
        $goods   = Db::name('goods')
            ->where('goods_id', '=', $goodsId)
            ->where('goods_status', '=', 10)
            ->where('is_delete', '=', 0)
            ->field('goods_id,goods_name,goods_type,spec_type,deduct_stock_type,content,sales_initial,sales_actual')
            ->find();
        if (empty($goods)) {
            $this->error = '很抱歉，商品不存在或已下架';
            return [];
        }

        // 秒杀 SKU：以 spec_sku_id 为键合并到主商品 SKU 上
        $sharpSku = Db::name('sharp_goods_sku')
            ->where('sharp_goods_id', '=', (int)$sharpGoodsId)
            ->select()->toArray();
        $sharpSkuMap = [];
        foreach ($sharpSku as $sku) {
            $sharpSkuMap[(string)$sku['spec_sku_id']] = $sku;
        }
        $skuList = Db::name('goods_sku')
            ->where('goods_id', '=', $goodsId)
            ->field('goods_sku_id,spec_sku_id,goods_no,goods_price,line_price,goods_weight,stock_num,image_id')
            ->select()->toArray();
        foreach ($skuList as &$sku) {
            $key = (string)$sku['spec_sku_id'];
            $sku['original_price'] = (float)$sku['goods_price'];
            if (isset($sharpSkuMap[$key])) {
                $sku['seckill_price'] = (float)$sharpSkuMap[$key]['seckill_price'];
                $sku['seckill_stock'] = (int)$sharpSkuMap[$key]['seckill_stock'];
            } else {
                $sku['seckill_price'] = (float)$sku['goods_price'];
                $sku['seckill_stock'] = 0;
            }
        }
        unset($sku);

        // 商品轮播图
        $images = Db::name('goods_image')
            ->where('goods_id', '=', $goodsId)
            ->order('id', 'asc')
            ->column('image_id');

        return array_merge($goods, [
            'sharp_goods_id'    => (int)$sharpGoodsId,
            'deduct_stock_type' => (int)$sharpGoods['deduct_stock_type'],
            'limit_num'         => (int)$sharpGoods['limit_num'],
            'seckill_stock'     => (int)$sharpGoods['seckill_stock'],
            'total_sales'       => (int)$sharpGoods['total_sales'],
            'image'             => $this->getGoodsImage($goodsId),
            'images'            => array_map(function ($id) {
                return BaseModel::getFileUrl((int)$id);
            }, $images),
            'sku'               => $skuList,
        ]);
    }

    /**
     * 商品所在活动信息（含倒计时与状态）
     */
    private function getGoodsActive($activeTimeId, $sharpGoodsId)
    {
        $row = Db::name('sharp_active_goods')
            ->where('active_time_id', '=', (int)$activeTimeId)
            ->where('sharp_goods_id', '=', (int)$sharpGoodsId)
            ->where('wxapp_id', '=', $this->wxappId)
            ->find();
        if (empty($row)) {
            $this->error = '很抱歉，该活动不存在或已结束';
            return [];
        }
        $active = Db::name('sharp_active')
            ->where('active_id', '=', (int)$row['active_id'])
            ->where('status', '=', 10)
            ->where('is_delete', '=', 0)
            ->find();
        $activeTime = Db::name('sharp_active_time')
            ->where('active_time_id', '=', (int)$row['active_time_id'])
            ->where('status', '=', 10)
            ->find();
        if (empty($active) || empty($activeTime)) {
            $this->error = '很抱歉，该活动不存在或已结束';
            return [];
        }

        $startTime    = $active['active_date'] + ($activeTime['active_time'] * 3600);
        $endTime      = $startTime + self::DURATION;
        $activeStatus = $this->getGoodsStatus($startTime, $endTime);

        return [
            'active_id'       => (int)$active['active_id'],
            'active_time_id'  => (int)$activeTime['active_time_id'],
            'active_time'     => $this->formatActiveTime($activeTime['active_time']),
            'sales_actual'    => (int)$row['sales_actual'],
            'start_time'      => $this->onFormatTime($startTime),
            'end_time'        => $this->onFormatTime($endTime),
            'active_status'   => $activeStatus,
            'count_down_time' => $activeStatus === self::STATE_BEGIN
                ? $this->onFormatTime($endTime)
                : ($activeStatus === self::STATE_SOON ? $this->onFormatTime($startTime) : false),
            'wxapp_id'        => (int)$row['wxapp_id'],
        ];
    }

    /** 商品在活动中的状态：未开始 / 进行中 / 已结束 */
    private function getGoodsStatus($startTime, $endTime)
    {
        $now = time();
        if ($now < $startTime) {
            return self::STATE_SOON;
        }
        if ($now < $endTime) {
            return self::STATE_BEGIN;
        }
        return self::STATE_END;
    }

    /** 抢购进度百分比（0-100，用于前端进度条） */
    private function getProgress($salesActual, $seckillStock)
    {
        $total = (int)$salesActual + (int)$seckillStock;
        if ($total <= 0) {
            return 0;
        }
        return (int)min(100, round((int)$salesActual / $total * 100));
    }

    /** 商品主图 URL */
    private function getGoodsImage($goodsId)
    {
        $imageId = (int)Db::name('goods_image')
            ->where('goods_id', '=', $goodsId)
            ->order('id', 'asc')
            ->value('image_id');
        return BaseModel::getFileUrl($imageId);
    }

    /** 场次时间显示：8 -> "08:00" */
    private function formatActiveTime($hour)
    {
        return str_pad((string)(int)$hour, 2, '0', STR_PAD_LEFT) . ':00';
    }

    private function onFormatTime($timeStamp)
    {
        return date('Y-m-d H:i', $timeStamp);
    }
}
