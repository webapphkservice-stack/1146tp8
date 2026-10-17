<?php

namespace app\index\controller;

/**
 * 根路径 /goods/* 的转发别名。
 * 实际页面在 h5 应用下，这里只做 302 跳转，让不带 /h5 前缀的 URL 也可用。
 */
class Goods
{
    protected function jump($action)
    {
        $query = request()->param();
        unset($query['s'], $query['_'], $query['route']);
        header('Location: /h5/goods/' . $action . '.html?' . http_build_query($query), true, 302);
        exit;
    }

    public function detail() { $this->jump('detail'); }
    public function list()   { $this->jump('list'); }
}