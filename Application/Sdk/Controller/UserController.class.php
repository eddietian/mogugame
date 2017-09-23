<?php
namespace Sdk\Controller;
use Think\Controller;
use User\Api\MemberApi;
use Org\XiguSDK\Xigu;
use Org\UcenterSDK\Ucservice;

class UserController extends BaseController{
    /**
    *SDK用户登陆
    */
    public function user_login()
    {
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $user = json_decode(base64_decode(file_get_contents("php://input")), true);
        #判断数据是否为空
        if (empty($user)) {
            $this->set_message(0, "fail", "登陆数据不能为空");
        }
        if (C('UC_SET') == 1) {
            $userApi = new MemberApi();
            $result = $userApi->login_($user["account"], $user['password'], 1, $user["game_id"], get_game_name($user["game_id"]), $user['sdk_version']);#调用登陆
            
            $res_msg = array();
            switch ($result) {
                case -1:
                    $this->uc_login($user['account'],$user['password']);
                    break;
                case -2:
                    //$this->uc_login($user['account'],$user['password']);
                    $this->set_message(-2, "fail", "密码错误");
                    break;
                default:
                    if (is_array($result)) {
                        $user["user_id"] = $result['user_id'];
                        $this->add_user_play($user);
                        $res_msg = array(
                            "status" => 1,
                            "return_code" => "success",
                            "return_msg" => "登陆成功",
                            "user_id" => $user["user_id"],
                            "token" => $result['token'],
                            "OTP_token" => think_encrypt(json_encode(array('uid' => $user["user_id"], 'time' => time())), 1),
                            'is_uc'=>0,
                        );
                    } else {
                       $this->uc_login($user['account'],$user['password']);
                    }
                    break;
            }
            echo base64_encode(json_encode($res_msg));
        } else {
            #实例化用户接口
            $userApi = new MemberApi();
            $result = $userApi->login_($user["account"], $user['password'], 1, $user["game_id"], get_game_name($user["game_id"]), $user['sdk_version']);#调用登陆
            $res_msg = array();
            switch ($result) {
                case -1:
                    $this->set_message(-1, "fail", "用户不存在或被禁用");
                    break;
                case -2:
                    $this->set_message(-2, "fail", "密码错误");
                    break;
                default:
                    if (is_array($result)) {
                        $user["user_id"] = $result['user_id'];
                        $this->add_user_play($user);
                        $res_msg = array(
                            "status" => 1,
                            "return_code" => "success",
                            "return_msg" => "登陆成功",
                            "user_id" => $user["user_id"],
                            "token" => $result['token'],
                            "OTP_token" => think_encrypt(json_encode(array('uid' => $user["user_id"], 'time' => time())), 1),
                            'is_uc'=>0,
                        );
                    } else {
                        $this->set_message(0, "fail", "未知错误");
                    }
                    break;
            }
            echo base64_encode(json_encode($res_msg));
        }

    }
    private function uc_login($account,$password){
        $user['account']=$account;
        $user['password']=$password;
        $uc = new Ucservice();
        $uidarray = $uc->uc_login($user['account'], $user['password'], 1);

        if ($uidarray == -1) {
            $this->set_message(-1, "fail", "用户不存在或被禁用");

        } else if ($uidarray == -2) {
            $this->set_message(-2, "fail", "密码错误");
        } else {
            if ($uidarray > 0) {
                $is_c = find_uc_account($user['account']);

                if (is_array($is_c)) {
                    $user['user_id'] = $is_c['id'];
                    $this->add_user_play($user);
                    $this->updateLogin_($is_c['id'], $is_c['account'], $user['fgame_id'], $user['game_id'], get_game_name($user["game_id"]));
                } else {
                    $user['user_id'] = $uidarray;
                }
              $this->set_login_msg($user["user_id"],MD5($uidarray . $user['account'] . $user['password'] . NOW_TIME . sp_random_string(7)),1);
            }
        }
        exit;
    }
  
