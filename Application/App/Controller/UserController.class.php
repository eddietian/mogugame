<?php
namespace App\Controller;
use App\Logic\AuthLogic;
use App\Logic\UserLogic;
use App\Model\UserModel;
use Think\Controller;
use User\Api\MemberApi;
use Org\XiguSDK\Xigu;
use Org\UcenterSDK\Ucservice;

class UserController extends BaseController
{

    /**
     * APP登录
     * @param account
     * @param password
     * author: xmy 280564871@qq.com
     */
    public function user_login($account, $password)
    {
        $user = new UserLogic();
        $user_id = $user->userLogin($account, $password);
        if ($user_id > 0) {
            $user_info = D('User')->getUserInfo($account);
            $result['account'] = $user_info['account'];
            $result['nickname'] = $user_info['nickname'];
            $result['head_img'] = $user_info['head_img'];//头像
            $result['balance'] = $user_info['balance'];   //平台币
            $result['sex'] = $user_info['sex'];
            $result['is_uc'] = 0;
        } elseif (C('UC_SET') && $user::USER_NOT_EXIST == $user_id) {//用户不存在并开启UC登录
            $user_id = $user->ucLogin($account, $password);
            $uc = new Ucservice();
            $result['account'] = $account;
            $result['nickname'] = "UC用户";
            $result['head_img'] = "";
            $result['balance'] = 0;
            $result['sex'] = 1;
            $result['is_uc'] = 1;
        }
        if ($user_id > 0) {
            $result['token'] = $this->login($account, $result['is_uc']);
            $this->set_message(1, 1, $result);
        } else {
            $this->set_message(-1, $user_id);
        }
    }

    /**
     * 手机注册
     * @param $phone
     * @param $password
     * @param $v_code 验证码
     * @param $sex
     * @param $nickname 昵称
     * author: xmy 280564871@qq.com
     */
    public function user_phone_register($phone, $password, $v_code, $sex, $nickname)
    {
        #验证短信验证码
        $code_result = UserLogic::smsVerify($phone, $v_code);
        if ($code_result == UserLogic::RETURN_SUCCESS) {
            $user['account'] = $phone;
            $user['password'] = $password;
            $user['sex'] = $sex;
            $user['nickname'] = $nickname;
            $result = 1;
            if (C('UC_SET') == 1) { //UC注册
                $result = D('User', 'Logic')->userRegisterByUc($user);
            }
            if ($result > 0) {
                $result = D('User', 'Logic')->userRegisterByApp($user);
            }
            if ($result < 0) {
                $this->set_message(-1, $result);
            }
            unset($user['password']);
            $user['token'] = $this->login($phone, 0);
            $this->set_message(1, 1, $user);
        } else {
            $this->set_message(-1, $code_result);
        }
    }

    /**
     * 用户登录
     * 把账号和是否为UC用户状态 转为json
     * 使用系统加密 后返回客户端
     * 每次请求需带着这个参数
     * @param $account  账号
     * @param $is_uc    是否为UC用户
     * @param int $day 过期时间
     * @return string
     * author: xmy 280564871@qq.com
     */
    private function login($account, $is_uc, $day = 7)
    {
        $end_time = 60 * 60 * 24 * $day;
        $info['account'] = $account;
        $info['is_uc'] = $is_uc;
        $result = $token = think_encrypt(json_encode($info), UC_AUTH_KEY, $end_time);
        return $result;
    }

    /**
     * 发送验证码
     * @param $phone    手机号
     * @param int $type 1：验证账号 2：不验证
     * author: xmy 280564871@qq.com
     */
    public function send_msg($phone, $type = 1)
    {
        if (empty($phone)) {
            $this->set_message(-1, "手机号不能为空");
        }
        $user = new UserLogic();
        if ($type == 2 || $user->checkUserExist($phone)) {
            $result = $user::sendMsg($phone);
            if ($result) {
                $this->set_message(1, "发送成功");
            } else {
                $this->set_message(-1, "发送失败");
            }
        } else {
            $this->set_message(-1, "用户已存在");
        }
    }

