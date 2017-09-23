<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/24
 * Time: 10:54
 */
namespace App\Model;

use Org\UcenterSDK\Ucservice;

class UserModel extends BaseModel {

	/**
	 * 获取用户信息
	 * @param $user_id
	 * @param array $field
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getUserInfo($account,$field=['id','account','nickname','promote_id','phone','balance','head_img','sex','point','idcard','real_name','age_status']){
		$map['account'] = $account;
		$data = $this->field($field)->where($map)->find();
		if (empty($data)){
			return $data;
		}
		$head_img = get_img_url($data['head_img']);
		$data['head_img'] = $head_img;
		if($head_img == false){
			$data['head_img'] = "";
		}
        if ($data['age_status']==0 && !empty($data['idcard']) && !empty($data['real_name'])){
            $data['age_status'] = 4;
        }
		return $data;
	}

	/**
	 * 获取用户信息
	 * @param $account
	 * @param array $filed
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getUserByAccount($account,$filed=['*']){
		$map['account'] = $account;
		$data = $this->field($filed)->where($map)->find();
		return $data;
	}

	/**
	 * 更新用户信息
	 * @param $account
	 * @param string $nickname
	 * @param string $sex
	 * @param string $password
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function updateUserInfo($account,$nickname="",$sex=""){
		$data['account'] = $account;
		empty($nickname) || $data['nickname'] = $nickname;
		$sex == "" || $data['sex'] = $sex;
		return $this->where(['account'=>$account])->save($data);
	}

	/**
	 * 修改密码
	 * @param $phone
	 * @param $old_pwd  旧密码
	 * @param $new_pwd  新密码
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function changePwd($account, $old_pwd, $new_pwd)
	{
		//修改UC密码
		$result = $this->changeUcPwd($account,$old_pwd,$new_pwd,0);
		if(!$result){
			return false;
		}
		$user = $this->getUserByAccount($account);
		if (!empty($user) && think_psw_md5($old_pwd, UC_AUTH_KEY) === $user['password'])
		{
			$user['password'] = think_psw_md5($new_pwd, UC_AUTH_KEY);
			$result = $this->save($user);
		}
		return $result;
	}

	/**
	 * 忘记密码
	 * @param $account
	 * @param $pwd
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function forgetPwd($account,$pwd){
		//UC忘记密码 暂时不可用
		/*$result = $this->changeUcPwd($account,"",$pwd,1);
		if(!$result){
			return false;
		}*/
		$user = $this->getUserByAccount($account);
		if(!empty($user)){
			$user['password'] = think_psw_md5($pwd, UC_AUTH_KEY);
			$result =  $this->save($user);
		}
		return $result;
	}

	/**
	 * 修改UC密码
	 * @param $account
	 * @param string $old_pwd   旧密码
	 * @param $new_pwd          新密码
	 * @param $type             0 修改密码 1 忘记密码
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	private function changeUcPwd($account,$old_pwd="",$new_pwd,$type){
		//修改UC密码
		if(C('UC_SET') == 1){
			$uc = new Ucservice();
			$data_uc = $uc->get_uc($account);
			if (is_array($data_uc)) {
				$result = $uc->uc_edit($account, $old_pwd, $new_pwd,'',$type);
				if($result < 0){
					return false;
				}
				return true;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}


	/**
	 * 绑币记录
	 * @param $user_id
	 * @param $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getUserBindCoin($user_id,$p){
		$map['user_id'] = $user_id;
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$data = M("user_play","tab_")
			->field("game_name,game_id,bind_balance")
			->where($map)
			->group("game_id")
			->page($page,$row)
			->select();
		return $data;
	}
}