<?php
namespace Sdk\Controller;
use Think\Controller;
class SpendController {

    /**
     * 转发支付url
     */
    public function get_pay_url($user_id,$game_id){
        $file=file_get_contents("./Application/Sdk/OrderNo/".$user_id."-".$game_id.".txt");
        $info=json_decode(base64_decode($file),true);
        redirect($info['pay_url']);
    }


}
