<?php
namespace Sdk\Controller;
use Think\Controller;
use Common\Api\GameApi;
use Org\WeixinSDK\Weixin;
use Org\HeepaySDK\Heepay;
use Org\UcenterSDK\Ucservice;
use Org\SwiftpassSDK\Swiftpass;

class WapPayController extends BaseController{

    private function pay($param=array()){
        $table  = $param['code'] == 1 ? "spend" : "deposit";
        $prefix = $param['code'] == 1 ? "SP_" : "PF_";
        $out_trade_no = $prefix.date('Ymd').date('His').sp_random_string(4);
        $user = get_user_entity($param['user_id']);
        switch ($param['apitype']) {
            case 'swiftpass':
                $pay  = new \Think\Pay($param['apitype'],$param['config']);
                break;
            
            default:
                $pay  = new \Think\Pay($param['apitype'],C($param['config']));
                break;
        }
        $discount = $this->get_discount($param['game_id'],$user['promote_id'],$param['user_id']);
        $discount = $discount['discount'];
        $vo   = new \Think\Pay\PayVo();
        $vo->setBody("充值记录描述")
            ->setFee($param['price'])//支付金额
            ->setTitle($param['title'])
            ->setBody($param['body'])
            ->setOrderNo($out_trade_no)
            ->setService($param['server'])
            ->setSignType($param['signtype'])
            ->setPayMethod("wap")
            ->setTable($table)
            ->setPayWay($param['payway'])
            ->setGameId($param['game_id'])
            ->setGameName(get_game_name($param['game_id']))
            ->setGameAppid($param['game_appid'])
            ->setServerId(0)
            ->setServerName("")
            ->setUserId($param['user_id'])
            ->setAccount($user['account'])
            ->setUserNickName($user['nickname'])
            ->setPromoteId($user['promote_id'])
            ->setPromoteName($user['promote_account'])
            ->setExtend($param['extend'])
            ->setSdkVersion($param['sdk_version'])
            ->setDiscount($discount);
        if($param['is_uc']==1){
            return $pay->buildRequestForm($vo,1);
        }else{
            return $pay->buildRequestForm($vo);
        }

    }

    /**
    *支付宝移动支付
    */
    public function alipay_pay(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        C(api('Config/lists'));
        if (empty($request)) {
            $this->set_message(0, "fail", "登陆数据不能为空");
        }
        if($request['price']<0){
            $this->set_message(0,"fail","充值金额有误");
        }

        $game_set_data = get_game_set_info($request['game_id']);
        $request['apitype'] = "alipay";
        $request['config']  = "alipay";
        $request['signtype']= "MD5";
        $request['server']  = "alipay.wap.create.direct.pay.by.user";
        $request['payway']  = 1;
            $request['title']=$request['price'];
            $request['body']=$request['price'];
            $request['price']=0.01;
            $pay_url=$this->pay($request);
            file_put_contents("./Application/Sdk/OrderNo/".$request['user_id']."-".$request['game_id'].".txt",base64_encode(json_encode(['pay_url'=>$this->pay($request)])));
            echo base64_encode(json_encode(array('pay_url'=>"http://".$_SERVER['HTTP_HOST'].'/sdk.php/Spend/get_pay_url/user_id/'.$request['user_id'].'/game_id/'.$request['game_id'])));
        
    }


    /**
    *微信支付
    */
    public function weixin_pay()
    {
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组 
        $request = json_decode(base64_decode(file_get_contents("php://input")), true);
        if (empty($request)) {
            $this->set_message(0, "fail", "登陆数据不能为空");
        }

        // $request['game_id']=1;
        // $request['user_id']=1;
        // $request['promote_id']=0;
        // $request['price']=0.01;

        C(api('Config/lists'));
          if($request['price']<0){
            $this->set_message(0,"fail","充值金额有误");
        }
            $table = $request['code'] == 1 ? "spend" : "deposit";
            $prefix = $request['code'] == 1 ? "SP_" : "PF_";
            $request['pay_order_number'] = $prefix . date('Ymd') . date('His') . sp_random_string(4);
            $request['pay_way'] = 3;
            $request['pay_status'] = 0;
            $request['spend_ip'] = get_client_ip();
            //折扣
            $user = get_user_entity($request['user_id']);
            $discount = $this->get_discount($request['game_id'],$user['promote_id'],$request['user_id']);
            $discount = $discount['discount'];
            $pay_amount = $discount * $request['price'] / 10;


            $Swiftpass=new Swiftpass(C('weixin_gf.partner'),C('weixin_gf.key'));
            // $Swiftpass=new Swiftpass('7552900037','11f4aca52cf400263fdd8faf7a69e007');
            $param['service']="pay.weixin.wappay";
            $param['ip']=  $request['spend_ip'];
            $param['pay_amount']=0.01;//$request['price'];
            $param['out_trade_no']= $request['pay_order_number'];
            $param['game_name']= get_game_name($request['game_id']);
            $param['body']="游戏充值";
            $url=$Swiftpass->submitOrderInfo($param);
            if($url['status']==000){ 
                if($request['code']==1){
                   $this->add_spend($request);
                }else{
                  $this->add_deposit($request);
                }
                $json_data['status']=1;
                $json_data['msg']=$url['msg'];
                $json_data['url']=$url;

            }else{
                $json_data['status']=0;
                $json_data['msg']=$url['msg'];
                $json_data['url']='http://'.$_SERVER ['HTTP_HOST'];
            }
               echo base64_encode(json_encode($json_data));

    }


}
