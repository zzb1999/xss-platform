<?php

namespace app\admin\validate;

use think\Validate;

class Module extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'title|模块名称' => 'require|max:30|token',
        'description|模块描述' => 'require',
        'code|代码' => 'require',
        'is_open|是否公开' => 'require|in:0,1'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];
}
