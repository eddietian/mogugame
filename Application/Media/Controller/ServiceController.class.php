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

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ServiceController extends BaseController {

	public function index($value=''){
		$yidata=M('Kefuquestion')
			->field('title,titleurl')
			->where(array('state'=>1,'title'=>array('neq','常见问题')))
			->group('title')
			->select();
		$erdata=M('Kefuquestion')
			->where(array('state'=>1,'istitle'=>2,'title'=>'常见问题'))
			->select();
		// foreach ($data as $key => $value) {
		// 	if($value['istitle']==1){
		// 		$yidata[$key]=$data[$key];
		// 	}elseif($value['istitle']==2){
		// 		$erdata[$key]=$data[$key];
		// 	}
		// }
		$oftendata=M('Kefuquestion')
			->where(array('state'=>1,'istitle'=>2,'title'=>array('eq','常见问题')))
			// ->group('title')
			->select();
			// var_dump($oftendata);exit;
		$this->assign('oftendata',$oftendata);
		$this->assign('yidata',$yidata);
		$this->assign('erdata',$erdata);
		$this->display();
	}
	public function detail(){
		$yidata=M('Kefuquestion')
			->field('title,titleurl')
			->where(array('state'=>1,'istitle'=>1))
			->group('title')
			->order('id asc')
			->select();
		if(I('kefu')!=''){
			$map1['state']=1;
			$map1['istitle']=2;
			$map1['titleurl']=I('kefu');
			if($_REQUEST['para']!=''){
				$map1['id']=$_REQUEST['para'];
			}
			$erdata=M('Kefuquestion')
			->where($map1)
			->select();
			$title=M('Kefuquestion')
				->where(array('titleurl'=>I('kefu')))
				->find();
			$this->assign('title',$title);
		}
		$this->assign('yidata',$yidata);
		$this->assign('erdata',$erdata);	
		$this->display();
	}
	public function uploadAvatar() {
		//dump($_FILES);
		// 成功  $this->ajaxReturn(array('state'=>'SUCCESS','url'=>'图片地址'),C('DEFAULT_AJAX_RETURN'));
		$this->ajaxReturn(array('state'=>'上传失败'),C('DEFAULT_AJAX_RETURN'));
	}
	
	public function sask() {
		if(IS_POST) {
			
		}
		
		$this->display();
	}
	
	public function sask2() {
		if(IS_POST) {
			
		}
		
		$this->display();
	}
	
	public function sask3() {
		if(IS_POST) {
			
		}
		
		$this->display();
	}
	
	public function spwd($p=1) {
		/* $model["model"] = "Service";
		$model['where']="sMark='spwd' and sPlatMark='".C('PLATMARK')."'";
        parent::pagelists($model,$p);
		$this->assign('spage','spwd'); */
		
		$this->display();
	}
	
	public function spay($p=1) {
		/* $model["model"] = "Service";
		$model['where']="sMark='spay' and sPlatMark='".C('PLATMARK')."'";
        parent::pagelists($model,$p);
		$this->assign('spage','spay'); */
		
		$this->display();
	}
	
	public function saccont($p=1) {
		/* $model["model"] = "Service";
        $model['where']="sMark='saccont' and sPlatMark='".C('PLATMARK')."'";
        parent::pagelists($model,$p);
		$this->assign('spage','saccont'); */
		
		$this->display();
	}
	
	public function sgift($p=1) {
		/* $model["model"] = "Service";
        $model['where']="sMark='sgift' and sPlatMark='".C('PLATMARK')."'";
        parent::pagelists($model,$p);
		$this->assign('spage','sgift'); */
		
		$this->display();
	}
	
	public function sother($p=1) {
		/* $model["model"] = "Service";
        $model['where']="sMark='sother' and sPlatMark='".C('PLATMARK')."'";
        parent::pagelists($model,$p);
		$this->assign('spage','sother'); */
		
		$this->display();
	}

}
