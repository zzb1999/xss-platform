<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use function Sodium\increment;
use think\Controller;
use think\Request;
use think\Validate;

class Index extends Common
{
    public function index()
    {
        $admin = model('Admin')->where('username','admin')->find();
        $user_num = model('User')->count();
        $projectcontent_num = model('ProjectContent')->count();
        $this->assign('admin',$admin);
        $this->assign('user_num',$user_num);
        $this->assign('projectcontent_num',$projectcontent_num);
        return $this->fetch();
    }

    public function login()
    {
        $adminuser = session('adminuser');
        if($adminuser == 'admin'){
            $this->redirect('admin/index/index');
        }
        return $this->fetch();
    }

    public function login_check()
    {
        $validate = Validate::make([
            'username|用户名'  => 'require',
            'password|密码' => 'require',
            'captcha|验证码' => 'require|captcha',
        ]);
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $result = Admin::where('username',$username)->where('password',md5($password))->find();
        if($result){
            cookie('adminuser','admin');
            session('adminuser','admin');
            session('admin_id',$result['id']);
            $user = model('Admin')->get($result['id']);
            $user->login_time = time();
            $user->last_ip = get_client_ip();
            $user->save();
            $this->success('登陆成功','admin/index/index');
        }else{
            $this->error('账号或密码错误，请重试','admin/index/login');
        }
    }

    public function logout()
    {
        session('adminuser',null);
        $this->success('退出成功','admin/index/login');
    }

    public function editPwd()
    {
        $this->assign('title','修改密码');
        return $this->fetch();
    }

    public function savePwd()
    {
        $validate = Validate::make([
            'oldpass|原密码' => 'require|token',
            'password|新密码' => 'require|max:30|min:5',
            'password2|确认密码' => 'require|confirm:password',
        ]);
        if(!$validate->check(input('post.'))){
            $this->error($validate->getError());
        }

        $oldpass = md5(input('post.oldpass'));
        $password = md5(input('post.password'));
        $admin = model('Admin')->where('password',$oldpass)->find();
        if(!$admin){
            $this->error('原密码不正确！');
        }
        $result = $admin->force()->save(['password'=>$password]);
        if($result){
            $this->success('修改密码成功');
        }else{
            $this->error('修改密码失败');
        }
    }
}
