<?php

namespace Sdk\Controller;

use Think\Controller;

use Common\Api\GameApi;

class AppleController extends BaseController{



    /**

    *ios移动支付

    */

    public function applePay(){

        C(api('Config/lists'));

        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组

        $request = json_decode(base64_decode(file_get_contents("php://input")),true);

          if(C('UC_SET')==1){

            if(!is_array(find_uc_account($request['account']))){

               $this->set_message(0,"fail","Uc用户暂不支持");

            }

        }

        #获取订单信息

        $prefix = $request['code'] == 1 ? "SP_" : "PF_";

        $out_trade_no = $prefix.date('Ymd').date('His').sp_random_string(4);

        $data = array("status"=>1,"out_trade_no"=>$out_trade_no);

        $request['pay_order_number'] = $out_trade_no;

        $request['pay_status'] = 0;

        $request['pay_way']    = 7;

        $request['title'] = $request['productId'];

        $request['spend_ip']   = get_client_ip();

        if($request['code'] == 1 ){

            #TODO添加消费记录

            $this->add_spend($request);

        }else{

            #TODO添加平台币充值记录

            $this->add_deposit($request);

        }

        echo base64_encode(json_encode($data));

    }



    private function getSignVeryfy($receipt, $isSandbox = false){

        if ($isSandbox) {     

            $endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';     

        }     

        else {     

            $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';     

        }     



        $postData = json_encode(

            array('receipt-data' => $receipt["paper"])

        );

      

        $ch = curl_init($endpoint);     

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     

        curl_setopt($ch, CURLOPT_POST, true);     

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);     

        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  //这两行一定要加，不加会报SSL 错误  

        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);   

        $response = curl_exec($ch);     

        $errno    = curl_errno($ch);     

        $errmsg   = curl_error($ch);     

        curl_close($ch);     

        //判断时候出错，抛出异常  

        if ($errno != 0) {     

            throw new \Think\Exception($errmsg, $errno);     

        }     

                  

        // $data = json_decode($response,true);     

        // //判断返回的数据是否是对象  

        // if (!is_object($data)) {     

        //     throw new \Think\Exception('Invalid response data');     

        // }     

        // //判断购买时候成功  

        // if (!isset($data['status']) || $data['status'] != 0) {     

        //     throw new \Think\Exception('Invalid receipt');     

        // }     

        return $response;   

    }



    /**

    *苹果支付验证

    */

    public function appleVerify(){

        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组

        $request = json_decode(base64_decode(file_get_contents("php://input")),true); 
        wite_text(json_encode($request),dirname(__FILE__).'/A.txt');
        $isSandbox = true;

        //开始执行验证  

        try  

        {  
            $data = $this->getSignVeryfy($request, $isSandbox);  
            wite_text($data,dirname(__FILE__).'/data.txt');   
            $info = json_decode($data,true); 
            wite_text(json_encode($info),dirname(__FILE__).'/Aaaa.txt');   
            if($info['status'] == 0){
                $paperVerify=M('spend','tab_')->field('id,order_number')->where(array('pay_way'=>7,'order_number'=>$info['receipt']['transaction_id']))->find();
                if($paperVerify){
                    echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"凭证重复")));
                    exit();
                }
                $out_trade_no = $request['out_trade_no'];
                $pay_where = substr($out_trade_no,0,2);
                $result = 0;
                $map['pay_order_number'] = $out_trade_no;
                $payamountVerify=M('spend','tab_')->field('id,pay_order_number,extend,pay_amount')->where($map)->find(); 
                if($payamountVerify['pay_amount']!=$request['price']){
                    wite_text(1111,dirname(__FILE__).'/asasa.txt');   
                    $disdata=array();
                    $disdata['spend_id']=$payamountVerify['id'];
                    $disdata['pay_order_number']=$payamountVerify['pay_order_number'];
                    $disdata['extend']=$payamountVerify['extend'];
                    $disdata['last_amount']=$request['price'];
                    $disdata['currency']=$request['currency'];
                    $disdata['create_time']=NOW_TIME;
                    $pay_distinction=M('spend_distinction','tab_')->add($disdata);
                    wite_text($pay_distinction,dirname(__FILE__).'/sql.txt');   
                    if(!$pay_distinction){
                        \Think\Log::record('数据插入失败 pay_order_number'.$payamountVerify['pay_order_number']);
                    }
                }
                $field = array("pay_status"=>1,"pay_amount"=>$request['price'],"receipt"=>$data,"order_number"=>$info['receipt']['transaction_id']);

                switch ($pay_where) {

                    case 'SP':

                        //$field = array("pay_status"=>1,"pay_amount"=>$request['price']);

                        $result = M('spend','tab_')->where($map)->setField($field);

                        $param['out_trade_no'] = $out_trade_no;

                        $game = new GameApi();

                        $game->game_pay_notify($param);

                        break;

                    case 'PF':

                        $result = M('deposit','tab_')->where($map)->setField($field);

                        break;

                    case 'AG':

                        $result = M('agent','tab_')->where($map)->setField($field);

                        break;

                    default:

                        exit('accident order data');

                        break;

                }

                if($result){

                    $this->set_ratio($out_trade_no);

                    echo base64_encode(json_encode(array("status"=>1,"return_code"=>"success","return_msg"=>"支付成功")));

                    exit();

                }else{

                    echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"支付状态修改失败")));

                    exit();

                }

            }else{

                echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"支付失败")));

                exit();

            }

        }  

        //捕获异常  

        catch(Exception $e)  

        {  

            echo 'Message: ' .$e->getMessage();  

        }  

    }



}

