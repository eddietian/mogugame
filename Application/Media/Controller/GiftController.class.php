<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Media\Controller;
/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class GiftController extends BaseController {

	public function __construct() {
		parent::__construct();
		$nav = array("navname" => "礼包中心", "navlink" => "/media.php?s=/Gift/gift.html");
		array_push($this->_NAV_PARAMS, $nav);
		$this->assign('navparams', $this->_NAV_PARAMS);
	}

	public function gift($game_id=0,$p=0){
		$map['status'] = 1; 
		$map['end_time']=array(array('gt',time()),array('eq',0),'or');
		if($game_id !=0){ $map['game_id'] = $game_id; }
		empty($_REQUEST['search_key'])? "":$map['tab_game.game_name'] = array('like','%'.$_REQUEST['search_key'].'%') ;
		empty($_REQUEST['game_name'])? "":$map['tab_giftbag.game_name'] = array('like','%'.$_REQUEST['game_name'].'%') ;
		//$map['game_type'] = $game_type==0?array("in",'1,2,3,4,5,6,7,8,9,10'):$game_type;
		$model = array(
			'm_name' => 'Giftbag',
			'prefix' => 'tab_',
			'field' =>array('tab_giftbag.id,relation_game_name,giftbag_name,tab_giftbag.game_name,desribe,start_time,giftbag_version,digest,novice,end_time,icon,game_id'),
			'join' =>'tab_game ON tab_giftbag.game_id = tab_game.id',
			'map' => $map,
			'order' => 'tab_giftbag.sort desc',
			'template_list' => 'Game/gift_list'
		);
		$sc=new IndexController();
		$sc->area();
		$sc->newgame(3);
		parent::join_list($model,$p);
	}
	function gift_detail(){
		header("Content-type: text/html; charset=utf-8");
		$gid=base64_decode($_REQUEST['gid']);
		// $gid=111;
		if(!is_numeric($gid)){
			echo "<script type='text/javascript'>alert('游戏数据不合法');history.go(-1);</script>";exit;
		}else{
			$map['game_status']=1;
	        $map['end_time']=array(array('gt',time()),array('eq',0),'or');
	        $map['tab_giftbag.id']=$gid;
	    	$model = array(
	    		'm_name'=>'Giftbag',
	    		'prefix'=>'tab_',
	    		'field' =>'tab_giftbag.id as gift_id,tab_giftbag.start_time,tab_giftbag.end_time,relation_game_name,relation_game_id,game_id,tab_giftbag.game_name,digest,desribe,giftbag_name,giftbag_version,tab_game.icon,server_name,tab_game.features,tab_game.game_type_name,tab_giftbag.create_time',
	    		'join'	=>'tab_game on tab_giftbag.game_id = tab_game.id',
	    		'map'   =>$map,
	    	);
	    	$gift = parent::join_data($model);
	    	if(!$gift){
	    		echo "<script type='text/javascript'>alert('游戏数据不合法');history.go(-1);</script>";exit;
	    	}
	        foreach ($gift as $key => $value) {
	            $gift[$key]['gift_num']=gift_recorded($value['game_id'],$value['gift_id']);
	        }
	        $gift=reset($gift);
	    	$this->assign('gift',$gift);
	    	$gift_list=get_gift_list('all');
	        $gift_keys=array_rand($gift_list,3);
	        foreach ($gift_keys as $val) {
	            $gift_like[]=$gift_list[$val];
	        }
			$this->assign('gift_like', $gift_like);
		}
		$this->display();
	}
	public function dow_url_generate($game_id=null){
		$url = "http://".$_SERVER['SERVER_NAME']."/media.php?s=/Down/down_file/game_id/".$game_id."/type/1.html";//
		$qrcode = $this->qrcode(base64_encode($url));
		return $qrcode;
	}
	public function qrcode($url='pc.vlcms.com',$level=3,$size=4){
		Vendor('phpqrcode.phpqrcode');
		$errorCorrectionLevel =intval($level) ;//容错级别 
		$matrixPointSize = intval($size);//生成图片大小 
		$url = base64_decode($url);
		//生成二维码图片 
		ob_clean();
		$object = new \QRcode();
		echo $object->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
	}

	public function error_mesg(){
		$mid = session('member_auth.mid');
		if(empty($mid)){
			$this->ajaxReturn(array('msg'=>'no-login'));
		}

		$model = M('message','tab_');
		$map['game_id'] = $_REQUEST['game_id'];
		$map['user_id'] = session('member_auth.mid');
		$d = $model->where($map)->find();
		if(!empty($d)){
			$this->ajaxReturn(array('msg'=>'no'));
		}

		
		$data['game_id'] = $_REQUEST['game_id'];
		//$data['game_name'] = $_REQUEST['game_game'];
		$data['user_id'] = session('member_auth.mid');
		$data['title'] = "游戏无法下载";
		$data['content'] = "";
		$data['status'] = 0;
		$data['type'] = 0;
		$data['create_time'] = NOW_TIME;
		if($model->add($data)){
			$this->ajaxReturn(array('msg'=>'ok'));
		}
	}

}
