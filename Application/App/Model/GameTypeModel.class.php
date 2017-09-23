<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/28
 * Time: 9:03
 */
namespace App\Model;

class GameTypeModel extends BaseModel{

	public function getGameTypeLists($map,$version){
		$map['t.status_show'] = 1;
		$data = $this->table("tab_game_type as t")
			->field("t.id,t.icon,t.type_name,count(g.id) as game_num")
			->join("left join tab_game as g on g.game_type_id = t.id and g.game_status = 1 and g.sdk_version = {$version}")
			->where($map)
			->group("t.id")
			->select();
		foreach ($data as $key => $val){
			$data[$key]['icon'] = get_img_url($val['icon']);
		}
		return $data;
	}

}