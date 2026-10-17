<?php

namespace app\common\model\article;

use app\common\model\BaseModel;

/**
 * 文章评论模型
 * Class ArticleComment
 * @package app\common\model\article
 */
class ArticleComment extends BaseModel
{
    protected $name = 'article_comment';
    protected $autoWriteTimestamp = false;
}
