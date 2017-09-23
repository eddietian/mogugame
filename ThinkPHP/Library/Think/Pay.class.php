<?php

/**
 * 通用支付接口类
 * @author yunwuxin<448901948@qq.com>
 */

namespace Think;
use Org\UcenterSDK\Ucservice;
class Pay {

    /**
     * 支付驱动实例
     * @var Object
     */
    private $payer;

    /**
     * 配置参数
     * @var type 
     */
    private $config;
    
    /**
     * 支付类型
     * @var type
     */
    private $apitype;

    /**
     * 构造方法，用于构造上传实例
     * @param string $driver 要使用的支付驱动
     * @param array  $config 配置
     */
    public function __construct($driver, $config = array()) {
        /* 配置 */
        $pos = strrpos($driver, '\\');
        $pos = $pos === false ? 0 : $pos + 1;
        $apitype = strtolower(substr($driver, $pos));
        $this->apitype = $apitype;
        $this->config['notify_url'] = 'http://'.$_SERVER ['HTTP_HOST']."/callback.php/Notify/notify/apitype/".$apitype.'/method/notify';
        $this->config['return_url'] = 'http://'.$_SERVER ['HTTP_HOST']."/callback.php/Notify/notify/apitype/".$apitype.'/method/return';
        $config = array_merge($this->config, $config);
        /* 设置支付驱动 */
        $class = strpos($driver, '\\') ? $driver : 'Think\\Pay\\Driver\\' . ucfirst(strtolower($driver));
        $this->setDriver($class, $config);
    }

    public function buildRequestForm(Pay\PayVo $vo,$uc=0) {
        $this->payer->check();
        $result = false;
        if($uc==0){
            switch ($vo->getTable()) {
            case 'spend':
                $result = $this->add_spend($vo);
                break;
            case 'deposit':
                $result = $this->add_deposit($vo);
                break;
            case 'agent':
                $result = $this->add_agent($vo);
                break;
            case 'RefundRecord':
                $result=1;
                break;
            case "bind_recharge":
	            $result = $this->add_bind_recharge($vo);
	            break;
            default:
                $result = false;
                break;
            }
            if($result !== false) {//$check !== false
                if($this->apitype=="alipay"&&C('alipay.usenew')){
                    if(C('alipay.appid')){
                        return $this->newbuildRequestForm($vo);
                    }else{
                        E("appid未设置");
                        exit();
                    }
                }else{
                    return $this->payer->buildRequestForm($vo);
                }
            } else {
                E(M($vo->getTable(),"tab_")->getDbError());
            }
        }else{
             return $this->payer->buildRequestForm($vo);
        }
        
    }
    
