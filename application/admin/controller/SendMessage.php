<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Validate;

class SendMessage extends Common
{
    public function index()
    {
        $emails = model('Email')->select();
        $this->assign('emails',$emails);
        $this->assign('title','发信设置');
        return $this->fetch();
    }

    public function addEmail()
    {
        $validate = Validate::make([
            'ehost' => 'require|token|max:100',
            'euser' => 'require|max:255',
            'epass' => 'require|max:255',
        ]);
        if(!$validate->check(input('post.'))){
            $this->error($validate->getError());
        }

        $data = [
            'ehost' => input('post.ehost'),
            'euser' => input('post.euser'),
            'epass' => input('post.epass'),
        ];
        $result = model('Email')->save($data);
        if($result){
            $this->success('添加邮箱成功');
        }else{
            $this->success('添加邮箱失败');
        }
    }

    public function delEmail()
    {
        $validate = Validate::make([
            'id' => 'require|token',
        ]);
        if(!$validate->check(input('get.'))){
            $this->error($validate->getError());
        }

        $id = input('get.id');
        $result = model('Email')->get($id)->delete();
        if($result){
            $this->redirect('admin/SendMessage/index');
        }else{
            $this->error('删除失败');
        }
    }

    public function testSendemail()
    {
        $validate = Validate::make([
            'id' => 'require|token',
            'email' => 'require|email',
        ]);
        if(!$validate->check(input('get.'))){
            return $validate->getError();
        }

        $id = input('get.id');
        $email = model('Email')->where('id',$id)->find();
        if(!$email){
            return '邮箱不存在。';
        }
        $option = [
            'host' => $email['ehost'],
            'sendemail' => $email['euser'],
            'password' => $email['epass'],
            'useremail' => input('get.email'),
            'username' => 'admin',
            'content' => '尊敬的：admin，测试发信成功。',
        ];
        $result = sendEmail($option);
        if($result === true){
            return 1;
        }else{
            return $result;
        }
    }
}
