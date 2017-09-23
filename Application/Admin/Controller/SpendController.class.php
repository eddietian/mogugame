<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class SpendController extends ThinkController {
	const model_name = 'Spend';
    public function lists(){
    	if(isset($_REQUEST['user_account'])){
    		$map['user_account']=array('like','%'.trim($_REQUEST['user_account']).'%');
    		unset($_REQUEST['user_account']);
    	}
        if(isset($_REQUEST['spend_ip'])){
            $map['spend_ip']=array('like','%'.trim($_REQUEST['spend_ip']).'%');
            unset($_REQUEST['spend_ip']);
        }
    	if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['pay_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['pay_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
            	$map['game_name']=$_REQUEST['game_name'];
            	unset($_REQUEST['game_name']);
            }
        }
        if(isset($_REQUEST['pay_order_number'])){
        	$map['pay_order_number']=array('like','%'.trim($_REQUEST['pay_order_number']).'%');
        	unset($_REQUEST['pay_order_number']);
        }
        if(isset($_REQUEST['pay_status'])){
            $map['pay_status']=$_REQUEST['pay_status'];
            unset($_REQUEST['pay_status']);
        }
        if(isset($_REQUEST['pay_way'])){
            $map['pay_way']=$_REQUEST['pay_way'];
            unset($_REQUEST['pay_way']);
        }
        if(isset($_REQUEST['pay_game_status'])){
            $map['pay_game_status']=$_REQUEST['pay_game_status'];
            unset($_REQUEST['pay_game_status']);
        }
        $map['order']='pay_time DESC';
        $map1=$map;
        $map1['pay_status']=1;
        // $dtotal=null_to_0(D(self::model_name)->where($map1)->page($_GET["p"], 10)->order($map['order'])->sum('pay_amount'));
        // var_dump(D(self::model_name)->getlastsql());exit;
        $total=null_to_0(D(self::model_name)->where($map1)->sum('pay_amount'));
        $ttotal=null_to_0(D(self::model_name)->where('pay_time'.total(1))->where(array('pay_status'=>1))->sum('pay_amount'));
        $ytotal=null_to_0(D(self::model_name)->where('pay_time'.total(5))->where(array('pay_status'=>1))->sum('pay_amount'));
        // var_dump(D(self::model_name)->getlastsql());exit;  
        // $this->assign('dtotal',$dtotal);
        $this->assign('total',$total);
        $this->assign('ttotal',$ttotal);
        $this->assign('ytotal',$ytotal);
    	parent::order_lists(self::model_name,$_GET["p"],$map);
    }
}