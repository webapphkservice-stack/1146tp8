<?php

namespace app\common\model;

/**
 * 微信小程序分类页模板
 * Class WxappCategory
 * @package app\common\model
 */
class WxappCategory extends BaseModel
{
    protected $name = 'wxapp_category';

    /**
     * 分类模板详情
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail()
    {
        $model = new static;
        $data = $model->where('wxapp_id', self::getWxappId())->find();
        if (empty($data)) {
            // 无记录时返回带默认值的模型实例，供视图渲染与保存复用
            $data = $model;
            $data['category_style'] = 10;
            $data['share_title'] = '';
            $data['wxapp_id'] = self::getWxappId();
        }
        return $data;
    }

}
