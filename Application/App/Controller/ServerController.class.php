<?php
namespace App\Controller;
use User\Api\MemberApi;
use User\Api\UserApi;
use Org\UcpaasSDK\Ucpaas;
use Org\UcenterSDK\Ucservice;

class ServerController extends BaseController{


	/**
	 * 获取区服列表
	 * @param $game_id
	 * author: xmy 280564871@qq.com
	 */
	public function get_server_lists($game_id){
		$data = D("Server")->getOpenServer($game_id);
		if (empty($data)){
			$this->set_message(-1,"开服列表为空");
		}
		$this->set_message(1,1,$data);

	}


	/**
	 * 区服列表
	 * @param int $p
	 * @param $status 1 已开服 2 未开服
	 * author: xmy 280564871@qq.com
	 */
	public function get_lists($p=1,$status,$version){
		$time = NOW_TIME;
		if($status == 1){//已开服
			$map['start_time'] = ['elt',$time];
		}else{//未开服
			$map['start_time'] = ['gt',$time];
		}
		$map['ser.server_version'] = $version;
		$data = D("Server")->getServerLists($map,$p);
		if (empty($data)){
			$this->set_message(-1,"开服列表为空");
		}
		$this->set_message(1,1,$data);
	}


}