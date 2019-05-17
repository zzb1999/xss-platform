<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Config extends Controller
{
    public function index()
    {
        $this->assign('config',config('siteconfig.'));
        $this->assign('title','网站配置');
        return $this->fetch();
    }

    public function save()
    {
        $validate = new \app\admin\validate\Config;
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }

        $data['version'] = 'V1.0';

        $data['title'] = addslashes(input('post.title'));
        $data['keywords'] = addslashes(input('post.keywords'));
        $data['description'] = addslashes(input('post.description'));
        $data['notice'] = addslashes(input('post.notice'));
        $data['url'] = addslashes(input('post.url'));
        $data['is_invite'] = addslashes(input('post.is_invite'));
        $data['is_sendemail'] = addslashes(input('post.is_sendemail'));
//        $data['is_message'] = addslashes(input('post.is_message'));
        $data['show_usermodule'] = addslashes(input('post.show_usermodule'));

        $file = '../config/siteconfig.php';
        $str = "<?php\r\nreturn ".var_export($data,true).';';
        if(file_put_contents($file,$str)){
            $this->success('修改配置成功','admin/config/index');
        }else{
            $this->error('修改配置失败');
        }
    }
}
