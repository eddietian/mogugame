<?php
namespace Mobile\Controller;

use Common\Api\GameApi;
use User\Api\SuserApi;
use Org\XiguSDK\Xigu;
class SubscriberController extends BaseController {
    
	private $suser;
    
    public function __construct() {
        parent::__construct();
        $this->suser = new SuserApi;
        if (!parent::islogin()) {
            $this->redirect('Index/index');
        }
        define('SA_ID',session('user_auth.user_id'));
        define('SA_ACCOUNT',session('user_auth.account'));
    }
	
	public function index() {
        $user = M('user','tab_')->find(SA_ID);
        $realname = $user['real_name'];
        $realname = mb_substr($realname,0,1,'utf-8');
        $user['real_name'] = str_replace($realname,'*',$user['real_name']);
        $idcard = $user['idcard'];
        $idcard = substr($idcard,3,-3);
        $user['idcard'] = str_replace($idcard,'**************',$user['idcard']);
        $this->assign("user",$user);
        
        $this->display();
	}
    
    public function profile() {
		if ($_POST) {
            $data = array_filter($_POST);
            if (empty($data)) {echo json_encode(array('status'=>0,'msg'=>'参数错误'));}
            $data['id'] = SA_ID;
            
            $bool = $this->suser->updateInfo($data);
			if ($bool) {
				$return['msg'] = '修改成功';
				$return['status']=1;
                $auth = session('suser_auth');
                $auth['nickname']=$data['nickname'];
                session('suser_auth',null);session('suser_auth_sign',null);
                session('suser_auth',$auth);
                session('suser_auth_sign', data_auth_sign($auth));
			} else {
				$return['msg'] = '修改失败';
				$return['status']=0;
			}
			echo json_encode($return); 
		} else {
            echo json_encode(array('status'=>0,'msg'=>'提交的数据有误'));
		}
	}
    
    public function gift($p=1) {
        
        $map['map'] = array(
            'tab_game.game_status' => 1,
            'tab_giftbag.status' => 1,
            'tab_gift_record.user_id' => SA_ID,
        );                
        
        $this->assign('lists',A('Game','Event')->giftrecord($p,$map,true));
        $this->assign('page',1);
        $this->display();
    }
    
    public function ajaxlists($name='',$p=2) {
        if ($name == 'play') {
            $map['map'] = array(
                'tab_game.game_status' => 1,
                'tab_user_play.user_id' => SA_ID,
            );
            $lists = A('Game','Event')->play_lists($p,$map);
        } else{
            $map['map'] = array(
                'tab_game.game_status' => 1,
                'tab_giftbag.status' => 1,
                'tab_gift_record.user_id' => SA_ID,
            );
            $status = 0;
            $lists = A('Game','Event')->giftrecord($p,$map);
        }
        
        if (!empty($lists)) {
            $status = 1;
        }             
        
        echo json_encode(array('status'=>$status,'page'=>$p,'lists'=>$lists));
    }
    
    public function play() {
        $map['map'] = array(
            'tab_game.game_status' => 1,
            'tab_user_play.user_id' => SA_ID,
        );
        $this->assign('lists',A('Game','Event')->play_lists($p,$map,true));
        $this->assign('page',1);
        $this->display();
    }
    
    public function modify($account='',$password='',$safecode='') {               
        if (IS_POST) {
            if (empty($account) || empty($password) || empty($safecode)) {
                echo json_encode(array('status'=>0,'msg'=>'提交数据不能为空'));exit;
            }
            
            A("Unlr")->checksafecode($account,$safecode,false);
            $res = $this->suser->checkAccount($account);
            if ($res) {
                echo json_encode(array('status'=>0,'msg'=>'该手机号码尚未经过验证'));exit;
            }
            
            $info = $this->suser->updateinfo($account,true);
            $res = $this->suser->updatePassword($info['id'],$password);
            if($res > 0){
                $data = array(
                    'status' => 1,
                    'msg' => '修改成功',
                );
            } else{
                $data = array(
                    'status' => $pid,
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
    

	// 验证密码
    
	public function checkpassword($password,$type) {
        $bool = $this->suser->checkPassword(SA_ACCOUNT,$password);
		if ($bool) {
			echo json_encode(array('status'=>1,'msg'=>'','type'=>$type));
		} else {
			echo json_encode(array('status'=>0,'msg'=>'你输入了错误的密码','type'=>$type));
		}
	}
	
	
	// 实名认证
    
	public function updateinfo($realname,$idcard) {
        $user = $this->suser->info(SA_ID);
		if (IS_POST) {			
            $data=array(
                'id' => SA_ID,
                'real_name' => $realname,
                'idcard' => $idcard
            );
            $bool = $this->suser->updateInfo($data);
			if ($bool) {
				echo json_encode(array('status'=>1,'msg'=>'实名认证成功'));
			} else {
				echo json_encode(array('status'=>0,'msg'=>$bool));
			}
		} else
			echo json_encode(array('status'=>0,'msg'=>'操作不合法'));
	}
	
    
    // 发送安全码
    
    public function sendsafecode($phone) {
        if (IS_POST) {
            $verify = new \Think\Verify();
            if(!($verify->check(I('verify'),I('vid')))){               
                echo json_encode(array('status'=>0,'msg'=>'验证码不正确'));exit;
            }
            $res = $this->suser->checkPhone($phone);
            if (!$res) {
                echo json_encode(array('status'=>0,'msg'=>'手机号码被占用'));exit;
            }
            
            A("Unlr")->telsafecode($phone);
        } else{
            echo json_encode(array('status'=>0,'msg'=>'请按正确的流程'));exit;
        } 
    }
    
    // 手机安全码验证
    // @param bool $flag  true 用于直接异步请求  false 用于方法调用  
    public function csafecode($phone,$vcode,$flag=true) {       
        A("Unlr")->checksafecode($phone,$vcode,false);
        $data=array(
            'id' => SA_ID,
            'phone' => $phone,
        );
        $bool = $this->suser->updateInfo($data);
        
        if ($flag) {
            echo json_encode(array('status'=>1));
        }
    }

    // 修改密码
    
    public function pwd() {
        $user = $this->suser->info(SA_ID);
        if (IS_POST) {  
            $oldpassword = I('old_password');
            $password = I('password');
            if (empty($oldpassword) || empty($password)) {
                echo json_encode(array('status'=>0,'msg'=>'提交的数据有误'));exit;
            }
            $bool = $this->suser->checkPassword(SA_ACCOUNT,$oldpassword);
            if (!$bool) {
                echo json_encode(array('status'=>0,'msg'=>'原密码输入错误'));exit;
            }
            $this->changepwd($password);   
        } else {           
            $this->assign('user',$user);
            $this->display();
        }
    }
       
    // 改密码
    
    private function changepwd($password) {
        $bool = $this->suser->updatePassword(SA_ID,$password);
        if ($bool) {
            $data['status'] =1;
            $data['msg'] = '密码重置成功';
        } else {
            $data['status']=0;
            $data['msg'] = '密码重置失败';
        }
        echo json_encode($data);  
    }
	

	

}