    /**
     * 第三方登录
     */
    public function oauth_login(){
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
         if(empty($request)){$this->set_message(0,"fail","登陆数据不能为空");}
        $openid=$request['openid'];
        if($request['login_type']=="wx"){
            Vendor("WxPayPubHelper.WxPayPubHelper");
            // 使用jsapi接口
            $jsApi = new \JsApi_pub();
            $wx = $jsApi->create_openid(C('wx_login.appid'), C('wx_login.appsecret'), $request['code']);
            if(empty($wx['openid'])){
                 echo base64_encode(json_encode(array("status"=>-1,"message"=>"微信参数错误")));exit();
            }
            $openid=$wx['openid'];
            $register_type=3;
        }elseif ($request['login_type']=="bd") {
            if(empty($request['accessToken'])){
                 $res['status'] = -1;
                 $res['message'] = '用户不存在或被禁用';
                 echo base64_encode(json_encode($res));exit();
            }
           $url="https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser?access_token=".$request['accessToken'];
           $baidu_url=json_decode(file_get_contents($url),true);
           $register_type=5;
           $openid=$baidu_url['uid'];
        }elseif($request['login_type']=="qq"){
           $register_type=4;
        }elseif($request['login_type']=="wb"){
           $register_type=6;
        }elseif($request['login_type']=="yk"){
           $register_type=0;
        }
        $map['openid']=$openid;
        if($request['login_type']=="yk"&&isset($request['account'])){
            unset($map['openid']);
            $map['account']=$request["account"];
            $map['register_type']=0;
        }elseif($request['login_type']=="yk"){
            $map['id']=-1;
        }
        $data = M('user','tab_')->where($map)->find();
        if(empty($data)){//注册
            do{
                $data['account'] = $request['login_type'].'_'.sp_random_string();
                $account = M('user','tab_')->where(['account'=>$data['account']])->find();
            }while(!empty($account));
            $data['password'] = sp_random_string(8);
            $data['nickname'] = $data['account'];
            $data['openid'] = $openid;
            $data['game_id'] = $request['game_id'];
            $data['game_name'] = get_game_name($request['game_id']);
            $data['promote_id'] = $request['promote_id'];
            $data['promote_account'] = get_promote_name($request['promote_id']);
            $data['register_way'] = 1;
            $data['register_type'] = $register_type;
            $data['sdk_version'] = $request['sdk_version'];
            $data['game_appid'] = $request['game_appid'];
            $userApi = new MemberApi();
            $uid = $userApi->tr_register($data);
            if($uid < 0){
                $res['status'] = -1;
                $res['message'] = '注册失败';
                echo base64_encode(json_encode($res));exit;
            }
        }
            //登录
            $userApi = new MemberApi();
            $result = $userApi->login_($data["account"], $data['account'], 1, $request["game_id"], get_game_name($request['game_id']), $request['sdk_version']);
            if ($result == -1) {
                $res['status'] = -1;
                $res['message'] = '用户不存在或被禁用';
            } else {
                $request["user_id"] = $result['user_id'];
                $this->add_user_play($request);
                $res['status'] = 1;
                $res['message'] = '登录成功';
                $res['user_id'] = $result['user_id'];
                $res['account'] = $data['account'];
                $res['token'] = $result['token'];
            }
        echo base64_encode(json_encode($res));
    }
    /**
    *第三方登陆设置
    */
    public function thirdparty($value='')
    {
        $str = "qq_login,wx_login,wb_login,bd_login";
        $this->BaseConfig($str);
    }
    /**
    *显示扩展设置信息
    */
    protected function BaseConfig($name='')
    {   
        $map['name'] = array('in',$name);
        $map['status'] = 1;
        $tool = M('tool',"tab_")->where($map)->select();
        $data['config']='';
        if(!empty($tool)){
            foreach ($tool as $key => $val) {
                if($val['name']=='qq_login'){
                    $data['config'].='qq|';
                }
                if($val['name']=='wx_login'){
                    $data['config'].='wx|';
                }
                if($val['name']=='wb_login'){
                    $data['config'].='wb|';
                }
                if($val['name']=='bd_login'){
                    $data['config'].='bd|';
                }
            }
        }
        if($data['config']!=''){
            $data['config']=substr($data['config'],0,strlen($data['config'])-1);
        }        
        echo base64_encode(json_encode($data));
    }
    /**
     * 第三方登录参数请求
     */
    public function oauth_param(){
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $type = $request['login_type'];
        switch ($type) {
            case 'qq':
                $param['qqappid'] = C('qq_login.appid');
                break;
            case 'wx':
                $param['weixinappid'] = C('wx_login.appid');
                $param['weixinappsecret'] = C('wx_login.appsecret');
                break;
            case 'wb':
                $param['weiboappkey'] = C('wb_login.appkey');
                break;
            case 'bd':
                $param['clientid'] = C('bd_login.clientid');
                break;
        }
        if (empty($param)){
            $result['status'] = -1;
            $result['message'] = '服务器未配置此参数';
        }else{
            $result['status'] = 1;
            $result['message'] = '请求成功';
            $result['param'] = $param;
        }
        echo base64_encode(json_encode($result));
    }


