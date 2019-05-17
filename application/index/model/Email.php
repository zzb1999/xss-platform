<?php

namespace app\index\model;

use think\Model;

class Email extends Model
{
    public function randEmail()
    {
        $emails = db('Email')->select();
        $key = array_rand($emails);
        return $emails[$key];
    }
}
