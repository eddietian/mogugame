<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/14
 * Time: 19:45
 */

namespace Admin\Model;


class CommentModel extends TabModel
{

	public function getLists($map = "", $order = "create_time desc", $p = 1)
	{
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$data['data'] = $this
			->where($map)
			->order($order)
			->page($page, $row)
			->select();
		$data['count'] = $this->where($map)->count();
		return $data;
	}
}