    public function user_register()
    {
        C(api('Config/lists'));
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $user = json_decode(base64_decode(file_get_contents("php://input")), true);
        #判断数据是否为空
        if (empty($user)) {
            $this->set_message(0, "fail", "注册数据不能为空");
        }
        if (C('UC_SET') == 1) {
            $uc = new Ucservice();
            $uc_id = $uc->uc_register($user['account'], $user['password'], "", $user['promote_id'], get_promote_name($user['promote_id']), $user['game_id'], get_game_name($user['game_id']), 1, 1,1);
            if ($uc_id == -1) {
                $this->set_message(-1, "fail", "用户名不合法");
            } elseif ($uc_id == -2) {
                $this->set_message(-2, "fail", "包含敏感字符");
            } elseif ($uc_id == -3) {
                $this->set_message(-3, "fail", "用户名已存在");
            } else {
                if ($uc_id >= 0) {
                    $this->reg_data($user);
                }
            }
        } else {
            $this->reg_data($user);
        }
    }
    /**
    *手机用户注册
    */
    public function user_phone_register(){
        C(api('Config/lists'));
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $user = json_decode(base64_decode(file_get_contents("php://input")),true);
        #判断数据是否为空
        if(empty($user)){$this->set_message(0,"fail","注册数据不能为空");}
        #验证短信验证码
        $this->sms_verify($user['account'],$user['code']);
        if(empty($user)){$this->set_message(0,"fail","注册数据不能为空");}
        if(C('UC_SET')==1){
        $uc = new Ucservice();
        $uc_id=$uc->uc_register($user['account'],$user['password'],"",$user['promote_id'],get_promote_name($user['promote_id']),$user['game_id'],get_game_name($user['game_id']),1,1,1);
        if($uc_id == -1) {
                 $this->set_message(-1,"fail","用户名不合法");
           } elseif($uc_id == -2) {
                $this->set_message(-2,"fail","包含敏感字符");
           } elseif($uc_id == -3) {
                $this->set_message(-3,"fail","用户名已存在");
            }else{
        if($uc_id>=0){
            $this->reg_data($user,2);
            }
         }
        }else{
            $this->reg_data($user,2);
        }

    }
    //注册信息
    public function reg_data($user,$type=1){
        #实例化用户接口
        $userApi = new MemberApi();
        // user表加game_id
        if($type==2){//手机2
        $result = $userApi->register_($user['account'],$user['password'],1,2,$user['promote_id'],get_promote_name($user['promote_id']),$user['account'],$user["game_id"],get_game_name($user["game_id"]),$user['sdk_version']);
        }else{//用户1
        $result = $userApi->register_($user['account'],$user['password'],1,1,$user['promote_id'],get_promote_name($user['promote_id']),$phone="",$user["game_id"],get_game_name($user["game_id"]),$user['sdk_version']);

        }
        $res_msg = array();
        if($result > 0){
            $this->set_message(1,"success","注册成功");
        }
        else{
            switch ($result) {
                case -3:
                    $this->set_message(-3,"fail","用户名已存在");
                    break;
                default:
                    $this->set_message(0,"fail","注册失败");
                    break;
            }
        }
    }

    /**
    *修改用户数据
    */
    public function user_update_data(){
        $user = json_decode(base64_decode(file_get_contents("php://input")),true);
         C(api('Config/lists'));
        #判断数据是否为空
        if(empty($user)){$this->set_message(0,"fail","操作数据不能为空");}
        #实例化用户接口
        $data['id'] = $user['user_id'];
        // $wx_user=M('user','tab_')->where($data)->find();
        // if($wx_user['openid']!=="0"){
        //   $this->set_message(0,"fail","微信用户暂不支持");
        // }
        $userApi = new MemberApi();
        $uc=new Ucservice();
        switch ($user['code']) {
            case 'phone':
                #验证短信验证码
                $this->sms_verify($user['phone'],$user['sms_code']);
                $data['phone'] = $user['phone'];
                break;
            case 'nickname':
                $data['nickname'] = $user['nickname'];
                break;
            case 'pwd':
                $data['old_password'] = $user['old_password'];
                $data['password'] = $user['password'];  
                break;
            case 'account':
                $data['account'] = $user['account'];
                $data['password'] = $user['password'];  
                $data['register_type'] = 1;  //游客改为账号注册
                $map['account'] = $user['account'];
                $res = M('user','tab_')->where($map)->find();
                if ($res) {
                     $this->set_message(-3,"fail","已存在该用户名");
                }
                if (C('UC_SET') == 1) {
                    $das =$uc->get_uc($user['account']);
                    if ($das) {
                        $this->set_message(-4,"fail","已存在该uc用户名");
                    }
                    //修改uc用户信息
                    //--------
                }
                $result = $this->updata_user_youke($data);
                break;
            default:
                $this->set_message(0,"fail","修改信息不明确");
                break;
        }
        $result = $userApi->updateUser($data);
        if($result == -2){
            $this->set_message(-2,"fail","旧密码输入不正确");
        }
        else if($result !== false){ 
            if(C('UC_SET')==1){
            if($user['code']=="pwd"){
                $data_uc=$uc->get_uc($user['account']);
            if(is_array($data_uc)){
                 $uc_id=$uc->uc_edit($user['account'],$user['old_password'],$user['password']);
                }
            }
            
            } 
            if($user['code']=='pwd'){
                $u_uid['uid']=$user['user_id'];
                M('user_pwd')->where($u_uid)->setField('password',think_encrypt($data['password']));
            }
            
            $this->set_message(1,"success","修改成功");
        }
        else{
            $this->set_message(0,"fail","修改失败");
        }
    }
    //游客改名，对应修改数据
    private function updata_user_youke($data){
        $map['user_id'] = $data['id'];
        $data1['user_account'] = $data['account']; 
        $res = M('user_play_info','tab_')->where($map)->setField($data1);
        $res1 = M('user_play','tab_')->where($map)->setField($data1);
        $res2 = M('user_login_record','tab_')->where($map)->setField($data1);
        $res3 = M('spend','tab_')->where($map)->setField($data1);
        $res4 = M('provide','tab_')->where($map)->setField($data1);
        $res5 = M('mend','tab_')->where($map)->setField($data1);
        $res6 = M('gift_record','tab_')->where($map)->setField($data1);
        $res7 = M('deposit','tab_')->where($map)->setField($data1);
        $res8 = M('bind_spend','tab_')->where($map)->setField($data1);
        $res9 = M('balance_edit','tab_')->where($map)->setField($data1);
        $res10 = M('agent','tab_')->where($map)->setField($data1);
        /*if ($res !== false && $res1 !== false && $res2 !== false && $res3 !== false && $res4 !== false && $res5 !== false && $res6 !== false && $res7 !== false && $res8 !== false && $res9 !== false && $res10 !== false) {
        }*/
    }

