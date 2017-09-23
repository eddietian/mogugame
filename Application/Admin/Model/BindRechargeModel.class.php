<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/8
 * Time: 10:05
 */

namespace Admin\Model;

class BindRechargeModel extends TabModel{

	public function getLists($map="",$order="create_time desc",$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$result['data'] = $this->where($map)->order($order)->page($page,$row)->select();
		$result['count'] = $this->where($map)->count();

		$today = strtotime(date("Ymd"));
		$today_map['create_time'] = ['between',[$today,$today+86400-1]];
		$yesterday_map['create_time'] = ['between',[$today-86400,$today-1]];
		//本页
		$result['page_total'] = $this->where($map)->where(['pay_status'=>1])->page($page,$row)->sum("real_amount");
		//昨日
		$result['yesterday'] = $this->where($today_map)->where(['pay_status'=>1])->sum("real_amount");
		//今日
		$result['today'] = $this->where($yesterday_map)->where(['pay_status'=>1])->sum("real_amount");
		//总计
		$result['total'] = $this->where(['pay_status'=>1])->sum("real_amount");
		return $result;
	}
}