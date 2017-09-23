<?php
namespace Mobile\Controller;

use Common\Api\GameApi;
use User\Api\SuserApi;
use Org\XiguSDK\Xigu;
class UnlrController extends BaseController {
	private $suser;
    
    public function __construct() {
        parent::__construct();
        $this->suser = new SuserApi;
    }
	
    // 账户验证 lwx  true 可以使用
    public function checkAccount($account){
        $res = $this->suser->checkAccount($account);
        if($res){
            echo json_encode(array('status'=>1));
        } else{
            echo json_encode(array('status'=>0));
        }
        
    }
    
    // 手机验证  lwx  true 可以使用
    public function checkMobile($phone) {
        $res = $this->suser->checkPhone($phone);
        if($res){
            echo json_encode(array('status'=>1));
        } else{
            echo json_encode(array('status'=>0));
        }
    }
    // 手机注册用户唯一性检测
    public function checkPhone($username) {
		if (IS_POST) {
			$len = strlen($username);
			if ($len !== mb_strlen($username) || $len !== 11 || !preg_match("/^1[3458][0-9]{9}$/u",$username)) {
				echo json_encode(array('status'=>0,'msg'=>'手机格式不正确'));exit;
			}
			$flag = $this->suser->checkAccount($username);
			if ($flag) {
                echo json_encode(array('status'=>1,'msg'=>'可以使用'));				
			} else {
                echo json_encode(array('status'=>0,'msg'=>'手机号码被占用'));
			}
		} else {
            echo json_encode(array('status'=>0,'msg'=>'服务器故障'));
        }
	}
    
    
    // 注册 lwx

    public function register($account=null,$password=null,$safecode=null,$register_way=0){
        if(IS_POST){   
            if (empty($account) || empty($password) || empty($safecode)) {
                echo json_encode(array('status'=>0,'msg'=>'提交数据不能为空'));exit;
            }
            
            $this->checksafecode($account,$safecode,false);
            $res = $this->suser->checkAccount($account);
            if (!$res) {
                echo json_encode(array('status'=>0,'msg'=>'手机号码被占用'));exit;
            }
            $register_way = 4;
            $register_type = 2;
            //$pid = $this->suser->register($account,$password,$account);
            if (isset($_POST['promote_id']) && !empty($_POST['promote_id'])) {
                $pid = $this->suser->register($account,$password,$account,$register_way,$register_type,$_POST['promote_id'],$_POST['promote_account']);
            } else {
                $pid = $this->suser->register($account,$password,$account,$register_way,$register_type);
            }
            if($pid > 0){
                $data = array(
                    'status' => 1,
                    'msg' => '注册成功',
                );
            } else{
                $data = array(
                    'status' => $pid,
                    'msg'  => '注册失败',
                );
            }
            echo json_encode($data);
        } else{
            if ($_REQUEST['url']) {
                $url= base64_decode($_REQUEST['url']);
            } else {
                $url=$_SERVER['HTTP_REFERER'];
            }
            $this->assign('url',$url);
            $this->display();
        }      
    }
    

    
    // 发送手机安全码
	public function telsafecode($phone='',$delay=10,$flag=true) {
        if (empty($phone)) {
            echo json_encode(array('status'=>0));exit;
        }
        //$res = $this->suser->checkPhone($phone);
        $rand = rand(100000,999999);
        $param = $rand.",".$delay;
        if(get_tool_status("sms_set")){
            checksendcode($phone,C('sms_set.limit'));
            $xigu = new Xigu(C('sms_set.smtp'));
	        $result = json_decode($xigu->sendSM(C('sms_set.smtp_account'),$phone,C('sms_set.smtp_port'),$param),true);
	        if ($result['send_status'] != '000000') {
		        echo json_encode(array('status'=>0,'msg'=>'发送失败，请重新获取'));exit;
	        }
        }elseif(get_tool_status("alidayu")){
            checksendcode($phone,C('alidayu.limit'));
            $xigu = new Xigu('alidayu');
	        $result = $xigu->alidayu_send($phone,$rand,$delay);
            $result['send_time'] = time();
	        if($result == false){
		        echo json_encode(array('status'=>0,'msg'=>'发送失败，请重新获取'));exit;
	        }
        }elseif(get_tool_status('jiguang')){
            checksendcode($phone,C('jiguang.limit'));
            $xigu = new Xigu('jiguang');
            $result = $xigu->jiguang($phone,$rand,$delay);
            $result['send_time'] = time();
            if($result == false){
                echo json_encode(array('status'=>0,'msg'=>'发送失败，请重新获取'));exit;
            }
        }elseif(get_tool_status('alidayunew')){
	        checksendcode($phone,C('alidayunew.limit'));
            $xigu = new Xigu('alidayunew');
            $result = $xigu->alidayunew_send($phone,$rand,$delay);
            $result['send_time'] = time();
            if($result == false){
                echo json_encode(array('status'=>0,'msg'=>'发送失败，请重新获取'));exit;
            }
        }elseif(get_tool_status('alidayumsg')){
            checksendcode($phone,C('alidayumsg.limit'));
            $xigu = new Xigu('alidayumsg');
            $result = $xigu->alidayumsg_send($phone,$rand,$delay);
            $result['send_time'] = time();
            if($result == false){
                echo json_encode(array('status'=>0,'msg'=>'发送失败，请重新获取'));exit;
            }
        }else{
            echo json_encode(array('status'=>0,'msg'=>'没有配置短信发送'));exit;
        }
        
	    // 存储短信发送记录信息
        $result['send_status'] = '000000';
        $result['phone'] = $phone;
        $result['create_time'] = time();
        $result['pid']=0;
        $result['create_ip']=get_client_ip();
        $r = M('Short_message')->add($result);

        // 存储短信发送记录信息
        $result['create_time'] = time();
        $result['pid']=0;
        $r = M('Short_message')->add($result);
        
        /*if ($result['send_status'] != '000000') {
            echo json_encode(array('status'=>0,'msg'=>'发送失败，请重新获取'));exit;
        }*/
        $safecode['code']=$rand;
        $safecode['phone']=$phone;
        $safecode['time']=time();//$result['send_time'];
        $safecode['delay']=$delay;
        session('safecode',$safecode);
        
        if ($flag) {
            echo json_encode(array('status'=>1,'msg'=>'安全码已发送，请查收','data'=>$safecode));
        } else
            echo json_encode(array('status'=>1,'msg'=>'')); 
    }
    