    //验证验证码
    public function verify_sms(){
        $user = json_decode(base64_decode(file_get_contents("php://input")),true);
        if(empty($user)){$this->set_message(0,"fail","操作数据不能为空");}
        $this->sms_verify($user['phone'],$user['code']);
    }
    /**
    *忘记密码接口
    */
    public function forget_password(){
        $user = json_decode(base64_decode(file_get_contents("php://input")),true);
        $userApi = new MemberApi();
        #验证短信验证码
        $this->sms_verify($user['phone'],$user['code']);
        $result = $userApi->updatePassword($user['user_id'],$user['password']);
        if($result == true){
            $this->set_message(1,"success","修改成功");
        }
        else{
            $this->set_message(0,"fail","修改失败");
        }
    }

    /**
    *添加玩家信息
    */
    private function add_user_play($user = array()){
        $user_play = M("UserPlay","tab_");
        $map["game_id"] = $user["game_id"];
        $map["user_id"] = $user["user_id"];
        $map['sdk_version']=$user['sdk_version'];
        $res = $user_play->where($map)->find();
        if(empty($res)){
            $user_entity = get_user_entity($user["user_id"]);
            $data["user_id"] = $user["user_id"];
            $data["user_account"] = $user_entity["account"];
            $data["user_nickname"] = $user_entity["nickname"];
            $data["game_id"] = $user["game_id"];
            $data["game_appid"] = $user["game_appid"];
            $data["game_name"] = get_game_name($user["game_id"]);
            $data["server_id"] = 0;
            $data["server_name"] = "";
            $data["role_id"] = 0;
            $data['parent_id']=$user_entity["parent_id"];
            $data['parent_name']=$user_entity["parent_name"];
            $data["role_name"] = "";
            $data["role_level"] = 0;
            $data["bind_balance"] = 0;
            $data["promote_id"] = $user_entity["promote_id"];
            $data["promote_account"] = $user_entity["promote_account"];
            $data['play_time']=time();
            $data['play_ip'] = get_client_ip();
            $data["sdk_version"] = $user["sdk_version"];
            $user_play->add($data);
        }
        $this->save_user_play_info($user);
    }

    //修改角色名称
    public function update_user_play(){
        $user = json_decode(base64_decode(file_get_contents("php://input")), true);
        if(empty($user)){
           $this->set_message(0,"fail","操作数据不能为空");
        }
        $map['user_id']=$user['user_id'];
        $map['game_id']=$user['game_id'];
        $userplay=M('user_play','tab_')->where($map)->find();
        if(null==$userplay){
             $this->set_message(0,"fail","玩家不存在");
        }else{
            $user_play_map['id']=$userplay['id'];
            if($user['type']==1){
                $update=M('user_play','tab_')->where($user_play_map)->setField('role_name',$user['role_name']);
            }else{
                $update=M('user_play','tab_')->where($user_play_map)->setField('role_level',$user['role_level']);
            }
            if($update){
                $this->set_message(1,"success","修改成功");
            }else{
                $this->set_message(0,"fail","修改失败");

            }
        }
    }

    /**
     * 添加游戏角色数据
     * @param $request
     */
    public function save_user_play_info($request){
        $user_id = $request['user_id'];
        $server_id = empty($request['server_id']) ? 0 : $request['server_id'];
        $map['user_id'] = $user_id;
        $map['game_id'] = $request['game_id'];
        $map['server_id'] = $server_id;
        $user_play = M('user_play_info','tab_');
        $res = $data = $user_play->where($map)->find();
        $user = M('user', 'tab_');
        $user_data = $user->find($user_id);
        $data['promote_id'] = $request['promote_id'];
        $data['promote_account'] = get_promote_account($request['promote_id']);
        $data['game_id'] = $request['game_id'];
        $data['game_name'] = get_game_name($request['game_id']);
        $data['server_id'] = $server_id;
        $data['server_name'] = $request['server_name'];
        $data['user_id'] = $user_id;
        $data['user_account'] = $user_data['account'];
        $data["user_nickname"] = $user_data["nickname"];
        $data['play_time'] = time();
        $data["sdk_version"] = $request["sdk_version"];
        $data['play_ip'] = get_client_ip();
        if(empty($res)){
            $user_play->add($data);
        }else{
            $user_play->save($data);
        }
    }

