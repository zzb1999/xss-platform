<?php
// [ 应用入口文件 ]
namespace think;

//安装入口
if(file_exists('./install') && !file_exists('./install/install.lock')){
    header('Location: install/index.php');
    exit;
}

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
Container::get('app')->run()->send();
