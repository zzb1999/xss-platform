<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Validate;

class Module extends Common
{
    public function index()
    {
        $module_list = model('Module')->order('add_time','desc')->paginate(10);

        $this->assign('module_list',$module_list);
        $this->assign('title','模块管理');
        return $this->fetch();
    }

    public function add()
    {
        $this->assign('title','添加模块');
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
        $data['user_id'] = 0;
        $data['user_name'] = 'admin';
        $data['add_time'] = time();
        $data['keys'] = input('?post.keys')?json_encode(input('post.keys')):json_encode([]);
        $data['setkeys'] = input('?post.setkeys')?json_encode(input('post.setkeys')):json_encode([]);

        $result = model('Module')->save($data);
        if($result){
            $this->success('添加模块成功！','admin/module/index');
        }else{
            $this->error('添加模块失败！','admin/module/index');
        }
    }

    public function edit()
    {
        $id = input('param.id');
        $module = model('Module')->where('id',$id)->find();
        $this->assign('module',$module);
        $this->assign('title','配置模块');
        return $this->fetch();
    }

    public function update()
    {
        $validate = new \app\admin\validate\Module();

        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }
        $id = input('post.id');
        $data['title'] = input('post.title');
        $data['description'] = input('post.description');
        $data['code'] = input('post.code');
        $data['is_open'] = input('post.is_open');
        $data['keys'] = input('?post.keys')?json_encode(input('post.keys')):json_encode([]);
        $data['setkeys'] = input('?post.setkeys')?json_encode(input('post.setkeys')):json_encode([]);

        $module = model('Module')->get($id);
        $result = $module->force()->save($data);
        if($result){
            $this->success('配置模块成功！','admin/module/index');
        }else{
            $this->error('配置模块失败！','admin/module/index');
        }
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
        $module = model('Module')->get($id);

        $result = $module->delete();
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}