    /**
    *短信发送
    */
    public function send_sms()
    {
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        //$time = NOW_TIME - session($data['phone'].".create_time");
        $phone = $data['phone'];
        /// 产生手机安全码并发送到手机且存到session
        $rand = rand(100000,999999);
        $xigu = new Xigu(C('sms_set.smtp'));
        $param = $rand.",".'1';
	    if(get_tool_status("sms_set")) {
		    $result = json_decode($xigu->sendSM(C('sms_set.smtp_account'), $phone, C('sms_set.smtp_port'), $param), true);
		    $result['create_time'] = time();
		    //$r = M('Short_message')->add($result);
		    #TODO 短信验证数据
		    if($result['send_status'] == '000000') {
			    session($phone,array('code'=>$rand,'create_time'=>NOW_TIME));
			    echo base64_encode(json_encode(array('status'=>1,'return_code'=>'success','return_msg'=>'验证码发送成功')));
		    }
		    else{
			    $this->set_message(0,"fail","验证码发送失败，请重新获取");
		    }
	    }elseif(get_tool_status("alidayu")){
		    $result = $xigu->alidayu_send($phone,$rand);
		    if($result == false){
			    $this->set_message(0,"fail","验证码发送失败，请重新获取");
		    }
		    session($phone,array('code'=>$rand,'create_time'=>NOW_TIME));
		    echo base64_encode(json_encode(array('status'=>1,'return_code'=>'success','return_msg'=>'验证码发送成功')));
	    }elseif(get_tool_status('jiguang')){
            $result = $xigu->jiguang($phone,$rand,'');
            if($result == false){
                echo base64_encode(json_encode(array('status'=>0,'msg'=>'发送失败，请重新获取')));exit;
            }
            session($phone,array('code'=>$rand,'create_time'=>NOW_TIME));
            echo base64_encode(json_encode(array('status'=>1,'return_code'=>'success','return_msg'=>'验证码发送成功')));
        }else{
		    $this->set_message(0,"fail","没有配置短信发送");
	    }

    }

    /**
    *用户基本信息
    */
    public function user_info(){
        C(api('Config/lists'));
        $user = json_decode(base64_decode(file_get_contents("php://input")),true);
        if(C('UC_SET')==1){
            //查找本地用户
            if(!is_array(find_uc_account($user['account']))){
                $uc = new Ucservice();
                $uc_user=$uc->get_user_from_uid($user['user_id']);//查找UC用户
                if(empty($uc_user)){
                    $this->set_message(0,"fail","UC用户数据异常");
                }else{
                    //UC存在则 查找其他数据库 配置文件里
                    $sqltype = 2;
                    $user = M('User', 'tab_', C('DB_CONFIG2'))->field('account,nickname,phone,balance')->where(array('account' => $user['account']))->find();
                    if (empty($user)) {
                        $sqltype = 3;
                        $user = M('User', 'tab_', C('DB_CONFIG3'))->field('account,nickname,phone,balance')->where(array('account' => $user['account']))->find();
                    }
                    if(empty($user)){
                        $this->set_message(0,"fail","用户数据异常");
                    }else{
                        $user['phone'] = empty($user["phone"])?" ":$user["phone"];
                        $user['status'] = 1;
                        $user['bind_balance'] = 0.00;
                        echo base64_encode(json_encode($user));exit;
                    }
                }
            }
        }

        $model = M("user","tab_");
        $data = array();
        switch ($user['type']) {
            case 0:
               $data = $model
                ->field("account,nickname,phone,balance,bind_balance,game_name,register_type,age_status,idcard,real_name")
                ->join("INNER JOIN tab_user_play ON tab_user.id = tab_user_play.user_id and tab_user.id = {$user['user_id']} and tab_user_play.game_id = {$user['game_id']}")
                ->find();
                break;
            default:
                $map['account'] = $user['user_id'];
                $data = $model->field("id,account,nickname,phone,balance,age_status,idcard,real_name")->where($map)->find();
                break;
        }        
        if(empty($data)){
            $this->set_message(0,"fail","用户数据异常");
        }
        $data['phone'] = empty($data["phone"])?" ":$data["phone"];
        $data['status'] = 1;

        echo base64_encode(json_encode($data));
    }

