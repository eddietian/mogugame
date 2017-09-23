<?php
namespace App\Controller;
use Think\Controller;
use Common\Api\GameApi;
use Org\WeixinSDK\Weixin;
class PayController extends BaseController{

	const ALI_PAY = 1;          //支付宝支付
	const WEIXIN_PAY =2;        //微信支付

	const PLATFORM_COIN = 1;        //平台币
	const BIND_PLATFORM_COIN = 2;   //绑定平台币


    private function pay($table,$prefix,$param){
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
//	    $param['real_pay_amount'] = 0.01;
        $vo   = new \Think\Pay\PayVo();
        $vo ->setFee($param['real_pay_amount'])//支付金额
	        ->setMoney($param['pay_amount'])
            ->setTitle($param['title'])
            ->setBody($param['body'])
            ->setOrderNo($out_trade_no)
            ->setService($param['server'])
            ->setSignType($param['signtype'])
            ->setPayMethod("mobile")
            ->setTable($table)
            ->setPayWay($param['payway'])
            ->setGameId($param['game_id'])
            ->setGameName($param['game_name'])
            ->setGameAppid($param['game_appid'])
            ->setServerId(0)
            ->setServerName("")
            ->setUserId($param['user_id'])
            ->setAccount($user['account'])
            ->setUserNickName($user['nickname'])
            ->setPromoteId($param['promote_id'])
            ->setPromoteName(get_promote_name($param['promote_id']))
            ->setExtend($param['extend'])
	        ->setDiscount($param['discount'])
            ->setSdkVersion($param['sdk_version']);
        return $pay->buildRequestForm($vo);
    }

	/**
	 * APP充值
	 * @param $token
	 * @param $pay_amount   金额
	 * @param $good_info    商品信息(json数组：type 1平台币 2绑币 game_id 游戏ID)
	 * @param $pay_way      1 支付宝 2微信
	 * author: xmy 280564871@qq.com
	 */
    public function recharge($token,$pay_amount,$good_info,$pay_way,$promote_id){
    	$this->auth($token);
	    $good_info = json_decode($good_info,true);
    	switch ($good_info['type']){
		    case self::PLATFORM_COIN:
		    	$table = "deposit";
		    	$prefix = "PF_";
			    $good['real_pay_amount'] = $pay_amount;
			    $good['title'] = "平台币";
			    $good['body'] = "平台币充值";
		    	break;
		    case self::BIND_PLATFORM_COIN:
		    	$table = "bind_recharge";
		    	$prefix = "BR_";
		    	$game_id = $good_info['game_id'];
			    $game = M("Game","tab_")->find($game_id);
			    if(empty($game)){
			    	$this->set_message(-1,"游戏不存在");
			    }
			    $discount = empty($game['bind_recharge_discount']) ? 10 : $game['bind_recharge_discount'];
			    $real_pay_amount = round($pay_amount * $discount / 10,2);
			    //构建商品信息
			    $good['title'] = "绑定平台币";
			    $good['body'] = "绑定平台币充值";
			    $good['game_id'] = $game_id;
			    $good['game_name'] = $game['game_name'];
			    $good['game_appid'] = $game['game_appid'];
			    $good['real_pay_amount'] = $real_pay_amount;
			    $good['discount'] = $discount;
			    break;
		    default:
		    	$this->set_message(-1,"商品信息错误");
	    }
	    $good['pay_amount'] = $pay_amount;
    	$good['promote_id'] = $promote_id;
    	switch ($pay_way){
		    case self::ALI_PAY :
			    $this->alipay_pay(USER_ACCOUNT,$good,$table,$prefix);
			    break;
		    case self::WEIXIN_PAY:
		    	$this->weixin_pay(USER_ACCOUNT,$good,$table,$prefix);
		    	break;
		    default:$this->set_message(-1,"暂无该支付选项");
	    }


    }


    /**
    *支付宝移动支付
    */
    private function alipay_pay($account,$param,$table,$prefix){
        $param['apitype'] = "alipay";
	    $param['config']  = "alipay";
	    $param['signtype']= "MD5";
	    $param['server']  = "mobile.securitypay.pay";
	    $param['payway']  = 1;
	    $param['user_id'] = get_user_id($account);
	    $data = $this->pay($table,$prefix,$param);
	    $md5_sign = $this->encrypt_md5(base64_encode($data['arg']),"mengchuang");
	    $data = array(
	    	"orderInfo" => base64_encode($data['arg']),
		    "out_trade_no" => $data['out_trade_no'],
		    "order_sign" => $data['sign'],
		    "md5_sign" => $md5_sign
	    );
        $this->set_message(1,1,$data);
    }

