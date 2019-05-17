<?php

namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'username|用户名' => 'require|alphaDash|max:20|min:4|token',
        'email|邮箱' => 'require|email',
        'password|密码' => 'require|max:30|min:4',
        'password2|确认密码'=>'require|confirm:password',
        'captcha|验证码'=>'require|captcha',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];
}
