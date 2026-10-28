<?php

namespace app\index\controller;

/**
 * PC 购物车。
 */
class Cart
{
    public function index()
    {
        return view('cart/index', ['active' => 'cart']);
    }
}
