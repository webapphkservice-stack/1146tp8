<?php

namespace app\common\model\article;

use app\common\model\BaseModel;

/**
 * 文章点赞模型
 * Class ArticleLike
 * @package app\common\model\article
 */
class ArticleLike extends BaseModel
{
    protected $name = 'article_like';
    protected $autoWriteTimestamp = false;
}
