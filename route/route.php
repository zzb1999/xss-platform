<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('admin$', 'admin/index/login');
Route::get(':urlkey$', 'index/project/code')->pattern(['urlkey'=>'[0-9a-zA-Z]{4}']);
Route::get(':urlkey$', 'index/project/code')->pattern(['urlkey'=>'[0-9a-zA-Z]{6}']);
Route::rule('api', 'index/project/api', 'GET|POST');
Route::get('keepsession', 'index/project/keepsession');
Route::get('authtest', 'index/project/authtest');

return [

];