    /**
    *用户平台币充值记录
    */
    public function user_deposit_record(){
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        $map["user_id"] = $data["user_id"];
        $map["pay_status"] = 1;
        if(C('UC_SET')==1){
            //查找本地用户
            if(!is_array(find_uc_account($data['account']))){
                $uc = new Ucservice();
                $uc_user=$uc->get_user_from_uid($data['user_id']);//查找UC用户
                if(empty($uc_user)){
                    $this->set_message(0,"fail","UC用户数据异常");
                }else{
                    $page=$data['index']==''?1:$data['index'];
                    $ucmap.=" user_account='".$data["account"]."' and ";
                    $ucmap.=" pay_status=1 and ";
                    $ucmap.=" version=1 and ";
                    $ucmap.="platform!=1";
                    $deposit=$uc->uc_deposit_select($page,10,$ucmap,1);
                    $count = $deposit['count'];
                    unset($deposit['count']);
                    unset($deposit['totalpage']);
                    unset($deposit['total']);
                    $return_data['status'] = 1;
                    $return_data['total'] = $count;
                    $return_data['data'] = $deposit;
                    echo base64_encode(json_encode($return_data));exit;
                }
            }
        }
        $deposit = M("deposit","tab_")->field('pay_way,pay_amount,create_time')->where($map)->order('id desc')->page($data['index'],10)->select();
        $count = M("deposit","tab_")->field('pay_way,pay_amount,create_time')->where($map)->count();
        if(empty($deposit)){
            echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"暂无记录")));exit();
        }
        $return_data['status'] = 1;
        $return_data['total'] = $count;
        $return_data['data'] = $deposit;
        echo base64_encode(json_encode($return_data));
    }

    /**
    *用户领取礼包记录
    */
    public function user_gift_record(){
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        $map["user_id"] = $data["user_id"];
        $map["game_id"] = $data["game_id"];
        $gift = M("GiftRecord","tab_")
        ->field("tab_gift_record.game_id,tab_gift_record.game_name,tab_giftbag.giftbag_name ,tab_giftbag.digest,tab_gift_record.novice,tab_gift_record.status,tab_giftbag.start_time,tab_giftbag.end_time")
        ->join("LEFT JOIN tab_giftbag ON tab_gift_record.gift_id = tab_giftbag.id where user_id = {$data['user_id']} and tab_gift_record.game_id = {$data['game_id']}")
        ->select();
        if(empty($gift)){
            echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"暂无记录")));exit();
        }
        foreach ($gift as $key => $val) {
            $gift[$key]['icon'] = $this->set_game_icon($val[$key]['game_id']);
            $gift[$key]['now_time'] = NOW_TIME;
        }
        
        $return_data['status'] = 1;
        $return_data['data'] = $gift;
        echo base64_encode(json_encode($return_data));
    }

    /**
    *用户平台币(绑定和非绑定)
    */
    public function user_platform_coin(){
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
         C(api('Config/lists'));
          if(C('UC_SET')==1){
            if(!is_array(find_uc_account($data['account']))){
                $uc = new Ucservice();
                $uc_user=$uc->get_user_from_uid($data['user_id']);//查找UC用户
                if(empty($uc_user)){
                    $this->set_message(0,"fail","UC用户数据异常");
                }else{
                    //UC存在则 查找其他数据库 配置文件里
                    $sqltype = 2;
                    $user = M('User', 'tab_', C('DB_CONFIG2'))->field('account,nickname,phone,balance')->where(array('account' => $data['account']))->find();
                    if (empty($user)) {
                        $sqltype = 3;
                        $user = M('User', 'tab_', C('DB_CONFIG3'))->field('account,nickname,phone,balance')->where(array('account' => $data['account']))->find();
                    }
                    if(empty($user)){
                        $this->set_message(0,"fail","用户数据异常");
                    }else{
                        $platform_coin = array();
                        $platform_coin['status'] = 1;
                        $platform_coin["balance"] = $user["balance"];
                        $platform_coin["bind_balance"] = 0;
                        echo base64_encode(json_encode($platform_coin));exit;
                    }
                }
            }
        }
        $user_play = M("UserPlay","tab_");
        $platform_coin = array();
        $user_data = array();
        #非绑定平台币信息
        $user_data = get_user_entity($data["user_id"]);
        $platform_coin['status'] = 1;
        $platform_coin["balance"] = $user_data["balance"];
        #绑定平台币信息
        $map["user_id"] = $data["user_id"];
        $map["game_id"] = $data["game_id"];
        $user_data = $user_play->where($map)->find();
        $platform_coin["bind_balance"] = $user_data["bind_balance"];
        echo base64_encode(json_encode($platform_coin));
    }

    //判断帐号是否存在
    public function account_exist(){
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        $map['account'] = $data['account'];
        $user = M('user','tab_')->where($map)->find();
        if(empty($user)){
            echo json_encode(array('status'=>-1,'msg'=>'帐号不存在'));
        }else{
            echo json_encode(array('status'=>1));
        }
    }

    //解绑手机
    public function user_phone_unbind(){
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        $this->sms_verify($data['phone'],$data['code']);
        $map['id']=$data['user_id'];
        $user=M('user','tab_')->where($map)->setField('phone',"");
        if($user){
            echo base64_encode(json_encode(array('status'=>1,'return_msg'=>'解绑成功')));

        }else{
             echo base64_encode(json_encode(array('status'=>-1,'return_msg'=>'解绑失败')));

        }
    }

    //常见问题
    public function get_problem(){
        $data = M('document')
            ->join("left join sys_category c on c.name='FAQ'")
            ->where('c.id = sys_document.category_id AND sys_document.status = 1')
            ->field('sys_document.id,sys_document.title,sys_document.description')
            ->select();
        echo base64_encode(json_encode($data));
    }

    //留言
    public function get_question(){
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $type = $request['type'];
        $user_id = $request['user_id'];
        $content = $request['content'];
        $map['user_id'] = $user_id;
        $data = M('question','tab_')->where($map)->find();
        if($type == 1){
            if(empty($data)){
                $data['create_time'] = time();
                $question[time()] = $content;
                $data['question'] = json_encode($question);
                $data['user_id'] = $user_id;
                $data['account'] = get_user_entity($user_id)['account'];
                M('question','tab_')->where($map)->add($data);
            }else{
                $question = json_decode($data['question'],true);
                $question[time()] = $content;
                $data['question'] = json_encode($question);
                $data['account'] = get_user_entity($user_id)['account'];
                M('question','tab_')->where($map)->save($data);
            }
        }
        $question = json_decode($data['question'],true);
        foreach ($question as $k=>$v) {
            $res[$k][1] = $v;
        }
        $answer = json_decode($data['answer'],true);
        foreach ($answer as $key=>$value) {
            $res[$key][2] = $value;
        }
        ksort($content);
        echo base64_encode(json_encode($res));
    }

    //获取开启的支付
    public function get_pay_server(){
      $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        if(pay_set_status('wei_xin_app')==1 || pay_set_status('weixin')==1){
            if(get_game_appstatus($request['game_id'])){
                $wx_game=1;
            }else{
                $wx_game=0;
            }
    }else if(pay_set_status('wei_xin_app')==0 && pay_set_status('weixin')==0){
        $wx_game=0;
    }
    if(pay_set_status('alipay')==1){
        if(get_game_appstatus($request['game_id'])){
            $zfb_game=1;
        }else{
            $zfb_game=0;
        }
    }else{
        $zfb_game=0;
    }
    if(pay_set_status('jubaobar')==1){
        $jby_game=1;
    }else{
        $jby_game=0;
    }
        echo base64_encode(json_encode(array('status'=>1,'wx_game'=>$wx_game,'zfb_game'=>$zfb_game,'jby_game'=>$jby_game)));
    }

    //获取渠道折扣
    public function get_user_discount(){
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $game_id = $request['game_id'];
        $user_id = $request['user_id'];
        $user = get_user_entity($user_id);
        $promote_id = $user['promote_id'];
        $discount = $this->get_discount($game_id,$promote_id,$user_id);
        echo base64_encode(json_encode($discount));
    }

    public function test($game_id,$user_id){
        $user = get_user_entity($user_id);
        $promote_id = $user['promote_id'];
        $discount = $this->get_discount($game_id,$promote_id,$user_id);
        var_dump($discount);exit;
    }

    /**
     * 实名认证信息   获得传递过来的UID，返回该玩家是否已经通过审核
     * @return mixed
     */
    public function return_age(){
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        if(empty($request)){$this->set_message(0,"fail","操作数据不能为空");}

        $mmm['account'] = $request['account'];
        $user = M('User','tab_')->where($mmm)->find();

        //添加登录记录
        $da=array(
            'user_id'=>$user['id'],
            'user_account'=>$user['account'],
            'user_nickname'=>$user['nickname'],
            'game_id'=>$request['game_id'],
            'game_name'=>get_game_name($request["game_id"]),
            'server_id'=>null,
            'type'=>1,
            'server_name'=>null,
            'login_time'=>NOW_TIME,
            'login_ip'=>get_client_ip(),
            'sdk_version'=>$request['sdk_version'],
            'promote_id' =>$request['promote_id']
        );
        $denglu = M('UserLoginRecord','tab_')->add($da);

        $data = C('age_prevent');
        $data['on-off'] = $data['status'];
        unset($data['status']);
        $res['date']=$data;
        $where['account'] = $request['account'];
        $re = M('User','tab_')->field('age_status')->where($where)->find();
        if ($re){
            $data['age_status'] = $re['age_status'];
        }else{
            $data['age_status'] = -1;
        }
        //计算用户的游戏时间 和 休息时间
        $map['user_id'] = $request['user_id'];
        $map['login_time | down_time'] = period(0);
        $map['down_time'] = 0;
        $map['status'] = 0;

        $map2['user_id'] = $request['user_id'];
        $map2['login_time | down_time'] = period(0);
        $map2['login_time'] = 0;
        $map2['status'] = 0;

        $login_ = M('UserLoginRecord','tab_')->where($map)->order('login_time ASC')->select();
        $down_ = M('UserLoginRecord','tab_')->where($map2)->order('down_time ASC')->select();
        $login_count =count($login_);
        $down_count = count($down_);
        $play=0;
        $down=0;
        if ($login_count >= $down_count && $down_count != 0){
            for ($i=0;$i<$down_count;$i++){
                $play += $down_[$i]['down_time'] - $login_[$i]['login_time'];
                if ($down_[$i+1]['down_time'] == 0 && $login_[$i+1]['login_time'] != 0){
                    $play+=time()-$login_[$i+1]['login_time'];
                }
                if ($login_[$i+1]['login_time'] != 0){
                    $down+=$login_[$i+1]['login_time']-$down_[$i]['down_time'];
                }
            }
        }
        if ($down_count == 0 && $login_count > 0){
            $play += time()- $login_[0]['login_time'];
        }

        $data['play_time'] = floor($play/60);
        $data['down_time'] = floor($down/60);

        //累计在线时间大于最长在线时间（两个未满18岁防沉迷时间的和） 继续在线就算在休息时间里面了
        if ($data['play_time']/60 >= ($data['hours_off_one'] + $data['hours_off_two'])){
            $data['down_time'] += $data['play_time'] - $data['hours_off_one']*60 - $data['hours_off_two']*60;
            $data['play_time'] = $data['hours_off_one']*60 + $data['hours_off_two']*60;
        }

        //一旦游戏时间满足恢复时间  两种时间全部清零
        if($data['down_time']-$data['hours_cover']*60 >= 0){
            $where2['user_id'] = $request['user_id'];
            $where2['login_time | down_time'] = period(0);
            $mmp['status'] = 1;
            M('UserLoginRecord','tab_')->where($where2)->save($mmp);
            $deng['id'] = $denglu;
            $de['status'] = 0;
            M('UserLoginRecord','tab_')->where($deng)->save($de);
        }

        echo base64_encode(json_encode(array('status'=>1,'data'=>$data)));
    }

    /**
     * 更改身份证账户   获得传递过来的UID，idcard，name进行更改数据库
     * @return mixed
     */
    public function idcard_change(){
        C(api('Config/lists'));
        $user = json_decode(base64_decode(file_get_contents("php://input")),true);
        if(empty($user['user_id']) || empty($user['idcard']) || empty($user['real_name'])){
            $this->set_message(0,"fail","用户数据异常");
        }
        $map['id'] = $user['user_id'];
        $data['idcard'] = $user['idcard'];
        $data['real_name'] = $user['real_name'];

        //身份证认证
        $where11['name'] = 'age';
        $tool = M('tool',"tab_")->where($where11)->find();
        if ($tool['status'] == 0){
            $data['age_status'] =0;
        }else{

            $re = age_verify($data['idcard'],$data['real_name']);
            switch ($re)
            {
                case -1:
                    $this->set_message(0,"fail","短信数量已经使用完！");$data['age_status'] =1;
                    break;
                case -2:
                    $this->set_message(0,"fail","连接接口失败");$data['age_status'] =1;
                    break;
                case 0:
                    $this->set_message(0,"fail","用户数据不匹配");$data['age_status'] =1;
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
        $return = M('User','tab_')->where($map)->save($data);
        if(!$return){
            $this->set_message(0,"fail","用户数据更新失败");
        }
        $data['status']=1;
        echo base64_encode(json_encode($data));
    }

    /**
     * 通过用户的user_id 返回用户的下线时间 必要user_id  可选game_id role_id
     */
    public function down_time(){
        C(api('Config/lists'));
        $user = json_decode(base64_decode(file_get_contents("php://input")),true);
        $map['user_id'] = $user['user_id'];
        $map['login_time'] = 0;
        if (!empty($user['game_id'])){
            $map['game_id'] = $user['game_id'];
        }
        if (!empty($user['role_id'])){
            $map['role_id'] = $user['role_id'];
        }
        $return = M('UserLoginRecord','tab_')->where($map)->limit(1)->order('id DESC')->select();
        if (empty($return)){
            $this->set_message(0,"fail","该用户没有下线记录");
        }
        echo base64_encode(json_encode($return));
    }


    /**
     * 接口  获得用户的下线数据并且存到数据库大众
     */
    public function get_down_time(){
        C(api('Config/lists'));
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        if (empty($request)){
            $this->set_message(0,"fail","参数错误");
        }
        $mmm['account'] = $request['account'];
        $user = M('User','tab_')->where($mmm)->find();
        if (!$user){
            $this->set_message(0,"fail","找不到该用户!");
        }
        $da=array(
            'user_id'=>$user['id'],
            'user_account'=>$user['account'],
            'user_nickname'=>$user['nickname'],
            'game_id'=>$request['game_id'],
            'game_name'=>get_game_name($request["game_id"]),
            'server_id'=>null,
            'type'=>1,
            'server_name'=>null,
            'down_time'=>NOW_TIME,
            'login_ip'=>get_client_ip(),
            'sdk_version'=>$request['sdk_version'],
            'promote_id' =>$request['promote_id']
        );
        $return = M('UserLoginRecord','tab_')->add($da);
        if ($return){
            echo base64_encode(json_encode(array('status'=>1,'return_msg'=>'数据新增成功！')));
        }else{
            $this->set_message(0,"fail","数据新增失败!");
        }
    }
}
