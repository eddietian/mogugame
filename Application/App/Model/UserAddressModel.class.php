<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/31
 * Time: 15:40
 */
namespace App\Model;


class UserAddressModel extends BaseModel{


	protected $_validate = [
		['user_id', 'require', '用户不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
		['name', 'require', '姓名不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
		['city', 'require', '省市区不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
		['address', 'require', '地址不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
		['phone', 'require', '手机号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
	];

	protected $_auto = [
		['default','0',self::MODEL_INSERT],
		['create_time','time',self::MODEL_INSERT,'function'],
	];


	/**
	 * 添加地址
	 * @param $user_id
	 * @param $address
	 * @param $phone
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function addAddress($user_id,$name,$city,$address,$phone,$is_default){
		$data['name'] = $name;
		$data['user_id'] = $user_id;
		$data['address'] = $address;
		$data['phone'] = $phone;
		$data['city'] = $city;
		$data = $this->create($data);
		if($is_default == 1){
			$data['is_default'] = time();
		}
		if(empty($data)){
			return false;
		}
		$result = $this->add($data);
		return $result;
	}


	/**
	 * 编辑地址
	 * @param $id
	 * @param $user_id
	 * @param $address
	 * @param $phone
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function editAddress($id,$user_id,$name,$city,$address,$phone,$is_default){
		$data['address'] = $address;
		$data['name'] = $name;
		$data['city'] = $city;
		$data['phone'] = $phone;
		if($is_default == 1){
			$data['is_default'] = time();
		}
		return $this->where(['id'=>$id,'user_id'=>$user_id])->save($data);
	}

	/**
	 * 删除地址
	 * @param $id
	 * @param $user_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function deleteAddress($id,$user_id){
		return $this->where(['id'=>$id,'user_id'=>$user_id])->delete();
	}

	/**
	 * 设为默认地址
	 * @param $id
	 * @param $user_id
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function setDefault($id,$user_id){
		$map['id'] = $id;
		$map['user_id'] = $user_id;
		return $this->where($map)->setField(['is_default'=>time()]);
	}

	/**
	 * 获取默认地址
	 * @param $user_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getDefault($user_id){
		$map['user_id'] = $user_id;
		$data = $this->field("id,name,city,address,phone")->where($map)->order("is_default desc")->find();
		return $data;
	}

	/**
	 * 获取地址列表
	 * @param $map
	 * @param $order
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getLists($map,$order="is_default desc"){
		return $this->field("id,name,city,address,phone")->where($map)->order($order)->select();

	}
}