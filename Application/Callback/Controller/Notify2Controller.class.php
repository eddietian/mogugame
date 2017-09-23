<?php

namespace Callback\Controller;

use Org\UcenterSDK\Ucservice;


/**
 * 新版支付回调控制器
 * @author 小纯洁
 */
class Notify2Controller extends BaseController
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
            unset($notify['methodtype']);
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
        Vendor('Alipay.AopSdk');
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = file_get_contents("./Application/Sdk/SecretKey/alipay/alipay2_public_key.txt");
        $result = $aop->rsaCheckV1($notify,'','RSA2');

        if ($result) {
            //获取回调订单信息
            if (I('get.methodtype') == "notify") {
                $order_info = $notify;
                if($order_info['trade_status'] == 'TRADE_SUCCESS'){
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
                    echo "success";
                }else{
                    $this->record_logs("支付失败！");
                    echo "fail";
                }
            }elseif (I('get.methodtype') == "return") {
                    redirect('http://' . $_SERVER['HTTP_HOST'] . '/media.php');
            }
        } else {
            $this->record_logs("支付验证失败");
            redirect('http://' . $_SERVER['HTTP_HOST'] . '/media.php', 3, '支付验证失败');
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