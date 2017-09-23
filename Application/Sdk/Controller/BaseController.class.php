<?php
namespace Sdk\Controller;
use Think\Controller\RestController;

class BaseController extends RestController{
    protected function _initialize(){
        C(api('Config/lists'));
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        #判断数据是否为空
        if(empty($data) || empty($data['game_id'])){
            echo base64_encode(json_encode(array("status"=>0,"return_msg"=>"操作数据或游戏ID不能为空")));exit();
        }
        $md5Sign = $data['md5_sign'];
        unset($data['md5_sign']);

        #获取游戏key
        $game_data = M("GameSet","tab_")->where("game_id=".$data["game_id"])->find();
        $md5_sign = $this->encrypt_md5($data,$game_data["access_key"]);//mengchuang DZQkkiz!@#9527
        if($md5Sign !== $md5_sign){
            $this->set_message(0,"fail","验签失败");
        }
    }

    /**
    *设置接口提示信息
    *@param  int     $status 提示状态 
    *@param  string  $return_code 提示代码
    *@param  string  $return_msg  提示信息
    *@return string  base64加密后的json格式字符串
    *@author 小纯洁
    */
    public function set_message($status=0,$return_code="fail",$return_msg="操作失败"){
        $msg = array(
            "status"      => $status,
            "return_code" => $return_code,
            "return_msg"  => $return_msg
        );
        echo base64_encode(json_encode($msg));
        exit();
    }

    /**
    *设置登录提示信息
    *@param  int     $status 提示状态 
    *@param  string  $return_code 提示代码
    *@param  string  $return_msg  提示信息
    *@return string  base64加密后的json格式字符串
    *@author 小纯洁
    */
    public function set_login_msg($uid,$token,$is_uc=0){
        if($is_uc){
            $res_msg = array(
                "status" => 1,
                "return_code" => "success",
                "return_msg" => "登陆成功",
                "user_id" => $uid,
                "token" => $token,
                'is_uc'=>1,
            );
        }else{
            $res_msg = array(
                "status" => 1,
                "return_code" => "success",
                "return_msg" => "登陆成功",
                "user_id" => $uid,
                "token" => $token,
            );
        }
        wite_text(json_encode($res_msg).'\n',dirname(__FILE__)."/res.txt");
        echo base64_encode(json_encode($res_msg));
    }


    /**
    *设置登录提示信息
    *@param  int     $status 提示状态 
    *@param  string  $return_code 提示代码
    *@param  string  $return_msg  提示信息
    *@return string  base64加密后的json格式字符串
    *@author 小纯洁
    */
    public function set_tr_login_msg($uid,$account,$token){
        $res_msg = array(
            "status" => 1,
            "return_code" => "success",
            "return_msg" => "登陆成功",
            "user_id" => $uid,
            "account" =>$account,
            "token" => $token,
        );
        echo base64_encode(json_encode($res_msg));
    }


    /**
    *验证签名
    */
    public function validation_sign($encrypt="",$md5_sign=""){
        $signString = $this->arrSort($encrypt);
        $md5Str = $this->encrypt_md5($signString,$key="");
        if($md5Str === $md5_sign){
            return true;
        }
        else{
            return false;
        }
    }

    /**
    *对数据进行排序
    */
    private function arrSort($para){
        ksort($para);
        reset($para);
        return $para;
    }

    /**
    *MD5验签加密
    */
    public function encrypt_md5($param="",$key=""){
        #对数组进行排序拼接
        if(is_array($param)){
            $md5Str = implode($this->arrSort($param));
        }
        else{
            $md5Str = $param;
        }
        $md5 = md5($md5Str . $key);
        return '' === $param ? 'false' : $md5;
    }


    

