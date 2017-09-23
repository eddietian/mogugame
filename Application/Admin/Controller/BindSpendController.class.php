<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class BindSpendController extends ThinkController {
	const model_name = 'BindSpend';

    public function lists(){
    	if(isset($_REQUEST['user_account'])){
    		$map['user_account']=array('like','%'.trim($_REQUEST['user_account']).'%');
    		unset($_REQUEST['user_account']);
    	}
        if(isset($_REQUEST['pay_order_number'])){
            $map['pay_order_number']=array('like','%'.trim($_REQUEST['pay_order_number']).'%');
            unset($_REQUEST['pay_order_number']);
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
        	}
        	unset($_REQUEST['game_name']);
        }
        if(isset($_REQUEST['server_name'])){
            if($_REQUEST['server_name']=='全部'){
                unset($_REQUEST['server_name']);
            }else{
                $map['server_name']=$_REQUEST['server_name'];
                unset($_REQUEST['server_name']);
            }
        }
        "" == I('promote_id') || $map['promote_id'] = I('promote_id');
        $map1=$map;
        $map1['pay_status']=1;
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
