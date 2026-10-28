<?php

namespace app\index\controller;

/**
 * PC 登录页。
 */
class Passport
{
    public function login()
    {
        return view('passport/login', ['active' => 'user']);
    }
}