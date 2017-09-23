<?php
namespace Callback\Controller;
/**
 * 支付回调控制器
 * @author 小纯洁 
 */
class GameNotifyController extends BaseController {
    /**
    *通知方法
    */
    public function notify()
    {
        if(IS_POST && !empty($_POST)){
            $param = $_POST;
            $md5_sign = md5($param['out_trade_no'].$param['price']."1".$param['extend']."mengchuang");
            if($param['sign'] == $md5_sign){
                echo "success";
            }else{
                echo "sign fail";
            }

        }else{
            echo "Access Denied";
        }
    }

    public function login_v(){
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        $url="http://local.newsy.com/sdk.php/LoginNotify/login_verify";
        echo $this->post($data,$url);
    }

    /**
    *post提交数据
    */
    protected function post($param,$url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置等待时间
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}