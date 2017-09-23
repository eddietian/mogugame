<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/1
 * Time: 10:14
 */

namespace App\Controller;

use App\Logic\UserLogic;

class ShareController extends BaseController{


	public function down(){
		$this->display();
	}

	public function register(){

		$this->display();
	}

	/**
	 * 获取分享链接
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function get_share_url($token){
		$this->auth($token);
		$url = U("Share/register",['invite_account'=>USER_ACCOUNT],true,true);
		$this->set_message(1,1,$url);
	}

	/**
	 * 邀请好友注册
	 * @param $phone
	 * @param $password
	 * @param $v_code
	 * @param string $invite_account
	 * author: xmy 280564871@qq.com
	 */
	public function share_register($phone,$password,$v_code,$invite_account="")
	{
		#验证短信验证码
		$code_result = UserLogic::smsVerify($phone,$v_code);
		if($code_result == UserLogic::RETURN_SUCCESS) {
			$user['account'] = $phone;
			$user['password'] = $password;
			$result = 1;
			if (C('UC_SET') == 1) { //UC注册
				$result = D('User', 'Logic')->userRegisterByUc($user);
			}
			if ($result > 0) {
				$result = D('User', 'Logic')->userRegisterByApp($user);
			}
			if($result < 0){
				$this->set_message(-1, $result);
			}

			if(!empty($invite_account)) {

				//添加邀请人记录
				D("ShareRecord")->addShareRecord($invite_account, $phone);

				//添加邀请好友注册积分
				D("PointRecord")->addPointByType("invite_friend", get_user_id($invite_account));
			}

			$this->set_message(1,1,$user);
		}else{
			$this->set_message(-1,$code_result);
		}
	}

	/**
	 * 获取邀请记录
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function get_my_invite_record($token){
		$this->auth($token);
		$invite_id = get_user_id(USER_ACCOUNT);
		$data = D("ShareRecord")->getMyInviteRecord($invite_id);
		if (empty($data)){
			$this->set_message(-1,"暂无记录");
		}else{
			$this->set_message(1,1,$data);
		}
	}


	/**
	 * 获取用户邀请统计
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function get_user_invite_info($token){
		$this->auth($token);
		$invite_id = get_user_id(USER_ACCOUNT);
		$data = D("ShareRecord")->getUserInviteInfo($invite_id);
		if (empty($data)){
			$this->set_message(-1,"暂无记录");
		}else{
			$this->set_message(1,1,$data);
		}
	}

}