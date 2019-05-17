<?php

namespace app\admin\validate;

use think\Validate;

class Config extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'title|网站标题' => 'require|max:100|token',
        'keywords|keywords关键字' => 'max:255',
        'description|description描述' => 'max:255',
        'notice|公告' => 'max:255',
        'url|网站url' => 'require|max:255',
        'is_invite|邀请码设置' => 'require|in:0,1',
        'is_sendemail|邮箱提醒' => 'require|in:0,1',
//        'is_message|短信提醒' => 'require|in:0,1',
        'show_usermodule|显示其他用户创建的模块' => 'require|in:0,1',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];
}
