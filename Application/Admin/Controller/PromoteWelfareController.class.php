<?php

namespace Admin\Controller;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PromoteWelfareController extends ThinkController {

	const model_name = 'PromoteWelfare';
    public function lists(){
        /*if(isset($_REQUEST['show_status'])){
            $extend['show_status']=$_REQUEST['show_status'];
            unset($_REQUEST['show_status']);
        }
        if(isset($_REQUEST['server_version'])){
            $extend['server_version']=$_REQUEST['server_version'];
            unset($_REQUEST['server_version']);
        }
        if(isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])){
            $extend['start_time']  =  array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start']) && isset($_REQUEST['end'])){
            $extend['start_time']  =  array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }*/
        $extend = '';
        if(isset($_REQUEST['promote_account']) && $_REQUEST['promote_account']!=''){
            $extend['promote_id']  =  $_REQUEST['promote_account'];
            unset($_REQUEST['promote_account']);
        }
        
        empty(I('game_id')) || $extend['game_id'] = I('game_id');
    	parent::order_lists(self::model_name,$_GET["p"],$extend);
    }

    public function add(){
    	$model = M('Model')->getByName(self::model_name);
    	parent::add($model["id"]);
    }

    public function edit($id=0){
		$id || $this->error('请选择要编辑的用户！');
		$model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
		parent::edit($model['id'],$id);
    }

    public function del($model = null, $ids=null){
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }

}
