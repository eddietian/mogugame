<?php

namespace Callback\Controller;

use Org\UcenterSDK\Ucservice;
use Org\SwiftpassSDK\Swiftpass;


/**
 * 支付回调控制器
 * @author 小纯洁
 */
class NotifyController extends BaseController
{

    /**
     *通知方法
     */

    public function notify()

    {

        C(api('Config/lists'));

        $apitype = I('get.apitype');#获取支付api类型

        if (IS_POST && !empty($_POST)) {

            $notify = $_POST;

        } elseif (IS_GET && !empty($_GET)) {

            $notify = $_GET;

            unset($notify['method']);

            unset($notify['apitype']);

        } else {

            $notify = file_get_contents("php://input");

            if (empty($notify)) {

                $this->record_logs("Access Denied");

                exit('Access Denied');

            }

        }


        $pay_way = $apitype;

        if ($apitype == "swiftpass") {
            $apitype = "weixin";
        }

        $pay = new \Think\Pay($pay_way, C($apitype));

        if ($pay->verifyNotify($notify)) {

            //获取回调订单信息

            $order_info = $pay->getInfo();

            if ($order_info['status']) {

                if (C('UC_SET') == 1) {


                    $uc = new Ucservice();

                    $uc_id = $uc->uc_rechange_notify($order_info['out_trade_no'], 1);

                }

                $pay_where = substr($order_info['out_trade_no'], 0, 2);

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

                    case 'BR':

                        $result = $this->set_bind_recharge($order_info);

                    default:

                        exit('accident order data');

                        break;

                }

                if (I('get.method') == "return") {

                    redirect('http://' . $_SERVER['HTTP_HOST'] . '/media.php');

                } else {

                    $pay->notifySuccess();

                }

            } else {

                $this->record_logs("支付失败！");

            }

        } else {

            $this->record_logs("支付验证失败");

            redirect('http://' . $_SERVER['HTTP_HOST'] . '/media.php', 3, '支付验证失败');

        }

    }





    /**
    *微信回调
    */
    public function swiftpass_callback(){
        $xml = file_get_contents('php://input');
        $Swiftpass=new Swiftpass(C('weixin_gf.partner'),C('weixin_gf.key'));
        $Swiftpass->resHandler->setContent($xml);
        $Swiftpass->resHandler->setKey(C('weixin_gf.key'));
        if($Swiftpass->resHandler->isTenpaySign()){
            if($Swiftpass->resHandler->getParameter('status') == 0 && $Swiftpass->resHandler->getParameter('result_code') == 0){
                $pay_where = substr($Swiftpass->resHandler->getParameter('out_trade_no'),0,2);
                $order_info['trade_no']=$Swiftpass->resHandler->getParameter('transaction_id');
                $order_info['out_trade_no']=$Swiftpass->resHandler->getParameter('out_trade_no');
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
                        exit('accident order data');
                        break;
                }
                echo 'success';
                exit();

                //echo $this->resHandler->getParameter('status');
                // //此处可以在添加相关处理业务，校验通知参数中的商户订单号out_trade_no和金额total_fee是否和商户业务系统的单号和金额是否一致，一致后方可更新数据库表中的记录。
                //更改订单状态

                // \Utils::dataRecodes('接口回调收到通知参数',$this->resHandler->getAllParameters());
              
                
            }else{
                echo 'failure';
                exit();
            }
        }else{
            echo 'failure';
        }

    }

    

    /**
     * 支付宝退款回调
     * @return [type] [description]
     */
    public function refund_validation()
    {
        if (empty($_POST)) {
            $this->record_logs("回调！");
        } else {
            $pay = new \Think\Pay('alipay', C('alipay'));

            if ($pay->verifyNotify($_POST)) {
                //批次号
                $batch_no = $_POST['batch_no'];
                //批量退款数据中转账成功的笔数
                $success_num = $_POST['success_num'];
                if ($success_num > 0) {
                    $map['batch_no'] = $batch_no;
                    $date['tui_status'] = 1;
                    $date['tui_time'] = time();
                    M('refund_record', 'tab_')->where($map)->save($date);
                    file_put_contents(dirname(__FILE__)."/as.txt", json_encode(M('refund_record','tab_')->getlastsql()));
                   
                    $map_spend['pay_order_number'] = get_refund_pay_order_number($batch_no);
                    $spen_date['sub_status']=1;
                    $spen_date['settle_check']= 1;
                    M('spend','tab_')->where($map_spend)->save($spen_date);
                }
                echo "success";        //请不要修改或删除

            } else {
                //验证失败
                echo "fail";
            }
        }
    }

    /**
     *微信回调
     */

    public function wxpay_callback()
    {

        $values = array();

        Vendor("WxPayPubHelper.WxPayPubHelper");

        $weixin = A("WeiXin", "Event");

        $request = file_get_contents("php://input");

        $reqdata = $weixin->xmlstr_to_array($request);

        if ($reqdata['return_code'] != 'SUCCESS') {

            $this->record_logs("return_code返回数据错误");
            exit();

        } else {

            if ($_REQUEST['method'] == "notify2") {//sdk

                $Common_util_pub = new \Common_util_pub(C('wei_xin_app.email'), C('wei_xin_app.partner'), C('wei_xin_app.key'));

            } elseif ($_REQUEST['method'] == "notify3") { //app

                $Common_util_pub = new \Common_util_pub(C('wei_xin_apps.email'), C('wei_xin_apps.partner'), C('wei_xin_apps.key'));

            } elseif ($_REQUEST['method'] == "notify") {//扫码

                $Common_util_pub = new \Common_util_pub(C('wei_xin.email'), C('wei_xin.partner'), C('wei_xin.key'));

            }

            if ($Common_util_pub->getSign($reqdata) == $reqdata['sign']) {

                $pay_where = substr($reqdata['out_trade_no'], 0, 2);

                $data['trade_no'] = $reqdata['transaction_id'];

                $data['out_trade_no'] = $reqdata['out_trade_no'];

                if (C('UC_SET') == 1) {

                    $uc = new Ucservice();

                    $uc_id = $uc->uc_rechange_notify($reqdata['out_trade_no'], 1);

                }

                switch ($pay_where) {

                    case 'SP'://充值游戏

                        if ($this->recharge_is_exist($reqdata['out_trade_no'])) {

                            echo " <xml> <return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                            exit();

                        }

                        $result = $this->set_spend($data);

                        if ($result) {

                            echo " <xml> <return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                        } else {

                            echo " <xml> <return_code><![CDATA[FAILURE]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                        }

                        break;

                    case 'PF'://充值平台币

                        if ($this->deposit_is_exist($reqdata["out_trade_no"])) {

                            echo " <xml> <return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                            exit();

                        }

                        $result = $this->set_deposit($data);

                        if ($result) {

                            echo " <xml> <return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                        } else {

                            echo " <xml> <return_code><![CDATA[FAILURE]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                        }

                        break;

                    case 'AG'://代充

                        if ($this->agent_is_exist($reqdata["out_trade_no"])) {

                            echo " <xml> <return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                            exit();

                        }

                        $result = $this->set_agent($data);

                        if ($result) {

                            echo " <xml> <return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                        } else {

                            echo " <xml> <return_code><![CDATA[FAILURE]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";

                        }

                        break;


                    default:

                        $this->record_logs("订单号错误！！");

                        break;

                }

            } else {

                $this->record_logs("支付验证失败");

                redirect('http://' . $_SERVER['HTTP_HOST'] . '/front.php/Recharge/index.html', 3, '支付验证失败');

            }

        }

        $this->wite_text(json_encode($reqdata), dirname(__FILE__) . "/notify.txt");

    }


    /**
     * 竣付通回调
     * @return [type] [description]
     */
    public function jft_callback(){
        @$p7_paychannelnum=$_POST['p7_paychannelnum'];
           if(empty($p7_paychannelnum))
           {
              $p7_paychannelnum="";
            }
        $signmsg=C('jft.key');//支付秘钥
        @$md5info_paramet = $_REQUEST['p1_usercode']."&".$_REQUEST['p2_order']."&".$_REQUEST['p3_money']."&".$_REQUEST['p4_status']."&".$_REQUEST['p5_jtpayorder']."&".$_REQUEST['p6_paymethod']."&".$_REQUEST['p7_paychannelnum']."&".$_REQUEST['p8_charset']."&".$_REQUEST['p9_signtype']."&".$signmsg;
        $md5info_tem= strtoupper(md5($md5info_paramet));
        $requestsign=$_REQUEST['p10_sign'];
                if ($md5info_tem == $_REQUEST['p10_sign'])
                {
                    $order_info['trade_no'] = $_REQUEST['p5_jtpayorder'];
                    $order_info['out_trade_no'] = $_REQUEST['p2_order'];
                    $pay_where = substr($_REQUEST['p2_order'], 0, 2);
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
                        exit('accident order data');
                        break;
                }

                    //改变订单状态，及其他业务修改
                   echo "success";  
                   //接收通知后必须输出”success“代表接收成功。

                 }else{
                    $this->record_logs("竣付通验证失败！！");
                 }

    }

    public function heepay_callback()
    {

        $result = $_GET['result'];

        $pay_message = $_GET['pay_message'];

        $agent_id = $_GET['agent_id'];

        $jnet_bill_no = $_GET['jnet_bill_no'];

        $agent_bill_id = $_GET['agent_bill_id'];

        $pay_type = $_GET['pay_type'];

        $pay_amt = $_GET['pay_amt'];

        $remark = $_GET['remark'];

        $return_sign = $_GET['sign'];


        $remark = iconv("GB2312", "UTF-8//IGNORE", urldecode($remark));//签名验证中的中文采用UTF-8编码;


        $signStr = '';

        $signStr = $signStr . 'result=' . $result;

        $signStr = $signStr . '&agent_id=' . $agent_id;

        $signStr = $signStr . '&jnet_bill_no=' . $jnet_bill_no;

        $signStr = $signStr . '&agent_bill_id=' . $agent_bill_id;

        $signStr = $signStr . '&pay_type=' . $pay_type;


        $signStr = $signStr . '&pay_amt=' . $pay_amt;

        $signStr = $signStr . '&remark=' . $remark;


        $signStr = $signStr . '&key=' . SIGN_KEY; //商户签名密钥


        $sign = '';

        $sign = strtolower(md5($signStr));

        if ($sign == $return_sign) {   //比较签名密钥结果是否一致，一致则保证了数据的一致性

            echo 'ok';

            //商户自行处理自己的业务逻辑

            $pay_where = substr($agent_bill_id, 0, 2);

            $data['trade_no'] = $reqdata['jnet_bill_no'];

            $data['out_trade_no'] = $reqdata['agent_bill_id'];

            switch ($pay_where) {

                case 'SP':

                    $result = $this->set_spend($data);

                    break;

                case 'PF':

                    $result = $this->set_deposit($data);

                    break;

                case 'AG':

                    $result = $this->set_agent($data);

                    break;

                default:

                    exit('accident order data');

                    break;

            }

        } else {

            echo 'error';

            //商户自行处理，可通过查询接口更新订单状态，也可以通过商户后台自行补发通知，或者反馈运营人工补发

        }


    }


    /**
     *判断平台币充值是否存在
     */

    protected function deposit_is_exist($out_trade_no)
    {

        $deposit = M('deposit', 'tab_');

        $map['pay_status'] = 1;

        $map['pay_order_number'] = $out_trade_no;

        $res = $deposit->where($map)->find();

        if (empty($res)) {

            return false;

        } else {

            return true;

        }

    }


    //判断充值是否存在

    public function recharge_is_exist($out_trade_no)
    {

        $recharge = M('spend', 'tab_');

        $map['pay_status'] = 1;

        $map['pay_order_number'] = $out_trade_no;

        $res = $recharge->where($map)->find();

        if (empty($res)) {

            return false;

        } else {

            return true;

        }

    }


    //判断代充是否存在

    public function agent_is_exist($out_trade_no)
    {

        $recharge = M('agent', 'tab_');

        $map['pay_status'] = 1;

        $map['pay_order_number'] = $out_trade_no;

        $res = $recharge->where($map)->find();

        if (empty($res)) {

            return false;

        } else {

            return true;

        }

    }


    function wite_text($txt, $name)
    {

        $myfile = fopen($name, "w") or die("Unable to open file!");

        fwrite($myfile, $txt);

        fclose($myfile);

    }

}