    // 发送安全码
    
    public function sendsafecode($phone,$delay=10) {
        if (IS_POST) {
            $verify = new \Think\Verify();
            if(!($verify->check(I('verify'),I('vid')))){               
                echo json_encode(array('status'=>0,'msg'=>'验证码不正确'));exit;
            }

            $res = $this->suser->checkAccount($phone);
            if (!$res) {
                echo json_encode(array('status'=>0,'msg'=>'手机号码被占用'));exit;
            }
            $this->telsafecode($phone);
            exit;
        } else{
            echo json_encode(array('status'=>0,'msg'=>'请按正确的流程'));exit;
        } 
    }
    
    public function forgetsafecode($phone) {
        if (IS_POST) {
            $verify = new \Think\Verify();
            if(!($verify->check(I('verify'),I('vid')))){               
                echo json_encode(array('status'=>0,'msg'=>'验证码不正确'));exit;
            }
            //$res = $this->suser->checkPhone($phone);
            
            $res = $this->suser->checkAccount($phone);
            if ($res) {
                echo json_encode(array('status'=>0,'msg'=>'该手机号码尚未经过验证'));exit;
            }
            
            $this->telsafecode($phone);
            exit;
        } else{
            echo json_encode(array('status'=>0,'msg'=>'请按正确的流程'));exit;
        } 
    }
    
    
    
    // 手机安全码验证
    // @param bool $flag  true 用于直接异步请求  false 用于方法调用  
    public function csafecode($phone,$vcode,$flag=true) {       
        $this->checksafecode($phone,$vcode,false);
        $data=array(
            'id' => SA_ID,
            'phone' => $phone,
        );
        $bool = $this->suser->updateInfo($data);
        
        if ($flag) {
            echo json_encode(array('status'=>1));
        }
    }
    
    
    // 手机安全码验证
    // @param bool $flag  true 用于直接异步请求  false 用于方法调用  
    public function checksafecode($phone,$vcode,$flag=true) {       
        $safecode = session('safecode');
        $time = (time() - $safecode['time'])/60;
        if ($time>$safecode['delay']) {
            session('safecode',null);unset($safecode);
            echo json_encode(array('status'=>0,'msg'=>'时间超时,请重新获取'));exit;
        }
        if (!($safecode['code'] == $vcode) || !($safecode['phone'] == $phone)) {
            echo json_encode(array('status'=>0,'msg'=>'安全码输入有误'));exit;
        }
        session('safecode',null);
        unset($safecode); 
        if ($flag) {
            echo json_encode(array('status'=>1));
        }
    }
    
    // 登录 lwx 
    public function login($account='',$password=''){
        if (IS_POST) {
            $res = $this->suser->login($account,$password);
            if($res > 0) {
                $data=array(
                    'status' => 1,
                    'msg' => '登录成功',
                );
            } else {
                switch($res) {
                    case -1000: $error = '账号不存在'; break; 
                    case -10021: $error = '密码错误！'; break;
                    case -1001: $error = '账号被禁用！'; break;
                    default: $error = '未知错误！'; break; 
                }
                $data=array(
                    'status' => 0,
                    'msg' => $error,
                );
            }
            echo json_encode($data);
        } else {
            if ($_REQUEST['url']) {
                $url= base64_decode($_REQUEST['url']);
            } else {
                $url=$_SERVER['HTTP_REFERER'];
            }
            $this->assign('url',$url);
            $this->display();
        }
    }
    
