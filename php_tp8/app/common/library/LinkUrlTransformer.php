<?php
declare(strict_types=1);

namespace app\common\library;

/**
 * DIY 装修组件 —— 跳转链接路径转换器
 *
 * 后台 wxapp_page.page_data JSON 里存的跳转链接一律是 **微信小程序路径**
 * （如 pages/index/index、pages/goods/detail?goods_id=10001）。
 * 小程序前端 wx.navigateTo({ url }) 可直接使用；
 * H5 前端不能直接用，需要转换成 H5 风格的 URL（/h5/...html?...）。
 *
 * 本类提供双向转换：
 *   - toH5(string $miniPath):   pages/xxx/index?x=y  → /h5/xxx.html?x=y
 *   - toMini(string $h5Url):    /h5/xxx.html?x=y     → pages/xxx/index?x=y
 *   - transformPageData(array $data, string $target): 遍历整份 pageData JSON 里的
 *                                 所有 linkUrl/link 字段，批量转换
 *
 * 设计要点：
 *   - 纯静态类，无依赖，可在任何层（store/api/h5）调用
 *   - 未识别路径不做转换（原封不动返回），保证安全
 *   - 参数（?goods_id=xx）保持不变
 */
class LinkUrlTransformer
{
    /**
     * 小程序 pages/ 路径 → H5 /h5/*.html
     *
     * 映射规则（覆盖 TP8 已实现的 H5 路由）：
     *
     *   小程序路径                         →  H5 URL
     *   ──────────────────────────────────────────────────────
     *   pages/index/index                  →  /h5/page/index.html
     *   pages/custom/index                 →  /h5/page/index.html  (?page_id 保留)
     *   pages/category/index               →  /h5/category/index.html
     *   pages/category/list                →  /h5/goods/list.html
     *   pages/goods/detail                 →  /h5/goods/detail.html
     *   pages/user/index                   →  /h5/user/index.html
     *   pages/cart/index                   →  /h5/cart/index.html
     *   pages/login/login                  →  /h5/passport/login.html
     *   pages/register/register            →  /h5/passport/register.html
     *   pages/article/index                →  /h5/article/index.html
     *   pages/article/detail/index         →  /h5/article/detail.html
     *   pages/shop/detail/index            →  /h5/shop/detail.html
     *   pages/delivery/index               →  /h5/delivery/index.html
     *   pages/sharing/index/index          →  /h5/sharing/index.html
     *   pages/sharing/goods/index          →  /h5/sharing/goods.html
     *   pages/sharing/active/index         →  /h5/sharing/active.html
     *   pages/bargain/index/index          →  /h5/bargain/index.html
     *   pages/bargain/goods/index          →  /h5/bargain/goods.html
     *   pages/bargain/task/index           →  /h5/bargain/task.html
     *   pages/sharp/index/index            →  /h5/sharp/index.html
     *   pages/sharp/goods/index            →  /h5/sharp/goods.html
     *   pages/user/coupon                  →  /h5/user/coupon.html
     *   pages/coupon/coupon                →  /h5/user/coupon.html
     *   pages/dealer/index/index           →  /h5/dealer/index.html
     *   pages/dealer/apply/apply           →  /h5/dealer/apply.html
     *   pages/dealer/order/order           →  /h5/dealer/order.html
     *   pages/dealer/team/team             →  /h5/dealer/team.html
     *   pages/dealer/withdraw/apply/apply  →  /h5/dealer/withdrawApply.html
     *   pages/dealer/withdraw/list/list    →  /h5/dealer/withdraw.html
     *   pages/dealer/qrcode/qrcode         →  /h5/dealer/qrcode.html
     */
    private const MAP = [
        // [小程序 path 正则] => [H5 URL 模板]
        '~^pages/index/index$~'                  => '/h5/page/index.html',
        '~^pages/custom/index~'                  => '/h5/page/index.html',
        '~^pages/category/index$~'               => '/h5/category/index.html',
        '~^pages/category/list~'                 => '/h5/goods/list.html',
        '~^pages/goods/detail~'                  => '/h5/goods/detail.html',
        '~^pages/user/index$~'                   => '/h5/user/index.html',
        '~^pages/cart/index$~'                   => '/h5/cart/index.html',
        '~^pages/login/login~'                   => '/h5/passport/login.html',
        '~^pages/register/register~'             => '/h5/passport/register.html',
        '~^pages/article/detail~'                => '/h5/article/detail.html',
        '~^pages/article/index~'                 => '/h5/article/index.html',
        '~^pages/shop/detail~'                   => '/h5/shop/detail.html',
        '~^pages/delivery/index~'                => '/h5/delivery/index.html',
        '~^pages/sharing/index~'                 => '/h5/sharing/index.html',
        '~^pages/sharing/goods~'                => '/h5/sharing/goods.html',
        '~^pages/sharing/active~'                => '/h5/sharing/active.html',
        '~^pages/bargain/index~'                 => '/h5/bargain/index.html',
        '~^pages/bargain/goods~'                => '/h5/bargain/goods.html',
        '~^pages/bargain/task~'                 => '/h5/bargain/task.html',
        '~^pages/sharp/index~'                  => '/h5/sharp/index.html',
        '~^pages/sharp/goods~'                  => '/h5/sharp/goods.html',
        '~^pages/user/coupon~'                   => '/h5/user/coupon.html',
        '~^pages/coupon/coupon$~'                => '/h5/user/coupon.html',
        // 分销（dealer）：注意 withdraw/apply 需排在 withdraw/list 之前
        '~^pages/dealer/index~'                  => '/h5/dealer/index.html',
        '~^pages/dealer/apply~'                  => '/h5/dealer/apply.html',
        '~^pages/dealer/order~'                  => '/h5/dealer/order.html',
        '~^pages/dealer/team~'                   => '/h5/dealer/team.html',
        '~^pages/dealer/qrcode~'                 => '/h5/dealer/qrcode.html',
        '~^pages/dealer/withdraw/apply~'         => '/h5/dealer/withdrawApply.html',
        '~^pages/dealer/withdraw/list~'          => '/h5/dealer/withdraw.html',
        '~^pages/dealer/withdraw$~'              => '/h5/dealer/withdraw.html',
    ];

