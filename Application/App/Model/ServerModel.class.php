<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/28
 * Time: 16:41
 */

namespace App\Model;

class ServerModel extends BaseModel {

	/**
	 * 获取开服表
	 * @param $game_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getOpenServer($game_id){
		$map['game_id'] = $game_id;
		$map['show_status'] = 1;
		$data = $this->field('server_name,start_time')->order("start_time desc")->where($map)->select();
		return $data;
	}

	/**
	 * 获取区服表
	 * @param $map
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getServerLists($map,$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$map['show_status'] = 1;
		$data = $this->table("tab_server as ser")
			->field('server_name,start_time,ser.game_name,sor.pack_name,ser.game_id')
			->join("left join tab_game_source as sor on sor.game_id = ser.game_id")
			->where($map)
			->order("start_time")
			->page($page,$row)
			->select();
		foreach ($data as $key => $val){
			$data[$key]['down_url'] = GameModel::generateDownUrl($val['game_id']);
		}
		return $data;
	}
}