<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Validate;

class Module extends Common
{
    public function index()
    {
        $user_id = session('userid');
        $myModules = model('Module')->where('user_id',$user_id)->paginate(10);

        $this->assign('myModules',$myModules);
        $this->assign('title','我的模块'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function create()
    {
        $this->assign('title','创建模块'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function save()
    {
        $validate = new \app\admin\validate\Module();

        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }
        $data['title'] = input('post.title');
        $data['description'] = input('post.description');
        $data['code'] = input('post.code');
        $data['is_open'] = input('post.is_open');
        $data['user_id'] = session('userid');
        $data['user_name'] = session('username');
        $data['add_time'] = time();
        $data['keys'] = input('?post.keys')?json_encode(input('post.keys')):json_encode([]);
        $data['setkeys'] = input('?post.setkeys')?json_encode(input('post.setkeys')):json_encode([]);

        $result = model('Module')->save($data);
        if($result){
            $this->success('创建模块成功！','index/module/index');
        }else{
            $this->error('创建模块失败！');
        }
    }

    public function edit()
    {
        $id = input('param.id');
        $user_id = session('userid');
        $module = model('Module')->where('id',$id)->where('user_id',$user_id)->find();
        if(!$module){
            $this->error('模块不存在或没有权限','index/module/index');
        }

        $this->assign('module',$module);
        $this->assign('title','编辑模块'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function update()
    {
        $validate = new \app\admin\validate\Module();

        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }
        $id = input('post.id');
        $user_id = session('userid');
        $data['title'] = input('post.title');
        $data['description'] = input('post.description');
        $data['code'] = input('post.code');
        $data['is_open'] = input('post.is_open');
        $data['keys'] = input('?post.keys')?json_encode(input('post.keys')):json_encode([]);
        $data['setkeys'] = input('?post.setkeys')?json_encode(input('post.setkeys')):json_encode([]);

        $module = model('Module')->get(['id'=>$id,'user_id'=>$user_id]);
        if(!$module){
            $this->error('模块不存在或没有权限');
        }

        $result = $module->force()->save($data);
        if($result){
            $this->success('编辑模块成功！','index/module/index');
        }else{
            $this->error('编辑模块失败！','index/module/index');
        }
    }

    public function open()
    {
        $this->assign('title','公共模块'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function view()
    {
        $id = input('param.id');
        $module = model('Module')->where('id',$id)->where('is_open','1')->find();
        if(!$module){
            $this->error('模块不存在或没有权限','index/module/index');
        }

        $this->assign('module',$module);
        $this->assign('title','查看模块'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function delete()
    {
        $validate = Validate::make([
            'id'  => 'require|token',
        ]);
        if (!$validate->check(input('get.'))) {
            $this->error($validate->getError());
        }

        $id = input('get.id');
        $user_id = session('userid');
        $module = model('Module')->get(['id'=>$id,'user_id'=>$user_id]);
        if(!$module){
            $this->error('模块不存在或没有权限');
        }

        $result = $module->delete();
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}