    /**
     * 更新用户信息
     * @param $account
     * @param string $nickname
     * @param string $sex 0 男 1 女
     * author: xmy 280564871@qq.com
     */
    public function update_user($token, $nickname = "", $sex = "")
    {
        $this->auth($token);
        if (IS_UC == 1) {
            $this->set_message(-1, "UC用户无法更改");
        }
        $result = D("User")->updateUserInfo(USER_ACCOUNT, $nickname, $sex);
        if ($result !== false) {
            $data = D("User")->getUserInfo(USER_ACCOUNT);
            $this->set_message(1, "更新成功", $data);
        } else {
            $this->set_message(-1, "更新失败");
        }
    }

    /**
     * 修改密码
     * @param $account
     * @param $old_pwd
     * @param $new_pwd
     * author: xmy 280564871@qq.com
     */
    public function change_pwd($token, $old_pwd, $new_pwd)
    {
        $this->auth($token);
        $result = D('User')->changePwd(USER_ACCOUNT, $old_pwd, $new_pwd);
        if ($result !== false) {
              $u_uid['account']=USER_ACCOUNT;
              M('user_pwd')->where($u_uid)->setField('password',think_encrypt($password));
            $this->set_message(1, "更新成功");
        } else {
            $this->set_message(-1, "更新失败");
        }
    }

    /**
     * 忘记密码发送短信
     * @param $account
     * author: xmy 280564871@qq.com
     */
   public function forget_send_msg($account)
    {
        $data = D("User")->getUserInfo($account);
        if(C('UC_SET')==1){
            $uc = new Ucservice();
            $data_uc = $uc->get_uc($account);
            if (empty($data) && !empty($data_uc)) {
                $this->set_message(-1, "UC用户不支持");
            }
        }
        if (empty($account)) {
            $this->set_message(-1, "用户不存在");
        } elseif (empty($data['phone'])) {
            $this->set_message(-1, "该用户未绑定手机号");
        }
        $result = UserLogic::sendMsg($data['phone']);
        if ($result) {
            $this->set_message(1, "发送成功");
        } else {
            $this->set_message(-1, "发送失败");
        }
    }
    /**
     * 忘记密码
     * @param $phone    手机号
     * @param $v_code   验证码
     * @param $password 密码
     * @return bool
     * author: xmy 280564871@qq.com
     */
    public function forget_password($account, $v_code, $password)
    {
        $data = D("User")->getUserInfo($account);
        $code_result = UserLogic::smsVerify($data['phone'], $v_code);
        if ($code_result == UserLogic::RETURN_SUCCESS) {
            $result = D('User')->forgetPwd($account, $password);
            if ($result !== false) {
                $u_uid['account']=$account;
                M('user_pwd')->where($u_uid)->setField('password',think_encrypt($password));
                $this->set_message(1, "修改成功");
            } else {
                $this->set_message(-1, "修改失败");
            }
        } else {
            $this->set_message(-1, $code_result);
        }
    }


    /**
     * 获取用户信息
     * @param $token
     * author: xmy 280564871@qq.com
     */
    public function get_user_info($token)
    {
        $this->auth($token);
        $data = D("User")->getUserInfo(USER_ACCOUNT);
        $this->set_message(1, 1, $data);
    }


    /**
     * 绑币记录
     * @param $token
     * @param int $p
     * author: xmy 280564871@qq.com
     */
    public function get_user_bind_coin($token, $p = 1)
    {
        $this->auth($token);
        $user_id = get_user_id(USER_ACCOUNT);
        $model = new UserModel();
        $data = $model->getUserBindCoin($user_id, $p);
        if (empty($data)) {
            $this->set_message(-1, "暂无数据");
        } else {
            $this->set_message(1, 1, $data);
        }
    }


