<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/28
 * Time: 16:29
 */
namespace App\Controller;

use App\Model\GiftbagModel;

class GiftController extends BaseController{

	/**
	 * 获取礼包列表
	 * @param $game_id
	 * author: xmy 280564871@qq.com
	 */
	public function get_gift_lists($game_id){
		$data = D("Giftbag")->getGiftLists($game_id);
		if(empty($data)){
			$this->set_message(-1,"暂无礼包");
		}
		$this->set_message(1,1,$data);
	}

	/**
	 * 礼包列表 礼包数量
	 * @param int $p
	 * author: xmy 280564871@qq.com
	 */
	public function gift_lists_num($p=1,$version,$game_name=""){
		if(!empty($game_name)){
			$game = D("Game")->where(['game_name'=>['like',"%".$game_name."%"]])->select();
			$map['game_id'] = ['in',array_column($game,"id")];
		}
		$map['giftbag_version'] = $version;
		$data = D("Giftbag")->giftListsNum($map,$p);
		if(empty($data)){
			$this->set_message(-1,"暂无礼包");
		}
		$this->set_message(1,1,$data);
	}

	/**
	 * 领取激活码
	 * @param $token
	 * @param $gift_id
	 * author: xmy 280564871@qq.com
	 */
	public function get_novice($token,$gift_id){
		$this->auth($token);
		$model = D("Giftbag");
		$exist = $model->checkAccountGiftExist(USER_ACCOUNT,$gift_id);
		if($exist){
			$this->set_message(-1,"已领取过该游戏");
		}
		$novice = $model->getNovice(USER_ACCOUNT,$gift_id);
		if(empty($novice)){
			$this->set_message(-1,"暂无激活码");
		}
		$this->set_message(1,1,$novice);
	}

	/**
	 * 礼包记录
	 * @param $token
	 * @param int $p
	 * author: xmy 280564871@qq.com
	 */
	public function get_my_gift_record($token,$p=1){
		$this->auth($token);
		$data = D("Giftbag")->getMyGiftRecord(USER_ACCOUNT,$p);
		if(empty($data)){
			$this->set_message(-1,"暂无礼包记录");
		}
		$this->set_message(1,1,$data);
	}


	/**
	 * 删除记录
	 * @param $token
	 * @param $gift_id
	 * author: xmy 280564871@qq.com
	 */
	public function delete_gift_record($token,$gift_id){
		$this->auth($token);
		$result = D("Giftbag")->changeRecordStatus($gift_id,USER_ACCOUNT,1);
		if($result !== false){
			$this->set_message(1,"删除成功");
		}else{
			$this->set_message(-1,"删除失败");
		}
	}

	/**
	 * 礼包详情
	 * @param $gift_id
	 * author: xmy 280564871@qq.com
	 */
	public function get_detail($gift_id){
		$model = new GiftbagModel();
		$data = $model->getDetail($gift_id);
		if(empty($data)){
			$this->set_message(-1,"礼包不存在");
		}else{
			$this->set_message(1,1,$data);
		}
	}
}