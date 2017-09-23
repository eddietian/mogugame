<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/1
 * Time: 10:16
 */

namespace App\Model;

class PointShopModel extends BaseModel{


	/**
	 * 列表
	 * @param $map
	 * @param string $order
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getLists($map,$order="",$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 4;
		$map['status'] = 1;
		$data = $this->field("id,cover,price,good_name")->where($map)->order($order)->page($page, $row)->select();
		foreach ($data as $key => $value) {
			$data[$key]['cover'] = get_img_url($value['cover']);
		}
		return $data;
	}

	/**
	 * @param $id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getData($id){
		$data = $this->find($id);
		if(empty($data)){
			return $data;
		}
		$number = $data['number'];
		if($data['good_type'] == 2){
			$key = json_decode($data['good_key']);
			$number = count($key);
		}
		$data['cover'] = get_img_url($data['cover']);
		$data['number'] = $number;

		return $data;
	}
}