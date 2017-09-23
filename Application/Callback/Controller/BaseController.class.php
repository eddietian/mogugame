<?php

namespace Callback\Controller;
use Think\Controller;
use Common\Api\GameApi;
use Org\UcenterSDK\Ucservice;
/**
 * 支付回调控制器
 * @author 小纯洁 
 */
class BaseController extends Controller {
    protected function _initialize(){
           C(api('Config/lists'));
       }
    /**
    *充值到游戏成功后修改充值状态和设置游戏币
    */
    protected function set_spend($data){
        $spend = M('Spend',"tab_");
        $map['pay_order_number'] = $data['out_trade_no'];
        $d = $spend->where($map)->find();
        if(empty($d)){$this->record_logs("数据异常");return false;}
        if($d['pay_status'] == 0){
            $data_save['pay_status'] = 1;
            $data_save['order_number'] = $data['trade_no'];
            $map_s['pay_order_number'] = $data['out_trade_no']; 
            $r = $spend->where($map_s)->save($data_save);
            $this->set_ratio($d['pay_order_number']);
            //APP邀请好友消费奖励平台币
	        D("App/ShareRecord")->inviteFriendAward($d['user_id'],$d['pay_amount'],$data['out_trade_no']);
	        //充值奖励积分
	        D("App/PointRecord")->rechargeAwardPoint($d['user_id'],$d['pay_amount']);

            if($r!== false){
                $game = new GameApi();
                $game->game_pay_notify($data,1);
                return true;
            }else{
                $this->record_logs("修改数据失败");
            }
        }
        else{
            return true;
        }
    }

    /**
    *充值平台币成功后的设置
    */
    protected function set_deposit($data){
        $deposit = M('deposit',"tab_");
        $map['pay_order_number'] = $data['out_trade_no'];
        $d = $deposit->where($map)->find();
        if(empty($d)&&C('UC_SET') == 1){
            $uc = new Ucservice();
            $extends = $uc->uc_deposit_find($data['out_trade_no']);//是否在uc找到订单
            if(empty($extends)){
                return false;
            }else{
                if($extends['pay_status']){
                    $res2 = 0;
                }else{
                    $res2 = $uc->uc_deposit_notify($data['out_trade_no'],1);
                    if($res2){
                        $extends['pay_status']=1;
                    }
                }
                if($res2==0){
                    return false;
                }
                $sqltype = 2;
                $user_data = M('User','tab_',C('DB_CONFIG2'))->where(array('account'=>$extends['user_account']))->find();
                if($user_data==''){
                    $sqltype = 3;
                    $user_data = M('User','tab_',C('DB_CONFIG3'))->where(array('account'=>$extends['user_account']))->find();
                }
                if($user_data){
                    if($sqltype==2){
                        M('User','tab_',C('DB_CONFIG2'))->where(array('account'=>$extends['user_account']))->setInc("balance",$extends['pay_amount']);
                        M('User','tab_',C('DB_CONFIG2'))->where(array('account'=>$extends['user_account']))->setInc("cumulative",$extends['pay_amount']); 
                    }else{
                        M('User','tab_',C('DB_CONFIG3'))->where(array('account'=>$extends['user_account']))->setInc("balance",$extends['pay_amount']);
                        M('User','tab_',C('DB_CONFIG3'))->where(array('account'=>$extends['user_account']))->setInc("cumulative",$extends['pay_amount']); 
                    }
                    return true; 
                }else{
                    $this->record_logs("uc用户来源已不存在");
                    return false;
                }
            }
        }else{
            if(empty($d)){return false;}
            if($d['pay_status'] == 0){
                $data_save['pay_status'] = 1;
                $data_save['order_number'] = $data['trade_no'];
                $map_s['pay_order_number'] = $data['out_trade_no'];
                $r = $deposit->where($map_s)->save($data_save);
                if($r !== false){
                    $user = M("user","tab_");
                    $user->where("id=".$d['user_id'])->setInc("balance",$d['pay_amount']);
                    $user->where("id=".$d['user_id'])->setInc("cumulative",$d['pay_amount']);

	                //APP邀请好友消费奖励平台币
	                D("App/ShareRecord")->inviteFriendAward($d['user_id'],$d['pay_amount'],$data['out_trade_no']);
	                //充值奖励积分
	                D("App/PointRecord")->rechargeAwardPoint($d['user_id'],$d['pay_amount']);

                }else{
                    $this->record_logs("修改数据失败");
                }
                return true;
            }
            else{
                return true;
            }
        }
    }

