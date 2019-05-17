<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Validate;

class User extends Common
{
    public function index()
    {
        $users = model('User')->order('reg_time','desc')->paginate(10);
        $this->assign('users',$users);
        $this->assign('title','用户管理');
        return $this->fetch();
    }

    public function edit()
    {
        $user_id = input('param.id');
        $user = model('User')->where('id',$user_id)->find();
        if(!$user){
            $this->error('用户不存在');
        }

        $this->assign('user',$user);
        $this->assign('title','编辑用户');
        return $this->fetch();
    }

    public function save()
    {
        $validate = Validate::make([
            'username|用户名'  => 'require|alphaDash|max:20|min:4|token',
            'email|邮箱' => 'require|email',
            'phone|手机'=>'require|mobile',
            'is_prohibit|账号状态' => 'require|in:0,1',
        ]);
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }

        $user_id = input('post.id');
        $data['username'] = input('post.username');
        $data['email'] = input('post.email');
        $data['phone'] = input('post.phone');
        $data['is_prohibit'] = input('post.is_prohibit');

        $result = model('User')->save($data,['id'=>$user_id]);
        if($result){
            $this->success('编辑信息成功');
        }else{
            $this->error('编辑信息失败');
        }
    }

    public function resetpass()
    {
        $validate = Validate::make([
            'id'  => 'require|token',
        ]);
        if (!$validate->check($this->request->get())) {
            $this->error($validate->getError());
        }

        $id = input('get.id');
        $data = ['password'=>md5('123456')];
        $result = model('User')->save($data,['id'=>$id]);
        if($result){
            $this->success('重置用户密码成功');
        }else{
            $this->error('重置用户密码失败');
        }
    }

    public function delete()
    {
        $validate = Validate::make([
            'id'  => 'require|token',
        ]);
        if (!$validate->check($this->request->get())) {
            $this->error($validate->getError());
        }

        $id = input('get.id');
        $result = model('User')->where('id',$id)->delete();
        if($result){
            $this->success('删除用户成功');
        }else{
            $this->error('删除用户失败');
        }
    }
}
