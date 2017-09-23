<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/28
 * Time: 13:46
 */
namespace App\Model;

class GiftbagModel extends BaseModel{

	/**
	 * 礼包列表
	 * @param $game_id
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getGiftLists($game_id,$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 5;
		$map['game_id'] = $game_id;
		$map['status'] = 1;
		$time = NOW_TIME;
		$map['start_time'] = ['elt',$time];
		$map['_string'] = "end_time > {$time} or end_time = 0";
		$data = $this->field('id,giftbag_name,desribe,novice')->where($map)->page($page,$row)->select();
		$game = $this->table("tab_game")->find($game_id);
		$icon = get_img_url($game['icon']);
		if(empty($data)){
			return $data;
		}
		foreach ($data as $key=>$val){
			$novice_arr = str2arr($val['novice'],',');
			$novice_num = count(array_filter($novice_arr));
			$data[$key]['novice_num'] = $novice_num;
			$data[$key]['icon'] = $icon;
			unset($data[$key]['novice']);
		}
		return $data;
	}

	/**
	 * 礼包列表 礼包数量
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function giftListsNum($map="",$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 5;
		$map['status'] = 1;
		$time = NOW_TIME;
		$map['start_time'] = ['elt',$time];
		$map['_string'] = "end_time > {$time} or end_time = 0";
		$data = $this->field("count(tab_giftbag.id) as num,tab_giftbag.game_id,tab_giftbag.desribe,tab_giftbag.game_name,tab_giftbag.giftbag_name,g.icon")
			->join("left join tab_game g on g.id = tab_giftbag.game_id")
			->where($map)->page($page,$row)
			->group("game_id")
			->select();
		foreach ($data as $key => $val){
			$data[$key]['icon'] = get_img_url($val['icon']);
		}
		return $data;
	}


	/**
	 * 领取激活码
	 * @param $account
	 * @param $gift_id
	 * author: xmy 280564871@qq.com
	 */
	public function getNovice($account,$gift_id){
		$data = $this->find($gift_id);
		$novice_str = $data['novice'];
		$novice_arr = str2arr($novice_str,",");
		if (empty($novice_arr)){
			return "";
		}
		$novice_arr = array_filter($novice_arr);
		$novice = array_pop($novice_arr);
		$data['novice'] = arr2str($novice_arr,",");
		$this->startTrans();
		$novice_result = $this->save($data);

		//记录领取
		$record['game_id'] = $data['game_id'];
		$record['game_name'] = get_game_name($data['game_id']);
		$record['gift_id'] = $gift_id;
		$record['gift_name'] = $data['giftbag_name'];
		$record['status'] = 0;
		$record['novice'] = $novice;
		$record['user_id'] = get_user_id($account);
		$record['user_account'] = $account;
		$record['create_time'] = time();
		$record['start_time'] = $data['start_time'];
		$record['end_time'] = $data['end_time'];
		$record_result = M("gift_record",'tab_')->add($record);
		if($novice_result === false || $record_result === false){
			$this->rollback();
			return "";
		}else{
			$this->commit();
			return $novice;
		}
	}


	/**
	 * 我的礼包记录
	 * @param $account
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getMyGiftRecord($account,$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 5;
		$map['user_account'] = $account;
		$map['status'] = 0;
		$data = $this
			->table("tab_gift_record as r")
			->field("r.gift_id,r.novice,r.gift_name,r.game_name,g.icon,r.start_time,r.end_time")
			->join("left join tab_game g on g.id = r.game_id")
			->where($map)
			->page($page,$row)
			->select();
		foreach ($data as $key => $val){
			$data[$key]['icon'] = get_img_url($val['icon']);
		}
		return $data;
	}

	/**
	 * 检查是否已经领取
	 * @param $account
	 * @param $gift_id
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function checkAccountGiftExist($account,$gift_id){
		$map['user_account'] = $account;
		$map['gift_id'] = $gift_id;
		$data = M("gift_record",'tab_')->where($map)->find();
		if (empty($data)){
			return false;
		}else{
			return true;
		}
	}


	/**
	 * 修改礼包记录状态
	 * @param $gift_id
	 * @param $account
	 * @param $status
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function changeRecordStatus($gift_id,$account,$status){
		$map['gift_id'] = $gift_id;
		$map['user_account'] = $account;
		return M("gift_record",'tab_')->where($map)->setField(['status'=>$status]);
	}


	/**
	 * 礼包详情
	 * @param $gift_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getDetail($gift_id){
		$time = NOW_TIME;
		$map['status'] = 1;
		$map['start_time'] = ['elt',$time];
		$map['_string'] = "end_time > {$time} or end_time = 0";
		$map['id'] = $gift_id;
		$data = $this->field("game_id,game_name,giftbag_name,novice,digest,desribe,end_time")->where($map)->find($gift_id);
		if(empty($data)){
			return "";
		}
		$novice_arr = str2arr($data['novice'],',');
		$novice_num = count(array_filter($novice_arr));
		unset($data['novice']);
		$game = $this->table("tab_game")->find($data['game_id']);
		$data['icon'] = get_img_url($game['icon']);
		$data['num'] = $novice_num;
		return $data;

	}
}