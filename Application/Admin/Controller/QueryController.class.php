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
            // var_dump($_REQUEST);exit;
            if($_REQUEST['unum']==2){
                $order='unum';
                $order_type=SORT_ASC;
            }else if($_REQUEST['unum']==1){
                $order='unum';
                $order_type=SORT_DESC;
            }
            if($_REQUEST['spay_amount']==2){
                $order='spay_amount';
                $order_type=SORT_ASC;
            }else if($_REQUEST['spay_amount']==1){
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
            $smap['tab_spend.pay_status']=1;
            $this->meta_title = '渠道结算列表';
            $this->assign('setdate',date("Y-m-d",strtotime("-1 day")));
            if($start && $end){
                if((strtotime($end)+24*60*60-1)<strtotime($start)){
                    $this->error('时间选择不正确！',U('Query/settlement'),'');
                }
                $umap['register_time']=array('BETWEEN',array(strtotime($start),strtotime($end)+24*60*60-1));
                if(isset($_REQUEST['game_name']) && $_REQUEST['game_name']!=''){
                    $umap['fgame_id']=get_game_id($_REQUEST['game_name']);
                    $smap['tab_spend.game_id']=get_game_id($_REQUEST['game_name']);
                }
                if(isset($_REQUEST['promote_account'])&&$_REQUEST['promote_account']!=''){
                    $allid=get_subordinate_promote($_REQUEST['promote_account'],'parent_name');
                    $allid[]=$_REQUEST['promote_account'];
                    $umap['tab_user.promote_account']=array('in',implode(',',$allid));
                    $smap['tab_spend.promote_account']=array('in',implode(',',$allid));
                    // $umap['tab_user.promote_account']=$_REQUEST['promote_account'];
                    // $smap['tab_spend.promote_account']=$_REQUEST['promote_account'];
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
                $this->display();
            }
        }
        if ($group == 2) {            
           if(isset($_REQUEST['stimestart'])&&isset($_REQUEST['stimeend'])){
                $map['create_time']=array('BETWEEN',array(strtotime($_REQUEST['stimestart']),strtotime($_REQUEST['stimeend'])+24*60*60-1));
            }
            if(isset($_REQUEST['timestart'])&&isset($_REQUEST['timeend'])){
                $map['starttime']=strtotime($_REQUEST['timestart']);
                $map['endtime']=strtotime($_REQUEST['timeend'])+24*60*60-1;
            }
            // var_dump($map);exit;
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
            $map1=$map;
            //$map1['status'] = 1;
            $ztotal=null_to_0(D('settlement')->where($map1)->sum('sum_money'));
            $this->assign('ztotal',$ztotal);
            $ttotal=null_to_0(D('settlement')->where('create_time'.total(1))->sum('sum_money'));
            $this->assign('ttotal',$ttotal);
            $ytotal=null_to_0(D('settlement')->where('create_time'.total(5))->sum('sum_money'));
            $this->assign('ytotal',$ytotal);
            $user = A('Bill','Event');
            $user->money_list($model,$p,$map);
        }
    }
    public function cpsettlement($p=0) {
        if($_REQUEST['sum_money']==2){
            $order='pay_amount desc';
        }else if($_REQUEST['sum_money']==1){
            $order='pay_amount asc';
        }
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
                'order'  => $order,
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
            if(isset($_REQUEST['game_name'])&&$_REQUEST['game_name']!='全部'){
                $map['g.game_name']=$_REQUEST['game_name'];
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
            if(isset($_REQUEST['game_name'])&&$_REQUEST['game_name']!='全部'){
                $map1['game_name']=$_REQUEST['game_name'];
            }
            if(isset($_REQUEST['timestart'])&&$_REQUEST['timestart']!=''){
                $map1['selle_time'] = $_REQUEST['timestart'];
            }
            $map1['pay_status'] = 1;
            $map1['selle_status'] = 1;
            $ztotal=null_to_0(D('spend')->where($map1)->sum('pay_amount*selle_ratio/100'));
            $this->assign('ztotal',$ztotal);
            $ttotal=null_to_0(D('spend')->where($map1)->where('selle_time'.total(1))->sum('pay_amount*selle_ratio/100'));
            $this->assign('ttotal',$ttotal);
            $ytotal=null_to_0(D('spend')->where($map1)->where('selle_time'.total(5))->sum('pay_amount*selle_ratio/100'));
            $this->assign('ytotal',$ytotal);
            $user = A('Spend','Event');
            $user->cpsettl_list($model,$p,$map);
        }else{
            $this->meta_title = '渠道结算列表';
            $this->display();
        }
    }
    public function generatesettlementAll(){
        $request    =   I('request.ids');
        if(empty($request)){
            $this->error('请选择要操作的数据');
        }
        if (is_array($request)) {
            foreach($request as $v) {
                $query = explode(',',$v);
                $ids[] = $query[0];
                $_REQUEST[$query[0]]['ids']=$query[0];
                $_REQUEST[$query[0]]['cooperation']=$query[1];
                $_REQUEST[$query[0]]['cps_ratio']=$query[2];
                $_REQUEST[$query[0]]['cpa_price']=$query[3];
                $_REQUEST[$query[0]]['unum']=$query[4];
                $_REQUEST[$query[0]]['spay_amount']=$query[5];
            } 
            unset($_REQUEST['ids']);
        } elseif (is_numeric($request)) {
            $id = $ids[] = $request;
            $_REQUEST[$id]['ids']=$id;
            $_REQUEST[$id]['cooperation']=$_REQUEST['cooperation'];
            $_REQUEST[$id]['cps_ratio']=$_REQUEST['cps_ratio'];
            $_REQUEST[$id]['cpa_price']=$_REQUEST['cpa_price'];
            $_REQUEST[$id]['unum']=$_REQUEST['unum'];
            $_REQUEST[$id]['spay_amount']=$_REQUEST['spay_amount'];
        } else {
            $this->error('参数有误！！！');
        }
        sort(array_unique($ids));
        if (is_array($ids)) {
            foreach ($ids as $k => $v) {
                $data[$k]['game_id'] = $v;
                $data[$k]['game_name']=get_game_name($v);
                $data[$k]['promote_account']=$_REQUEST['promote_account'];
                $data[$k]['promote_id']=get_promote_id($_REQUEST['promote_account']);
                $data[$k]['total_money']=$_REQUEST[$v]['spay_amount'];
                $data[$k]['total_number']=$_REQUEST[$v]['unum'];
                $data[$k]['starttime']=strtotime($_REQUEST['timestart']);
                $data[$k]['endtime']=strtotime($_REQUEST['timeend'])+24*60*60-1;
                $data[$k]['pattern']=$_REQUEST[$v]['cooperation']=='CPS'?0:1;
                $data[$k]['ratio']=$_REQUEST[$v]['cps_ratio'];
                $data[$k]['money']=$_REQUEST[$v]['cpa_price'];
                $data[$k]['create_time']=time();
                $data[$k]['settlement_number']='JS-'.date('Ymd').date('His').sp_random_string(4);
                if(get_settlement($data[$k]['starttime'],$data[$k]['promote_id'],$data[$k]['game_id'])){
                    $this->error('该结算周期不可结算，请重新选择');
                }
                if($data[$k]['pattern']){
                    $data[$k]['sum_money']=$data[$k]['total_number']*$data[$k]['money'];
                }else{
                    $data[$k]['sum_money']=$data[$k]['total_money']*$data[$k]['ratio']/100;
                }
                if($data[$k]['game_id']==''||$data[$k]['promote_id']==''||$data[$k]['starttime']==''||$data[$k]['endtime']==''){
                    $this->error('必要参数不存在');
                }
                $map['fgame_id']=$data[$k]['game_id'];
                // $map['is_check']=1;
                $map['register_time']=array('BETWEEN',array($data[$k]['starttime'],$data[$k]['endtime']));
                $allid=get_subordinate_promote($data[$k]['promote_account'],'parent_name');
                $allid[]=$data[$k]['promote_account'];
                $map['promote_id']=array('in',$data[$k]['promote_id']);
                $u=M('User','tab_');
                $user=$u->where($map)->setField('settle_check',1);
                unset($map['register_time']);
                $map['pay_time']=array('BETWEEN',array($data[$k]['starttime'],$data[$k]['endtime']));
                $s=M('spend','tab_');
                $spend=$s->where($map)->setField('settle_check',1);
            }
        }
        $result=M('settlement','tab_')->addAll($data);
        if($result){
            $this->success('结算成功');
        }else{
            $this->error('结算失败');
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
        // var_dump($_REQUEST);exit;
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
            $datas['game_id']=$game_id;
            $datas['game_name']=get_game_name($game_id);
            $datas['promote_id']='0';
            $datas['promote_account']='0';
            $datas['total_money']=$_REQUEST['total'];
            $datas['total_number']=0;
            $datas['starttime']=$starttime;
            $datas['endtime']=$endtime;
            $datas['pattern']=$_REQUEST['cooperation']=='CPS'?0:1;
            $datas['ratio']=$_REQUEST['selle_ratio'];
            $datas['sum_money']=$_REQUEST['selle_ratio']*$_REQUEST['total']/100;
            $datas['money']=0;
            $datas['developers']=$_REQUEST['developers'];
            $datas['create_time']=time();
            $datas['settlement_number']='JS-'.date('Ymd').date('His').sp_random_string(4);
            $result=M('settlement','tab_')->add($datas);
            $this->success('结算成功');
        }else{
            $this->error('结算失败');
        }
        $map1=array('status'=>1,'selle_status'=>1);
        $total=null_to_0(D('spend')->where($map1)->sum('pay_amount'));
        $ttotal=null_to_0(D('spend')->where('pay_time'.total(1))->where($map1)->sum('pay_amount'));
        $ytotal=null_to_0(D('spend')->where('pay_time'.total(5))->where($map1)->sum('pay_amount'));
//         var_dump(D(self::model_name)->getlastsql());exit;
        // $this->assign('dtotal',$dtotal);
        $this->assign('total',$total);
        $this->assign('ttotal',$ttotal);
        $this->assign('ytotal',$ytotal);

        
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
    public function cp_withdraw(){
        if(isset($_REQUEST['settlement_number'])){
            $map['settlement_number']=$_REQUEST['settlement_number'];
        }
        if(isset($_REQUEST['status'])){
            $map['status']=$_REQUEST['status'];
        }
        if(isset($_REQUEST['developers'])){
            if($_REQUEST['developers']=='全部'){
                unset($_REQUEST['developers']);
            }else{
                $map['developers'] = $_REQUEST['developers'];
            }
        }else{
            $map['developers'] = array('neq',0);

        }

        if($_REQUEST['create_time']==2){
            $order='create_time desc';
        }elseif($_REQUEST['create_time']==1){
            $order='create_time asc';
        }else{
            $order='create_time desc';
        }
        if($_REQUEST['sum_money']==2){
            $order='sum_money desc';
        }elseif($_REQUEST['sum_money']==1){
            $order='sum_money asc';
        }
        $model = array(
            'm_name' => 'withdraw',
            'order'  => $order,
            'title'  => '渠道提现',
            'template_list' =>'cp_withdraw',
        );
        $map1=array('status'=>1);
        $total=null_to_0(D('withdraw')->where($map1)->sum('sum_money'));
        $ttotal=null_to_0(D('withdraw')->where('end_time'.total(1))->where($map1)->sum('sum_money'));
        $ytotal=null_to_0(D('withdraw')->where('end_time'.total(5))->where($map1)->sum('sum_money'));
        // var_dump(D(self::model_name)->getlastsql());exit;  
        // $this->assign('dtotal',$dtotal);
        $this->assign('total',$total);
        $this->assign('ttotal',$ttotal);
        $this->assign('ytotal',$ytotal);
        $user = A('Bill','Event');
        $user->money_list($model,$p,$map);
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
        }else{
            $map['promote_id'] = array('gt',0);

        }

        if($_REQUEST['create_time']==2){
            $order='create_time desc';
        }elseif($_REQUEST['create_time']==1){
            $order='create_time asc';
        }else{
            $order='create_time desc';
        }
        if($_REQUEST['sum_money']==2){
            $order='sum_money desc';
        }elseif($_REQUEST['sum_money']==1){
            $order='sum_money asc';
        }
        $model = array(
            'm_name' => 'withdraw',
            'order'  => $order,
            'title'  => '渠道提现',
            'template_list' =>'withdraw',
        );
        $map1=array('status'=>1);
        $total=null_to_0(D('withdraw')->where($map1)->sum('sum_money'));
        $ttotal=null_to_0(D('withdraw')->where('end_time'.total(1))->where($map1)->sum('sum_money'));
        $ytotal=null_to_0(D('withdraw')->where('end_time'.total(5))->where($map1)->sum('sum_money'));
        // var_dump(D(self::model_name)->getlastsql());exit;  
        // $this->assign('dtotal',$dtotal);
        $this->assign('total',$total);
        $this->assign('ttotal',$ttotal);
        $this->assign('ytotal',$ytotal);
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