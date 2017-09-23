<?php
/**
 * 逻辑层model
 * Created by PhpStorm.
 * User: xmy
 * Date: 2017/3/23
 * Time: 14:57
 */

namespace App\Logic;

use Think\Model;

class BaseLogic extends Model{

	const EMPTY_DATA = -100;        //数据为空
	const SIGN_ERROR = -99;         //验签失败

	const USER_NOT_EXIST = -1000;    //用户不存在
	const USER_FORBIDDEN = -1001;   //被禁用
	const USER_PWD_ERROR = -10021;  //密码错误
	const UNKNOWN_ERROR = -1100;    //未知错误

	const CODE_TIMEOUT = -98;       //验证码超时
	const CODE_ERROR = -97;         //验证码错误

	const RETURN_SUCCESS = 1;
	const RETURN_FALSE = 2;


    protected function _initialize()
    {
	    $this->tablePrefix = "tab_";
        C(api('Config/lists'));//加载配置变量
    }
}