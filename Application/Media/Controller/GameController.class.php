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
class GameController extends BaseController {

	public function game_center($game_type=0,$p=0){
		$this->assign('sys',$_REQUEST['sdk_version']);
		$this->assign('gt',$_REQUEST['game_type']);
		$this->assign('t',$_REQUEST['theme']);
		$this->assign('gn',$_REQUEST['game_name']);
		$map['game_status'] = 1;
		$map['apply_status'] = 1;
		empty($_REQUEST['game_type']) ?  "":$map['game_type_id'] = $_REQUEST['game_type'];
		empty($_REQUEST['sdk_version']) ?  "":$map['sdk_version'] = $_REQUEST['sdk_version'];
		empty($_REQUEST['theme']) ?  "":$map['short'] = array('like',$_REQUEST['theme'].'%');
		empty($_REQUEST['game_name'])? "":$map['game_name'] = array('like','%'.trim($_REQUEST['game_name']).'%');
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$game  = M("Game","tab_");
		$row = 12;
		$data  = $game->where($map)->order("sort desc")->group("relation_game_id")->page($page, $row)->select();
		$count = count($game->where($map)->order("sort desc")->group("relation_game_id")->select());
		//分页
		if($count > $row){
			$page = new \Think\Page($count, $row);
			$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
			$this->assign('_page', $page->show());
		}
		unset($map['sdk_version']);
		$data=game_merge($data,$map);
		$this->assign("count",$count);
		$this->assign('list_data', $data);
		$this->display();
	}

	public function gift_list($game_id=0,$p=0){
		$map['status'] = 1; 
		if($game_id !=0){ $map['game_id'] = $game_id; }
		empty($_REQUEST['search_key'])? "":$map['tab_game.game_name'] = array('like','%'.$_REQUEST['search_key'].'%') ;
		//$map['game_type'] = $game_type==0?array("in",'1,2,3,4,5,6,7,8,9,10'):$game_type;
		$model = array(
			'm_name' => 'Giftbag',
			'prefix' => 'tab_',
			'field' =>array('tab_giftbag.id,giftbag_name,tab_giftbag.game_name,desribe,start_time,end_time,icon,game_id'),
			'join' =>'tab_game ON tab_giftbag.game_id = tab_game.id',
			'map' => $map,
			'order' => 'id desc',
			'template_list' => 'Game/gift_list'
		);
		parent::join_list($model,$p);
	}

	/* 文档模型详情页 */
	public function game_detail($id = 0, $p = 1){
		/* 获取详细信息 */
		$game = new GameModel();
		$info = $game->detail($id);
		if(!$info){
			$this->error($game->getError());
		}
		$tmpl = 'Game/game_detail';

		//$gift_list=get_gift_list('all',7);
        //$gift_like=shuffle_assoc($gift_list);
        
    $hotgame = $game->field('id,game_name,icon,game_type_name,game_type_id,game_size')->order('id desc')->limit(10)->where(array('game_status'=>1,'recommend_status'=>2))->select(); 

    
        $this->assign('gamesou',get_game_source($info['and_id'],$info['ios_id']));
		$this->assign('data', $info);
		//$this->assign('gift_like', $gift_like);
		$this->assign('hotgame', $hotgame);
		$this->assign('page', $p); //页码
		$this->display($tmpl);
	}

	/* 文档分类检测 */	
	private function category($id = 0){
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			$this->error('没有指定文档分类！');
		}

		/* 获取分类信息 */
		$category = D('Category')->info($id);
		if($category && 1 == $category['status']){
			switch ($category['display']) {
				case 0:
					$this->error('该分类禁止显示！');
					break;
				//TODO: 更多分类显示状态判断
				default:
					return $category;
			}
		} else {
			$this->error('分类不存在或被禁用！');
		}
	}

	public function dow_url_generate($game_id=null){
		$url = "http://".$_SERVER['SERVER_NAME']."/media.php?s=/Down/down_file/game_id/".$game_id."/type/1.html";//
		$qrcode = $this->qrcode($url);
		return $qrcode;
	}
  /**
   * 根据地址生成二维码
   * @author 鹿文学
   * @date 2017年8月30日
   */
  public function dow_url($url='') {
      $url = base64_decode($url);
      $qrcode = $this->qrcode($url);
      return $qrcode;
  }
	public function qrcode($url='pc.vlcms.com',$level=3,$size=4){
		Vendor('phpqrcode.phpqrcode');
		$errorCorrectionLevel =intval($level) ;//容错级别 
		$matrixPointSize = intval($size);//生成图片大小 
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
