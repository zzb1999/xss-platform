<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Validate;

class ProjectContent extends Common
{
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
        $project_content = model('ProjectContent')->get($id);
        if(!$project_content){
            $this->error('删除的项目内容不存在');
        }
        $project = model('Project')->where('id',$project_content['project_id'])->where('user_id',$user_id)->find();
        if(!$project){
            $this->error('项目不存在或没有权限');
        }

        $result = $project_content->delete();
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    public function delContents()
    {
        $validate = Validate::make([
            'ids'  => 'require|token',
        ]);
        if (!$validate->check(input('post.'))) {
            return $validate->getError();
        }

        $ids = explode('|',input('post.ids'));
        $result = \app\index\model\ProjectContent::destroy($ids);
        if($result){
            return 1;
        }else{
            return '删除失败';
        }
    }
}
