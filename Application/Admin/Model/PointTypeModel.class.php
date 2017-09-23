<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/1
 * Time: 15:41
 */

namespace Admin\Model;

class PointTypeModel extends TabModel {

	/**
	 * 列表
	 * @param string $map
	 * @param string $order
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getLists($map="",$order="create_time desc",$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$data['data'] = $this->where($map)->order($order)->page($page,$row)->select();
		$data['count'] = $this->where($map)->count();
		return $data;
	}

	/**
	 * 编辑
	 * @param $id
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function edit($id){
		$map['id'] = $id;
		$data = $this->create();
		if(!$data){
			return false;
		}
		$result = $this->where($map)->save($data);
		return $result;
	}
}