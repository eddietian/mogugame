<?php
/**
 * 用户验证
 * Created by PhpStorm.
 * User: xmy
 * Date: 2017/3/23
 * Time: 15:00
 */

namespace App\Logic;

use Org\UcenterSDK\Ucservice;
use Org\XiguSDK\Xigu;
use User\Api\MemberApi;

class UserLogic extends BaseLogic{

	const USER_NOT_ILLEGAL = -1;        //用户名不合法
	const USER_HAVE_SENSITIVE_STR = -2; //包含敏感字符
	const USER_HAS_REGISTERED = -3;     //用户已存在

	const USER_PROMOTE_NATURAL = 0;//自然注册

	/**
	 * 数据验签
	 * @param $sign 签名
	 * @param $param    参数
	 * @param string $salt  混淆字符串
	 * @return bool|int
	 * author: xmy 280564871@qq.com
	 */
    public function md5Sign($sign,$param,$salt="mengchuang"){
        $encrypt = $this->encrypt_md5($param,$salt);
        if($sign === $encrypt){
            return true;
        }else{
            return self::SIGN_ERROR;
        }
    }

	/**
	 * 数据加密
	 * @param string $param
	 * @param string $key
	 * @return string
	 * author: xmy 280564871@qq.com
	 */
    private function encrypt_md5($param = "", $key = "")
    {
        #对数组进行排序拼接
        if (is_array($param)) {
            $md5Str = implode($this->arrSort($param));
        } else {
            $md5Str = $param;
        }
        $md5 = md5($md5Str . $key);
        return '' === $param ? 'false' : $md5;
    }

	/**
	 * 数组排序
	 * @param $para
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
    private function arrSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

	/**
	 * UC登录
	 * @param $account
	 * @param $password
	 * @return array|int
	 * author: xmy 280564871@qq.com
	 */
    public function ucLogin($account,$password){
	    $uc = new Ucservice();
	    $user_id = $uc->uc_login($account, $password, 1);
	    if($user_id < 0){
		    switch ($user_id) {
			    case -1 :
				    $user_id = self::USER_NOT_EXIST;
				    break;
			    case -2 :
				    $user_id = self::USER_PWD_ERROR;
				    break;
			    default :
				    $user_id = self::UNKNOWN_ERROR;
		    }
	    }
	    return $user_id;
    }

	/**
	 * 用户登录
	 * @param $account
	 * @param $password
	 * @return int
	 * author: xmy 280564871@qq.com
	 */
    public function userLogin($account,$password){
	    $userApi = new MemberApi();
	    $user_id = $userApi->login($account, $password);//调用登录
	    return $user_id;
    }