    /**
    *微信支付
    */
	private function weixin_pay($account, $param, $table, $prefix)
	{
		$param['user_id'] = get_user_id($account);
		$tool = M("tool","tab_")->where(['name'=>"wei_xin_apps"])->find();
		if ($tool['status'] == 1) {//官方
			$param['pay_order_number'] = $prefix . date('Ymd') . date('His') . sp_random_string(4);
			$param['pay_way'] = 3;
			$param['pay_status'] = 0;
			$param['spend_ip'] = get_client_ip();
			$weixn = new Weixin();
			$is_pay = json_decode($weixn->weixin_pay($param['title'], $param['pay_order_number'], $param['real_pay_amount'], 'APP', 3), true);
			if ($is_pay['status'] === 1) {
				switch ($table){
					case 'deposit':
						$this->add_deposit($param);
						break;
					case "bind_recharge":
						$this->add_bind_recharge($param);
						break;
				}
				$json_data['appid'] = $is_pay['appid'];
				$json_data['partnerid'] = $is_pay['mch_id'];
				$json_data['prepayid'] = $is_pay['prepay_id'];
				$json_data['noncestr'] = $is_pay['noncestr'];
				$json_data['timestamp'] = $is_pay['time'];
				$json_data['package'] = "Sign=WXPay";
				$json_data['sign'] = $is_pay['sign'];
				$json_data['status'] = 1;
				$json_data['return_msg'] = "下单成功";
				$json_data['wxtype'] = "wx";
				$this->set_message(1,1,$json_data);
			}
		} else {
			$param['apitype'] = "swiftpass";
			$param['config'] = array("partner" => C('weixin_app.partner'), "email" => "", "key" => C('weixin_app.key'));
			$param['signtype'] = "MD5";
			$param['server'] = "unified.trade.pay";
			$param['payway'] = 3;
			$result_data = $this->pay($table,$prefix,$param);
			$data['status'] = 1;
			$data['wxtype'] = "wft";
			$data['return_code'] = "success";
			$data['return_msg'] = "下单成功";
			$data['token_id'] = $result_data['token_id'];
			$data['out_trade_no'] = $result_data['out_trade_no'];
			$this->set_message(1,1,$data);
		}
	}


    /**
    *支付验证
    */
    public function pay_validation(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $out_trade_no = $request['out_trade_no'];
        $pay_where = substr($out_trade_no,0,2);
        $result = 0;
        $map['pay_order_number'] = $out_trade_no;
        switch ($pay_where) {
            case 'SP':
                $data = M('spend','tab_')->field('pay_status')->where($map)->find();
                $result = $data['pay_status'];
                break;
            case 'PF':
                $data = M('deposit','tab_')->field('pay_status')->where($map)->find();
                $result = $data['pay_status'];
                break;
            case 'AG':
                $data = M('agent','tab_')->field('pay_status')->where($map)->find();
                $result = $data['pay_status'];
                break;
            default:
                exit('accident order data');
                break;
        }
        if($result){
            echo base64_encode(json_encode(array("status"=>1,"return_code"=>"success","return_msg"=>"支付成功")));
            exit();
        }else{
            echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"支付失败")));
            exit();
        }
    }

    /**
    *sdk客户端显示支付
    */
    public function payShow(){
        $map['type'] = 1;
        $map['status'] = 1;
        $data = M("tool","tab_")->where($map)->select();
        if(empty($data)){
            echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"暂无数据")));
            exit();
        }
        foreach ($data as $key => $value) {
            $pay_show_data[$key]['mark']  = $value['name'];
            $pay_show_data[$key]['title'] = $value['title'];
        }
        echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"成功","pay_show_data"=>$pay_show_data)));
        exit();
    }

	/**
	 * 支付返回
	 * @param int $status
	 * @param int $return_msg
	 * @param array $data
	 * author: xmy 280564871@qq.com
	 */
    public function set_message($status, $return_msg = 0, $data = [])
    {
    	$data['status'] = $status;
    	$data['msg'] = $return_msg;
    	echo json_encode($data);
    	exit();
    }
}
