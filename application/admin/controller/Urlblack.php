<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Validate;

class Urlblack extends Common
{
    public function index()
    {
        $urlblacks = model('Urlblack')->where('user_id',0)->select();
        $this->assign('urlblacks',$urlblacks);
        $this->assign('title','URL黑名单');
        return $this->fetch();
    }

    public function add()
    {
        $validate = Validate::make([
            'url' => 'require|max:100|token',
        ]);
        if(!$validate->check(input('post.'))){
            $this->error($validate->getError());
        }

        $data = [
            'project_id' => 0,
            'user_id' => 0,
            'url' => input('post.url'),
            'add_time' => time(),
        ];
        $result = model('Urlblack')->save($data);
        if($result){
            $this->redirect('admin/Urlblack/index');
        }else{
            $this->error('添加失败');
        }
    }

    public function delurl()
    {
        $validate = Validate::make([
            'id' => 'require|token',
        ]);
        if(!$validate->check(input('get.'))){
            $this->error($validate->getError());
        }
        $id = input('get.id');
        $result = model('Urlblack')->where('id',$id)->delete();
        if($result){
            $this->redirect('admin/Urlblack/index');
        }else{
            $this->error('删除失败');
        }
    }
}
