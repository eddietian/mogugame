<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/8
 * Time: 11:12
 */

namespace App\Model;

class AdvModel extends BaseModel{


	public function getAdv($pos_name,$limit){
		$map['p.name'] = $pos_name;
		$map['a.status'] = 1;
		$now = NOW_TIME;
		$map['a.start_time'] = ['lt',$now];
		$data = $this->table("tab_adv as a")
			->field("a.title,a.data,a.url")
			->join("left join tab_adv_pos p on p.id=a.pos_id")
			->where($map)
			->where("a.end_time > $now or a.end_time = 0")
			->order("a.sort")
			->limit($limit)
			->select();
		foreach ($data as $key => $val) {
			$data[$key]['data'] = get_img_url($val['data']);
		}
		return $data;
	}
}