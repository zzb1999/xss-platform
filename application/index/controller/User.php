<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Validate;

class User extends Common
{
    public function index()
    {
        $user_id = session('userid');
        $myProject = model('Project')->where('user_id',$user_id)->order('add_time','desc')->paginate(10);
        foreach ($myProject as $k=>$v){
            $myProject[$k]['count'] = model('ProjectContent')->where('project_id',$v['id'])->count();
        }

        $this->assign('myProject',$myProject);
        $this->assign('title','用户中心'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function doLogin()
    {
        $validate = Validate::make([
            'username|用户名'  => 'require',
            'password|密码' => 'require',
            'captcha|验证码' => 'require|captcha',
        ]);
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }

        $username = input('post.username');
        $password = md5(input('post.password'));
        $result = model('User')->where('username|email',$username)->where('password',$password)->find();
        if($result){
            if($result['is_prohibit']==1){
                $this->error('您的账号已被禁用，请联系网站管理员！');
            }
            cookie('username',$result['username']);
            session('username',$result['username']);
            session('userid',$result['id']);
            $result->save(['login_time'=>time()]);
            $this->success('登陆成功','index/user/index','',1);
        }else{
            $this->error('用户名或密码错误！有问题请联系网站管理员！');
        }
    }

    public function logout()
    {
        session('username',null);
        session('userid',null);
        $this->success('退出成功','index/index/index','',1);
    }

    public function register()
    {
        $this->assign('title','注册'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function doRegister()
    {
        $validate = new \app\index\validate\User;
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }

        if(config('siteconfig.is_invite') == 1){
            //开启了邀请码注册
            $invite_key = input('post.invitecode');
            $inviteCode = model('InviteCode')->where('invite_key',$invite_key)->where('is_reg',0)->find();
            if(!$inviteCode){
                $this->error('邀请码不存在或已被注册！');
            }
        }

        $data['username'] = input('post.username');
        $data['email'] = input('post.email');
        $data['password'] = md5(input('post.password'));
        $data['reg_time'] = time();

        $result = model('User')->getByUsernameEmail($data['username'],$data['email']);
        if($result){
            $this->error('用户名或邮箱已注册');
        }

        $user = new \app\index\model\User();
        $result = $user->save($data);
        if($result){
            if(isset($inviteCode)){
                $inviteCode->save([
                    'is_reg' => 1,
                    'reg_userid' => $user->id,
                    'reg_name' => $data['username'],
                    'reg_time' => time(),
                ]);
            }
            $this->success('注册成功','index/index/index');
        }else{
            $this->error('注册失败');
        }
    }

    public function setting()
    {
        $user_id = session('userid');
        $user = model('User')->where('id',$user_id)->find();
        $emails = model('Email')->select();

        $this->assign('user',$user);
        $this->assign('emails',$emails);
        $this->assign('title','个人设置'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function saveset()
    {
        $validate = Validate::make([
            'email|邮箱'  => 'require|email|token',
            'phone|手机' => 'require|mobile',
            'sendemail|邮件提醒' => 'require|in:0,1',
            'sendmessage|短信提醒' => 'require|in:0,1',
        ]);
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }


        $user_id = session('userid');
        $data['email'] = input('post.email');
        $result = model('User')->getByEmail($user_id,$data['email']);
        if($result){
            $this->error('邮箱已存在');
        }
        $data['phone'] = input('post.phone');
        $data['sendemail'] = input('post.sendemail');
        $data['sendmessage'] = input('post.sendmessage');
        $result = model('User')->save($data,['id'=>$user_id]);
        if($result){
            $this->success('设置成功');
        }else{
            $this->error('设置失败');
        }
    }

    public function resetpass()
    {
        $validate = Validate::make([
            'oldpass|原密码'  => 'require|token',
            'password|新密码' => 'require|max:30|min:4',
            'password2|确认密码'=>'require|confirm:password',
        ]);
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }

        $user_id = session('userid');
        $oldpass = input('post.oldpass');
        $password = input('post.password');
        $user = model('User')->where('id',$user_id)->where('password',md5($oldpass))->find();
        if(!$user){
            $this->error('原密码不正确！');
        }
        $result = $user->force()->save(['password'=>md5($password)]);
        if($result){
            $this->success('密码修改成功');
        }else{
            $this->error('密码修改失败');
        }
    }

    public function urlblack()
    {
        $sys_urlblacks = model('Urlblack')->where('user_id',0)->select();
        $my_urlblacks = model('Urlblack')->where('user_id',session('userid'))->select();

        $this->assign('sys_urlblacks',$sys_urlblacks);
        $this->assign('my_urlblacks',$my_urlblacks);
        $this->assign('title','URL黑名单设置'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function addUrlblack()
    {
        $validate = Validate::make([
            'id'  => 'require|token',
            'url|黑名单URL' => 'require|min:3',
        ]);
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }
        $project_id = input('post.id');
        $url = input('post.url');
        $user_id = session('userid');

        $project = model('Project')->where('id',$project_id)->where('user_id',$user_id)->find();
        if(!$project){
            $this->error('项目不存在或没有权限');
        }

        $data = [
            'project_id' => $project_id,
            'user_id' => $user_id,
            'url' => $url,
            'add_time' => time(),
        ];
        $result = model('Urlblack')->save($data);
        if($result){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }

    public function delUrlblack()
    {
        $validate = Validate::make([
            'id'  => 'require|token',
        ]);
        if (!$validate->check($this->request->get())) {
            $this->error($validate->getError());
        }

        $id = input('get.id');
        $user_id = session('userid');
        $urlblack = model('Urlblack')->where('id',$id)->where('user_id',$user_id)->find();
        if(!$urlblack){
            $this->error('删除失败，URL不存在或没有权限！');
        }

        $result = $urlblack->delete();
        if($result){
            $this->redirect('index/user/urlblack');
        }else{
            $this->error('删除失败');
        }
    }
}
