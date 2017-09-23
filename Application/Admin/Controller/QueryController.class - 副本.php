<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 推广查询控制器
 * @author 王贺
 */
class QueryController extends ThinkController {
    public function settlement($p=0) {
        $group = I('group',1);
        $this->assign('group',$group);
        if(isset($_REQUEST['total_status'])){
            unset($_REQUEST['total_status']);
        }
        if ($group == 1) {
            if($_REQUEST['unum']==1){
                $order='unum';
                $order_type=SORT_ASC;
            }else if($_REQUEST['unum']==0){
                $order='unum';
                $order_type=SORT_DESC;
            }
            if($_REQUEST['spay_amount']==1){
                $order='spay_amount';
                $order_type=SORT_ASC;
            }else if($_REQUEST['spay_amount']==0){
                $order='spay_amount';
                $order_type=SORT_DESC;
            }
            $model = array(
                'title'  => '渠道结算',
                'template_list' =>'settlement',
                'order' => $order,
                'order_type'=>$order_type//0倒序  1 正序
            );
            $start=$_REQUEST['timestart'];
            $end=$_REQUEST['timeend'];
            if(I('group')!=''){
                if($start==''||$end==''&&$_REQUEST['promote_account']==''){
                    $this->error('结算周期、所属渠道不能为空！','',1);
                }
                if($start==''||$end==''){
                    $this->error('请选择结算周期！','',1);
                }
                if($_REQUEST['promote_account']==''){
                    $this->error('请选择渠道！','',1);
                }
            }
            if($start && $end){
                if((strtotime($end)+24*60*60-1)<strtotime($start)){
                    $this->error('时间选择不正确！',U('Query/settlement'),'');
                }
                $umap['register_time']=array('BETWEEN',array(strtotime($start),strtotime($end)+24*60*60-1));
                if(isset($_REQUEST['game_name'])&&$_REQUEST['game_name']!=''){
                    $umap['fgame_name']=$_REQUEST['game_name'];
                    $smap['tab_spend.game_name']=$_REQUEST['game_name'];
                }
                if(isset($_REQUEST['promote_account'])&&$_REQUEST['promote_account']!=''){
                    $allid=get_subordinate_promote($_REQUEST['promote_account'],'parent_name');
                    $allid[]=$_REQUEST['promote_account'];
                    $umap['tab_user.promote_account']=array('in',implode(',',$allid));
                    $smap['tab_spend.promote_account']=array('in',implode(',',$allid));
                }else{
                    $this->error('未选择渠道！','',1);
                }
                $umap['is_check']=1;
                $smap['pay_time']=array('BETWEEN',array(strtotime($start),strtotime($end)+24*60*60-1));
                $smap['is_check']=1;
                $map['umap']=$umap;
                $map['smap']=$smap;
                $user = A('Settlement','Event');
                $user->settlement($model,$p,$map);
            }else{
                $this->assign('setdate',date("Y-m-d",strtotime("-1 day")));
                $this->display();
            }
        }
        if ($group == 2) {            
           if(isset($_REQUEST['timestart'])&&isset($_REQUEST['timeend'])){
                $map['create_time']=array('BETWEEN',array(strtotime($_REQUEST['timestart']),strtotime($_REQUEST['timeend'])+24*60*60-1));
            }
            if(isset($_REQUEST['game_name'])){
                if($_REQUEST['game_name']=='全部'){
                    unset($_REQUEST['game_name']);
                }else{
                    $map['game_name'] = $_REQUEST['game_name'];
                }
            }
            if(isset($_REQUEST['promote_account'])){
                if($_REQUEST['promote_account']=='全部'){
                    unset($_REQUEST['promote_account']);
                }else{
                    $map['promote_account'] = $_REQUEST['promote_account'];
                }
            }
             if(!empty($_REQUEST['settlement_number'])){
                $map['settlement_number'] = $_REQUEST['settlement_number'];
            }
            $model = array(
                'm_name' => 'settlement',
                'order'  => 'create_time desc ',
                'title'  => '结算账单',
                'template_list' =>'settlement',
            );
            $user = A('Bill','Event');
            $user->money_list($model,$p,$map);
        }
    }
    public function cpsettlement($p=0) {
        $group = I('group',1);
        $this->assign('group',$group);
        if(isset($_REQUEST['timestart'])&&$_REQUEST['timestart']!=''&&$_REQUEST['group']==1){
            $starttime=strtotime($_REQUEST['timestart'].'-01');
             if($starttime>=strtotime(date('Y-m-01'))){
                 $this->error('时间选择不正确','',1);exit;
             }
            $endtime=strtotime($_REQUEST['timestart']."+1 month -1 day")+24*3600-1;
            if(isset($_REQUEST['game_name'])&&$_REQUEST['game_name']!='全部'){
                $map['g.game_name']=$_REQUEST['game_name'];
            }
            if(isset($_REQUEST['selle_status'])){
                if($_REQUEST['selle_status']=="未结算"){
                    $map['s.selle_status']=0;
                }else if($_REQUEST['selle_status']=="已结算"){
                    $map['s.selle_status']=1;
                }
            }
            $map['s.pay_status']=1;
            $map['pay_time']=array('BETWEEN',array($starttime,$endtime));
            $model = array(
                'm_name' => 'Spend as s',
                'order'  => 's.id',
                'title'  => '渠道结算',
                'group'  => 'g.developers,g.id',
                'fields'  =>'sum(s.pay_amount) as total,s.selle_ratio,s.id,g.developers,s.selle_status,g.id as gid,g.game_name,s.pay_status,s.pay_amount',
                'template_list' =>'cpsettlement',
            );
            
            $user = A('Spend','Event');
            $user->cpsettl_list($model,$p,$map);
        }else if($_REQUEST['group']==2){
            if(isset($_REQUEST['timestart'])&&$_REQUEST['timestart']!=''){
                $starttime=strtotime($_REQUEST['timestart'].'-01');
                if($starttime>=strtotime(date('Y-m-01'))){
                    $this->error('时间选择不正确','',1);exit;
                }
                $starttime=strtotime($_REQUEST['timestart'].'-01');
                $endtime=strtotime($_REQUEST['timestart']."+1 month -1 day")+24*3600-1;
                $map['pay_time']=array('BETWEEN',array($starttime,$endtime));  
            }
            $map['s.pay_status']=1;
            $map['s.selle_status']=1;//已结算
            $model = array(
                'm_name' => 'Spend as s',
                'order'  => 's.id',
                'title'  => '渠道结算',
                'group'  => 'g.developers,g.id',
                'fields'  =>'sum(s.pay_amount) as total, s.id,s.selle_ratio,g.developers,s.selle_status,s.selle_time,g.id as gid,g.game_name,s.pay_status,s.pay_amount',
                'template_list' =>'cpsettlement',
            );
            
            $user = A('Spend','Event');
            $user->cpsettl_list($model,$p,$map);
        }else{
            $this->meta_title = '渠道结算列表';
            $this->display();
        }
    }
    public function generatesettlement(){
        //批量结算要加判断
        $data['game_id']=$_REQUEST['game_id'];
        $data['game_name']=get_game_name($_REQUEST['game_id']);
        $data['promote_id']=$_REQUEST['promote_id'];
        $data['promote_account']=get_promote_name($_REQUEST['promote_id']);
        $data['total_money']=$_REQUEST['spay_amount'];
        $data['total_number']=$_REQUEST['unum'];
        $data['starttime']=strtotime($_REQUEST['starttime']);
        $data['endtime']=strtotime($_REQUEST['endtime'])+24*60*60-1;
        $data['pattern']=$_REQUEST['cooperation']=='CPS'?0:1;
        $data['ratio']=$_REQUEST['cps_ratio'];
        $data['money']=$_REQUEST['cpa_price'];
        $data['create_time']=time();
        $data['settlement_number']='JS-'.date('Ymd').date('His').sp_random_string(4);
        if(get_settlement($data['starttime'],$data['promote_id'],$data['game_id'])){
            $this->error('该结算周期不可结算，请重新选择');
        }
        if($data['pattern']){
            $data['sum_money']=$data['total_number']*$data['money'];
        }else{
            $data['sum_money']=$data['total_money']*$data['ratio']/100;
        }
        if($data['game_id']==''||$data['promote_id']==''||$data['starttime']==''||$data['endtime']==''){
            $this->error('必要参数不存在');
        }
        $map['fgame_id']=$data['game_id'];
        // $map['is_check']=1;
        $map['register_time']=array('BETWEEN',array($data['starttime'],$data['endtime']));
        $allid=get_subordinate_promote($data['promote_account'],'parent_name');
        $allid[]=$data['promote_account'];
        $map['promote_id']=array('in',$data['promote_id']);
        $u=M('User','tab_');
        $user=$u->where($map)->setField('settle_check',1);
        unset($map['register_time']);
        $map['pay_time']=array('BETWEEN',array($data['starttime'],$data['endtime']));
        $s=M('spend','tab_');
        $spend=$s->where($map)->setField('settle_check',1);
        $result=M('settlement','tab_')->add($data);
        if($result){
            $this->success('结算成功');
        }else{
            $this->error('结算失败');
        }
    }
    public function generatecpsettlement() {//cp结算
        $game_id    =   I('request.ids');
        if(empty($game_id)){
            $this->error('请选择要操作的数据');
        }
        $starttime=strtotime($_REQUEST['timestart'].'-01');
        $endtime=strtotime($_REQUEST['timestart']."+1 month -1 day")+24*3600-1;
        $map['s.pay_status']=1;
        $map['s.selle_status']=0;
        if(is_array($game_id)){
            $map['s.game_id']=array('in',$game_id);
        }else{
            $map['s.game_id']=$game_id;
        }
        $map['pay_time']=array('BETWEEN',array($starttime,$endtime));
        $spe=M('spend as s','tab_');
        $smap= array('s.selle_time'=>$_REQUEST['timestart'],'s.selle_status'=>1);
        $data=$spe
        ->field('s.id,s.selle_status,s.selle_time')
        ->join('tab_game as g on g.id=s.game_id','LEFT')
        ->where($map)
        ->setField($smap);
        if($data){
            $this->success('结算成功');
        }else{
            $this->error('结算失败');
        }
        
    }
    public function changeratio(){
        $gid    =   I('request.game_id');
        if(empty($gid)){
             $this->ajaxReturn(0,"请选择要操作的数据",0);exit;
        }
        $starttime=strtotime($_REQUEST['timestart'].'-01');
        $endtime=strtotime($_REQUEST['timestart']."+1 month -1 day")+24*3600-1;
        $map['s.pay_status']=1;
        $map['s.selle_status']=0;
        $map['s.game_id']=$_REQUEST['game_id'];
        $map['pay_time']=array('BETWEEN',array($starttime,$endtime));
        $spe=M('spend as s','tab_');
        $data=$spe
        ->field('s.id,s.selle_status,s.selle_ratio')
        ->join('tab_game as g on g.id=s.game_id','LEFT')
        ->where($map)
        ->setField('s.selle_ratio',$_POST['ratio']);
        if($data){
            $this->ajaxReturn($data);
        }else{
            $this->ajaxReturn(-1);
        }
    }
    public function withdraw() {
        
        if(isset($_REQUEST['settlement_number'])){
            $map['settlement_number']=$_REQUEST['settlement_number'];
        }
        if(isset($_REQUEST['status'])){
            $map['status']=$_REQUEST['status'];
        }
        if(isset($_REQUEST['promote_account'])){
            if($_REQUEST['promote_account']=='全部'){
                unset($_REQUEST['promote_account']);
            }else{
                $map['promote_account'] = $_REQUEST['promote_account'];
            }
        }


        $model = array(
            'm_name' => 'withdraw',
            'order'  => 'create_time desc ',
            'title'  => '渠道提现',
            'template_list' =>'withdraw',
        );
        
        $user = A('Bill','Event');
        $user->money_list($model,$p,$map);
        
    }
    
       public function set_withdraw_status($model='withdraw') {
        $withdraw=M('withdraw',"tab_");
        $seet=M('settlement',"tab_");
        $count=count($_REQUEST['ids']);
        if($count>1){
            for ($i=0; $i <$count; $i++) { 
            $map['id']=$_REQUEST['ids'][$i];
            $dind=$withdraw->where($map)->find();
            $se_map['settlement_number']=$dind['settlement_number'];
            $seet->where($se_map)->save(array("ti_status"=>$_REQUEST['status']));
            $withdraw->where($map)->save(array("end_time"=>time()));
            }
        }else{
            $map['id']=$_REQUEST['ids'];
            $dind=$withdraw->where($map)->find();
            $se_map['settlement_number']=$dind['settlement_number'];
            $seet->where($se_map)->save(array("ti_status"=>$_REQUEST['status']));
            $withdraw->where($map)->save(array("end_time"=>time()));
        }

        parent::set_status($model);
    }


    protected function upPromote($promote_id){
        $model = D('Promote');
        $data['id'] = $promote_id;
        $data['money'] = 0;
        return $model->save($data);
    }
}