    /**
    *短信验证
    */
    public function sms_verify($phone="" ,$code=""){
        $session = session($phone);
        if(empty($session)){
            $this->set_message(0,"fail","数据获取失败！");
        }
        #验证码是否超时
        $time = NOW_TIME - session($phone.".create_time");
        if($tiem > 60){//$tiem > 60
            $this->set_message(0,"fail","验证超时！请重新获取");
        }
        #验证短信验证码
        if(session($phone.".code") != $code){
            $this->set_message(0,"fail","输入验证码不正确");
        }
        return true;
    }

    

    /**
    *消费记录表 参数
    */
    private function spend_param($param=array()){
        $user_entity = get_user_entity($param['user_id']);
        $data_spned['user_id']          = $param["user_id"];
        $data_spned['user_account']     = $user_entity["account"];
        $data_spned['user_nickname']    = $user_entity["nickname"];
        $data_spned['game_id']          = $param["game_id"];
        $data_spned['game_appid']       = $param["game_appid"];
        $data_spned['game_name']        = get_game_name($param["game_id"]);
        $data_spned['server_id']        = 0;
        $data_spned['server_name']      = "";
        $data_spned['promote_id']       = $user_entity["promote_id"];
        $data_spned['promote_account']  = $user_entity["promote_account"];
        $data_spned['order_number']     = $param["order_number"];
        $data_spned['pay_order_number'] = $param["pay_order_number"];
        $data_spned['props_name']       = $param["title"];
        $data_spned['cost']       = $param["price"];//原价
        $data_spned['pay_time']         = NOW_TIME;
        $data_spned['pay_status']       = $param["pay_status"];
        $data_spned['pay_game_status']  = 0;
        $data_spned['extend']           = $param['extend'];
        $data_spned['pay_way']          = $param["pay_way"];
        if($data_spned['pay_way'] != 7){
            $discount = $this->get_discount($param['game_id'],$user_entity['promote_id'],$param['user_id']);
            $data_spned['pay_amount']       = $param["price"] * $discount['discount']/10;//实付金额
            $data_spned['discount_type']    = $discount['discount_type'];
        }else{//苹果支付不计入折扣
            $data_spned['pay_amount']       = $param["price"];
            $data_spned['discount_type']    = 0;
        }
        $data_spned['spend_ip']         = $param["spend_ip"];
        $data_spned['sdk_version']      = $param["sdk_version"];
//         wite_text(json_encode($discount).'\n',dirname(__FILE__)."/data_spned.txt");
        
        return $data_spned;
    }

    /**
    *平台币充值记录表 参数
    */
    private function deposit_param($param=array()){
        $user_entity = get_user_entity($param['user_id']);
        $data_deposit['order_number']     = $param["order_number"];
        $data_deposit['pay_order_number'] = $param["pay_order_number"];
        $data_deposit['user_id']          = $param["user_id"];
        $data_deposit['user_account']     = $user_entity["account"];
        $data_deposit['user_nickname']    = $user_entity["nickname"];
        $data_deposit['promote_id']       = $user_entity["promote_id"];
        $data_deposit['promote_account']  = $user_entity["promote_account"];
        $data_deposit['pay_amount']       = $param["price"];
        $data_deposit['cost']       = $param["price"];
        $data_deposit['reality_amount']   = $param["price"];
        $data_deposit['pay_status']       = $param["pay_status"];
        $data_deposit['pay_source']       = 2;
        $data_deposit['pay_way']          = $param["pay_way"];
        $data_deposit['pay_ip']           = $param["spend_ip"];
        $data_deposit['sdk_version']      = $param["sdk_version"];
        $data_deposit['create_time']      = NOW_TIME;
//     wite_text(json_encode($data_deposit).'\n',dirname(__FILE__)."/data_spned.txt");
        return $data_deposit;
    }

