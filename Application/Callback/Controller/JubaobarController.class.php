<?php

namespace Callback\Controller;
/**
 * 支付回调控制器
 * @author 小纯洁 
 */
class JubaobarController extends BaseController {

    public function jubaobar_notify(){
        Vendor("Jubaobar.jubaopay");
        $message   = $_POST["message"];
        $signature = $_POST["signature"];
        $jubaopay  = new \jubaopay('./Application/Sdk/SecretKey/jubaopay/jubaopay.ini');
        $jubaopay->decrypt($message);
        // 校验签名，然后进行业务处理
        $result = $jubaopay->verify($signature);
        if($result==1) {
          
          $order_info = array(
            "out_trade_no"=>$jubaopay->getEncrypt("payid"),
            "trade_no"=>$jubaopay->getEncrypt("orderNo"),
            'status'=>$jubaopay->getEncrypt("state")
          );
          if($order_info['status']=="2"){
          $pay_where  = substr($jubaopay->getEncrypt("payid"),0,2);
          $result = false;
          switch ($pay_where) {
              case 'SP':
                  $result = $this->set_spend($order_info);
                  break;
              case 'PF':
                  $result = $this->set_deposit($order_info);
                  break;
              case 'AG':
                  $result = $this->set_agent($order_info); 
                  break;
              default:
                  $this->record_logs("accident order data");
                  exit('accident order data');
                  break;
          }
          }
          echo "success"; // 像服务返回 "success"
        } else {
          $this->record_logs("验签失败");
          echo "verify failed";
        }
    }
        function wite_text($txt,$name){
        $myfile = fopen($name, "w") or die("Unable to open file!");
        fwrite($myfile, $txt);
        fclose($myfile);
    }
}