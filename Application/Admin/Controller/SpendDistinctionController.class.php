<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class SpendDistinctionController extends ThinkController {
	const model_name = 'SpendDistinction';
    public function lists(){
    	if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['pay_order_number'])){
        	$map['pay_order_number']=array('like','%'.trim($_REQUEST['pay_order_number']).'%');
        	unset($_REQUEST['pay_order_number']);
        }
        $map['order']='create_time DESC';
    	parent::lists(self::model_name,$_GET["p"],$map);
    }
}