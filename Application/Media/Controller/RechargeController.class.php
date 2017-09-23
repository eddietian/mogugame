<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Media\Controller;

use Admin\Model\GameModel;
use Common\Api\PayApi;
use Org\WeixinSDK\Weixin;
use Org\JubaobarSDK\Jubaobar;
use Org\JtpaySDK\Jtpay;
use Org\UcenterSDK\Ucservice;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class RechargeController extends BaseController
{

    public function pay($value = '')
    {
        //var_dump($_SERVER['DOCUMENT_ROOT']);
        $this->display();
    }

    /**
     *支付宝支付
     */
    public function beginPay()
    {
        #判断账号是否存在
        $user = get_user_entity($_POST['uname1'], true);
        if (empty($user)) {
                     $this->error("用户不存在");
                    exit();

        } else {
            $data2["user_id"] = $user["id"];
            $data2["user_account"] = $user['account'];
            $data2["user_nickname"] = $user['nickname'];
        }
        #支付配置
        $data['order_no'] = 'PF_' . date('Ymd') . date('His') . sp_random_string(4);
        switch ($_POST['apitype']) {
            case 'alipay':
                $data['fee'] = 0.01;//$_POST['amount'];
                $data['pay_type'] = $_POST['apitype'];
                $data['config'] = "alipay";
                $data['service'] = "create_direct_pay_by_user";
                $data['pay_way'] = 1;
                break;
            case 'weixin':
                $data['fee'] = 0.01;//$_POST['amount'];
                $data['pay_type'] = "swiftpass";
                $data['config'] = wft_status()==1?$_POST['apitype']:"weixin_gf";             
                $data['service'] = "pay.weixin.native";
                if (get_wx_type() == 0) {
                    $data['pay_way'] = 2;//官方扫码
                } else {
                    $data['pay_way'] = 4;//威富通
                }
                break;
            default:
                # code...
                break;
        }
        //页面上通过表单选择在线支付类型，支付宝为alipay 财付通为tenpay
        $pay = new \Think\Pay($data['pay_type'], C($data['config']));
        $vo = new \Think\Pay\PayVo();
        $vo->setBody("平台币充值")
            ->setFee($data['fee'])//支付金额
            ->setTitle("平台币")
            ->setOrderNo($data['order_no'])
            ->setService($data['service'])
            ->setSignType("MD5")
            ->setPayMethod("direct")
            ->setTable("deposit")
            ->setPayWay($data['pay_way'])
            ->setUserId($data2['user_id'])
            ->setAccount($user['account'])
            ->setUserNickName($user['nickname'])
            ->setPromoteId($user['promote_id'])
            ->setUc($data2['uc'])
            ->setPromoteName($user['promote_account']);
        switch ($_POST['apitype']) {
            case 'alipay':
                //判断是否开启支付宝充值
                if (pay_set_status('alipay') == 0) {
                    $this->error("网站未启用支付宝充值", '', 1);
                    exit();
                }
                echo $pay->buildRequestForm($vo);
                break;
            case 'weixin':
                if (pay_set_status('wei_xin') == 0 && pay_set_status('weixin') == 0&&pay_set_status('weixin_gf')==0 ) {
                    $this->error("网站未启用微信充值", '', 1);
                    exit();
                }
                if (get_wx_type() == 0) {//微信官方
                    $data['pay_order_number'] = $data['order_no'];
                    $weixn = new Weixin();
                    $is_pay = json_decode($weixn->weixin_pay("平台币充值", $data['pay_order_number'], $data['fee']), true);
                    if ($is_pay['status'] === 1) {
                        $html = '<div class="d_body" style="height:px;">
							<div class="d_content">
								<div class="text_center">
									<table class="list" width="100%">
										<tbody>
										<tr>
											<td class="text_right">订单号</td>
											<td class="text_left">' . $data["order_no"] . '</td>
										</tr>
										<tr>
											<td class="text_right">充值金额</td>
											<td class="text_left">本次充值' . $data["fee"] . '元，实际付款' . $data["fee"] . '元</td>
										</tr>
										</tbody>
									</table>
									<img src="' . U('qrcode', array('level' => 3, 'size' => 4, 'url' => base64_encode(base64_encode($is_pay['url'])))) . '" height="301" width="301">
									<img src=' . __ROOT__ . '"/Public/Media/images/wx_pay_tips.png">
								</div>
							</div>
						</div>';
                        $this->add_deposit($data, $user);
                        $this->ajaxReturn(array("status" => 1, "html" => $html));
                    }
                } else {                    
                    //威富通
                    $result = $pay->buildRequestForm($vo);
                    if ($result['status1'] === 500) {
                        \Think\Log::record($result['msg']);
                        $html = '<div class="d_body" style="height:px;">
							<div class="d_content">
								<div class="text_center">' . $result["msg"] . '</div>
							</div>
							</div>';
                        $json_data = array("status" => 1, "html" => $html);
                    } else {
                        $html = '<div class="d_body" style="height:px;">
							<div class="d_content">
								<div class="text_center">
									<table class="list" width="100%">
										<tbody>
										<tr>
											<td class="text_right">订单号</td>
											<td class="text_left">' . $data["order_no"] . '</td>
										</tr>
										<tr>
											<td class="text_right">充值金额</td>
											<td class="text_left">本次充值' . $data["fee"] . '元，实际付款' . $data["fee"] . '元</td>
										</tr>
										</tbody>
									</table>
									<img src="' . $result["code_img_url"] . '" height="301" width="301">
									<img src=' . __ROOT__ . '"/Public/Media/images/wx_pay_tips.png">
								</div>
							</div>
						</div>';
                        $json_data = array("status" => 1, "html" => $html);
                    }
                    $this->ajaxReturn($json_data);
                }
                break;
            default:
                # code...
                break;
        }

    }

    /**
     * 聚宝云支付
     */
    public function juhe_pay()
    {

        if($_GET['ttype']==1){
            //判断是否开启聚宝云充值
            if (pay_set_status('jubaobar') == 0) {
                $this->error("网站未启用聚宝云充值", '', 1);
                exit();
            }

        }else{
            //判断是否开启竣付通充值
            if (pay_set_status('jft') == 0) {
                $this->error("网站未启用竣付通充值", '', 1);
                exit();
            }
        }

        #判断账号是否存在
        $user = get_user_entity($_POST['uname1'], true);

        if (empty($user)) {
                 $this->error("用户不存在");
                exit();
        } else {
            $data2["user_id"] = $user["id"];
            $data2["user_account"] = $user['account'];
            $data2["user_nickname"] = $user['nickname'];
        }

        #支付配置
        $data['order_no'] = 'PF_' . date('Ymd') . date('His') . sp_random_string(4);
        // $data['fee']      = $_POST['amount'];//$_POST['amount'];
        $data['fee'] = 0.01;
        #平台币记录数据
        $data['order_number'] = "";
        $data['pay_order_number'] = $data['order_no'];
        $data['user_id'] = $data2['user_id'];
        $data['user_account'] = $data2['user_account'];
        $data['user_nickname'] = $data2['user_nickname'];
        $data['promote_id'] = $user['promote_id'];
        $data['promote_account'] = $user['promote_account'];
        $data['pay_amount'] = $_POST['amount'];
        $data['pay_status'] = 0;
        $data['pay_way'] = 5;//聚宝云
        $data['pay_source'] = 1;
        $data['uc'] = $data2['uc'];
        if($_GET['ttype']==1){
            $Jubaobar = new Jubaobar();
            $this->add_deposit($data, $user);
            echo $Jubaobar->jubaobar_pay($data['order_no'], $data['fee'], '平台币充值');
        }else{
            $sign=think_encrypt(md5($_POST['amount'].$data['order_no']));
            redirect(U('pay_way', array('type'=>'UnionPay','account' =>$data2['user_account'],'pay_amount'=>$_POST['amount'],'sign'=>$sign,'pay_order_number'=>$data['order_no'])));
                       // jt_pay($order_no,$amount=0.02,$account="测试",$ip,$p9_paymethod=1,$p26_iswappay=1,$p25_terminal=1){


        }
        
    }

    //竣付通
    public function pay_way(){
        if(IS_POST){
            $msign=think_encrypt(md5($_POST['pay_amount'].$_POST['pay_order_number']));
            if($msign!==$_POST['sign']){
                $this->error('验证失败',U('Recharge/pay'));exit;
            }
             #判断账号是否存在
             $user = get_user_entity($_POST['account'], true);
             $jtpay=new Jtpay();
             #平台币记录数据            
            $data['pay_order_number'] = $_POST['pay_order_number'];
            $data['user_id'] = $user['id'];
            $data['user_account'] = $user['account'];
            $data['user_nickname'] = $user['nickname'];
            $data['promote_id'] = $user['promote_id'];
            $data['promote_account'] = $user['promote_account'];
            $data['fee'] = $_POST['pay_amount'];
            $data['pay_way'] = 6;//竣付通
            $data['pay_source'] = 1;

             $this->add_deposit($data, $user);
             $_POST['pay_amount']=0.01;
            switch ($_POST['type']) {
                case 'UnionPay':
                 echo $jtpay->jt_pay($_POST['pay_order_number'],$_POST['pay_amount'],$_POST['account'],get_client_ip(),$_POST['p10_paychannelnum']);
                    break;
                default:
                echo $jtpay->jt_pay($_POST['pay_order_number'],$_POST['pay_amount'],$_POST['account'],get_client_ip(),"",$_POST['p9_paymethod']);
                    break;
            }
           
        }else{
            $this->assign('type',$_GET['type']);
            $this->display();
        }
    }

    public function UnionPay(){
        $this->display();
    }


    /**
     *平台币充值记录
     */
    private function add_deposit($data, $user)
    {
        if (!empty($data['uc'])) {
            $uc = new Ucservice();
            $uc_user = $uc->get_user_from_uid($data['user_id']);
            $uc_id = $uc->uc_deposit($data['user_id'], $data['user_account'], $data['user_nickname'], $data['game_id'], $data['game_appid'], $data['game_name'], 0, '', $user['promote_id'], $user['promote_account'], "", $data['out_trade_no'], $data['amount'], time(), $data['extend'], $data['payway'], get_client_ip(), '', 5, $uc_user['platform'], '', '');
        } else {
            // $user = get_user_entity($username,true);
            $deposit = M("deposit", "tab_");
            $deposit_data['order_number'] = "";
            $deposit_data['pay_order_number'] = $data['pay_order_number'];
            $deposit_data['user_id'] = $user['id'];
            $deposit_data['user_account'] = $user['account'];
            $deposit_data['user_nickname'] = $user['nickname'];
            $deposit_data['promote_id'] = $user['promote_id'];
            $deposit_data['promote_account'] = $user['promote_account'];
            $deposit_data['pay_amount'] = $data['fee'];
            $deposit_data['reality_amount'] = $data['fee'];
            $deposit_data['pay_status'] = 0;
            $deposit_data['pay_way'] = $data['pay_way'];
            $deposit_data['pay_source'] = 0;
            $deposit_data['pay_ip'] = get_client_ip();
            $deposit_data['pay_source'] = 0;
            $deposit_data['create_time'] = NOW_TIME;
            $deposit_data['sdk_version'] = $data['sdk_version'];
            $result = $deposit->add($deposit_data);
            return $result;
        }
    }


    /**
     * @param int $level
     * @param int $size
     */
    public function qrcode($level = 3, $size = 4, $url = "")
    {
        ob_clean();
        Vendor('phpqrcode.phpqrcode');
        $errorCorrectionLevel = intval($level);//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        //生成二维码图片
        $object = new \QRcode();
        echo $object->png(base64_decode(base64_decode($url)), false, $errorCorrectionLevel, $matrixPointSize, 2);
    }


}
