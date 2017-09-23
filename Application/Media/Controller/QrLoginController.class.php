<?php
/**
 * Created by PhpStorm.
 * User: xmy
 * Date: 2016/10/27
 * Time: 17:52
 */
namespace Media\Controller;

use User\Api\MemberApi;

class QrLoginController extends BaseController{

    /**
     * 推送消息
     * @param $token
     */
    public function send($token){
        // 建立socket连接到内部推送端口
        $client = stream_socket_client('tcp://'.$_SERVER['SERVER_ADDR'].':5678', $errno, $errmsg, 1,  STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT);
        // 推送的数据，包含uid字段，表示是给这个uid推送
        $data['token'] = $token;
        $data['status'] = 1;
        // 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
        fwrite($client, json_encode($data)."\n");
        // wite_text(json_encode($client),dirname(__FILE__).'/pc.txt');
        // 读取推送结果
        if(fread($client, 8192) == 1){
            echo base64_encode(json_encode(array('status'=>1,'msg'=>'扫码成功')));
        }else{
            echo base64_encode(json_encode(array('status'=>2,'msg'=>'扫码失败')));
        }
    }

    /**
     * 获取唯一token
     */
    public function getToken(){
        $token = think_encrypt(time(),1,120);
        $res['token']= $token;
        $this->ajaxReturn($res);
    }

    /**
     * 生成二维码
     * @param string $url
     * @param int $level
     * @param int $size
     */
    public function QrCode($url='',$level=3,$size=4){
        $url = base64_decode(base64_decode($url));
        Vendor('phpqrcode.phpqrcode');
        $errorCorrectionLevel =intval($level) ;//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        //生成二维码图片
        //echo $_SERVER['REQUEST_URI'];
        ob_clean();
        $object = new \QRcode();
        return  $object->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
    }


    /**
     * 获取二维码
     * @param $token
     */
    public function inQrCode($token) {
        session('QrLogin_token',$token);
        $url = "http://".$_SERVER['HTTP_HOST']."/sdk.php/OTP/QrLogin/token/".$token;
        $url = U('QrLogin/QrCode',(array('url'=>base64_encode(base64_encode($url)))));
        echo '<img src="'.$url.'" >';
    }

    /**
     * 扫码登录
     * @param $token
     */
    public function qrLogin($token){
        if($token === session('QrLogin_token') && !empty(think_encrypt($token,1))){
            $data = M('qr_login','tab_')->where(array('token'=>$token,'status'=>1))->field('id,uid')->find();
            if(!empty($data)){
                $user = M('user','tab_')->where(array('id'=>$data['uid']))->field('account')->find();
                $member = new MemberApi();
                $res = $member->qrLogin($user['account'],'');
                if ($res > 0) {
                    parent::autoLogin($res);
                    $data['status'] = 2;
                    $res = M('qr_login','tab_')->save($data);
                    $this->ajaxReturn(array('status' => 1, 'msg' => '登录成功'));
                }
            }else{
                $this->ajaxReturn(array('status' => 2, 'msg' => '登录失败'));
            }
        }
    }
}