    /**
    *设置代充数据信息
    */
    protected function set_agent($data){
        $agent = M("agent","tab_");
        $map['pay_order_number'] = $data['out_trade_no'];
        $d = $agent->where($map)->find();
        if(empty($d)){return false;}
        if($d['pay_status'] == 0){
            $data_save['pay_status'] = 1;
            $data_save['order_number'] = $data['trade_no'];
            $map_s['pay_order_number'] = $data['out_trade_no'];
            $r = $agent->where($map_s)->save($data_save);
            if($r!== false){
                $user = M("user_play","tab_");
                $map_play['user_id'] = $d['user_id'];
                $map_play['game_id'] = $d['game_id'];
                $res = $user->where($map_play)->setInc("bind_balance",$d['amount']);
            }else{
                $this->record_logs("修改数据失败");
            }
            return true;
        }
        else{
            return true;
        }
    }


	/**
	 * 绑币充值回调
	 * @param $data
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	protected function set_bind_recharge($data)
	{
		$bind_recharge = M("bind_recharge", "tab_");
		$map['pay_order_number'] = $data['out_trade_no'];
		$d = $bind_recharge->where($map)->find();
		if (empty($d)) {
			return false;
		}
		if ($d['pay_status'] == 0) {
			$data_save['pay_status'] = 1;
			$data_save['order_number'] = $data['trade_no'];
			$map_s['pay_order_number'] = $data['out_trade_no'];
			$r = $bind_recharge->where($map_s)->save($data_save);
			if ($r !== false) {
				$user = M("user_play", "tab_");
				$map_play['user_id'] = $d['user_id'];
				$map_play['game_id'] = $d['game_id'];
				$res = $user->where($map_play)->setInc("bind_balance", $d['amount']);

				//APP邀请好友消费奖励平台币
				D("App/ShareRecord")->inviteFriendAward($d['user_id'],$d['real_amount'],$data['out_trade_no']);
				//充值奖励积分
				D("App/PointRecord")->rechargeAwardPoint($d['user_id'],$d['real_amount']);
			} else {
				$this->record_logs("修改数据失败");
			}
			return true;
		} else {
			return true;
		}
	}

    /**
    *游戏返利
    */
    protected function set_ratio($data)
    {
        $map['pay_order_number']=$data;
        $spend=M("Spend","tab_")->where($map)->find();
        $reb_map['game_id']=$spend['game_id'];
        $time = time();
        $reb_map['starttime'] = ['lt',$time];
        $reb_map_str = "endtime > {$time} or endtime = 0";
        if($spend['promote_id'] == 0){
            $reb_map['promote_id'] = ['in','0,-1'];
        }else{
            $reb_map['promote_id'] = ['neq',0];
        }
//             wite_text(json_encode($reb_map).'\n',dirname(__FILE__)."/ss.txt");
        $rebate=M("Rebate","tab_")->where($reb_map)->where($reb_map_str)->find();
        if (!empty($rebate)) {
            if ($rebate['money'] > 0 && $rebate['status'] == 1) {
                if ($spend['pay_amount'] >= $rebate['money']) {
                    $this->compute($spend, $rebate);
                } else {
                    return false;
                }
            } else {
                $this->compute($spend, $rebate);
            }
        }else {
            return false;
        }
    }

    //计算返利
    protected function compute($spend,$rebate){
        $user_map['user_id']=$spend['user_id'];
        $user_map['game_id']=$spend['game_id'];            
        $bind_balance=$spend['pay_amount']*($rebate['ratio']/100);
        $spend['ratio']=$rebate['ratio'];
        $spend['ratio_amount']=$bind_balance;
        M("rebate_list","tab_")->add($this->add_rebate_list($spend));
        $re=M("UserPlay","tab_")->where($user_map)->setInc("bind_balance",$bind_balance);
        return $re;
    }
    /**
    *返利记录
    */
    protected function add_rebate_list($data){
        $add['pay_order_number']=$data['pay_order_number'];
        $add['game_id']=$data['game_id'];
        $add['game_name']=$data['game_name'];
        $add['user_id']=$data['user_id'];
        $add['user_name'] = $data['user_account'];
        $add['pay_amount']=$data['pay_amount'];
        $add['ratio']=$data['ratio'];
        $add['ratio_amount']=$data['ratio_amount'];
        $add['promote_id']=$data['promote_id'];
        $add['promote_name']=$data['promote_account'];
        $add['create_time']=time();
        return $add;
    }

    /**
    *日志记录
    */
    protected function record_logs($msg=""){
        \Think\Log::record($msg);
    }

    function wite_text($txt,$name){
        $myfile = fopen($name, "w") or die("Unable to open file!");
        fwrite($myfile, $txt);
        fclose($myfile);
    }
}