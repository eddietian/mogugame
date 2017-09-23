<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/31
 * Time: 16:34
 */

namespace App\Controller;

use App\Model\UserAddressModel;

class UserAddressController extends BaseController{


	public function _initialize(){
		parent::_initialize();
		$this->auth(I("post.token"));
		$this->user_id = get_user_id(USER_ACCOUNT);

	}

	/**
	 * @param $address
	 * @param $phone
	 * author: xmy 280564871@qq.com
	 */
	public function add($name,$city,$address,$phone,$is_default=0){
		$model = new UserAddressModel();
		$user_id = $this->user_id;
		$data = $model->addAddress($user_id,$name,$city,$address,$phone,$is_default);
		if($data){
			$this->set_message(1,1);
		}else{
			$this->set_message(-1,"添加失败：".$model->getError());
		}
	}

	/**
	 * @param $id
	 * @param $address
	 * @param $phone
	 * author: xmy 280564871@qq.com
	 */
	public function edit($id,$name,$city,$address,$phone,$is_default=0){
		$model = new UserAddressModel();
		$user_id = $this->user_id;
		$data = $model->editAddress($id,$user_id,$name,$city,$address,$phone,$is_default);
		if($data){
			$this->set_message(1,1);
		}else{
			$this->set_message(-1,"编辑失败：".$model->getError());
		}
	}

	/**
	 * @param $id
	 * author: xmy 280564871@qq.com
	 */
	public function delete($id){
		$model = new UserAddressModel();
		$user_id = $this->user_id;
		$data = $model->deleteAddress($id,$user_id);
		if($data){
			$this->set_message(1,1);
		}else{
			$this->set_message(-1,"删除失败：".$model->getError());
		}
	}

	/**
	 * 获取默认地址
	 * author: xmy 280564871@qq.com
	 */
	public function get_default(){
		$model = new UserAddressModel();
		$user_id = $this->user_id;
		$data = $model->getDefault($user_id);
		if($data){
			$this->set_message(1,1,$data);
		}else{
			$this->set_message(-1,"暂无数据");
		}
	}

	/**
	 * 获取列表
	 * author: xmy 280564871@qq.com
	 */
	public function get_lists(){
		$model = new UserAddressModel();
		$map['user_id'] = $this->user_id;
		$data = $model->getLists($map);
		if($data){
			$this->set_message(1,1,$data);
		}else{
			$this->set_message(-1,"暂无数据");
		}
	}

	/**
	 * 设为默认地址
	 * @param $id
	 * author: xmy 280564871@qq.com
	 */
	public function set_default($id){
		$model = new UserAddressModel();
		$map['user_id'] = $this->user_id;
		$user_id = $this->user_id;
		$data = $model->setDefault($id,$user_id);
		if($data){
			$this->set_message(1,1,$data);
		}else{
			$this->set_message(-1,"操作失败");
		}
	}
}