    /**
     * 实名认证信息   获得传递过来的UID，返回该玩家是否已经通过审核
     * @return mixed
     */
    public function return_age()
    {
        /*$request = json_decode(file_get_contents("php://input"),true);*/
        $request = $_GET;
        if (empty($request)) {
            $this->set_message(0, "fail", "操作数据不能为空");
        }

        $this->auth($request['token']);

        $data = C('age_prevent');
        $data['on-off'] = $data['status'];
        unset($data['status']);
        $res['date'] = $data;
        $where['account'] = USER_ACCOUNT;
        $re = M('User', 'tab_')->field('age_status')->where($where)->find();
        if ($re) {
            $data['age_status'] = $re['age_status'];
            if ($data['age_status'] == 0 && !empty($data['idcard']) && !empty($data['real_name'])) {
                $data['age_status'] = 4;
            }
        } else {
            $data['age_status'] = -1;
        }
        //计算用户的游戏时间 和 休息时间
        $map['user_id'] = $request['user_id'];
        $map['login_time | down_time'] = period(0);
        $return = M('UserLoginRecord', 'tab_')->where($map)->order('id ASC')->select();
        $count = count($return);
        $play = 0;
        $down = 0;
        //游戏时间
        if ($count % 2 == 0) {
            for ($i = 0; $i < $count / 2; $i++) {
                $play += $return[$i * 2 + 1]['down_time'] - $return[$i * 2]['login_time'];
            }
        } else {
            for ($i = 0; $i < ceil($count / 2); $i++) {
                if (!empty($return[$i * 2 + 1]['down_time'])) {
                    $play += $return[$i * 2 + 1]['down_time'] - $return[$i * 2]['login_time'];
                } else {
                    $play += time() - $return[$i * 2]['login_time'];
                }
            }
        }
        if ($count <= 1) {
            $down = 0;
        } else if ($count == 2) {
            $down += time() - $return[1]['down_time'];
        } else {
            if ($count % 2 == 0) {
                for ($i = 0; $i < ($count / 2 - 1); $i++) {
                    $down += $return[$i * 2 + 2]['login_time'] - $return[$i * 2 + 1]['down_time'];
                }
                $down += time() - $return[$count - 1]['down_time'];
            } else {
                for ($i = 0; $i < (ceil($count / 2) - 1); $i++) {
                    $down += $return[$i * 2 + 2]['login_time'] - $return[$i * 2 + 1]['down_time'];
                }
            }
        }
        /*var_dump(floor($play/60));//向下取整的分钟数
        var_dump(floor($down/60));//向下取整的分钟数*/
        $data['play_time'] = floor($play / 60);
        $data['down_time'] = floor($down / 60);
        echo json_encode(array('status' => 1, 'data' => $data));
    }

    /**
     * 更改身份证账户   获得传递过来的UID，idcard，name进行更改数据库
     * @return mixed
     */
    public function idcard_change($token,$idcard,$real_name)
    {
        /*C(api('Config/lists'));
        $user = json_decode(file_get_contents("php://input"),true);*/
        //$user = $_GET;
        if (empty($token) || empty($idcard) || empty($real_name)) {
            $this->set_message(0, "fail", "用户数据异常");
        }
        $this->auth($token);
        $map['account'] = USER_ACCOUNT;
        $data['idcard'] = $idcard;
        $data['real_name'] = $real_name;

        //身份证认证
        $where11['name'] = 'age';
        $tool = M('tool',"tab_")->where($where11)->find();
        if ($tool['status'] == 0){
            $data['age_status'] =0;
        }else {
            $re = age_verify($data['idcard'], $data['real_name']);
            switch ($re) {
                case -1:
                    $this->set_message(0, "fail", "短信数量已经使用完！");
                    $data['age_status'] = 1;
                    break;
                case -2:
                    $this->set_message(0, "fail", "连接接口失败");
                    $data['age_status'] = 1;
                    break;
                case 0:
                    $this->set_message(0, "fail", "用户数据不匹配");
                    $data['age_status'] = 1;
                    break;
                case 1://成年
                    $data['age_status'] = 2;
                    break;
                case 2://未成年
                    $data['age_status'] = 3;
                    break;
                default:
            }
        }
        $return = M('User', 'tab_')->where($map)->save($data);
        if (!$return) {
            $this->set_message(0, "fail", "用户数据更新失败");
        }
        $data['status'] = 1;
        echo json_encode($data);
    }

    /**
     * 开机动画
     */
    public function open_picture(){
        $url=get_cover(C('APP_SET_COVER'),'path');
        if(substr($url,0,1)=='h'){
            $data=$url;
        }else{
            $data="http://".$_SERVER['HTTP_HOST'].$url;
        }
        echo json_encode(array('status' => 1, 'data' => $data));
    }
}
