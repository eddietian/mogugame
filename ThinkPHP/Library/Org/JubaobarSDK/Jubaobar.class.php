<?php
namespace Org\JubaobarSDK;
use Think\Exception;

class Jubaobar  {

  public function jubaobar_pay($order_no,$amount=0.02,$payerName="测试",$payMethod="ALL"){
        header("Content-type:text/html;charset=utf-8");
        Vendor("Jubaobar.jubaopay");
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $partnerid="14061642390911131749";
        $remark=$_POST["remark"];
         //商户利用支付订单（payid）和商户号（mobile）进行对账查询
        $jubaopay=new \jubaopay('./Application/Sdk/SecretKey/jubaopay/jubaopay.ini');
        $jubaopay->setEncrypt("payid", $order_no);//订单号
        $jubaopay->setEncrypt("partnerid", C('jubaobar.partner'));//商户号
        $jubaopay->setEncrypt("amount", $amount);//金额
        $jubaopay->setEncrypt("payerName", $payerName);//商品名
        $jubaopay->setEncrypt("remark", '梦创科技');//备注
        $jubaopay->setEncrypt("returnURL", $returnURL);//
        $jubaopay->setEncrypt("callBackURL", "http://".$_SERVER['HTTP_HOST']."/callback.php/Jubaobar/jubaobar_notify");//回调
        //对交易进行加密=$message并签名=$signature
        $jubaopay->interpret();
        $message=$jubaopay->message;
        $signature=$jubaopay->signature;
        //将message和signature一起aPOST到聚宝支付
        $html="<form method='post' action='http://www.jubaopay.com/apipay.htm' id='payForm'>";
        $html.="<input type=\"hidden\" name=\"message\" value=\"$message\"/>";
        $html.="<input type=\"hidden\" name=\"signature\" value=\"$signature\"/>";
        $html.="<input type=\"hidden\" name=\"payMethod\" value=\"{$payMethod}\"/>";
        $html.="<input type='hidden' name='tab' value=''/>";
        $html.='</form>';
        $html.="<script type='text/javascript'>document.getElementById('payForm').submit();</script>";
        return $html;
    }
}