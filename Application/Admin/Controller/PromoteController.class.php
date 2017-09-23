<?php
namespace Admin\Controller;
use User\Api\PromoteApi;
use User\Api\UserApi;
use Org\XiguSDK\Xigu;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PromoteController extends ThinkController {

    const model_name = 'Promote';

    public function lists(){
        if(isset($_REQUEST['account'])){
            $map['account']=array('like','%'.$_REQUEST['account'].'%');
            unset($_REQUEST['account']);
        }
        if(isset($_REQUEST['admin'])){
            if($_REQUEST['admin']=="全部"){
            unset($_REQUEST['admin']);
            }else{
            $map['admin_id']=get_admin_id($_REQUEST['admin']);    
            }
        }
        if (isset($_REQUEST['parent_id'])) {
            if ($_REQUEST['parent_id']=='全部') {
                unset($_REQUEST['parent_id']);
            }
            $zid=get_zi_promote_id($_REQUEST['parent_id']);
            if($zid){
                $zid=$zid.','.$_REQUEST['parent_id'];
            }else{
                $zid=$_REQUEST['parent_id'];
            }
            $map['id']=array('in',$zid);
            unset($_REQUEST['parent_id']);
        }
        if(I('promote_level') == 1){
            $map['parent_id'] = 0;
        }elseif(I('promote_level') == 2){
            $map['parent_id'] = ['neq',0];
        }
    	parent::order_lists(self::model_name,$_GET["p"],$map);
    }

    public function add($account=null,$password=null,$second_pwd=null,$real_name=null,$email=null,$mobile_phone=null,$bank_name=null,$bank_card=null,$admin=null,$status=null){
        if(IS_POST){
            $data=array('account'=>$account,'password'=>$password,'second_pwd'=>$second_pwd,'real_name'=>$real_name,'email'=>$email,'mobile_phone'=>$mobile_phone,'bank_name'=>$bank_name,'bank_card'=>$bank_card,'admin_id'=>$admin,'status'=>$status);
            $user = new PromoteApi();
            $res = $user->promote_add($data);
            if($res>0){
                $this->success("添加成功",U('lists'));
            }
            else{
                $this->error($res);
            }
        }
        else{
            $this->meta_title ='新增渠道信息';
            $this->display();
        }
    }

    public function del($model = null, $ids=null){
        $model = M('Model')->getByName(self::model_name); 
        /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }
    //代充删除
    public function agent_del($model = null, $ids=null){
        $model = M('Model')->getByName('Agent'); 
        /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }
    public function edit($id=0){
		$id || $this->error('请选择要查看的用户！');
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        $data = array();
        if(IS_POST){
//         	empty(I('post.id')) || $data['id'];
//         	empty(I('post.password')) || $data['password'];
//         	empty(I('post.second_pwd')) || $data['second_pwd'];
//         	empty(I('post.status')) || $data['status'];
//         	empty(I('post.mark1')) || $data['mark1'];
//         	empty(I('post.mark2')) || $data['mark2'];
        	empty(I('post.id')) ?  : $data['id'] =I('post.id');
        	empty(I('post.password')) ?  : $data['password'] =I('post.password');
        	empty(I('post.second_pwd')) ?  : $data['second_pwd'] =I('post.second_pwd');
        	empty(I('post.status')) ?  : $data['status'] =I('post.status');
        	empty(I('post.mark1')) ? : $data['mark1'] =I('post.mark1');
        	empty(I('post.mark2')) ? : $data['mark2'] = I('post.mark2');
           $data['admin_id'] = I('admin_id');
            $pwd = trim($_POST['password']);
            $second_pwd = trim($_POST['second_pwd']);
            $use = new UserApi();
            $data['password']=think_ucenter_md5($pwd,UC_AUTH_KEY);
            $data['second_pwd']=think_ucenter_md5($second_pwd,UC_AUTH_KEY);
            if(empty($pwd)){unset($data['password']);}
            if(empty($second_pwd)){unset($data['second_pwd']);}
            $res=M("promote","tab_")->where(array("id"=>$_POST['id']))->save($data);
            if($res !== false){
                $this->success('修改成功',U('lists'));
            }
            else{
                $this->error('修改失败');
            }
        }
        else{
            $model = D('Promote');
            $data = $model->find($id);
            $data['bank_area']=explode(',',$data['bank_area']);
            $this->assign('data',$data);
            $this->meta_title ='编辑渠道信息';
            $this->display();
        }
    }
    //设置状态
    public function set_status($model='Promote'){
        if(isset($_REQUEST['model'])){
            $model=$_REQUEST['model'];
            unset($_REQUEST['model']);
        }
        $a=0;
        $map['id']=array('in',$_REQUEST['ids']);
        $set=M('promote','tab_')->where($map)->setField('status',$_REQUEST['status']);
        if($set){
            if($_REQUEST['status']==1){
                $select=M('promote','tab_')->where($map)->select();
                foreach ($select as $key => $value) {
                   // $count=$this->send_sms($value['mobile_phone']);
                    if($count=="000000"){
                        $a++;
                    }
                }
                $this->success('操作成功,已通知'.$a.'人');
            }else{
                $this->success('操作成功');
            }
            
        }else{
            $this->success('操作失败');
        }
    }

     /**
    *短信发送
    */
    public function send_sms($phone)
    {
        /// 产生手机安全码并发送到手机且存到session
        $rand = rand(100000,999999);
        $xigu = new Xigu(C('sms_set.smtp'));
        $param = $rand.",".'1';
        $result = json_decode($xigu->sendSM(C('sms_set.smtp_account'),$phone,C('sms_set.smtp_notice_port'),$param),true); 
        $result['create_time'] = time();
        #TODO 短信验证数据 
        
        return$result['send_status']; // '000000'
    }


    //设置对账状态yyh
    public function set_check_status($model='Promote'){
        if(isset($_REQUEST['model'])){
            $model=$_REQUEST['model'];
            unset($_REQUEST['model']);
        }
        parent::set_status($model);
    }
    /**
    *渠道注册列表
    */
    public function ch_reg_list(){
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $map['fgame_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        $map['tab_user.promote_id'] = array("neq",0);
        if(isset($_REQUEST['promote_name'])){
            if($_REQUEST['promote_name']=='全部'){
                unset($_REQUEST['promote_name']);
            }else if($_REQUEST['promote_name']=='自然注册'){
                $map['tab_user.promote_id']=array("elt",0);
                unset($_REQUEST['promote_name']);
            }else{
                $map['tab_user.promote_id']=array('eq',get_promote_id($_REQUEST['promote_name']));
                unset($_REQUEST['promote_name']);
            }
        }
        if(isset($_REQUEST['parent_id'])){
            $allid=get_subordinate_promote($_REQUEST['parent_id'],'parent_name');
            $allid[]=$_REQUEST['parent_id'];
            $map['tab_user.promote_account']=array('in',implode(',',$allid));
            unset($_REQUEST['parent_id']);
        }
        if(isset($_REQUEST['admin'])){
            $admin_id=get_admin_id($_REQUEST['admin']);
            $all_promote=array_column(get_admin_promotes($admin_id),'id');
            if($all_promote==''){
                $all_promote[]=-1;
            }
            $map['tab_user.promote_id']=array($map['tab_user.promote_id'],array('in',implode(',',$all_promote)),'and');
        }
        if(isset($_REQUEST['is_check'])&&$_REQUEST['is_check']!="全部"){
            $map['is_check']=check_status($_REQUEST['is_check']);
            unset($_REQUEST['is_check']);
        }
        if(isset($_REQUEST['account'])){
            $map['tab_user.account']=array('like','%'.$_REQUEST['account'].'%');
            unset($_REQUEST['account']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['register_time']=array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time_end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['register_time']=array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        $model = array(
            'm_name' => 'User',
            'fields' => array('id','tab_user.account','tab_user.fgame_name','nickname','email','phone','promote_id','parent_id','register_time','register_way','register_ip','promote_account','parent_name','is_check'),
            'key'    => array('tab_user.account','tab_game.fgame_name'),
            'map'    => $map,
            'order'  => 'id desc',
            'title'  => '渠道注册',
            'template_list' =>'ch_reg_list',
        );
        $total=D($model['m_name'])->where(array('tab_user.promote_id'=>array('neq',0)))->count();
        $ttotal=D($model['m_name'])->where('register_time'.total(1))->where(array('tab_user.promote_id'=>array('neq',0)))->count();
        $ytotal=D($model['m_name'])->where('register_time'.total(5))->where(array('tab_user.promote_id'=>array('neq',0)))->count();
        // var_dump(D($model['m_name'])->getlastsql());exit;  
        // $this->assign('dtotal',$dtotal);
        $this->assign('total',$total);
        $this->assign('ttotal',$ttotal);
        $this->assign('ytotal',$ytotal);
        $user = A('User','Event');
        $user->user_join__($model,$_GET['p']);
    }

    /**
    *渠道充值
    */
    public function spend_list(){
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $map['game_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        if(!empty($_REQUEST['server_id'])){
            $map['server_id']=$_REQUEST['server_id'];
            unset($_REQUEST['server_id']);
        }
        if(!empty($_REQUEST['pay_order_number'])){
            $map['pay_order_number']=array('like','%'.$_REQUEST['pay_order_number'].'%');
            unset($_REQUEST['pay_order_number']);
        }
        if(isset($_REQUEST['promote_name'])){
            if($_REQUEST['promote_name']=='全部'){
                unset($_REQUEST['promote_name']);
            }else if($_REQUEST['promote_name']=='自然注册'){
                $map['promote_id']=array("lte",0);
                unset($_REQUEST['promote_name']);
            }else{
                $map['promote_id']=get_promote_id($_REQUEST['promote_name']);
                unset($_REQUEST['promote_name']);
            }
        }else{
            $map['promote_id']=array("gt",0);
        }
        
        if(isset($_REQUEST['pay_way'])){
            $map['pay_way']=$_REQUEST['pay_way'];
            unset($_REQUEST['pay_way']);
        }
        // if(isset($_REQUEST['is_check'])&&$_REQUEST['is_check']!="全部"){
        //     $map['is_check']=check_status($_REQUEST['is_check']);
        //     unset($_REQUEST['is_check']);
        // }
        if(isset($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['spend_ip'])){
            $map['spend_ip']=array('like','%'.$_REQUEST['spend_ip'].'%');
            unset($_REQUEST['spend_ip']);
        }
        if(isset($_REQUEST['promote_name'])){
            $map['promote_account']=$_REQUEST['promote_name'];
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['pay_time']=array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time_end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['pay_time']=array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(!empty(I('parent_id'))){
            $pro = M('promote','tab_')->where(['parent_id'=>I('parent_id')])->select();
            $pro_ids = array_column($pro,'id');
            $pro_ids[] = I('parent_id');
            $map['promote_id'] = ['in',$pro_ids];
        }
        $model = array(
            'm_name' => 'Spend',
            'map'    => $map,
            'order'  => 'id desc',
            'title'  => '渠道充值',
            'template_list' =>'spend_list',
        );
        $map1=$map;
        $map1['pay_status']=1;
        $total=null_to_0(D('Spend')->where($map1)->sum('pay_amount'));
        $ttotal=null_to_0(D('Spend')->where('pay_time'.total(1))->where(array('pay_status'=>1))->where(array('promote_id'=>array("gt",0)))->sum('pay_amount'));
        $ytotal=null_to_0(D('Spend')->where('pay_time'.total(5))->where(array('pay_status'=>1))->where(array('promote_id'=>array("gt",0)))->sum('pay_amount'));
        $this->assign('total',$total);
        $this->assign('ttotal',$ttotal);
        $this->assign('ytotal',$ytotal);
        $user = A('Spend','Event');
        $user->spend_list($model,$_GET['p']);
    }

    /**
    *代充记录
    */
    public function agent_list(){
        $map['promote_id'] = array("neq",0);
        if(isset($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['pay_status'])){
            $map['pay_status']=$_REQUEST['pay_status'];
            unset($_REQUEST['pay_status']);
        }
        if(isset($_REQUEST['promote_name'])){
            if($_REQUEST['promote_name']=='全部'){
                unset($_REQUEST['promote_name']);
            }else if($_REQUEST['promote_name']=='自然注册'){
                $map['promote_id']=array("elt",0);
                unset($_REQUEST['promote_name']);
            }else{
                $map['promote_id']=get_promote_id($_REQUEST['promote_name']);
                unset($_REQUEST['promote_name']);
                unset($_REQUEST['promote_id']);
            }
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['create_time']=array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time_end']);
        }elseif (isset($_REQUEST['time-start'])){
        	$map['create_time']=array('BETWEEN',array(strtotime($_REQUEST['time-start']),time()));
        	unset($_REQUEST['time-start']);unset($_REQUEST['time_end']);
        }elseif (isset($_REQUEST['time-end'])){
        	$map['create_time']=array('BETWEEN',array(0,strtotime($_REQUEST['time-end'])+24*60*60-1));
        	unset($_REQUEST['time-start']);unset($_REQUEST['time_end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['create_time']=array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $map['game_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        empty(I('promote_id')) || $map['promote_id'] = I('promote_id');
        $map1=$map;
        $map1['pay_status']=1;
        $total=D('Agent')->field('sum(amount) amount,sum(real_amount) real_amount')->where($map1)->find();
        $ttotal=D('Agent')->field('sum(amount) amount,sum(real_amount) real_amount')->where('create_time'.total(1))->where(array('pay_status'=>1))->find();
        $ytotal=D('Agent')->field('sum(amount) amount,sum(real_amount) real_amount')->where('create_time'.total(5))->where(array('pay_status'=>1))->find();
        // $this->assign('dtotal',$dtotal);
        $this->assign('total',$total);
        $this->assign('ttotal',$ttotal);
        $this->assign('ytotal',$ytotal);
        parent::order_lists('Agent',$_GET["p"],$map);
    }
    /**
    *代充额度
    */
    public function pay_limit(){
        if(isset($_REQUEST['account'])){
            if ($_REQUEST['account']=='全部') {
                unset($_REQUEST['account']);
            }
            $map['account']=array('like','%'.$_REQUEST['account'].'%');
            unset($_REQUEST['account']);
        }
        $row=10;
        $map['pay_limit']=array('gt','0');
        $page = intval($_GET['p']);
        $page = $page ? $page : 1; //默认显示第一页数据
        $arraypage=$page;
        $model=D('Promote');
        $data=$model
        ->field('id,account,pay_limit,set_pay_time,pay_limit_game')
        ->where($map)
        // ->page($page, 10)
        ->select();
        // $count=$model
        // ->field('id,account,pay_limit')
        // ->where($map)
        // ->count();
        $count=count($data);
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        if($_REQUEST['data_order']!=''){
            $data_order=reset(explode(',',$_REQUEST['data_order']));
            $data_order_type=end(explode(',',$_REQUEST['data_order']));
            $this->assign('userarpu_order',$data_order);
            $this->assign('userarpu_order_type',$data_order_type);
        }
        $data=my_sort($data,$data_order_type,(int)$data_order);
        $size=$row;//每页显示的记录数
        $pnum = ceil(count($data) / $size); //总页数，ceil()函数用于求大于数字的最小整数
        //用array_slice(array,offset,length) 函数在数组中根据条件取出一段值;array(数组),offset(元素的开始位置),length(组的长度)
        $data = array_slice($data, ($arraypage-1)*$size, $size);
        $this->assign('list_data', $data);
        $this->meta_title ='代充额度';
        $this->display();
    }
    public function pay_limit_add()
    {
        $limit=D("Promote");
        if(IS_POST){
            if(trim($_REQUEST['promote_id'])==''){
            $this->error("请选择渠道账号");
            }
            $preg=trim($_REQUEST['limits']);
            if($preg==''){
            $this->error("请输入代充额度");
            } 
            if(!preg_match('/^[1-9]\d*$/',$preg)){
                $this->error("请输入大于0的整数");
            }
            $data['id']=$_REQUEST['promote_id'];
            $data['pay_limit']=$preg;
            $find=$limit->where(array("id"=>$data['id']))->find();
            if($find['pay_limit']!=0&&$find['set_pay_time']!=null){
            $this->error("已经设置过该渠道",U('pay_limit'));
            }else{
             $limit->where(array("id"=>$data['id']))->setField('pay_limit',$preg);
             $limit->where(array("id"=>$data['id']))->setField('set_pay_time',time());
             $limit->where(array("id"=>$data['id']))->setField('pay_limit_game',$_REQUEST['pay_limit_game']);
             $this->success("添加成功！",U('pay_limit'));
            }
        }else{
            $this->meta_title ='新增代充额度';
            $this->display();
        }
    }
    public function pay_limit_del()
    {
        $limit=D("Promote");
        if(empty($_REQUEST['ids'])){
            $this->error('请选择要操作的数据');
        }
        if(isset($_REQUEST['ids'])){
            $id=$_REQUEST['ids'];
        }
        if(is_array($id)){
            $map['id']=array('in',$id);
        }else{
            $map['id']=$id;
        }
         $limit
         ->where($map)
         ->setField('pay_limit','0');
         $this->success("删除成功！",U('pay_limit'));
    }
    public function pay_limit_edit()
    {
        $limit=D("Promote");
        if(IS_POST){
            if(trim($_REQUEST['promote_id'])==''){
            $this->error("请选择管理员推广员");
            }
            $preg=trim($_REQUEST['limits']);
            if($preg==''){
            $this->error("请输入代充额度");
            }
            if(!preg_match('/^[1-9]\d*$/',$preg)){
                $this->error("请输入大于0的整数");
            }
            $data['id']=$_REQUEST['promote_id'];
             $edit=$limit->where(array("id"=>$data['id']))->setField('pay_limit',$preg);
             $limit->where(array("id"=>$data['id']))->setField('set_pay_time',time());
             $limit->where(array("id"=>$data['id']))->setField('pay_limit_game',$_REQUEST['pay_limit_game']);
             if($edit===false){
                $this->error('数据未更改');
             }else{
                $this->success("编辑成功！",U('pay_limit'));
            }
        }else{
            $edit_data=$limit
            ->where(array('id'=>$_REQUEST['ids']))
            ->find();
            $this->assign('edit_data',$edit_data);
            $this->meta_title ='编辑代充额度';
            $this->display();
        }
    }
    
}