    /**
     * 把一条小程序路径转成 H5 URL
     *
     * @param string $miniPath  例如 "pages/goods/detail?goods_id=10005"
     * @return string           未识别则原样返回
     */
    public static function toH5(string $miniPath): string
    {
        $miniPath = trim($miniPath);
        if ($miniPath === '') return $miniPath;

        // 拆分 path + query（小程序用 ? 带查询参数）
        $qPos = strpos($miniPath, '?');
        $path = $qPos === false ? $miniPath : substr($miniPath, 0, $qPos);
        $query = $qPos === false ? '' : substr($miniPath, $qPos + 1);

        // 匹配映射表
        foreach (self::MAP as $pattern => $h5Tpl) {
            if (preg_match($pattern, $path)) {
                $result = $h5Tpl;
                if ($query !== '') {
                    $result .= (strpos($h5Tpl, '?') === false ? '?' : '&') . $query;
                }
                return $result;
            }
        }

        // 未识别 → 原样返回（H5 前端自己判断要不要处理）
        return $miniPath;
    }

    /**
     * 遍历整份 pageData，把所有 linkUrl/link/jump 相关字段批量转换
     *
     * @param array  $pageData  getPageData() 返回的完整数组
     * @param string $target    'h5' | 'mini'  本次只实现 'h5'
     * @return array
     */
    public static function transformPageData(array $pageData, string $target = 'h5'): array
    {
        if ($target !== 'h5') return $pageData;

        $walk = function (array $arr) use (&$walk): array {
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $arr[$k] = $walk($v);
                } elseif (is_string($v)) {
                    // 需要转换的字段名（linkUrl / link / jump_url / url 等）
                    if (in_array($k, [
                        'link', 'linkUrl', 'link_url', 'jump_url', 'actionUrl',
                        'url', 'redirect', 'redirect_url',
                    ]) && trim($v) !== '' && strpos(trim($v), 'pages/') === 0) {
                        $arr[$k] = self::toH5($v);
                    }
                }
            }
            return $arr;
        };

        return $walk($pageData);
    }

    /**
     * 判断是否是小程序 pages/ 路径
     */
    public static function isMiniPath(string $url): bool
    {
        return strpos(trim($url), 'pages/') === 0;
    }

    /**
     * 判断是否是 H5 /h5/*.html URL
     */
    public static function isH5Url(string $url): bool
    {
        return (bool)preg_match('#^/h5/[a-zA-Z0-9_/-]+\.html#', trim($url));
    }

    /**
     * 获取完整的映射表（供后台 links.php 展示对照用）
     *
     * @return array<int, array{mini:string, h5:string, note:string}>
     */
    public static function getMappingTable(): array
    {
        return [
            ['mini' => 'pages/index/index',               'h5' => '/h5/page/index.html',             'note' => '商城首页'],
            ['mini' => 'pages/custom/index?page_id=xxx',   'h5' => '/h5/page/index.html?page_id=xxx', 'note' => '自定义DIY页面'],
            ['mini' => 'pages/category/index',            'h5' => '/h5/category/index.html',         'note' => '商品分类'],
            ['mini' => 'pages/category/list',              'h5' => '/h5/goods/list.html',             'note' => '商品列表'],
            ['mini' => 'pages/goods/detail?goods_id=xxx',  'h5' => '/h5/goods/detail.html?goods_id=xxx', 'note' => '商品详情'],
            ['mini' => 'pages/user/index',                 'h5' => '/h5/user/index.html',             'note' => '我的（需登录）'],
            ['mini' => 'pages/cart/index',                 'h5' => '/h5/cart/index.html',             'note' => '购物车'],
            ['mini' => 'pages/login/login',                'h5' => '/h5/passport/login.html',         'note' => '登录'],
            ['mini' => 'pages/register/register',         'h5' => '/h5/passport/register.html',      'note' => '注册'],
            ['mini' => 'pages/coupon/coupon',              'h5' => '/h5/user/coupon.html',            'note' => '我的优惠券'],
            ['mini' => 'pages/article/index',              'h5' => '/h5/article/index.html',          'note' => '文章列表'],
            ['mini' => 'pages/article/detail/index?article_id=xxx', 'h5' => '/h5/article/detail.html?article_id=xxx', 'note' => '文章详情'],
            ['mini' => 'pages/shop/detail/index?shop_id=xxx', 'h5' => '/h5/shop/detail.html?shop_id=xxx', 'note' => '门店详情'],
            ['mini' => 'pages/delivery/index',             'h5' => '/h5/delivery/index.html',         'note' => '马来西亚外送'],
            ['mini' => 'pages/sharing/index/index',        'h5' => '/h5/sharing/index.html',          'note' => '拼团首页'],
            ['mini' => 'pages/sharing/goods/index?goods_id=xxx', 'h5' => '/h5/sharing/goods.html?goods_id=xxx', 'note' => '拼团商品'],
            ['mini' => 'pages/sharing/active/index?active_id=xxx', 'h5' => '/h5/sharing/active.html?active_id=xxx', 'note' => '拼单详情'],
            ['mini' => 'pages/bargain/index/index',        'h5' => '/h5/bargain/index.html',          'note' => '砍价首页'],
            ['mini' => 'pages/bargain/goods/index?active_id=xxx', 'h5' => '/h5/bargain/goods.html?active_id=xxx', 'note' => '砍价商品'],
            ['mini' => 'pages/bargain/task/index?task_id=xxx', 'h5' => '/h5/bargain/task.html?task_id=xxx', 'note' => '砍价记录'],
            ['mini' => 'pages/sharp/index/index',          'h5' => '/h5/sharp/index.html',            'note' => '秒杀会场'],
            ['mini' => 'pages/sharp/goods/index?active_time_id=xxx&sharp_goods_id=xxx', 'h5' => '/h5/sharp/goods.html?active_time_id=xxx&sharp_goods_id=xxx', 'note' => '秒杀商品'],
            ['mini' => 'pages/dealer/index/index',           'h5' => '/h5/dealer/index.html',          'note' => '分销中心'],
            ['mini' => 'pages/dealer/apply/apply?referee_id=xxx', 'h5' => '/h5/dealer/apply.html?referee_id=xxx', 'note' => '申请成为分销商'],
            ['mini' => 'pages/dealer/order/order',           'h5' => '/h5/dealer/order.html',          'note' => '分销订单'],
            ['mini' => 'pages/dealer/team/team',             'h5' => '/h5/dealer/team.html',           'note' => '我的团队'],
            ['mini' => 'pages/dealer/withdraw/apply/apply',  'h5' => '/h5/dealer/withdrawApply.html',  'note' => '申请提现'],
            ['mini' => 'pages/dealer/withdraw/list/list',    'h5' => '/h5/dealer/withdraw.html',       'note' => '提现明细'],
            ['mini' => 'pages/dealer/qrcode/qrcode',         'h5' => '/h5/dealer/qrcode.html',         'note' => '推广二维码'],
        ];
    }
}