    /**
     * 新版支付宝接口调用  
     * */
    public function newbuildRequestForm(Pay\PayVo $vo) {
        Vendor('Alipay.AopSdk');
        $aop = new \AopClient();
        $aop->appId = C('alipay.appid');
        $aop->signType = 'RSA2';
        $aop->rsaPrivateKey = file_get_contents("./Application/Sdk/SecretKey/alipay/rsa2_private_key.txt");
        $type = $vo->getPayMethod();
        switch ($type) {
            case 'direct':
                $productcode = 'FAST_INSTANT_TRADE_PAY';
                $request = new \AlipayTradePagePayRequest();
                $request->setReturnUrl('http://'.$_SERVER ['HTTP_HOST'].'/callback.php/Notify2/notify/apitype/alipay/methodtype/return');
                break;
            case 'wap':
                $productcode = 'QUICK_WAP_PAY';
                $request = new \AlipayTradeWapPayRequest();
                $request->setReturnUrl('http://'.$_SERVER ['HTTP_HOST'].'/callback.php/Notify2/notify/apitype/alipay/methodtype/return');
                break;
            case 'mobile':
                $aop->alipayrsaPublicKey = file_get_contents("./Application/Sdk/SecretKey/alipay/alipay2_public_key.txt");
                $productcode = 'QUICK_MSECURITY_PAY';
                $request = new \AlipayTradeAppPayRequest();
                break;
            case 'refund':
                $aop->alipayrsaPublicKey = file_get_contents("./Application/Sdk/SecretKey/alipay/alipay2_public_key.txt");
                $request = new \AlipayTradeRefundRequest();
                break;
            default:
                $productcode = 'FAST_INSTANT_TRADE_PAY';
                $request = new \AlipayTradePagePayRequest(); 
                break;
        }
        $request->setNotifyUrl('http://'.$_SERVER ['HTTP_HOST'].'/callback.php/Notify2/notify/apitype/alipay/methodtype/notify');
        
        switch ($type) {
            case 'direct':
                $request->setBizContent('{"product_code":"'.$productcode.'","body":"'.$vo->getBody().'","subject":"'.$vo->getTitle().'","total_amount":"'.$vo->getFee().'","out_trade_no":"'.$vo->getOrderNo().'"}');
                return $aop->pageExecute ($request,'POST'); 
                break;
            case 'wap':
                /*参数  out_trade_no：系统订单号*/
                $request->setBizContent('{"product_code":"'.$productcode.'","body":"'.$vo->getBody().'","subject":"'.$vo->getTitle().'","total_amount":"'.$vo->getFee().'","out_trade_no":"'.$vo->getOrderNo().'"}');
                return $aop->pageExecute ($request,'GET');
                break;
            case 'mobile':
                /*参数  out_trade_no：系统订单号*/
                $request->setBizContent('{"body":"'.$vo->getBody().'","subject":"'.$vo->getTitle().'","out_trade_no":"'.$vo->getOrderNo().'","timeout_express":"30m","total_amount":"'.$vo->getFee().'","product_code":"'.$productcode.'"}');
                $response = $aop->sdkExecute($request);
                $sHtml['arg'] = $response['orderstr'];
                $sHtml['sign'] = $response['sign'];
                $sHtml['out_trade_no'] = $vo->getOrderNo();
                return $sHtml;
                break;
            case 'refund':
                $batch_no = $vo->getBatchNo();
                $map['batch_no'] = $batch_no;
                $refund = M('refund_record', 'tab_')->where($map)->find();
                /*参数  out_trade_no：系统订单号  trade_no：支付宝订单号 refund_amount：退款金额  out_request_no：退款请求号  refund_reason：退款原因*/
                $request->setBizContent('{"out_trade_no":"","trade_no":"'.$vo->getOrderNo().'","refund_amount":"'.$refund['pay_amount'].'","out_request_no":"'.$batch_no.'","refund_reason":"调单"}');
                $result = $aop->execute($request); 
                $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;
                if(!empty($resultCode)&&$resultCode == 10000){
                    $date['tui_status'] = 1;
                    $date['tui_time'] = time();
                    M('refund_record', 'tab_')->where($map)->save($date);
                    $map_spend['pay_order_number'] = get_refund_pay_order_number($batch_no);
                    $spen_date['sub_status']=1;
                    $spen_date['settle_check']= 1;
                    M('spend','tab_')->where($map_spend)->save($spen_date);
                    return "10000";//退款成功
                } else {
                    return "退款失败";
                }
                break;
        }  
    
    }

    /**
    *消费表添加数据
    */
    private function add_spend(Pay\PayVo $vo){
        $spend = M("spend","tab_");
        $spend_data['user_id']          = $vo->getUserId();
        $spend_data['user_account']     = $vo->getAccount();
        $spend_data['user_nickname']    = $vo->getUserNickName();
        $spend_data['game_id']          = $vo->getGameId();
        $spend_data['game_appid']       = $vo->getGameAppid();
        $spend_data['game_name']        = $vo->getGameName();
        $spend_data['server_id']        = $vo->getServerId();
        $spend_data['server_name']      = $vo->getServerName();
        $spend_data['promote_id']       = $vo->getPromoteId();
        $spend_data['promote_account']  = $vo->getPromoteName();
        $spend_data['order_number']     = "";
        $spend_data['selle_ratio']      = $vo->getRatio();
        $spend_data['pay_order_number'] = $vo->getOrderNo();
        $spend_data['props_name']       = $vo->getTitle();
        $spend_data['cost']             = $vo->getFee();
        $discount = $vo->getDiscount()==0?10:$vo->getDiscount(); //获取折扣
        $price = $vo->getFee(); //获取原价
        $pay_amount = $discount * $price / 10; //计算折扣后的价格
        $vo->setFee($pay_amount);//构造表单 设置金额为折扣后的价格
        $spend_data['pay_amount']       = $pay_amount;
        $spend_data['pay_way']          = $vo->getPayWay();
        $spend_data['pay_time']         = NOW_TIME;
        $spend_data['pay_status']       = 0;
        $spend_data['pay_game_status']  = 0;
        $spend_data['extend']           = $vo->getExtend();
        $spend_data['spend_ip']         = get_client_ip();
        $spend_data['sdk_version']      = $vo->getSdkVersion();
        $result = $spend->add($spend_data);
        return $result;
    }

