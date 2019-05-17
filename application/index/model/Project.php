<?php

namespace app\index\model;

use think\Model;

class Project extends Model
{
    /*
     * 生成短urlkey
     * 参数1：已存在的urlkey数组
     * 参数2：生成的位数
     */
    public function makeUrlkey($existed=[],$num=4){
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($str);
        while (true){
            $code = '';
            for($i = 0;$i < $num;$i++){
                $k = rand(0,$len-1);
                $code .= $str[$k];
            }
            if(!in_array($code,$existed)){
                return $code;
            }
        }
    }
}
