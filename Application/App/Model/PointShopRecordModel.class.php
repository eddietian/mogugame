<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/1
 * Time: 10:16
 */

namespace App\Model;

class PointShopRecordModel extends BaseModel{


	/**
	 * 记录列表
	 * @param $map
	 * @param string $order
	 * @param $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getLists($map,$order="",$p){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 8;
		$data = $this->field("id,good_name,good_type,number,pay_amount,create_time")->where($map)->order($order)->page($page, $row)->select();
		return $data;
	}

	/**
	 * 购买商品
	 * @param $good_id
	 * @param $user_id
	 * @param $num          购买数量
	 * @param $address_id   地址ID
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function buy($good_id,$user_id,$num,$address_id=""){
		$good = D("PointShop")->getData($good_id);
		if ($good['number'] < $num){
			$this->error = "库存不足";
			return false;
		}
		$pay_amount = $num * $good['price'];//支付价格
		$user_point = D("User")->where(['id'=>$user_id])->getField("point");
		if($pay_amount > $user_point){
			$this->error = "积分不足";
			return false;
		}
		if($good['good_type'] == 1){
			$address = D("UserAddress")->where(['id'=>$address_id,'user_id'=>$user_id])->find();
			if (empty($address)){
				$this->error = "地址不存在";
				return false;
			}
			$data['user_name'] = $address['name'];
			$data['address'] = $address['city'].$address['address'];
			$data['phone'] = $address['phone'];
		}else{
			//领取兑换码
			$good_key = json_decode($good['good_key']);
			$i = $num;
			while ($i > 0){
				$key[] = array_shift($good_key);
				$i--;
			}
			$good['good_key'] = json_encode($good_key);
			$result['good_key'] = $key;
			$data['good_key'] = json_encode($key);
		}
		//生成购买记录
		$good['number'] -= $num;
		$data['user_id'] = $user_id;
		$data['pay_amount'] = $pay_amount;
		$data["good_id"] = $good['id'];
		$data['good_name'] = $good['good_name'];
		$data['good_type'] = $result['good_type'] = $good['good_type'];
		$data['number'] = $num;
		$data['status'] = 1;
		$data['create_time'] = time();
		$this->startTrans();
		$good_result = D("PointShop")->save($good);
		$user_result = D("User")->where(['id'=>$user_id])->setField(['point'=>$user_point-$pay_amount]);
		$record_result = $this->add($data);
		if($user_result !== false && $record_result !== false && $good_result !== false){
			$this->commit();
			return $result;
		}else{
			$this->rollback();
			return false;
		}
	}

	/**
	 * 获取用户购买记录详情
	 * @param $id
	 * @param $user_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getBugRecordDetail($id,$user_id){
		$map['sr.id'] = $id;
		$map['sr.user_id'] = $user_id;
		$data = $this->table("tab_point_shop_record as sr")
			->field("ps.good_name,ps.good_type,ps.good_info,ps.good_usage,ps.cover,sr.number,sr.good_key,sr.user_name,sr.address,sr.phone")
			->join("left join tab_point_shop ps on ps.id = sr.good_id")
			->where($map)
			->find();
		$data['cover'] = get_img_url($data['cover']);
		$data['good_key'] = json_decode($data['good_key']);
		return $data;
	}


	/**
	 * 积分兑换平台币
	 * @param $user_id
	 * @param $num      兑换数量
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function PointConvertCoin($user_id,$num){
		$pay_amount = $num * 100;//平台币价格
		$user = D("User")->find($user_id);
		$user_point = $user['point'];
		if($pay_amount > $user_point){
			$this->error = "积分不足";
			return false;
		}

		$data['user_id'] = $user_id;
		$data['pay_amount'] = $pay_amount;
		$data["good_id"] = 0;
		$data['good_name'] = "平台币";
		$data['good_type'] = 3;
		$data['number'] = $num;
		$data['status'] = 1;
		$data['create_time'] = time();
		$this->startTrans();
		$record_result = $this->add($data);
		$user['point'] -= $pay_amount;
		$user['balance'] += $num;
		$user_result = D("User")->save($user);
		if($record_result !== false && $user_result !== false){
			$this->commit();
			return true;
		}else{
			$this->rollback();
			return false;
		}

	}

	public function getUserSpendPoint($user_id){
		$map['user_id'] = $user_id;
		$data = $this->where($map)->sum("pay_amount");
		return $data;
	}
}