    /**
    *平台币充值记录
    */
    private function add_deposit(Pay\PayVo $vo){
        if (!empty($vo->getUc())) {
            $uc = new Ucservice();
            $uc_user=$uc->get_user_from_uid($vo->getUserId());
            $uc_id = $uc->uc_deposit($vo->getUserId(),$vo->getAccount(),$vo->getUserNickName(),$vo->getGameId(),$vo->getGameAppid(),$vo->getGameName(),0,'',$vo->getPromoteId(),$vo->getPromoteName(),"",$vo->getOrderNo(),$vo->getFee(),time(),$vo->getExtend(),$vo->getPayWay(),get_client_ip(),'',5,$uc_user['platform'],'','');
        }else{
            $deposit = M("deposit","tab_");
            // $ordercheck = $deposit->where(array('pay_order_number'=>$data["order_no"]))->find();
            // if($ordercheck)$this->error("订单已经存在，请刷新充值页面重新下单！");
            $deposit_data['order_number']     = "";
            $deposit_data['pay_order_number'] = $vo->getOrderNo();
            $deposit_data['user_id']          = $vo->getUserId();
            $deposit_data['user_account']     = $vo->getAccount();
            $deposit_data['user_nickname']    = $vo->getUserNickName();
            $deposit_data['promote_id']       = $vo->getPromoteId();
            $deposit_data['promote_account']  = $vo->getPromoteName();
            $deposit_data['pay_amount']       = $vo->getFee();
            $deposit_data['reality_amount']   = $vo->getFee();
            $deposit_data['pay_status']       = 0;
            $deposit_data['pay_way']          = $vo->getPayWay();
            $deposit_data['pay_source']       = 0;
            $deposit_data['pay_ip']           = get_client_ip();
            $deposit_data['pay_source']       = 0;
            $deposit_data['create_time']      = NOW_TIME;
            $deposit_data['sdk_version']       = $vo->getSdkVersion();
            $result = $deposit->add($deposit_data);
            return $result;
        }
    }

    /**
 *添加代充记录 支付宝 /威富通
 */
	private function add_agent(Pay\PayVo $vo){
		$agent = M("agent","tab_");
		$agnet_data['order_number']     = "";
		$agnet_data['pay_order_number'] = $vo->getOrderNo();
		$agnet_data['game_id']          = $vo->getGameId();
		$agnet_data['game_appid']       = $vo->getGameAppid();
		$agnet_data['game_name']        = $vo->getGameName();
		$agnet_data['promote_id']       = $vo->getPromoteId();
		$agnet_data['promote_account']  = $vo->getPromoteName();
		$agnet_data['user_id']          = $vo->getUserId();
		$agnet_data['user_account']     = $vo->getAccount();
		$agnet_data['user_nickname']    = $vo->getUserNickName();
		$agnet_data['pay_type']         = 0;//代充 转移
		$agnet_data['amount']           = $vo->getMoney();
		$agnet_data['real_amount']      = $vo->getFee();
		$agnet_data['pay_status']       = 0;
		$agnet_data['pay_way']          = $vo->getPayWay();
		$agnet_data['create_time']      = time();
		$agnet_data['zhekou']           = $vo->getParam();
		// $agnet_data['sdk_version']       = $vo->getSdkVersion();
		$agent->create($agnet_data);
		$result = $agent->add();
		return $result;
	}

	/**
	 * 绑币充值记录
	 * @param Pay\PayVo $vo
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	private function add_bind_recharge(Pay\PayVo $vo){
		$agent = M("bind_recharge","tab_");
		$agnet_data['order_number']     = "";
		$agnet_data['pay_order_number'] = $vo->getOrderNo();
		$agnet_data['game_id']          = $vo->getGameId();
		$agnet_data['game_appid']       = $vo->getGameAppid();
		$agnet_data['game_name']        = $vo->getGameName();
		$agnet_data['promote_id']       = $vo->getPromoteId();
		$agnet_data['promote_account']  = $vo->getPromoteName();
		$agnet_data['user_id']          = $vo->getUserId();
		$agnet_data['user_account']     = $vo->getAccount();
		$agnet_data['user_nickname']    = $vo->getUserNickName();
		$agnet_data['pay_type']         = 0;
		$agnet_data['amount']           = $vo->getMoney();
		$agnet_data['real_amount']      = $vo->getFee();
		$agnet_data['pay_status']       = 0;
		$agnet_data['pay_way']          = $vo->getPayWay();
		$agnet_data['create_time']      = time();
		$agnet_data['zhekou']           = $vo->getDiscount();
		$agent->create($agnet_data);
		$result = $agent->add();
		return $result;
	}

    /**
     * 设置支付驱动
     * @param string $class 驱动类名称
     */
    private function setDriver($class, $config) {
        $this->payer = new $class($config);
        if (!$this->payer) {
            E("不存在支付驱动：{$class}");
        }
    }

    public function __call($method, $arguments) {
        if (method_exists($this, $method)) {
            return call_user_func_array(array(&$this, $method), $arguments);
        } elseif (!empty($this->payer) && $this->payer instanceof Pay\Pay && method_exists($this->payer, $method)) {
            return call_user_func_array(array(&$this->payer, $method), $arguments);
        }
    }

}
