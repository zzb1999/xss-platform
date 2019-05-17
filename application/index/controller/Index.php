<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $username = session('username');
        if($username){
            $this->redirect('index/user/index');
        }
        return $this->fetch();
    }

    public function doKeepsession()
    {
        $keepsessions = model('Keepsession')->select();
        foreach ($keepsessions as $keepsession){
            if(time() > $keepsession['update_time']+300){
                $ch = curl_init();
                $url = $keepsession['url'];
                $cookie = $keepsession['cookie'];
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_COOKIE, $cookie);
                curl_exec($ch);
                curl_close($ch);

                model('Keepsession')->save(['update_time'=>time()],['id'=>$keepsession['id']]);
            }
        }
    }
}
