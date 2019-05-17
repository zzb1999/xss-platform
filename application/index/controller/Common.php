<?php

namespace app\index\controller;

use think\Controller;
use think\Request;

class Common extends Controller
{
    protected $beforeActionList = [
        'isLogin'=>['except' => 'doLogin,register,doRegister,code,api,keepsession,authtest'],
        'showInfo' => ['except' => 'doLogin,register,doRegister,code,api,keepsession,authtest']
    ];

    public function isLogin()
    {
        $username = session('username');
        if(!$username){
            $this->error('您还未登录！','/');
        }
    }

    public function showInfo()
    {
        $user_id = session('userid');
        $my_project = model('Project')->where('user_id',$user_id)->order('add_time','desc')->select();
        $my_modules = model('Module')->where('user_id',$user_id)->select();
        if(config('siteconfig.show_usermodule')==1){
            //显示其他用户创建的模块
            $open_modules = model('Module')->where('is_open','1')->order('add_time','desc')->select();
        }else{
            $open_modules = model('Module')->where('is_open','1')->where('user_id',0)->order('add_time','desc')->select();
        }


        $this->assign('my_project',$my_project);
        $this->assign('open_modules',$open_modules);
        $this->assign('my_modules',$my_modules);
    }
}