    // 忘记密码
    public function forget($account='',$password='',$safecode='') {               
        if (IS_POST) {
            $account1=$_POST['account1'];
            // dump($_POST);die();
            if (empty($account) || empty($password) || empty($safecode || empty($account1))) {
                echo json_encode(array('status'=>0,'msg'=>'提交数据不能为空'));exit;
            }
            
            $this->checksafecode($account,$safecode,false);
            $res = $this->suser->checkAccount($account);
            if ($res) {
                echo json_encode(array('status'=>0,'msg'=>'该手机号码尚未经过验证'));exit;
            }
            
            // $info = $this->suser->updateinfo($account,true);
            $info= M('user','tab_')->where(['account'=>$account1])->find();
            if($info){
                if($info['phone']!=$account){
                    $data=['status'=>0,'msg'=>'帐号和手机号不匹配！'];
                    echo json_encode($data);exit;
                    // $this->error('帐号和手机号不匹配！');
                }
            }else{
                $data=['status'=>0,'msg'=>'暂无此帐号！'];
                echo json_encode($data);exit;
                // $this->error('暂无此帐号！');
            }
            $res = $this->suser->updatePassword($info['id'],$password);
            if($res > 0){
                $data = array(
                    'status' => 1,
                    'msg' => '修改成功',
                );
            } else{
                $data = array(
                    'status' => 0,
                    // 'status' => $pid,
                    'msg'  => '修改失败',
                );
            }
            echo json_encode($data);
        } else {            
            if ($_REQUEST['url']) {
                $url= base64_decode($_REQUEST['url']);
            } else {
                $url=$_SERVER['HTTP_REFERER'];
            }
            $this->assign('url',$url);
            $this->display();
        }
    }
    
    // 登录验证 lwx 2016-05-25
    public function islogin() {
//        var_dump(session());exit;
        if (parent::islogin()) {
            if (session('user_auth.nickname')) {
                $account = session('user_auth.nickname');
            } else {
                $account = session('user_auth.account');
            }
            echo json_encode(array('status'=>1,'account'=>$account));
        } else {           
            echo json_encode(array('status'=>0));
        }
    }
    
    // 退出 lwx 2016-05-25
    public function logout() {
        session('suser_auth', null);
        session('suser_auth_sign', null);
        session('[destroy]');
        echo json_encode(array('status'=>1));                   
    }
    
    // 注册 session lwx 2016-05-25
    private function autologin($user) {        
        $auth = array(
            'id'              => $user['id'],
            'account'         => $user['account'],
            'nickname'        => $user['nickname'],
            'phone'           => $user['phone'],
        );
        session('suser_auth', $auth);
        session('suser_auth_sign', data_auth_sign($auth));
    }
    
    public function checkverify($verify) {
        if(check_verify($verify)){
            echo json_encode(array('status'=>1));
        } else {
            echo json_encode(array('status'=>0));           
        }
    }
    
    
    public function getGameGift($giftid,$giftname,$gameid,$gamename) {		
    
		if(parent::islogin()) {   
			$list=M('Gift_record',"tab_");
			$uid = session('user_auth.user_id');
			$info=$list->field('novice')->where("gift_id=$giftid and user_id=$uid")->find();
            
			if($info) {
				$data=$info['novice'];
				$this->ajaxReturn(array('status'=>'2','msg'=>'不要太贪心啊，给别人一个领取的机会吧','url'=>'','data'=>$data),'json');
			} else {
				$giftbag = M('Giftbag','tab_');
				$n = $giftbag->field('novice')->where("id=$giftid")->find();
				$n = $n['novice'];
				if (empty($n)) {
					$this->ajaxReturn(array('status'=>'0','msg'=>'你来晚了一步，礼包已被领取完了','url'=>'','data'=>''),'json');	
				} else {					
					$novice = explode(",",$n);
					$guid = $novice[0];										
					$data['game_id']=$gameid;
					$data['game_name']=$gamename;
					$data['gift_id']=$giftid;
					$data['gift_name']=$giftname;
					$data['status']=0;
					$data['novice']=$guid;
					$data['user_id']=$uid;
					$data['create_time']=time();
					$list->add($data);
					array_shift($novice);
					$n = implode(",",$novice);
					$giftbag->where("id=$giftid")->setField('novice',$n);
					$this->ajaxReturn(array('status'=>'1','msg'=>'恭喜你，领取成功！','url'=>'','data'=>$guid),'json');
				}
			}    
		} else {
			$this->ajaxReturn(array('status'=>'0','msg'=>'您还未登录，请登录后领取','url'=>U('Subscriber/login')),C('DEFAULT_AJAX_RETURN'));
		}
	}
    
    // 验证码 lwx 2016-05-27
    public function verify($vid=1){
		$config = array(
			'seKey'     => 'ThinkPHP.CN',   //验证码加密密钥
			'fontSize'  => 16,              // 验证码字体大小(px)
			'imageH'    => 42,               // 验证码图片高度
			'imageW'    => 107,               // 验证码图片宽度
			'length'    => 4,               // 验证码位数
			'fontttf'   => '4.ttf',              // 验证码字体，不设置随机获取
		);
        $verify = new \Think\Verify($config);
        ob_clean();
        $verify->entry($vid);
	}
}