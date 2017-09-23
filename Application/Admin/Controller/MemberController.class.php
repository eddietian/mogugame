<?php

namespace Admin\Controller;
use Sdk\Controller\AgeController;
use User\Api\MemberApi as MemberApi;
class MemberController extends ThinkController {
	    /**
    *玩家列表信息
    */
    public function user_info($p=0){
        $hav = '';
        "" == I('promote_id') || $hav .= "tab_user.promote_id =".I('promote_id');
        if(isset($_REQUEST['account'])){
//            $map['tab_user.account'] = array('like','%'.$_REQUEST['account'].'%');
            empty($hav) || $hav .= ' AND ';
            $hav .= "tab_user.account like '%".I('account')."%'";
            unset($_REQUEST['account']);
        }
        if(isset($_REQUEST['age_status'])){
//            $map['register_way'] = $_REQUEST['register_way'];
            empty($hav) || $hav .= ' AND ';
            $hav .= 'tab_user.age_status ='.I('age_status');
            unset($_REQUEST['age_status']);
        }
        if(isset($_REQUEST['register_way'])){
//            $map['register_way'] = $_REQUEST['register_way'];
            empty($hav) || $hav .= ' AND ';
            $hav .= 'tab_user.register_way ='.I('register_way');
            unset($_REQUEST['register_way']);
        }
        if(isset($_REQUEST['time_start']) && isset($_REQUEST['time_end'])){
            empty($hav) || $hav .= ' AND ';
            $hav .= 'tab_user.register_time BETWEEN '.strtotime(I('time_start')).' AND '.(strtotime(I('time_end'))+24*60*60-1);
            unset($_REQUEST['time_start']);unset($_REQUEST['time_end']);
        }
        if(isset($_REQUEST['start']) && isset($_REQUEST['end'])){
            empty($hav) || $hav .= ' AND ';
            $hav .= 'tab_user.register_time BETWEEN '.strtotime(I('start')).' AND '.strtotime(I('end'));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(!empty(I('line_day'))){
            $day = strtotime(date('Y-m-d')) - intval(I('line_day')) * 86400;
            empty($hav) || $hav .= ' AND ';
            $hav .= $day.'> tab_user.login_time';
        }
        if (isset($_REQUEST['status'])) {
            $map['lock_status']=$_REQUEST['status'];
            unset($_REQUEST['status']);
        }

        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $row = 10;
        //排序
        $order = '';
        if (I('key') == 1) {
            if (I('balance_status') == 1) {
                $order = 'balance,';
            } elseif (I('balance_status') == 2) {
                $order = 'balance desc,';
            }
        }else{
            if (I('recharge_status') == 1) {
                $order .= 'recharge_total,';
            } elseif (I('recharge_status') == 2) {
                $order .= 'recharge_total desc,';
            }
        }
        $order .= 'tab_user.id desc';
        //数据
        $data = M('user','tab_')->field('tab_user.id,tab_user.age_status,tab_user.account,tab_user.balance,tab_user.promote_account,register_time,tab_user.lock_status,tab_user.register_way,tab_user.register_type,tab_user.register_ip,tab_user.login_time,IFNULL(sum(b.pay_amount),0) AS recharge_total')
            ->join('left join tab_deposit AS b ON tab_user.id = b.user_id AND b.pay_status = 1')
            ->where($map)
            ->group('tab_user.id')
            ->having($hav)
            ->page($page,$row)
            ->order($order)
            ->select();
            $data2 = M('user','tab_')->field('tab_user.id,tab_user.age_status,tab_user.account,tab_user.balance,tab_user.promote_account,register_time,tab_user.lock_status,tab_user.register_way,tab_user.register_type,tab_user.register_ip,tab_user.login_time,IFNULL(sum(c.pay_amount),0) AS recharge_total')
            ->join('left join tab_spend AS c ON c.user_id = tab_user.id  AND c.pay_status = 1 AND c.pay_way != 0')
            ->where($map)
            ->group('tab_user.id')
            ->having($hav)
            ->page($page,$row)
            ->order($order)
            ->select(); 
            foreach ($data as $k => &$value) {
                foreach ($data2 as $k2 => $value2) {
                    if ($value['id']==$value2['id']) {
                        $value['recharge_total']=sprintf("%.2f",($value['recharge_total']+$value2['recharge_total']));
                    }
                }  
            }
        //计数
            $count = M('user','tab_')
            ->where($map)
            ->group('tab_user.id')
            ->having($hav)
            ->select();
            $count = count($count);
        $model = M('Model')->getByName('user');
        //分页
        if($count > 10){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $this->assign('list_data', $data);
        $this->assign('model', $model);
        $this->meta_title = '用户信息列表';
        $this->display();
    }
    //设置状态
    public function set_status($model='User'){
        if(isset($_REQUEST['model'])){
            $model=$_REQUEST['model'];
            unset($_REQUEST['model']);
        }
        parent::set_status($model);
    }
    public function edit($id=null){
        if(IS_POST){
            $member = new MemberApi();
            $data = $_REQUEST;
            if(empty($data['password'])){unset($data['password']);}
            $res = $member->updateInfo($data);
            if($res !== false){
                if(C('UC_SET')==1&&!empty($data['password'])){
                    $data_uc=$uc->get_uc(get_user_account($id));
                    if(is_array($data_uc)){
                        $uc_id=$uc->uc_edit($data_uc[1],"11",$data['password'],"",1);
                    }
                }
                $this->success('修改成功',U('user_info'));
            }
            else{
                $this->error('修改失败');
            }

        }
        else{
            $user = A('User','Event');
            $data=$user->user_entity($id);
            $this->assign('data',$data);
            $this->meta_title = '玩家信息';
            $this->display();
        }

    }

    public function bind_balance($p=1){
        $map['user_id']=$_REQUEST['id'];
        $data = M("user_play","tab_")
            // 查询条件
            ->where($map)
            ->group('user_account,game_name')
            /* 执行查询 */
            ->select();
        $this->assign('list_data', $data);
        $this->display();
    }

     public function balance($p=1){
        $map['user_id']=$_REQUEST['id'];
        $data = M("user","tab_")
            // 查询条件
            ->where($map)
            /* 执行查询 */
            ->select();
        $this->assign('list_data', $data);
        $this->display();
    }

    /**
     * 系统非常规MD5加密方法
     * @param  string $str 要加密的字符串
     * @return string
     */
    function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

    public function checkpwd(){
        //session('second_pwd',I('second_pwd'));
        $res = D('Member')->check_sc_pwd(I('second_pwd'));
        if($res){
            $this->ajaxReturn(array("status"=>1,"msg"=>"成功"));
        }
        else{
            $this->ajaxReturn(array("status"=>-1,"msg"=>"二级密码错误"));
        }
    }

    public function balance_edit(){
        $res = D('Member')->check_sc_pwd($_REQUEST['second_pwd_']);
        if($res){
             $map['id']=$_REQUEST['id'];
            $data=array(
            'balance' => $_REQUEST['balance']
            );
            $user=M('user','tab_')->where($map)->find();
            $pro=M("user","tab_")->where($map)->setfield('balance',$_REQUEST['balance']);
            if($pro!==false){
                $data=array(
                    'user_id' => $_REQUEST['id'],
                    'user_account' => get_user_account($_REQUEST['id']),
                    'game_id' =>'',
                    'game_name' =>'',
                    'prev_amount' =>$user['balance'],
                    'amount' =>$_REQUEST['balance'],
                    'type' => 0,
                    'op_id' =>UID,
                    'op_account' =>get_admin_name(UID),
                    'create_time' => time()
            );
            M('balance_edit','tab_')->add($data);
            //session('second_pwd','');
            $this->ajaxReturn(array("status"=>1,"msg"=>"成功"));
        }
        else{
            $this->ajaxReturn(array("status"=>-1,"msg"=>"失败"));
        }
        }
        else{
            $this->ajaxReturn(array("status"=>-1,"msg"=>"二级密码错误"));
        }
       
    }

    public function bind_balance_edit($p=1){
        $map['id']=$_REQUEST['id'];
        $map['user_id']=$_REQUEST['user_id'];
        $map['game_id']=$_REQUEST['game_id'];
        $res = D('Member')->check_sc_pwd($_REQUEST['second_pwd']);
        if($res){
            $user_play = M('user_play','tab_')->where($map)->find();
            $pro = M("user_play","tab_")
                ->where($map)
                ->setField('bind_balance',$_REQUEST['bind_balance']);
            if($pro!==false){
                $data=array(
                'user_id' => $_REQUEST['user_id'],
                'user_account' => get_user_account($_REQUEST['user_id']),
                'game_id' => $_REQUEST['game_id'],
                'game_name' => get_game_name($_REQUEST['game_id']),
                'prev_amount' => $user_play['bind_balance'],
                'amount' => $_REQUEST['bind_balance'],
                'type' => 1,
                'op_id' => is_login(),
                'op_account' => get_admin_nickname(is_login()),
                'create_time' => time()
                );
                M('balance_edit','tab_')->add($data);
                $this->ajaxReturn(array("status"=>1,"msg"=>"成功"));
            }
            else{
                $this->ajaxReturn(array("status"=>0,"msg"=>"失败"));
            }
        }else{
            $this->ajaxReturn(array("status"=>0,"msg"=>"二级密码错误"));
        }
    }

    public function chax($p=1)
    {
        $map['user_account']=get_user_account($_REQUEST['id']);
        $map['pay_status']=1;
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $row    = 10;
        //$new_model = D($name);
        $data = M("spend","tab_")
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order('pay_time desc')
            /* 数据分页 */
            ->page($page, $row)
            /* 执行查询 */
            ->select();

        /* 查询记录总数 */
        $count =M("spend","tab_")->where($map)->count();
         //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $total=null_to_0(D('spend')->where($map)->sum('pay_amount'));
        $ttotal=null_to_0(D('spend')->where('pay_time'.total(1))->where($map)->sum('pay_amount'));
        $ytotal=null_to_0(D('spend')->where('pay_time'.total(5))->where($map)->sum('pay_amount'));
        // var_dump(D(self::model_name)->getlastsql());exit;  
        // $this->assign('dtotal',$dtotal);
        $this->assign('total',$total);
        $this->assign('ttotal',$ttotal);
        $this->assign('ytotal',$ytotal);
        $this->assign('list_data', $data);
        $this->display();
    }
    public function changephone(){
        if(preg_match('/^[1][3578][0-9]{9}/',$_REQUEST['phone'])){
            $map['id']=$_REQUEST['id'];
            $pro = M("User","tab_")
                ->where($map)
                ->setField('phone',$_REQUEST['phone']);
            if($pro!==false){
                $this->ajaxReturn(array("status"=>1,"msg"=>"手机修改成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,"msg"=>"手机修改失败"));
            }
        }else{
            $this->ajaxReturn(array("status"=>0,"msg"=>"手机输入错误"));
        }
    }
     public function denglu($p=1){
        $map['user_id']=$_REQUEST['id'];
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $row    = 10;
        //$new_model = D($name);
        $data = M("user_login_record","tab_")
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order('login_time desc')
            /* 数据分页 */
            ->page($page, $row)
            /* 执行查询 */
            ->select();
        /* 查询记录总数 */
        $count =M("user_login_record","tab_")->where($map)->count();
         //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $this->assign('list_data', $data);
        $this->display();
    }
    /**
    *用户登陆记录
    */
    public function login_record($p=1){
        if(isset($_REQUEST['game_name'])){
            $extend['game_name'] = $_REQUEST['game_name'];
            unset($_REQUEST['game_name']);
        }
         if(isset($_REQUEST['login_ip'])){
            $map['login_ip']=$_REQUEST['login_ip'];
            unset($_REQUEST['login_ip']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['login_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['login_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['account'])){
            $map['user_account']=array('like','%'.trim($_REQUEST['account']).'%');
            unset($_REQUEST['account']);
        }
        $map['type']=1;
        $map['login_time'] = array('neq',0);
        $extend=array();
        $extend['map']=$map;
        parent::lists("UserLoginRecord",$p,$extend['map']);
    }
    public function del($model = null, $ids=null){
        $map=array();
        if(isset($_REQUEST['id'])){
            $map['id']=$_REQUEST['id'];
            $data=M('user_login_record','tab_')
            ->where($map)->delete();
            $this->success('删除成功！',U('login_record'),2);
        }else{
            $this->error('请选择要操作的数据！');
        }
    }
     public function delprovide($ids)
    {
      $list=M("user_login_record","tab_");
      $map['id']=array("in",$ids);
      $map['status']=0;
        $delete=$list->where($map)->delete();
        if($delete){
            $this->success("批量删除成功！",U("login_record"));
        }else{
        $this->error("批量删除失败！",U("login_record"));
        }
    }

    /**
     * 锁定玩家
     * @param $id
     * @param $lock_status
     */
    public function lock_status($id,$lock_status){
        $map['id'] = $id;
        $res = M('user','tab_')->where($map)->setField(['lock_status'=>$lock_status]);
        if($res){
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }
    }

    /**
     * 对年龄的审核
     */
    public function age_check(){
        $id = I("id");
        $arr = explode(',',$id);
        $i=0;
        $q=0;
        $t=0;
        //已经审核过的不审核 信息为空的也不审核
        foreach ($arr as $key){
            $where['id'] = $key;
            $where['status'] = array('neq','2');
            $res = D("User")->where($where)->select();
            if (empty($res[0]['idcard']) || empty($res[0]['real_name'])){
                $i++;
            }else{
                $return = age_verify($res[0]['idcard'],$res[0]['real_name']);
                if ($return == 1 && $return>0){
                    $q++;
                    $data['id'] = $key;
                    $data['age_status'] = 2;
                }elseif ($return == 2 && $return>0){
                    $q++;
                    $data['id'] = $key;
                    $data['age_status'] = 3;
                }elseif($return == -1){
                    echo json_encode(array('status'=>2,'info'=>'短信数量已经使用完'));exit;
                }elseif($return == -2){
                    echo json_encode(array('status'=>2,'info'=>'连接失败，请检查数据库配置'));exit;
                }else{
                   $t++;
                   $data['id'] = $key;
                   $data['age_status'] = 1;
                }
                $re = D("User")->data($data)->save();
                /*if ($re){
                    echo json_encode(array('status'=>1,'info'=>'操作成功！'));exit;
                }else{
                    echo json_encode(array('status'=>2,'info'=>'操作失败'));exit;
                }*/
            }
        }
        echo json_encode(array('status'=>1,'info'=>'未填写'.$i."个，审核通过".$q."个，审核未通过".$t."个"));exit;
    }


}