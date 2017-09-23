<?php
namespace Org\HeepaySDK;
use Think\Exception;

class Heepay  {

    public function heepay_pay($pay){
      /*************创建签名***************/
        $sign_str = '';
        $sign_str  = $sign_str . 'version=' . 1;
        $sign_str  = $sign_str . '&agent_id=' . $pay['agent_id'];
        $sign_str  = $sign_str . '&agent_bill_id=' . $pay['order_no'];
        $sign_str  = $sign_str . '&agent_bill_time=' . $pay['time'];
        $sign_str  = $sign_str . '&pay_type=' . $pay['pay_type'];
        $sign_str  = $sign_str . '&pay_amt=' . $pay['amount'];
        $sign_str  = $sign_str .  '&notify_url=http://'.$_SERVER['HTTP_HOST']."/callback.php/Notify/heepay_callback";
        $sign_str  = $sign_str . '&user_ip=' . $pay['user_ip'];
        $sign_str = $sign_str . '&key=' . $pay['sign_key'];//密钥
        $sign = md5($sign_str); //签名值

        $data=array(
            'version'=>1,
            'agent_id'=>$pay['agent_id'],//商户号
            'agent_bill_id'=>$pay['order_no'],//订单号
            'agent_bill_time'=>$pay['time'],//date('YmdHis', time()) 时间
            'pay_type'=>$pay['pay_type'],//支付类型
            'pay_amt'=>$pay['amount'],//支付金额
            'notify_url'=>"http://".$_SERVER['HTTP_HOST']."/callback.php/Notify/heepay_callback",//通知地址
            'return_url'=>"http://",
            'user_ip'=>$pay['user_ip'],//用户ip
            'goods_name'=>$pay['payerName'],//商品名
            'goods_num'=>$pay['number'],//商品数量
            'goods_note'=>$pay['goods_note'],//说明
            'remark'=>"",//备注
            'sign'=>$sign,
            );
        $xml=$this->request_post("https://pay.heepay.com/Phone/SDK/PayInit.aspx",$data);
        $string=explode("<token_id>",$xml);
        $token_id=explode("</token_id>",$string[1]);
        return $token_id[0];
    }

    //https服务器 POST请求 
    public function request_post($url = '', $post_data = array()) {
        if (empty($url) || empty($post_data)) {
            return false;
        }
        
        $o = "";
        foreach ( $post_data as $k => $v ) 
        { 
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
        $postUrl = $url;
        $curlPost = $post_data;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL, $postUrl); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $data = curl_exec($curl);//运行curl
        curl_close($curl);
        return $data;
    }

}