<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/6
 * Time: 11:06
 */

namespace App\Controller;

use App\Model\PointShopModel;
use App\Model\PointShopRecordModel;

class PointShopController extends BaseController{


	/**
	 * 商品列表
	 * @param int $p
	 * author: xmy 280564871@qq.com
	 */
	public function get_lists($p=1){
		$model = new PointShopModel();
		$data = $model->getLists("","create_time desc",$p);
		$this->set_message(1,1,$data);
	}

	/**
	 * 商品详情
	 * @param $id
	 * author: xmy 280564871@qq.com
	 */
	public function get_data($id){
		$model = new PointShopModel();
		$data = $model->getData($id);
		$result['number'] = $data['number'];
		$result['cover'] = $data['cover'];
		$result['good_type'] = $data['good_type'];
		$result['good_name'] = $data['good_name'];
		$result['price'] = $data['price'];
		$result['good_info'] = $data['good_info'];
		$result['good_usage'] = $data['good_usage'];
		$this->set_message(1,1,$result);
	}


	/**
	 * 购买商品
	 * @param $good_id
	 * @param $token
	 * @param $num      购买数量
	 * @param $address_id   地址ID
	 * author: xmy 280564871@qq.com
	 */
	public function buy($good_id,$token,$num,$address_id=""){
		$this->auth($token);
		$user_id = get_user_id(USER_ACCOUNT);
		$model = new PointShopRecordModel();
		$result = $model->buy($good_id,$user_id,$num,$address_id);
		if($result !== false){
			$this->set_message(1,$result['good_type'],$result['good_key']);
		}else{
			$this->set_message(-1,"购买失败：".$model->getError());
		}
	}



	/**
	 * 获取用户兑换记录
	 * @param $token
	 * @param int $p
	 * @param int $type 1:全部 2：商品 3：平台币
	 * author: xmy 280564871@qq.com
	 */
	public function get_user_buy_record($token,$p=1,$type=1){
		$this->auth($token);
		$user_id = get_user_id(USER_ACCOUNT);
		$model = new PointShopRecordModel();
		$map['user_id'] = $user_id;
		if ($type == 2){
			$map['good_type'] = ['in',[1,2]];//商品
		}elseif ($type == 3) {
			$map['good_type'] = 3;//平台币
		}
		$result = $model->getLists($map,"create_time desc",$p);
		$this->set_message(1,1,$result);
	}


	/**
	 * 购买记录详情
	 * @param $id
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function get_buy_record_detail($id,$token){
		$this->auth($token);
		$user_id = get_user_id(USER_ACCOUNT);
		$model = new PointShopRecordModel();
		$data = $model->getBugRecordDetail($id,$user_id);
		if(empty($data)){
			$this->set_message(-1,"数据不存在");
		}else{
			$data['service_qq'] = C("APP_QQ");
			$this->set_message(1,1,$data);
		}
	}


	/**
	 * 积分兑换平台币
	 * @param $token
	 * @param $num
	 * author: xmy 280564871@qq.com
	 */
	public function point_convert_coin($token,$num){
		$this->auth($token);
		$user_id = get_user_id(USER_ACCOUNT);
		$model = new PointShopRecordModel();
		$result = $model->PointConvertCoin($user_id,$num);
		if($result){
			$this->set_message(1,"兑换成功");
		}else{
			$this->set_message(-1,"兑换失败：".$model->getError());
		}
	}
}