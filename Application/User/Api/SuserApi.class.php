<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace User\Api;
use Admin\Model\UserModel;
use User\Api\Api;
/**
 * 短信用户接口
 * @author lwx
 */
class SuserApi extends Api{
    /**
     * 构造方法，实例化操作模型
     */
    protected function _init(){
        $this->model = new UserModel();
    }

    /**
     * 注册一个新用户
     * @param  string $account 用户名
     * @param  string $password 用户密码
     * @param  string $phone    用户电话
     * @param  int $register_way  注册方式
     * @param  int $promote_id  推广员标识
     * @param  string $promote_account  推广员账号
     * @return integer          注册成功-用户信息，注册失败-错误编号
     * @author lwx
     */
    public function register($account, $password,$phone="",$register_way=0,$register_type=0,$promote_id=0,$promote_account="自然注册"){
        $data['account'] = $account;
        $data['password'] = $password;
        $data['phone'] = $phone;
        $data['register_way'] = $register_way;
        $data['register_type'] = $register_type;
        $data['promote_id'] = $promote_id;
        $data['promote_account'] = $promote_account;
        return $this->model->register($data);
    }
    
    /**
     * 注册一个新用户
     * @param  string $account 用户名
     * @param  string $password 用户密码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function app_register($account, $password,$register_way=0,$nickname="",$sex=0){
        return $this->model->register($account,$password,$register_way,$nickname,$sex);
    }

    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-游戏登陆, 2-PC登录）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     * @author lwx
     */
    public function login($account, $password,$type=2,$game_id=0,$game_name=''){
        return $this->model->login($account, $password ,$type,$game_id,$game_name);
    }

    /**
     * 获取用户信息
     * @param  string  $uid         用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     * @author lwx
     */
    public function info($uid, $is_username = false){
        return $this->model->info($uid, $is_username);
    }
    
    /**
     * 检测用户名
     * @param  string $username    用户名
     * @return true 可以使用，false 用户名被占用
     * @author lwx
     */
    public function checkAccount($username) {
        $flag =  $this->model->checkAccount($username);
        return $flag == 1?true:false;
    }
    
    /**
     * 检测密码
     * @param  string $username    用户名
     * @param  string $password    密码
     * @return true 密码正确，false 密码错误
     * @author lwx
     */
    public function checkPassword($account,$password) {
        return $this->model->checkPassword($account,$password);
    }
    
    /**
     * 更新密码
     * @param  string $id          用户ID
     * @param  string $password    密码
     * @return true 密码修改成功，false 密码修改失败
     * @author lwx
     */
    public function updatePassword($id,$password) {
        return $this->model->updatePassword($id,$password);
    }

    /**
     * 检测邮箱
     * @param  string $email        邮箱
     * @return true 可以使用，false 邮箱被占用
     * @author lwx
     */
    public function checkEmail($email){
        $flag = $this->model->checkField($email, 2);
        return $flag == 1?true:false;
    }
    
    /**
     * 检测电话
     * @param  string $mobile       电话
     * @return true 可以使用，false 电话被占用
     * @author lwx
     */
    public function checkPhone($mobile){
        return $this->model->checkPhone($mobile);
    }
    
    /**
     * 更新用户信息
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author lwx
     */
    public function updateInfo($data){
        $return = $this->model->updateInfo($data);
        return $return;
    }

}