    /**
    *绑定平台币消费
    */
    private function bind_spend_param($param = array()){
        $user_entity = get_user_entity($param['user_id']);
        $data_bind_spned['user_id']          = $param["user_id"];
        $data_bind_spned['user_account']     = $user_entity["account"];
        $data_bind_spned['user_nickname']    = $user_entity["nickname"];
        $data_bind_spned['game_id']          = $param["game_id"];
        $data_bind_spned['game_appid']       = $param["game_appid"];
        $data_bind_spned['game_name']        = get_game_name($param["game_id"]);
        $data_bind_spned['server_id']        = 0;
        $data_bind_spned['server_name']      = "";
        $data_bind_spned['promote_id']       = $user_entity["promote_id"];
        $data_bind_spned['promote_account']  = $user_entity["promote_account"];
        $data_bind_spned['order_number']     = $param["order_number"];
        $data_bind_spned['pay_order_number'] = $param["pay_order_number"];
        $data_bind_spned['props_name']       = $param["title"];
        $data_bind_spned['cost']             = $param["price"];//原价
//        $discount = $this->get_discount($param['game_id'],$param['promote_id'],$param['user_id']);//折扣
        $data_bind_spned['pay_amount']       = $param["price"];
        $data_bind_spned['pay_time']         = NOW_TIME;
        $data_bind_spned['pay_status']       = $param["pay_status"];
        $data_bind_spned['pay_game_status']  = 0;
        $data_bind_spned['pay_way']          = 1;
        $data_bind_spned['extend']           = $param['extend'];
        $data_bind_spned['spend_ip']         = $param["spend_ip"];
        $data_bind_spned['sdk_version']      = $param["sdk_version"];
        return $data_bind_spned;
    }

    //用户登录记录
    public function user_login_record($data,$type,$game_id,$game_name,$sdk_version){
        $data=array(
            'user_id'=>$data['id'],
            'user_account'=>$data['account'],
            'user_nickname'=>$data['nickname'],
            'game_id'=>$game_id,
            'game_name'=>$game_name,
            'server_id'=>null,
            'type'=>$type,
            'server_name'=>null,
            'login_time'=>NOW_TIME,
            'login_ip'=>get_client_ip(),
            'sdk_version'=>$sdk_version,
        );
            $uid =M('user_login_record','tab_')->add($data);
            return $uid ? $uid : 0; //0-未知错误，大于0登录记录成功
    }



  //判断game_id是否有值
    public function updateLogin_($uid,$account,$user_fgame_id,$game_id,$game_name){
        $model = M('User','tab_');
        $data["id"] = $uid;
        $data["login_time"] = NOW_TIME;
        $data["login_ip"] = get_client_ip();
        if($user_fgame_id){
            $model->save($data);
        }else{
            $data['fgame_id']=$game_id;
            $data['fgame_name']=$game_name;
            $model->save($data);
        }
    }


    /**
    *消费表添加数据
    */
    public function add_spend($data){
        $spend = M("spend","tab_");
        $spend_data =  $this->spend_param($data);
        $ordercheck = $spend->where(array('pay_order_number'=>$spend_data["pay_order_number"]))->find();
        if($ordercheck)
        {
            $this->set_message(0,'fail',"订单已经存在，请刷新充值页面重新下单！");
        }
        $result = $spend->add($spend_data);
        return $result;
    }

    /*
    *平台币充值记录
    */
    public function add_deposit($data){
        $deposit = M("deposit","tab_");
        $deposit_data  = $this->deposit_param($data);
        $ordercheck = $deposit->where(array('pay_order_number'=>$deposit_data["pay_order_number"]))->find();
        if($ordercheck)$this->set_message(0,'fail',"订单已经存在，请刷新充值页面重新下单！");
        $result = $deposit->add($deposit_data);
        return $result;
    }

    /*
    *绑定平台币消费记录
    */
    public function add_bind_spned($data){
        $bind_spned = M("BindSpend","tab_");
        $data_bind_spned  = $this->bind_spend_param($data);
        $ordercheck = $bind_spned->where(array('pay_order_number'=>$data_bind_spned["pay_order_number"]))->find();
        if($ordercheck)$this->set_message(0,'fail',"订单已经存在，请刷新充值页面重新下单！");
        $result = $bind_spned->add($data_bind_spned);
        return $result;
    }

