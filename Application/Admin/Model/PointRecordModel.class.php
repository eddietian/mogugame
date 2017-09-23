<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/1
 * Time: 15:41
 */

namespace Admin\Model;

class PointRecordModel extends TabModel {

	protected $_auto = [
		['create_time','time',self::MODEL_BOTH,'function'],
	];


	public function getLists($map="",$order="create_time desc",$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$data['data'] = $this
			->table("tab_point_record as pr")
			->field("pr.*,u.account,pt.name")
			->where($map)
			->join("tab_user u on u.id=pr.user_id")
			->join("tab_point_type pt on pr.type_id = pt.id")
			->order($order)
			->page($page,$row)
			->select();
		$data['count'] = $this
			->table("tab_point_record as pr")
			->where($map)
			->join("tab_user u on u.id=pr.user_id")
			->join("tab_point_type pt on pr.type_id = pt.id")
			->count();
		return $data;
	}
}