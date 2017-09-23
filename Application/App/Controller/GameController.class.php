<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/28
 * Time: 9:01
 */
namespace App\Controller;

use App\Model\GameModel;

class GameController extends BaseController{

	/**
	 * 排行榜
	 * @param $p            分页
	 * @param $recommend    推荐状态    1 推荐 2 热门 3 最新 4 下载量
	 * @param $version      游戏版本 1 android 2 ios
	 * author: xmy 280564871@qq.com
	 */
	public function get_game_rank_lists($p=1,$recommend,$version){
		if(GameModel::ORDER_DOWN_NUM == $recommend){
			$order = "dow_num desc";
		}else{
			$order = "sort desc";
			$map['recommend_status'] = $recommend;
		}
		$map['g.sdk_version'] = $version;
		$data = D('Game')->getGameLists($map,$order,$p);
		if(empty($data)){
			$this->set_message(-1,"暂无数据");
		}else{
			$this->set_message(1,1,$data);
		}
	}

	/**
	 * 游戏详情
	 * @param $game_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function get_game_detail($game_id,$token=""){
		if(!empty($token)){
			$this->auth($token);
		}
		$data = D('Game')->getGameDetail($game_id);
		if (empty($data)){
			$this->set_message(-1,"游戏不存在");
		}
		$this->set_message(1,1,$data);
	}


	/**
	 * 获取游戏类型列表
	 * author: xmy 280564871@qq.com
	 */
	public function get_game_type_lists($name="",$version){
		empty($name) || $map['t.type_name'] = ["like","%".$name."%"];
		$data = D("GameType")->getGameTypeLists($map,$version);
		if(empty($data)){
			$this->set_message(-1,"游戏类型为空");
		}else{
			$this->set_message(1,1,$data);
		}
	}

	/**
	 * 根据游戏类型获取游戏列表
	 * @param $p
	 * @param $version 游戏版本 1 android 2 ios
	 * author: xmy 280564871@qq.com
	 */
	public function get_game_lists_by_type($type,$name="",$version,$p=1){
		$map['g.sdk_version'] = $version;
		$map['game_type_id'] = $type;
		empty($name) || $map['g.game_name'] = ['like',"%".$name."%"];
		$data = D('Game')->getGameLists($map,"sort desc",$p);
		if(empty($data)){
			$this->set_message(-1,"没有游戏");
		}else{
			$this->set_message(1,1,$data);
		}
	}

	/**
	 * 获取折扣游戏列表
	 * @param string $name
	 * @param int $p
	 * @param $version
	 * author: xmy 280564871@qq.com
	 */
	public function get_discount_game_lists($name="",$p=1,$version){
		empty($name) || $map['g.game_name'] = ['like','%'.$name.'%'];
		$map['bind_recharge_discount'] = [['gt',0],['lt',10]];
		$map['g.sdk_version'] = $version;
		$data = D('Game')->getGameLists($map,'sort desc',$p);
		if(empty($data)){
			$this->set_message(-1,"没有游戏");
		}else{
			$this->set_message(1,1,$data);
		}
	}

	/**
	 * 搜索游戏
	 * @param string $name
	 * @param int $p
	 * @param $version
	 * author: xmy 280564871@qq.com
	 */
	public function get_game_lists_by_name($name="",$p=1,$version){
		if(empty($name)){
			$this->set_message(-1,"游戏名称不能为空");
		}
		$map['g.game_name'] = ['like','%'.$name.'%'];
		$map['g.sdk_version'] = $version;
		$data = D('Game')->getGameLists($map,'sort desc',$p);
		if(empty($data)){
			$this->set_message(-1,"没有游戏");
		}else{
			$this->set_message(1,1,$data);
		}
	}

	/**
	 * 游戏收藏
	 * @param $game_id
	 * @param $token
	 * @param $status   1 收藏 2 取消收藏
	 * author: xmy 280564871@qq.com
	 */
	public function collect_game($game_id,$token,$status){
		$this->auth($token);
		$result = D("Game")->collectGame($game_id,USER_ACCOUNT,$status);
		if($result !== false){
			$this->set_message(1,"操作成功");
		}else{
			$this->set_message(-1,"操作失败");
		}
	}

	/**
	 * 我的游戏收藏
	 * @param $token
	 * @param int $p
	 * author: xmy 280564871@qq.com
	 */
	public function my_collect_game($token,$p=1){
		$this->auth($token);
		$data = D("Game")->getMyCollectGame(USER_ACCOUNT,$p);
		if(empty($data)){
			$this->set_message(-1,"未收藏游戏");
		}else{
			$this->set_message(1,1,$data);
		}
	}

	/**
	 * 获取绑币充值折扣
	 * @param $game_id
	 * author: xmy 280564871@qq.com
	 */
	public function get_game_recharge_discount($game_id){
		$discount = D("Game")->getBindRechargeDiscount($game_id);
		$this->set_message(1,1,$discount);
	}

	/**
	 * 获取游戏列表
	 * author: xmy 280564871@qq.com
	 */
	public function get_game_lists($version){
		$map['sdk_version'] = $version;
		$data = D("Game")->getLists($map);
		$this->set_message(1,1,$data);
	}

	/**
	 * 首页推荐游戏
	 * author: xmy 280564871@qq.com
	 */
	public function get_home_recommend($version){
		$map['g.sdk_version'] = $version;
		$model = new GameModel();
		$map['g.recommend_status'] = 1;
		$data['recommend'] = $model->getGameLists($map);
		$map['g.recommend_status'] = 2;
		$data['hot'] = $model->getGameLists($map);
		$map['g.recommend_status'] = 3;
		$data['new'] = $model->getGameLists($map);
		$data['down_num'] = $model->getGameLists(['g.sdk_version'=>$version],"dow_num desc");
		$this->set_message(1,1,$data);
	}

	/**
	 * 获取游戏分享信息
	 * @param $game_id
	 * author: xmy 280564871@qq.com
	 */
	public function get_share_info($game_id){
		$model = new GameModel();
		$game = $model->getGameDetail($game_id);
		if(empty($game_id)){
			$this->set_message(-1,"游戏不存在");
		}
		$result['title'] = $game['game_name'];
		$result['icon'] = $game['icon'];
		$result['content'] = $game['features'];
		$result['url'] = U('Game/share_game',['game_id'=>$game_id],true,true);
		$this->set_message(1,1,$result);
	}

	/**
	 * 分享游戏页面
	 * @param $game_id
	 * author: xmy 280564871@qq.com
	 */
	public function share_game($game_id){
		$model = new GameModel();
		$data = $model->getGameDetail($game_id);
		$this->assign("data",$data);
		$this->display();
	}

	/**
	 * 绑币充值游戏列表
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function get_user_recharge_game($token){
		$this->auth($token);
		$model = new GameModel();
		$user_id = get_user_id(USER_ACCOUNT);
		$data = $model->getUserRechargeGame($user_id);
		if (empty($data)){
			$this->set_message(-1,"游戏不存在");
		}
		$this->set_message(1,1,$data);
	}
}