<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/5
 * Time: 13:32
 */
namespace App\Controller;

use Admin\Model\PointTypeModel;
use App\Model\PointRecordModel;
use App\Model\PointShopRecordModel;

class PointController extends BaseController{


	/**
	 * 获取积分
	 * @param $token
	 * @param $name
	 * author: xmy 280564871@qq.com
	 */
	public function add_point_by_type($token,$name){
		$this->auth($token);
		$user_id = get_user_id(USER_ACCOUNT);
		$model = new PointRecordModel();
		$result = $model->addPointByType($name,$user_id);
		if($result !== false){
			$this->set_message(1,"增加成功！");
		}else{
			$this->set_message(-1,$model->getError());
		}
	}


	/**
	 * 签到
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function sign_in($token){
		$this->auth($token);
		$user_id = get_user_id(USER_ACCOUNT);
		$model = new PointRecordModel();
		$result = $model->addPointBySignIn($user_id);
		$type = $model->getPointType('sign_in');
		$data['increase_point'] = $type['time_of_day'];
		$data['base_point'] = $type['point'];

		if($result !== false){
			$data['day'] = $result;
			$this->set_message(1,"签到成功",$data);
		}else{
			$this->set_message(-1,$model->getError(),$data);
		}
	}


	/**
	 * 获取签到信息
	 * author: xmy 280564871@qq.com
	 */
	public function get_sign_in_info($token){
		$this->auth($token);
		$user_id = get_user_id(USER_ACCOUNT);
		$model = new PointRecordModel();
		$type = $model->getPointType('sign_in');
		//签到积分信息
		$data['base_point'] = $type['point'];
		$data['increase_point'] = $type['time_of_day'];

		$record = $model->getSignInInfo($user_id,$type['id']);
		if(!$type){
			$this->set_message(-1,$model->getError());
		}else{
			$sign_day = empty($record['day']) ? 0 : $record['day'];
			//计算间隔时间
			$today = strtotime(date("Y-m-d"));
			$time = strtotime(date("Y-m-d",$record['create_time']));
			$day = ($today-$time)/86400;
			if($record['day'] >= 7){
				$data['day'] = 0;
				$this->set_message(1,1,$data);
			}
			if($day == 0){//今日已经签到
				$data['day'] = $sign_day;
				$this->set_message(-1,"今日已签到",$data);
			}elseif ($day==1){//昨日签到过
				$data['day'] = $sign_day;
				$this->set_message(1,1,$data);
			}else{//断签
				$data['day'] = 0;
				$this->set_message(1,1,$data);
			}
		}

	}

	/**
	 * 获取签到积分信息
	 * author: xmy 280564871@qq.com
	 */
	public function get_sign_in(){
		$model = new PointRecordModel();
		$type = $model->getPointType('sign_in');
		$data['base_point'] = $type['point'];
		$data['increase_point'] = $type['time_of_day'];
		$this->set_message(1,1,$data);
	}

	/**
	 * 获取用户积分记录
	 * @param $token
	 * @param int $p
	 * @param int $type 1 获取记录 2 使用记录
	 * author: xmy 280564871@qq.com
	 */
	public function get_user_data($token,$p=1,$type=1){
		$this->auth($token);
		$user_id = get_user_id(USER_ACCOUNT);
		$model = new PointRecordModel();
		if($type == 1){
			$map['pr.user_id'] = $user_id;
			$map['pr.type'] = $type;
			$data = $model->getUserAchieveRecord($map,"create_time desc",$p);
		}else{
			$map['user_id'] = $user_id;
			$data = D("PointShopRecord")->getLists($map,"create_time desc",$p);
		}
		if(empty($data)){
			$this->set_message(-1,"暂无数据");
		}else{
			$this->set_message(1,1,$data);
		}
	}


	/**
	 * 积分指南
	 * author: xmy 280564871@qq.com
	 */
	public function point_guide(){
		$model = new PointTypeModel();
		$data = $model->getLists();
		foreach ($data['data'] as $key=>$val) {
			$result[$val['key']]['point'] = $val['point'];
			$result[$val['key']]['time_of_day'] = $val['time_of_day'];
			$result[$val['key']]['name'] = $val['name'];
			$result[$val['key']]['remake'] = $val['remake'];
		}
		$this->assign("data",$result);
		$this->display();
	}

	/**
	 * 获取用户消费积分合计
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function get_user_spend_point($token){
		$this->auth($token);
		$model = new PointShopRecordModel();
		$user_id = get_user_id(USER_ACCOUNT);
		$data = $model->getUserSpendPoint($user_id);
		$this->set_message(1,1,$data);
	}
}