	/**
	 * 检查账号是否存在
	 * @param $account
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
    public function checkUserExist($account){
	    $map['account'] = $account;
	    $data = $this->where($map)->find();
	    if(empty($data)){
	    	return true;
	    }else{
	    	return false;
	    }
    }


	/**
	 * 发送 短信验证码
	 * @param $phone
	 * @param $time 有效时间 (分钟)
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
    public static function sendMsg($phone,$time=10){
    	$session = session($phone);
	    if(!empty($session) && (NOW_TIME - $session['create_time']) < 60){
		    return false;
	    }
	    $code = rand(100000,999999);
	    $xigu = new Xigu(C('sms_set.smtp'));
	    if(get_tool_status("sms_set")) {
	        appchecksendcode($phone,C('sms_set.limit'));
		    $param = $code . "," . $time;
		    $result = json_decode($xigu->sendSM(C('sms_set.smtp_account'), $phone, C('sms_set.smtp_port'), $param), true);
		    $result['create_time'] = time();
		    $result['pid'] = 0;
	        $result['create_ip']=get_client_ip();
		    M('short_message')->add($result);
		    if($result['send_status'] == '000000') {
			    session($phone,['code'=>$code,'create_time'=>NOW_TIME]);
			    return true;
		    }else{
			    return false;
		    }
	    }elseif(get_tool_status("alidayu")){
	        appchecksendcode($phone,C('alidayu.limit'));
		    $result = $xigu->alidayu_send($phone,$code,$time);
		    session($phone,['code'=>$code,'create_time'=>NOW_TIME]);
		    if($result){
    		    // 存储短信发送记录信息
    		    $result2['send_status'] = '000000';
                $result2['send_time'] = time();
    		    $result2['phone'] = $phone;
    		    $result2['create_time'] = time();
    		    $result2['pid']=0;
    		    $result2['create_ip']=get_client_ip();
    		    $r = M('Short_message')->add($result2);
		    }
		    return $result;
	    }elseif(get_tool_status('jiguang')){
	        appchecksendcode($phone,C('jiguang.limit'));
            $result = $xigu->jiguang($phone,$code,'');
            session($phone,['code'=>$code,'create_time'=>NOW_TIME]);
		    if($result){
    		    // 存储短信发送记录信息
    		    $result2['send_status'] = '000000';
                $result2['send_time'] = time();
    		    $result2['phone'] = $phone;
    		    $result2['create_time'] = time();
    		    $result2['pid']=0;
    		    $result2['create_ip']=get_client_ip();
    		    $r = M('Short_message')->add($result2);
		    }
            return $result;
        }elseif(get_tool_status("alidayunew")){
	        appchecksendcode($phone,C('alidayunew.limit'));
		    $result = $xigu->alidayunew_send($phone,$code,$time);
		    session($phone,['code'=>$code,'create_time'=>NOW_TIME]);
		    if($result){
    		    // 存储短信发送记录信息
    		    $result2['send_status'] = '000000';
                $result2['send_time'] = time();
    		    $result2['phone'] = $phone;
    		    $result2['create_time'] = time();
    		    $result2['pid']=0;
    		    $result2['create_ip']=get_client_ip();
    		    $r = M('Short_message')->add($result2);
		    }
		    return $result;
	    }elseif(get_tool_status("alidayumsg")){
            appchecksendcode($phone,C('alidayumsg.limit'));
            $result = $xigu->alidayumsg_send($phone,$code,$time);
            session($phone,['code'=>$code,'create_time'=>NOW_TIME]);
            if($result){
                // 存储短信发送记录信息
                $result2['send_status'] = '000000';
                $result2['send_time'] = time();
                $result2['phone'] = $phone;
                $result2['create_time'] = time();
                $result2['pid']=0;
                $result2['create_ip']=get_client_ip();
                $r = M('Short_message')->add($result2);
            }
            return $result;
        }

    }


	/**
	 * 验证 短信验证码
	 * @param $phone
	 * @param $code     验证码
	 * @param int $time 超时时间
	 * @return int
	 * author: xmy 280564871@qq.com
	 */
    public static function smsVerify($phone,$code,$time=10){
    	$session = session($phone);
    	if(empty($session)){
    		return self::RETURN_FALSE;
	    }elseif((NOW_TIME - $session['create_time']) > $time*60*60){
		    return self::CODE_TIMEOUT;
	    }elseif ($session['code'] != $code){
		    return self::CODE_ERROR;
	    }
        session($phone,null);
        return self::RETURN_SUCCESS;
    }

	/**
	 * APP注册
	 * @param $user_data
	 * @return int|MemberApi
	 * author: xmy 280564871@qq.com
	 */
    public function userRegisterByApp($user_data){
    	$user = new MemberApi();
	    $result = $user->app_register($user_data['account'], $user_data['password'], 2, 2, $user_data['nickname'], $user_data['sex'],PROMOTE_ID);
    	return $result;
    }

	/**
	 * UC用户注册
	 * @param $user_data
	 * @return int
	 * author: xmy 280564871@qq.com
	 */
    public function userRegisterByUc($user_data){
	    $uc = new Ucservice();
	    $result = $uc->uc_register($user_data['account'], $user_data['password'], "", PROMOTE_ID, "自然注册", 0, "", 1, 1);
	    return $result;
    }

	/**
	 * 操作用户积分
	 * @param $user_id
	 * @param $point    积分
	 * @param $type     1 增加 2减少
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
    public function operationPoint($user_id,$point,$type){
    	$user = $this->find($user_id);
    	if($type == 1){
    		$user['point'] += $point;
	    }else{
    		if($user['point'] >= $point){
    			$user['point'] -= $point;
		    }else{
    			return false;
		    }
	    }
	    return $this->save($user);

    }
}