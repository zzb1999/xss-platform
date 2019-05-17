<?php
/**
 * Created by PhpStorm.
 * User: ixysec
 * Date: 2019/5/17
 * Time: 10:24
 */
header("Content-type: text/html;charset=utf-8");

if(file_exists('./install.lock')){
    header('Location: '.$_SERVER['SERVER_NAME']);
    exit;
}

$action = isset($_GET['step']) ? $_GET['step'] : '';

if($action == 'step1'){
    //获取php版本
    if(version_compare(PHP_VERSION,'5.6.0') < 0){
        echo "<script>alert('PHP版本过低，需要php5.6以上版本，推荐7.0+');javascript:history.back(-1);</script>";
        exit;
    }
    //数据库配置文件读写
    $database_file = str_replace('/public/install','',str_replace('\\', '/', __DIR__)).'/config/database.php';
    $default_database_file = str_replace('/public/install','',str_replace('\\', '/', __DIR__)).'/config/database_default.php';
    if(!is_readable($default_database_file) && !is_writeable($database_file)){
        echo "<script>alert('数据库配置文件不可读写，请检查/config/database.php和/config/database_default.php的读写权限！');javascript:history.back(-1);</script>";
        exit;
    }
    //lock写入权限
    if(!is_readable(dirname(__FILE__))){
        echo "<script>alert('install.lock文件不可写，请检查/public/install的读写权限！');javascript:history.back(-1); </script>";
        exit;
    }
    require './step1.html';
}
/*
 * 数据库连接信息
 */
else if($action == 'step2'){
    //获取数据库连接信息
    $dbhost = $_POST['host'];
    $dbuser = $_POST['user'];
    $dbpassword = $_POST['pass'];
    $dbname = $_POST['dbname'];
    $dbprefix = $_POST['dbprefix'];
    if(empty($dbhost) || empty($dbuser) || empty($dbpassword) || empty($dbname)){
        echo "<script> alert('请将信息填写完整');javascript:history.back(-1); </script>";
        exit;
    }
    $res = create_db($dbname,$dbuser,$dbpassword,$dbhost,$dbprefix);
    if ($res['error'] == 1) {
        echo "<script> alert('".$res['msg']."');javascript:history.back(-1); </script>";
        exit;
    }
    //写入lock
    $content = "系统名称：ixysec-XSS管理平台,创建时间: " .date("Y-m-d H:i:s").'作者：laot';
    $file = fopen("./install.lock","w");
    fwrite($file, $content);
    fclose($file);
    // 完成安装
    require('./success.html');
}
else{
    require './welcome.html';
}

/*
 * 创建数据库并创建表
 */
function create_db($dbname,$dbuser,$dbpassword,$dbhost,$dbprefix){
    $res = [
        'error' => 1,
        'msg' => '不可预估错误！'
    ];
    //连接数据库
    $conn = @mysqli_connect($dbhost,$dbuser,$dbpassword);
    if(!$conn){
        $res = [
            'error' => 1,
            'msg' => '数据库连接错误，请核对数据库连接信息'
        ];
        return $res;
    }
    //读取sql文件
    if(!file_exists('./install.sql')){
        $res = [
            'error' => 1,
            'msg' => '数据库文件不存在，请检查/public/install/install.sql是否存在！'
        ];
        return $res;
    }

    $database_file = str_replace('/public/install','',str_replace('\\', '/', __DIR__)).'/config/database.php';
    $default_database_file = str_replace('/public/install','',str_replace('\\', '/', __DIR__)).'/config/database_default.php';

    $sql_file = @file_get_contents('./install.sql');
    //设置数据库编码
    $conn->query("SET NAMES 'utf8'");
    //创建数据库
    $sql = "CREATE DATABASE IF NOT EXISTS ".$dbname." default character set utf8 COLLATE utf8_general_ci;";
    if(!$conn->query($sql)){

        $res = [
            'error' => 1,
            'msg' => '数据库创建失败！'
        ];
        return $res;
    }
    //选择数据库
    $conn->select_db($dbname);

    //替换数据表前缀
    if(!empty($dbprefix)){
        $sql_file = str_replace('xss_',$dbprefix,$sql_file);
    }
    //sql文件语句已;号结束，将每条语句分割到数组
    $sql_arr = explode(';',$sql_file);
    foreach($sql_arr as $val){
        //sql运行需要;号，所以还得加上
        $sql = $val . ';';
        $conn->query($sql);
    }
    //导入模块数据
    $module_file = @file_get_contents('./module.sql');
    if(!empty($dbprefix)){
        $module_file = str_replace('xss_',$dbprefix,$module_file);
    }
    //每条语句以laotxss结束，将每条语句分割到数组
    $module_arr = explode('laotxss',$module_file);
    foreach($module_arr as $val){
        //sql运行需要;号，所以还得加上
        $sql = $val . ';';
        $conn->query($sql);
    }

    //关闭数据库
    mysqli_close($conn);

    //修改database配置
    $database_config = @file_get_contents($default_database_file);
    //替换配置
    $database_config = str_replace('default_host',$dbhost,$database_config);
    $database_config = str_replace('default_database',$dbname,$database_config);
    $database_config = str_replace('default_user',$dbuser,$database_config);
    $database_config = str_replace('default_password',$dbpassword,$database_config);
    if(!empty($dbpassword)){
        $database_config = str_replace('default_prefix',$dbprefix,$database_config);
    }else{
        $database_config = str_replace('default_prefix','xss_',$database_config);
    }
    @file_put_contents($database_file,$database_config);
}






















