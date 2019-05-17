<?php

namespace app\index\model;

use think\Model;

class User extends Model
{
    /*
     * 根据用户名，邮箱获取用户
     */
    public function getByUsernameEmail($username,$email)
    {
        return self::whereOr(['username'=>$username,'email'=>$email])->find();
    }

    /*
     * 根据邮箱获取用户,排除user_id等于$user_id的
     */
    public function getByEmail($user_id,$email)
    {
        return self::where('email',$email)->where('id','<>',$user_id)->find();
    }
}
