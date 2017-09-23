<?php
/**
 * Created by PhpStorm.
 * User: xmy
 * Date: 2016/10/27
 * Time: 17:52
 */
namespace Admin\Controller;

use User\Api\MemberApi;
use User\Api\UserApi;
use User\Model\UcenterMemberModel;
class QrLoginController{

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
        if(1){//验证session
            $data = M('qr_login','tab_')->where(array('token'=>$token,'status'=>1))->field('id,uid')->find();
            if(!empty($data)){
                $userr = M('ucenter_member')->where(array('admin_openid'=>$data['uid']))->field('id,username')->find();
                if($userr){
                    $User = new UserApi;
                    $uid = $User->login($userr['id'],'',5);
                    if(0 < $uid){ //UC登录成功
                        /* 登录用户 */
                        $Member = D('Member');
                        if($Member->login($uid)){ //登录用户
                            //TODO:跳转到登录前页面
                            M('qr_login','tab_')->where(array('token'=>$token))->setField('status',2);
                           echo json_encode(array('status' => 1, 'msg' => '登陆成功'));
                        } else {
                           echo json_encode(array('status' => 2, 'msg' => '用户不存在或已被禁用！'));
                        }

                    } else { //登录失败
                        switch($uid) {
                            case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                            case -2: $error = '密码错误！'; break;
                            default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                        }
                        echo json_encode(array('status' => 2, 'msg' => $error));
                    }
                }else{
                    echo json_encode(array('status' => 2, 'msg' => '该微信未绑定管理员账号'));
                }
            }else{
                echo json_encode(array('status' => 2, 'msg' => '二维码已过期'));
            }
        }
    }
}