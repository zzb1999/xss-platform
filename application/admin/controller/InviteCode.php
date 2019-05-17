<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Validate;

class InviteCode extends Common
{
    public function index()
    {
        $code_list = model('InviteCode')->order('is_reg','asc')->order('add_time','desc')->paginate(10);
        $page = $code_list->render();
        $this->assign('code_list',$code_list);
        $this->assign('page',$page);
        $this->assign('title','邀请码管理');
        return $this->fetch();
    }

    public function create(){
        $validate = Validate::make([
            'number|数量' => 'require|between:1,100|token'
        ]);
        if(!$validate->check($this->request->post())){
            $this->error($validate->getError());
        }

        $num = $this->request->post('number');
        $time = time();
        for($i=1;$i<=$num;$i++){
            $data[] = ['invite_key'=>model('InviteCode')->make_code(),'add_time'=>$time];
        }
        $result = model('InviteCode')->saveAll($data);
        if($result){
            $this->success('邀请码生成成功！','admin/InviteCode/index');
        }else{
            $this->error('邀请码生成失败！','admin/InviteCode/index');
        }
    }

    public function delete(){
        $validate = Validate::make([
            'id' => 'require|token'
        ]);
        if(!$validate->check($this->request->get())){
            $this->error($validate->getError());
        }
        $id = input('get.id');
        $result = \app\admin\model\InviteCode::destroy($id);
        if($result){
            $this->redirect('admin/InviteCode/index');
        }else{
            $this->error('邀请码删除失败！');
        }
    }
}
