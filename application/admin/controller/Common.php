<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Common extends Controller
{
    protected $beforeActionList = [
        'isLogin'=>['except' => 'login,login_check'],

    ];

    public function isLogin()
    {
        $username = session('adminuser');
        if(!$username=='admin'){
            $this->error('请先登陆','admin/index/login');
        }
    }
}
