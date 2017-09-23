<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/1
 * Time: 10:16
 */

namespace Admin\Model;

class PointShopRecordModel extends TabModel{



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
		$row = 10;
		$data['data'] = $this
			->table("tab_point_shop_record as sr")
			->field("sr.user_id,sr.good_name,sr.number,sr.pay_amount,sr.create_time,sr.address")
			->join("left join tab_user u on u.id = sr.user_id")
			->where($map)
			->order($order)
			->page($page, $row)
			->select();
		$data['count'] = $this
			->table("tab_point_shop_record as sr")
			->where($map)
			->join("left join tab_user u on u.id = sr.user_id")
			->count();
		return $data;
	}

}