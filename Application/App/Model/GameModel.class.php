<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/28
 * Time: 9:03
 */
namespace App\Model;

class GameModel extends BaseModel{
	const ORDER_RECOMMEND = 1; //推荐
	const ORDER_HOT = 2;        //热门
	const ORDER_NEWEST = 3;     //最新
	const ORDER_DOWN_NUM = 4;   //下载量
	const ANDROID = 1;
	const IOS = 2;
	const DOWN_OFF = 0;
	const DOWN_ON = 1;
	const COLLECT = 1;
	const COLLECT_CANCEL = 2;

	/**
	 * 游戏列表
	 * @param string $map
	 * @param string $order
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getGameLists($map="",$order="sort desc",$p=0){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$map['g.game_status'] = 1;
		$map['g.apply_status'] = 1;
		$promote_id = empty(PROMOTE_ID) ? 0 : PROMOTE_ID;
		$rebate_join = "and (r.promote_id = {$promote_id} or r.promote_id = -1)";
		$time = NOW_TIME;
		$data = $this->table('tab_game as g')
			->field('g.id,g.game_name,g.icon,g.dow_num,g.marking,g.game_size,g.game_score,g.dow_status,g.features,g.bind_recharge_discount as discount,s.pack_name,IFNULL(r.ratio,0) as ratio')
			//游戏原包
			->join("left join tab_game_source as s on s.game_id = g.id")
			//返利
			->join("left join tab_rebate r on r.game_id = g.id  {$rebate_join} and r.starttime < {$time} and (endtime = 0 or endtime > {$time})")
			->where($map)
			->page($page, $row)
			->order($order)
			->group("g.id")
			->select();
		foreach ($data as $key => $val){
			$data[$key]['icon'] = get_img_url($val['icon']);
			$data[$key]['game_score'] = round($val['game_score'] / 2);
			if($val['dow_status'] == self::DOWN_OFF){
				$data[$key]['down_url'] = "";
			}else{
				$data[$key]['down_url'] = $this::generateDownUrl($val['id']);
			}
		}
		return $data;
	}

	/**
	 * 游戏详情
	 * @param $id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getGameDetail($id){
		$map['id'] = $id;
		$data = $this->field('id,game_name,icon,dow_num,game_size,game_score,screenshot,features,introduction,bind_recharge_discount as discount')->where($map)->find();
		if(empty($data)){
			return $data;
		}
		//原包包名
		$pack = $this->table("tab_game_source")->where(['game_id'=>$data['game_id']])->find();
		$data['pack_name'] = $pack['pack_name'];
		//返利
		$time = NOW_TIME;
		$rebate = $this->field("ratio")->table("tab_rebate")->where("game_id = {$id} and promote_id = -1 and starttime < {$time} and endtime = 0 or endtime > {$time}")->find();
		$data['ratio'] = empty($rebate['ratio']) ? 0 : $rebate['ratio'];
		//是否收藏
		$data['is_collect'] = self::COLLECT_CANCEL;
		if(defined("USER_ACCOUNT")){
			$collect = M("collect_game",'tab_')->where(["account"=>USER_ACCOUNT,'status'=>self::COLLECT,"game_id"=>$id])->find();
			empty($collect) || $data['is_collect'] = self::COLLECT;
		}

		$screenshot = str2arr($data['screenshot'],',');
		$data['icon'] = get_img_url($data['icon']);
		$data['game_score'] = round($data['game_score'] / 2);
		$data['screenshot'] = array_map("get_img_url",$screenshot);//截图转URL
		!empty($data['discount']) || $data['discount'] = 10;    //未设置折扣则返回10折
		return $data;
	}

	/**
	 * 生成游戏下载链接
	 * @param $game_id
	 * @return string
	 * author: xmy 280564871@qq.com
	 */
	public static function generateDownUrl($game_id){
		$url = U('Down/down',['game_id'=>$game_id,'promote_id'=>PROMOTE_ID],'',true);
		return $url;
	}

	/**
	 * 游戏下载信息
	 * @param $game_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getGameDownInfo($game_id){
		$map['id'] = $game_id;
		$map['apply_status'] = 1;
		$map['game_status'] = 1;
		$data['game_info'] = $this->field('id,game_name,add_game_address,ios_game_address,sdk_version')->where(['id'=>$game_id])->find();
		$data['packet'] = M("game_source",'tab_')->where(['game_id'=>$game_id])->find();
		return $data;
	}

	/**
	 * 增加下载次数
	 * @param $game_id
	 * author: xmy 280564871@qq.com
	 */
	public function addGameDownNum($game_id){
		$map['id'] = $game_id;
		$this->where($map)->setInc('dow_num');
	}


	/**
	 * 游戏收藏
	 * @param $game_id
	 * @param $account
	 * @param $status   1 收藏 2 取消收藏
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function collectGame($game_id,$account,$status){
		$data['game_id'] = $game_id;
		$data['account'] = $account;
		$collect = M("collect_game",'tab_')->where($data)->find();
		if(empty($collect)){
			$data['create_time'] = time();
			$data['status'] = $status;
			$result = M("collect_game",'tab_')->add($data);
		}else{
			$result = M("collect_game",'tab_')->where($data)->setField(['status'=>$status]);
		}
		return $result;
	}

	/**
	 * 我的游戏收藏
	 * @param $account
	 * @param $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getMyCollectGame($account,$p){
		$map['account'] = $account;
		$map['status'] = self::COLLECT;
		$collect = M("collect_game",'tab_')->field("game_id")->where($map)->select();
		if(empty($collect)){
			return $collect;
		}
		$game_map['g.id'] = ['in',array_column($collect,"game_id")];
		$data = $this->getGameLists($game_map,'',$p);
		return $data;
	}

	/**
	 * 获取绑币充值折扣
	 * @param $game_id
	 * @return int
	 * author: xmy 280564871@qq.com
	 */
	public function getBindRechargeDiscount($game_id){
		$data = $this->field("bind_recharge_discount")->find($game_id);
		$discount = empty($data['bind_recharge_discount']) ? 10 : $data['bind_recharge_discount'];
		return $discount;
	}

	/**
	 * 游戏列表
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getLists($map=""){
		$map['game_status'] = 1;
		$map['apply_status'] = 1;
		return $this->field("id,game_name")->where($map)->select();
	}

	/**
	 * 绑币充值游戏列表
	 * @param $user_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getUserRechargeGame($user_id){
		$map['user_id'] = $user_id;
		$data = M("user_play","tab_")->alias("p")->field("p.game_name,p.game_id")
			->join("right join tab_game g on g.id = p.game_id")
			->where($map)->where('g.game_status=1')->select();
		return $data;
	}
}