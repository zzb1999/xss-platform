<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

function sendEmail($option)
{
    $mail = new \PHPMailer\PHPMailer\PHPMailer();

    $mail->isSMTP();    //使用smtp协议发送                                            // Set mailer to use SMTP
    $mail->Host       = $option['host'];   //邮件服务器
    $mail->SMTPAuth   = true;    //开启smtp授权
    $mail->Username   = $option['sendemail'];   //发信邮箱
    $mail->Password   = $option['password'];   //发信邮箱密码
    $mail->SMTPSecure = 'ssl';            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;  //邮件发送端口

    //Recipients
    $mail->setFrom($option['sendemail'], '饼干快递公司');
    $mail->addAddress($option['useremail'], $option['username']);     // Add a recipient

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->CharSet = 'utf-8';
    $mail->Subject = '饼干商城';
    $mail->Body    = $option['content'];
    $result = $mail->send();
    if($result){
        return true;
    }else{
        return $mail->ErrorInfo;
    }
}

// 应用公共文件
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if(isset($_SERVER['HTTP_X_REAL_IP'])){//nginx 代理模式下，获取客户端真实IP
        $ip=$_SERVER['HTTP_X_REAL_IP'];
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
    }else{
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function get_ip() {
    $ip = "";
    if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown') && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['REMOTE_ADDR'])){
        $ip = $_SERVER['REMOTE_ADDR'];
    } elseif(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function adders($ip){
    $str = file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=".$ip);
    $str = json_decode($str,true);
    if($str['data']['region']){
        $str = $str['data']['region'].' '.$str['data']['city'];
    }else{
        $str = $str['data']['country'].' '.$str['data']['country_id'];
    }
    return $str;
}