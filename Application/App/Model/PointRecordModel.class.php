<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/5
 * Time: 13:37
 */

namespace App\Model;

use App\Logic\UserLogic;

class PointRecordModel extends BaseModel{

	const HAVE_GET = -1;//已领取
	const ADD_POINT = 1; //增加积分


	/**
	 * 增加积分
	 * @param $type_key
	 * @param $user_id
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function addPointByType($type_key,$user_id){
		$type = $this->getPointType($type_key);
		if(!$type){
			return false;
		}
		$times = $type['time_of_day'];
		$map['type_id'] = $type['id'];
		$map['user_id'] = $user_id;
		$today = strtotime(date("Y-m-d"));
		$map['create_time'] = ['between',[$today,$today+86400-1]];
		$num = $this->where($map)->count();

		if($num >= $times && $times != 0){//判断是否超过当日领取次数
			$this->error = "当日已到达最大领取次数";
			return false;
		}else{
			$this->startTrans();
			$user_logic = new UserLogic();
			$user_result = $user_logic->operationPoint($user_id,$type['point'],self::ADD_POINT);
			$data['user_id'] = $user_id;
			$data['type_id'] = $type['id'];
			$data['point'] = $type['point'];
			$data['create_time'] = time();
			$data['type'] = 1;
			$record_result = $this->add($data);
			if($user_result !== false && $record_result !== false){
				$this->commit();
				return true;
			}else{
				$this->rollback();
				return false;
			}
		}
	}

	/**
	 * 用户签到
	 * @param $user_id
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function addPointBySignIn($user_id){
		$type = $this->getPointType("sign_in");
		if(!$type){
			return false;
		}
		$base_point = $type['point'];
		$increase_point = $type['time_of_day'];
		$record = $this->getSignInInfo($user_id,$type['id']);
		//计算间隔时间
		$today = strtotime(date("Y-m-d"));
		$time = strtotime(date("Y-m-d",$record['create_time']));
		$day = ($today-$time)/86400;
		if($day == 0){
			$this->error = "今日已签过";
			return false;
		}elseif($day == 1){//昨日积分 + 递增积分
			$add_point = $record['point'] + $increase_point;
			//超过7天 重新计算
			if ($record['day'] >= 7) {
				$add_point = $base_point;
				$sign_day = 1;
			}else{
				$sign_day = $record['day'] + 1;
			}
			$data['point'] = $add_point;
		}else{//基础积分
			$data['point'] = $base_point;
			$sign_day = 1;
		}
		$data['user_id'] = $user_id;
		$data['type_id'] = $type['id'];
		$data['create_time'] = time();
		$data['day'] = $sign_day;
		$data['type'] = 1;
		$this->startTrans();
		$record_result = $this->add($data);
		$user_result = D("User","Logic")->operationPoint($user_id,$data['point'],self::ADD_POINT);
		if($user_result !== false && $record_result !== false){
			$this->commit();
			return $sign_day;
		}else{
			$this->rollback();
			return false;
		}
	}


	/**
	 * 获取用户签到信息
	 * @param $user_id
	 * @param $type_id
	 * @return bool|int
	 * author: xmy 280564871@qq.com
	 */
	public function getSignInInfo($user_id,$type_id){
		$map['type_id'] = $type_id;
		$map['user_id'] = $user_id;
		$record = $this->where($map)->order("create_time desc")->find();
		return $record;
	}


	/**
	 * 获取积分方式
	 * @param $key
	 * @return bool|mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getPointType($key){
		$map['key'] = $key;
		$map['status'] = 1;
		$type = M("point_type","tab_")->where($map)->find();
		if(empty($type)){
			$this->error = "此奖励不存在或被禁用";
			return false;
		}
		return $type;
	}


	/**
	 * 积分获取记录
	 * @param string $map
	 * @param string $order
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getUserAchieveRecord($map="",$order="create_time desc",$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$data = $this
			->table("tab_point_record as pr")
			->field("pr.point,pr.create_time,pt.name")
			->where($map)
			->join("tab_user u on u.id=pr.user_id")
			->join("tab_point_type pt on pr.type_id = pt.id")
			->order($order)
			->page($page,$row)
			->select();
		return $data;
	}


	/**
	 * 充值获得积分
	 * @param $user_id
	 * @param $pay_amount
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function rechargeAwardPoint($user_id,$pay_amount){
		$user = M("user","tab_")->find($user_id);
		if(empty($user_id)){
			return true;
		}
		//奖励用户积分
		$point_type = $this->getPointType("recharge_spend");
		$point = intval($point_type['point'] * $pay_amount);

		$this->startTrans();

		if($point > 0){
			$user['point'] += $point;

			//积分记录
			$data['user_id'] = $user_id;
			$data['type_id'] = $point_type['id'];
			$data['point'] = $point;
			$data['create_time'] = time();
			$data['type'] = 1;
			$point_result = M("point_record","tab_")->add($data);//积分记录存储
		}
		$user_result = M("user","tab_")->save($user);//被邀请人积分存储

		if($point_result === false || $user_result === false){
			$this->rollback();
			return false;
		}else{
			$this->commit();
			return true;
		}
	}


}