    /**
    *设置数据里游戏的图片
    */
    public function set_game_icon($game_id=0){
        $game = M("Game","tab_")->find($game_id);
        $icon_url ="http://".$_SERVER['HTTP_HOST'].get_cover($game['icon'],"path");
        return $icon_url;
    }

    /**
     * 获取用户折扣
     * @param $game_id  游戏ID
     * @param $promote_id   渠道ID
     * @param $user_id  用户ID
     * @return mixed
     */
    protected function get_discount($game_id,$promote_id,$user_id){
        //获取折扣
        $map['game_id'] = $game_id;
        $map['promote_id'] = $promote_id;
        $discount = M('Promote_welfare','tab_')->where($map)->find();
        $discount = discount_data($discount);
        if(empty($discount)){
            $res['discount'] = 10;
            $res['discount_type'] = 0;//无折扣
            return $res;
        }

        //判断用户是否为首冲
        $where['game_id'] = $game_id;
        $where['user_id'] = $user_id;
        $where['pay_status'] = 1;
        $data = M('bind_spend','tab_')->where($where)->find();
        if(!empty($data) || !empty(M('spend','tab_')->where($where)->find())){
            $res['discount'] = $discount['continue_discount'];//续冲
            $res['discount_type'] = 2;
        }else{
            $res['discount'] = $discount['first_discount'];//首冲
            $res['discount_type'] = 1;
        }
        return $res;
    }

    /**
     *游戏返利
     */
    public function set_ratio($data){
        $map['pay_order_number']=$data;
        $spend=M("Spend","tab_")->where($map)->find();
        $reb_map['game_id']=$spend['game_id'];
        $time = time();
        $reb_map['starttime'] = ['lt',$time];
        $reb_map_str = "endtime > {$time} or endtime = 0";
        if($spend['promote_id'] == 0){
            $reb_map['promote_id'] = 0;
        }else{
            $reb_map['promote_id'] = ['neq',0];
        }
//             wite_text(json_encode($reb_map).'\n',dirname(__FILE__)."/ss.txt");
        $rebate=M("Rebate","tab_")->where($reb_map)->where($reb_map_str)->find();
        if (!empty($rebate)) {
            if($rebate['money']>0&&$rebate['status']==1){
                if($spend['pay_amount']>=$rebate['money']){
                    $this->compute($spend,$rebate);
                }else{
                    return false;
                }
            }else{
                $this->compute($spend,$rebate);
            }
        }else{
            return false;
        }
    }

    //计算返利
    public function compute($spend,$rebate){
        $user_map['user_id']=$spend['user_id'];
        $user_map['game_id']=$spend['game_id'];
        $bind_balance=$spend['pay_amount']*($rebate['ratio']/100);
        $spend['ratio']=$rebate['ratio'];
        $spend['ratio_amount']=$bind_balance;
        M("rebate_list","tab_")->add($this->add_rebate_list($spend));
        $re=M("UserPlay","tab_")->where($user_map)->setInc("bind_balance",$bind_balance);
        return $re;
    }

    /**
     *返利记录
     */
    protected function add_rebate_list($data){
        $add['pay_order_number']=$data['pay_order_number'];
        $add['game_id']=$data['game_id'];
        $add['game_name']=$data['game_name'];
        $add['user_id']=$data['user_id'];
        $add['user_name']=$data['user_account'];
        $add['pay_amount']=$data['pay_amount'];
        $add['ratio']=$data['ratio'];
        $add['ratio_amount']=$data['ratio_amount'];
        $add['promote_id']=$data['promote_id'];
        $add['promote_name']=$data['promote_account'];
        $add['create_time']=time();
        return $add;
    }
}
