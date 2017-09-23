<?php

namespace Admin\Controller;
use Admin\Model\SpendModel;
use Open\Model\UserLoginRecordModel;
use Admin\Model\UserPlayModel;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class StatController extends ThinkController
{
    /**
     * 留存统计
     * @param int $p
     * 06.12.3
     * xmy
     */
    public function userretention($p = 0)
    {
        $request=$_REQUEST;
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $arraypage=$page;
        $row = 10;
        $start = I('start');
        $end = empty(I('end')) ? time_format(time(),'Y-m-d') : I('end');
        if(!empty($start)){
            $game_id = I('game_id');
            $promote_id = I('promote_id');
            $map_list = array();
            /*if(!empty(I('game_name')) && I('game_name') != '全部'){
                $map_list['game_name'] = array('like','%'.$game_name.'%');
            }*/
            if(I('game_id') !=0){
                $this->assign('game_name',get_game_name(I('game_id')));
            }
            if(I('promote_id') !=0){
                $this->assign('promote_name',get_promote_account(I('promote_id')));
            }
            //统计每日注册数
            $data = $this->count_register($start,$end,$game_id,$promote_id,$page);

            $day = array(1,2,3,4,5,6,7,15,30,60);
            foreach ($data as $k=>$v) {
                //当日注册人帐号
                $map = $map_list;
                $time = $v['time'];
                $map["FROM_UNIXTIME(register_time,'%Y-%m-%d')"] = $time;
                //每日留存
                foreach ($day as $key => $value) {
                    $map = $map_list;
                    $map["FROM_UNIXTIME(register_time,'%Y-%m-%d')"] = $time;
                    $login_time = date('Y-m-d', strtotime("+{$value} day",strtotime($time)));
                    $num = M('user','tab_')
                        ->field('count(DISTINCT tab_user.id) as num')
                        ->join("right join tab_user_login_record as ur on ur.user_id = tab_user.id and FROM_UNIXTIME(ur.login_time,'%Y-%m-%d') = '{$login_time}'")
                        ->where($map)
                        ->group('user_id')
                        ->find();
//                         var_dump( M('user','tab_')->getlastsql());exit;
                    $data[$k][$value] = $num['num'];
                }
            }

            //分页
            $time_map['time'] = array('between',array($start,$end));
            $count = M('date_list')->where($time_map)->count();
            if($count > $row){
                $page = new \Think\Page($count, $row);
                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                $this->assign('_page', $page->show());
            }
        }else{
            unset($_REQUEST['data_order']);
            if(count(I('get.'))!=0){
                $this->error('时间选择错误，请重新选择！');
            }
        }
        if($request['data_order']!=''){
            $data_order=reset(explode(',',$request['data_order']));
            $data_order_type=end(explode(',',$request['data_order']));
            $this->assign('userarpu_order',$data_order);
            $this->assign('userarpu_order_type',$data_order_type);
        }
        $data=my_sort($data,$data_order_type,(int)$data_order,SORT_STRING);
        $size=10;//每页显示的记录数
        $pnum = ceil(count($data) / $size); //总页数，ceil()函数用于求大于数字的最小整数
        //用array_slice(array,offset,length) 函数在数组中根据条件取出一段值;array(数组),offset(元素的开始位置),length(组的长度)
        $data = array_slice($data, ($arraypage-1)*$size, $size);
        // var_dump($data);exit;
        $this->assign('data',$data);
        $this->meta_title = '留存统计';
        $this->display();
    }

    /**
     * 流失分析
     */
    public function userloss(){
        $start=get_lastweek_name(6);
        $end=date('Y-m-d');
        $this->assign('start',$start);
        $this->assign('end',$end);
        $result=$this->loss_pic($_REQUEST);
        foreach ($result['day'] as $key => $value) {
            $res['lossplayer']['loss'][$value]=$result['loss_count'][$key];
            $res['lossplayer']['lossrate'][$value]=$result['loss_rate'][$key];
        }
        $money_arr=array(
            ">$2000","$1000~2000","$600~1000","$200~600","$100~200","$40~100","$20~40","$10~20","$2~$10","<$2",
        );
        foreach ($money_arr as $key => $value) {
            $res['lossmoney'][$value]=$result['loss_money'][$key];
        }
        $times_arr=array(
            ">50次","41~50次","31~40次","21~30次","11~20次","6~10次","5次","4次","3次","2次","1次","未付费",
        );
        foreach ($times_arr as $key => $value) {
            $res['losstimes'][$value]=$result['loss_times'][$key];
        }
        $this->assign("json_data",json_encode($res));
        $this->display();
    }
    //流失率分析
    public function loss_pic($para){
        if(isset($para['time_start'])&&isset($para['time_end'])&&$para['time_start']!==null&&$para['time_end']!==null){
            $dd=prDates($para['time_start'],$para['time_end']);
            $day=$dd;
            $this->assign('tt',array_chunk($dd,1));
        }else{
            $defTimeE=date("Y-m-d",time());
            $defTimeS=date("Y-m-d",time()-24*60*60*6);
            $day=every_day(7);
            $dd=prDates($defTimeS,$defTimeE);
        }
        if(isset($para['game_id'])){
            $map['r.game_id']=$para['game_id'];
            $map1['r.game_id']=$para['game_id'];
        }
        if(isset($para['channel_id'])&&$para['channel_id']!=""){
            if($para['channel_id']==2){
                $d=7;
            }else{
                $d=3;
            }
        }else{
            $d=3;
        }
        $limitI=count($dd);
        $Record = new UserLoginRecordModel();
        for($i=0;$i<$limitI;$i++){
            $start=$this->get_time($dd[$i],$d);
            $end=$start+24*60*60-1;
            $map['login_time']=array('between',array($start,$end));
            if(isset($para['promote_id'])&&$para['promote_id']!=""){
                $map['developers']=$para['promote_id'];
            }
            $logins=$Record->getPlayers($map);
            if($logins==null){
                $loss=null;
            }else{
                $loss=null;
                foreach ($logins as $key => $value) {
                    $start=date("Y-m-d", $value['login_time']+24*60*60);
                    $start=$this->get_time($start,0);
                    $end=$start+24*60*60*$d;
                    $map1['login_time']=array('between',array($start,$end));
                    $map1['user_id']=$value['user_id'];
                    if(isset($para['promote_id'])&&$para['promote_id']!=""){
                        $map1['developers']=$para['promote_id'];
                    }
                    $result1=$Record->findPlayer($map1);
                    if($result1==null){
                        $loss[]=$logins[$key];
                    }
                }
            }
            if($loss!=null){
                $loser[]=$loss;
            }
            $loss_count[]=count($loss);
            $loss_rate[]=count($loss)/count($logins)*100?sprintf("%.2f",count($loss)/count($logins)*100):0;
        }
        foreach ($loser as $key => $value) {
            foreach ($value as $k => $v) {
                $losers[]=$v['user_id'];
            }
        }
        $data2=$this->loss_pic2($losers);
        $data3=$this->loss_pic3($losers);
        $result['day']=$day;
        $result['loss_count']=$loss_count;
        $result['loss_rate']=$loss_rate;
        $result['loss_money']=$data2;
        $result['loss_times']=$data3;
        return $result;

    }
    /**
     * 流失用户消费金额分析，包括不同等级的人数和所占比例
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function loss_pic2($data){
        $Spend=new SpendModel();
        foreach ($data as $key => $value) {
            $list[$key]['user_id']=$value;
            $list[$key]['amount']=$Spend->totalSpend($list[$key]);
        }
        foreach ($list as $k => $v) {
            if($v['amount']<2){
                $two[]=$v;
            }elseif($v['amount']<10){
                $ten[]=$v;
            }elseif($v['amount']<20){
                $twenty[]=$v;
            }elseif($v['amount']<40){
                $forty[]=$v;
            }elseif($v['amount']<100){
                $hundred[]=$v;
            }elseif($v['amount']<200){
                $thundred[]=$v;
            }elseif($v['amount']<600){
                $shundred[]=$v;
            }elseif($v['amount']<1000){
                $thousand[]=$v;
            }elseif($v['amount']<2000){
                $tthousand[]=$v;
            }else{
                $thousands[]=$v;
            }
        }
        $result=[count($thousands),count($tthousand),count($thousand),count($shundred),count($thundred),count($hundred),count($forty),count($twenty),count($ten),count($two)];
        return $result;
    }
    /**
     * 流失用户消费次数分析，包括不同次数所占人数和比例
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function loss_pic3($data){
        $Spend=new SpendModel();
        foreach ($data as $key => $value) {
            $list[$key]['user_id']=$value;
            $list[$key]['count']=$Spend->totalSpendTimes($list[$key]);
        }
        foreach ($list as $k => $v) {
            if($v['count']==0){
                $zero[]=$v;
            }elseif($v['count']==1){
                $one[]=$v;
            }elseif($v['count']==2){
                $two[]=$v;
            }elseif($v['count']==3){
                $three[]=$v;
            }elseif($v['count']==4){
                $four[]=$v;
            }elseif($v['count']==5){
                $five[]=$v;
            }elseif($v['count']<11){
                $ten[]=$v;
            }elseif($v['count']<21){
                $twenty[]=$v;
            }elseif($v['count']<31){
                $thirty[]=$v;
            }elseif($v['count']<41){
                $forty[]=$v;
            }elseif($v['count']<51){
                $fifty[]=$v;
            }else{
                $fiftys[]=$v;
            }
        }
        $result=[count($fiftys),count($fifty),count($forty),count($thirty),count($twenty),count($ten),count($five),count($four),count($three),count($two),count($one),count($zero)];
        return $result;
    }


    /**
     * 统计注册数
     * @param $start    开始时间
     * @param $end      结束时间
     * @param string $game_name     游戏名称
     * @param string $promote_id    渠道ID
     * @param int $page
     */
    protected function count_register($start,$end,$game_id="",$promote_id="",$page=0,$row=10){
        $map['time'] = array('between',array($start,$end));
        $join = "left join tab_user u on FROM_UNIXTIME(u.register_time,'%Y-%m-%d') = time";
        /*if(!empty($game_name) && $game_name != "全部"){
            $join .= " AND u.fgame_name LIKE '%{$game_name}%'";
        }*/
        if($game_id != ''){
            $join .= " AND u.fgame_id = {$game_id}";
        }
        if($promote_id != ''){
            $join .= " AND u.promote_id = {$promote_id}";
        }
        //统计每日注册数
        $data = M('date_list')
            ->field("time,COUNT(u.id) as register_num")
            ->join($join)
            ->where($map)
            ->group('time')
            // ->page($page,$row)
            ->select();
        return $data;

    }


    /**
     * [get_time 通过日期获得时间戳]
     * @param  [type] $date [description]
     * @return [type]       [int]
     */
    private function get_time($date,$d){
        $date= explode("-",$date);
        $year=$date[0];
        $month=$date[1];
        $day=$date[2];
        $start=mktime(0,0,0,$month,$day,$year)-$d*24*60*60;
        return $start;
    }




    /*
     * 根据时间计算注册人数
     */
    public function getcount($day, $n = null)
    {
        if (null !== $n) {
            $map['register_time'] = get_start_end_time($day, $n);
        } else {
            $map = get_last_day_time($day, "register_time");
        }
        if (isset($_REQUEST['promote_name'])) {
            if ($_REQUEST['promote_name'] == '全部') {
                unset($_REQUEST['promote_name']);
            } else if ($_REQUEST['promote_name'] == '自然注册') {
                $map['tab_user.promote_id'] = array("elt", 0);

            } else {
                $map['tab_user.promote_id'] = get_promote_id($_REQUEST['promote_name']);

            }
        }
        if (isset($_REQUEST['game_name'])) {
            if ($_REQUEST['game_name'] == '全部') {
                unset($_REQUEST['game_name']);
            } else {
                $map['tab_user.fgame_id'] = get_game_id($_REQUEST['game_name']);
            }
            $r_user_id = D('User')
                // ->join('tab_user_play ON tab_user.id=tab_user_play.user_id')
                ->where($map)
                ->select();
            for ($i = 0; $i < count($r_user_id); $i++) {
                $sd[] = $r_user_id[$i]['user_id'];
            }
            $pid = implode(",", $sd);
            $count = D("User")
                // ->join("tab_user_play on tab_user.id=tab_user_play.user_id")
                ->where($map)
                ->count();
        } else {
            $r_user_id = D("User")->where($map)->select();
            for ($i = 0; $i < count($r_user_id); $i++) {
                $sd[] = $r_user_id[$i]['id'];
            }
            $pid = implode(",", $sd);
            $count = D("User")->where($map)->count();
        }
        $r_count = array($pid, $count);
        return $r_count;
    }

    /*
     * 计算留存率
     *
     */
    public function onelogincount($day, $count, $n = null)
    {
        if (null !== $n) {
            $onetime['login_time'] = get_start_end_time($day, $n);
        } else {
            $onetime = get_last_day_time($day, "login_time");
        }
        if (isset($_REQUEST['promote_name'])) {
            if ($_REQUEST['promote_name'] == '全部') {
                unset($_REQUEST['promote_name']);
            } else if ($_REQUEST['promote_name'] == '自然注册') {
                $onetime['promote_id'] = array("elt", 0);

            } else {
                $onetime['promote_id'] = get_promote_id($_REQUEST['promote_name']);

            }
        } else {
            $onetime['promote_id'] = array('egt', 0);
        }
        if (isset($_REQUEST['game_name'])) {
            if ($_REQUEST['game_name'] == '全部') {
                unset($_REQUEST['game_name']);
            } else {
                $onetime['game_id'] = get_game_id($_REQUEST['game_name']);
            }

        } else {
            $onetime['game_id'] = array('gt', 0);
        }
        $onetime['user_id'] = array('in', (string)$count[0]);
        $onelogincount = M("user_login_record", "tab_")->where($onetime)->count('distinct user_id');

        if ($onelogincount != 0) {
            if ($count[1] == 0) {
                $baifen = "";
            } else {
                $lu = $onelogincount / $count[1];
                $baifen = $lu * 100;
                $baifen = $baifen > 100 ? '100%' : $baifen . '%';
            }
        } else {

            if ($count[1] == 0) {
                $baifen = "";
            } elseif ($count[1] != 0) {
                $baifen = "0%";
            }

        }
        return round($baifen) . '%';

    }

    public function userarpu($p=0)
    {
        $request=$_REQUEST;
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据arraypage
        $arraypage = $page ? $page : 1; //默认显示第一页数据
        $row = 10;
        $start = I('start');
        $end = empty(I('end')) ? time_format(time(),'Y-m-d') : I('end');
        $game_id = I('game_id');
        $promote_id = I('promote_id');
        $map_list = array();
        if(I('game_id') !=0){
            $this->assign('game_name',get_game_name(I('game_id')));
        }
        if(I('promote_id') !=0){
            $this->assign('promote_name',get_promote_account(I('promote_id')));
        }
        if(I('game_id') != '') $map_list['game_id'] = I('game_id');
        if(I('promote_id') != '') $map_list['promote_id'] = I('promote_id');
        if(!empty($start)) {
            //新增玩家
            // $data = $this->count_register($start, $end, $game_id, $promote_id, $page);
            $data = $this->count_register($start, $end, $game_id, $promote_id);
            // var_dump($data);exit;
            foreach ($data as $key => $value) {
                $time = $value['time'];
                //活跃玩家
                $data[$key]['act_user'] = $this->count_act_user($time,$game_id,$promote_id);
                //1日留存
                $map = $map_list;
                if($map_list['promote_id']!=''){
                    $map['tab_user.promote_id']=$map_list['promote_id'];
                }
                unset($map['promote_id']);
                $map["FROM_UNIXTIME(register_time,'%Y-%m-%d')"] = $time;
                $login_time = date('Y-m-d', strtotime("+1 day",strtotime($time)));
                $num = M('user','tab_')
                    ->field('count(DISTINCT tab_user.id) as num')
                    ->join("right join tab_user_login_record as ur on ur.user_id = tab_user.id and FROM_UNIXTIME(ur.login_time,'%Y-%m-%d') = '{$login_time}'")
                    ->where($map)
                    ->group('user_id')
                    ->find();
                // var_dump(M('user','tab_')->getlastsql());exit;
                $data[$key]['keep_num'] = round($num['num']/$data[$key]['register_num'],4)*100;
                //充值
                $map = $map_list;
                empty($game_name ) || $map['game_name'] = array('like','%'.$game_name.'%');
                empty($promote_id) || $map['promote_id'] = $promote_id;
                $map['pay_status'] = 1;
                $map["FROM_UNIXTIME(pay_time,'%Y-%m-%d')"] = $time;
                $spend = M('spend','tab_')->field("IFNULL(sum(pay_amount),0) as money,IFNULL(count(distinct user_id),0) as people")->where($map)->find();
                $data[$key]['spend'] = $spend['money'];
                //付费玩家数
                $data[$key]['spend_people'] = $spend['people'];
                //新付费玩家
                $map = $map_list;
                $map['pay_status'] = 1;
                $sql = M('spend','tab_')->field("user_id,min(pay_time) as time")->group('user_id')->where($map)->select(false);
                $sql = "select IFNULL(count(user_id),0) as num from ({$sql}) as t WHERE  FROM_UNIXTIME(t.time,'%Y-%m-%d') = '{$time}'";
                $query = M()->query($sql);
                $data[$key]['new_pop'] = $query[0]['num'];
                //付费率
                $data[$key]['spend_rate'] = round($data[$key]['spend_people']/$data[$key]['act_user'],4)*100;
                //ARPU
                $data[$key]['ARPU'] = round($data[$key]['spend']/$data[$key]['act_user'],2);
                //ARPPU
                $data[$key]['ARPPU'] = round($data[$key]['spend']/$data[$key]['spend_people'],2);

                //累计付费玩家
                $map = $map_list;
                $map['pay_status'] = 1;
                $map["FROM_UNIXTIME(pay_time,'%Y-%m-%d')"] = array('elt',$time);
                $pop_num = M('spend','tab_')->field('count(distinct user_id) as num')->where($map)->find();
                $data[$key]['pop_num'] = $pop_num['num'];
            }
            //分页
            $time_map['time'] = array('between',array($start,$end));
            $count = M('date_list')->where($time_map)->count();
            if($count > $row){
                $page = new \Think\Page($count, $row);
                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                $this->assign('_page', $page->show());
            }
        }else{
            unset($_REQUEST['data_order']);
            if(count(I('get.'))!=0){
                $this->error('时间选择错误，请重新选择！');
            }
        }
        if($request['data_order']!=''){
            $data_order=reset(explode(',',$request['data_order']));
            $data_order_type=end(explode(',',$request['data_order']));
            $this->assign('userarpu_order',$data_order);
            $this->assign('userarpu_order_type',$data_order_type);
        }
        $data=my_sort($data,$data_order_type,(int)$data_order,SORT_STRING);
        $size=10;//每页显示的记录数
        $pnum = ceil(count($data) / $size); //总页数，ceil()函数用于求大于数字的最小整数
        //用array_slice(array,offset,length) 函数在数组中根据条件取出一段值;array(数组),offset(元素的开始位置),length(组的长度)
        $data = array_slice($data, ($arraypage-1)*$size, $size);
        $this->meta_title = 'ARPU统计';
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * 获取活跃用户数
     * @param $time
     */
    protected function count_act_user($time,$game_id="",$promote_id=""){
        $map["FROM_UNIXTIME(login_time,'%Y-%m-%d')"] = $time;
        $map1["FROM_UNIXTIME(register_time,'%Y-%m-%d')"]=$time;
//        empty($game_name) || $map['game_name'] = array('like','%'.$game_name.'%');
        empty($game_id) || $map['game_id'] = $game_id;
        empty($game_id) || $map1['fgame_id'] = $game_id;
        if(!empty($promote_id)){
            $user=M('User','tab_')->field('id')->where(array('promote_id'=>$promote_id))->select();
            $user=implode(',',array_column($user,'id'));
            $map['user_id']=array('in',$user);
            $map1['id']=array('in',$user);
        };
        $uudata=M('User','tab_')
                ->field('id')
                ->where($map1)
                ->select(false);
        $data = M('user_login_record','tab_')
                ->field('id')
                ->where($map)
                ->union($uudata)
                ->group('user_id')
                ->select();
        $data=count($data);
        return $data;
    }




//计算指定日期新用户数
    public function getnewcount($time)
    {
        $map = array();
        if (!empty($time)) {
            $map['register_time'] = get_start_end_time($_REQUEST['time-start']);
        } else {
            $map['register_time'] = -1;
        }
        if (isset($_REQUEST['promote_name'])) {
            if ($_REQUEST['promote_name'] == '全部') {
                unset($_REQUEST['promote_name']);
            } else if ($_REQUEST['promote_name'] == '自然注册') {
                $map['promote_id'] = array("elt", 0);

            } else {
                $map['promote_id'] = get_promote_id($_REQUEST['promote_name']);

            }
        } else {
            $map['promote_id'] = array('egt', 0);
        }
        if (isset($_REQUEST['game_name'])) {
            if ($_REQUEST['game_name'] == '全部') {
                unset($_REQUEST['game_name']);
            } else {
                $map['fgame_id'] = get_game_id($_REQUEST['game_name']);
            }
        } else {
            $map['fgame_id'] = array('gt', 0);
        }
        $r_user_id = M("User", "tab_")
            ->where($map)
            ->select();
        for ($i = 0; $i < count($r_user_id); $i++) {
            $sd[] = $r_user_id[$i]['id'];
        }
        $pid = implode(",", $sd);
        $count = M("User", "tab_")
            ->where($map)
            ->count();
        $count = array($pid, $count);
        return $count;

    }

//计算留存数
    public function getplaycount($day, $count, $n = null)
    {
        if (null !== $n) {
            $onetime['login_time'] = get_start_end_time($day, $n);
        } else {
            $onetime = get_last_day_time($day, "login_time");
        }
        if (isset($_REQUEST['promote_name'])) {
            if ($_REQUEST['promote_name'] == '全部') {
                unset($_REQUEST['promote_name']);
            } else if ($_REQUEST['promote_name'] == '自然注册') {
                $onetime['promote_id'] = array("elt", 0);
            } else {
                $onetime['promote_id'] = get_promote_id($_REQUEST['promote_name']);
            }
        } else {
            $onetime['promote_id'] = array('egt', 0);
        }
        if (isset($_REQUEST['game_name'])) {
            if ($_REQUEST['game_name'] == '全部') {
                unset($_REQUEST['game_name']);
            } else {
                $onetime['game_id'] = get_game_id($_REQUEST['game_name']);
            }
        } else {
            $onetime['game_id'] = array('gt', 0);
        }
        $onetime['user_id'] = array('in', (string)$count[0]);
        $onelogincount = M("user_login_record", "tab_")->where($onetime)->count('distinct user_id');
        return $onelogincount;
    }

//计算次日留存率
    function get_cilogin($newcount, $cicount)
    {
        if ($cicount == 0) {
            return sprintf("%.2f", 0) . '%';
        } else {
            return round($cicount / $newcount * 100) . '%';
        }
    }

//计算指定游戏 用户总数 与时间无关
    public function getallcount()
    {
        $map = array();
        if (!isset($_REQUEST['time-start'])) {
            $map['a.register_time'] = -1;
        }
        if (isset($_REQUEST['game_name'])) {
            if ($_REQUEST['game_name'] == '全部') {
                unset($_REQUEST['game_name']);
            } else {
                $map['a.fgame_id'] = get_game_id($_REQUEST['game_name']);
            }
        } else {
            $map['a.fgame_id'] = array('gt', 0);
        }
        if (isset($_REQUEST['promote_name'])) {
            if ($_REQUEST['promote_name'] == '全部') {
                unset($_REQUEST['promote_name']);
            } else if ($_REQUEST['promote_name'] == '自然注册') {
                $map['a.promote_id'] = array("elt", 0);
            } else {
                $map['a.promote_id'] = get_promote_id($_REQUEST['promote_name']);
            }
        } else {
            $map['a.promote_id'] = array('egt', 0);
        }
        $count = M("User as a", "tab_")
            ->field("count(*) as count")
            ->where($map)
            ->count();
        return $count;
    }

//计算付费用户数
    public function getpaycount()
    {
        $count = 0;
        if (isset($_REQUEST['time-start'])) {
            $map['pay_time'] = array("lt", strtotime($_REQUEST['time-start']) + (60 * 60 * 24));
        } else {
            $map['pay_time'] = -1;
        }
        $map['pay_status'] = 1;
        if (isset($_REQUEST['game_name'])) {
            if ($_REQUEST['game_name'] == '全部') {
                unset($_REQUEST['game_name']);
            } else {
                $map['game_id'] = get_game_id($_REQUEST['game_name']);
            }
        } else {
            $map['game_id'] = array('gt', 0);
        }
        if (isset($_REQUEST['promote_name'])) {
            if ($_REQUEST['promote_name'] == '全部') {
                unset($_REQUEST['promote_name']);
            } else if ($_REQUEST['promote_name'] == '自然注册') {
                $map['promote_id'] = array("elt", 0);
            } else {
                $map['promote_id'] = get_promote_id($_REQUEST['promote_name']);
            }
        } else {
            $map['promote_id'] = array('egt', 0);
        }
        $count = M("spend", "tab_")
            ->where($map)
            ->count('distinct id');
        return $count;
    }

//计算新用户付费金额
    public function getnewpaycount()
    {
        $count = 0;
        if (isset($_REQUEST['time-start'])) {
            $map['pay_time'] = array("lt", strtotime($_REQUEST['time-start']) + (60 * 60 * 24));
            $newuser = $this->getnewcount($_REQUEST['time-start']);
            $map['user_id'] = array('in', (string)$newuser[0]);
        } else {
            $map['pay_time'] = -1;
        }
        $map['pay_status'] = 1;
        if (isset($_REQUEST['game_name'])) {
            if ($_REQUEST['game_name'] == '全部') {
                unset($_REQUEST['game_name']);
            } else {
                $map['a.fgame_id'] = get_game_id($_REQUEST['game_name']);
            }
        } else {
            $map['a.fgame_id'] = array('gt', 0);
        }
        if (isset($_REQUEST['promote_name'])) {
            if ($_REQUEST['promote_name'] == '全部') {
                unset($_REQUEST['promote_name']);
            } else if ($_REQUEST['promote_name'] == '自然注册') {
                $map['a.promote_id'] = array("elt", 0);
            } else {
                $map['a.promote_id'] = get_promote_id($_REQUEST['promote_name']);
            }
        } else {
            $map['a.promote_id'] = array('egt', 0);
        }
        $list = M("User as a", "tab_")
            ->field("sum(pay_amount) as sum")
            ->join("tab_spend as c on c.game_id=a.fgame_id")
            ->where($map)
            ->group('a.id')
            ->find();
        if (!empty($list['sum'])) {
            $count = $list['sum'];
        }
        return sprintf("%.2f", $count);
    }

//计算总付费金额
    public function getallpaycount()
    {
        $count = 0;
        $map = array();
        if (!isset($_REQUEST['time-start'])) {
            $map['pay_time'] = -1;
        }
        $map['pay_status'] = 1;
        if (isset($_REQUEST['game_name'])) {
            if ($_REQUEST['game_name'] == '全部') {
                unset($_REQUEST['game_name']);
            } else {
                $map['game_id'] = get_game_id($_REQUEST['game_name']);
            }
        } else {
            $map['game_id'] = array('gt', 0);
        }
        if (isset($_REQUEST['promote_name'])) {
            if ($_REQUEST['promote_name'] == '全部') {
                unset($_REQUEST['promote_name']);
            } else if ($_REQUEST['promote_name'] == '自然注册') {
                $map['promote_id'] = array("elt", 0);
            } else {
                $map['promote_id'] = get_promote_id($_REQUEST['promote_name']);
            }
        } else {
            $map['promote_id'] = array('egt', 0);
        }
        $list = M("spend", "tab_")
            ->field("sum(pay_amount) as sum")
            ->where($map)
            ->find();
        if (!empty($list['sum'])) {
            $count = $list['sum'];
        }
        return sprintf("%.2f", $count);
    }

//计算总付费率
    public function getrate()
    {
        $pr = $this->getpaycount();//总付费人数
        $all = $this->getallcount();
        if ($all == 0) {
            $count = 0;
        } else {
            $count = $pr / $all;
            $count = $count > 1 ? 100 : $count * 100;
        }
        return sprintf("%.2f", $count) . '%';
    }

// 计算活跃用户数(当前日期所在一周的时间)
    public function gethuocount()
    {
        $count = 0;
        if (isset($_REQUEST['game_name']) || isset($_REQUEST['time-start'])) {
            if (isset($_REQUEST['game_name'])) {
                if ($_REQUEST['game_name'] == '全部') {
                    unset($_REQUEST['game_name']);
                } else {
                    $map['game_id'] = $_REQUEST['game_id'];
                    $time = date("Y-m-d", time());
                    $start = strtotime("$time - 6 days");
                    //周末
                    $end = strtotime("$time");
                    $map['login_time'] = array("between", array($start, $end));
                }
            }
            if (isset($_REQUEST['time-start'])) {
                $time2 = $_REQUEST['time-start'];
                $start2 = strtotime("$time2 - 6 days");
                //周末
                $end2 = strtotime("$time2");
                $map['login_time'] = array("between", array($start2, $end2));
            }
            $data = M("user_login_record", "tab_")
                ->group('user_id')
                ->having('count(user_id) > 2')
                ->where($map)
                ->select();
            $count = count($data);
        }
        return sprintf("%.2f", $count);
    }

//获取用户ARPU
    public function getuserarpu()
    {
        $new = $this->getnewpaycount();
        if (isset($_REQUEST['time-start'])) {
            $newcount = end($this->getnewcount($_REQUEST['time-start']));
            if ($newcount == 0) {
                $count = 0;
            } else {
                $count = $new / $newcount;
            }
        } else {
            $count = 0;
        }
        return sprintf("%.2f", $count);

    }

// 获取活跃ARPU
    public function gethuoarpu()
    {
        if (isset($_REQUEST['game_name']) || isset($_REQUEST['time-start'])) {
            if (isset($_REQUEST['game_name'])) {
                if ($_REQUEST['game_name'] == '全部') {
                    unset($_REQUEST['game_name']);
                } else {
                    $map['tab_user_login_record.game_id'] = get_game_id($_REQUEST['game_name']);
                    $time = date("Y-m-d", time());
                    $start = strtotime("$time - 6 days");
                    //周末
                    $end = strtotime("$time");
                    $map['login_time'] = array("between", array($start, $end));
                }
            }
            if (isset($_REQUEST['time-start'])) {
                $time2 = $_REQUEST['time-start'];
                $start2 = strtotime("$time2 - 6 days");
                //周末
                $end2 = strtotime("$time2");
                $map['login_time'] = array("between", array($start2, $end2));
            }
            $data = M("user_login_record", "tab_")
                ->group('user_id')
                ->having('count(user_id) > 2')
                ->where($map)
                ->select();
            foreach ($data as $key => $value) {
                $data1[] = $value['user_id'];
            }
            foreach ($data1 as $value) {
                $user_account[] = get_user_account($value);
            }
            $pid = implode(',', $user_account);
        }
        $map['user_account'] = array('in', $pid);
        if ($pid != '') {
            $huosum = M("spend ", "tab_")
                ->distinct(true)
                ->field("pay_amount")
                ->join("tab_user_login_record on tab_spend.game_id = tab_user_login_record.game_id")
                ->where($map)
                ->select();
            foreach ($huosum as $value) {
                $huosum2[] = $value['pay_amount'];
            }
            $sum = array_sum($huosum2);
            // if($pid!=null&&$huosum/!='')
            $count = count($data);
            $return = $sum / $count;
        } else {
            $return = 0;
        }

        return $return;

    }

//获取付费ARPU
    public function getpayarpu()
    {
        $paysum = $this->getallpaycount();//所有用户付费
        $paycount = $this->getpaycount();//新用户付费
        if ($paycount != 0) {
            $count = $paysum / $paycount;
        } else {
            $count = 0;
        }
        return sprintf("%.2f", $count);
    }
    public function cha_userarpu($p=0){
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据arraypage
        $arraypage = $page ? $page : 1; //默认显示第一页数据
        $row = 10;
        $time = $_REQUEST['time'];
        $promote_id = $_REQUEST['promote_id'];
        $join = "left join tab_user u on u.fgame_id = tab_game.id";
        if($time==''){
            $this->error('参数错误，缺少时间');
        }else{
            $map['register_time']=array('between',array(strtotime($time),strtotime($time)+24*60*60-1));
        }
        if($promote_id!=''){
            $map_list['promote_id']=$promote_id;
            $map['promote_id']=$promote_id;
            $join .= " AND u.promote_id = {$promote_id}";
        }        
        $data=M('Game','tab_')->field('id as game_id, game_name')->order('id desc')->select();
        foreach ($data as $key => $value) {
            $game_id = $value['game_id'];
            $map_list['game_id']=$game_id;
            $user=M('User','tab_');
            $spend=M('spend','tab_');
            //新增人数
            $rdata=$user
                    ->field('count(id) as register_num')
                    ->where(array('fgame_id'=>$game_id))
                    ->where($map)
                    ->find();
            $data[$key]['register_num']=$rdata['register_num'];
            //活跃玩家
            $data[$key]['act_user'] = $this->count_act_user($time,$game_id,$promote_id);
            //1日留存
            $mapl=$map_list;
            $mapl["FROM_UNIXTIME(register_time,'%Y-%m-%d')"] = $time;
            $mapl['tab_user.promote_id']=$mapl['promote_id'];
            unset($mapl['promote_id']);
            $login_time = date('Y-m-d', strtotime("+1 day",strtotime($time)));
            $num = $user
                ->field('count(DISTINCT tab_user.id) as num')
                ->join("right join tab_user_login_record as ur on ur.user_id = tab_user.id and FROM_UNIXTIME(ur.login_time,'%Y-%m-%d') = '{$login_time}'")
                ->where($mapl)
                ->find();
            $data[$key]['keep_num'] = round($num['num']/$data[$key]['register_num'],4)*100;
            //充值
            $mapl = $map_list;
            empty($game_name ) || $mapl['game_name'] = array('like','%'.$game_name.'%');
            empty($promote_id) || $mapl['promote_id'] = $promote_id;
            $mapl['pay_status'] = 1;
            $mapl["FROM_UNIXTIME(pay_time,'%Y-%m-%d')"] = $time;
            $spend = $spend->field("IFNULL(sum(pay_amount),0) as money,IFNULL(count(distinct user_id),0) as people")->where($mapl)->find();
            $data[$key]['spend'] = $spend['money'];
            //付费玩家数
            $data[$key]['spend_people'] = $spend['people'];
            //新付费玩家
            // $mapl = $map_list;
            // $mapl['pay_status'] = 1;
            // $sql = M('spend','tab_')->field("user_id,min(pay_time) as time")->group('user_id')->where($mapl)->select(false);
            // $sql = "select IFNULL(count(user_id),0) as num from {$sql} as t WHERE  FROM_UNIXTIME(t.time,'%Y-%m-%d') = '{$time}'";
            // $query = M()->query($sql);
            // $data[$key]['new_pop'] = $query[0]['num'];
            //付费率
            $data[$key]['spend_rate'] = round($data[$key]['spend_people']/$data[$key]['act_user'],4)*100;
            //ARPU
            $data[$key]['ARPU'] = round($data[$key]['spend']/$data[$key]['act_user'],2);
            //ARPPU
            $data[$key]['ARPPU'] = round($data[$key]['spend']/$data[$key]['spend_people'],2);
            if($data[$key]['register_num']==0&&$data[$key]['act_user']==0&&$data[$key]['keep_num']==0&&$data[$key]['spend']==0&&$data[$key]['spend_people']==0){
                unset($data[$key]);
            }
            // //累计付费玩家
            // $map = $map_list;
            // $map['pay_status'] = 1;
            // $map["FROM_UNIXTIME(pay_time,'%Y-%m-%d')"] = array('lt',$time);
            // $pop_num = M('spend','tab_')->field('count(distinct user_id) as num')->where($map)->find();
            // $data[$key]['pop_num'] = $pop_num['num'];
        }
        $count=count($data);
        if($count > $row){
                $page = new \Think\Page($count, $row);
                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                $this->assign('_page', $page->show());
            }
        $size=$row;//每页显示的记录数
        $pnum = ceil(count($data) / $size); //总页数，ceil()函数用于求大于数字的最小整数
        //用array_slice(array,offset,length) 函数在数组中根据条件取出一段值;array(数组),offset(元素的开始位置),length(组的长度)
        $data = array_slice($data, ($arraypage-1)*$size, $size);
        $this->assign('list_data',$data);
        $this->display();
    }
    function game_analysis(){
        if($_REQUEST['time-start']!=''&&$_REQUEST['time-end']!=''){
            $start=$_REQUEST['time-start'];
            $end=$_REQUEST['time-end'];
        }else{
            $start=get_lastweek_name(6);
            $end=date('Y-m-d');
        }
        $umap['register_time']=array('BETWEEN',array(strtotime($start),strtotime($end)+24*60*60-1));
        $smap['pay_time']=array('BETWEEN',array(strtotime($start),strtotime($end)+24*60*60-1));
        if($_REQUEST['promote_id']!=''){
            $umap['promote_id']=$_REQUEST['promote_id'];
            $smap['promote_id']=$_REQUEST['promote_id'];
        }
        $data=M('Game','tab_')->field('id as game_id, game_name')->order('id desc')->select();
        foreach ($data as $key => $value) {
            $umap['fgame_id']=$value['game_id'];
            $smap['game_id']=$value['game_id'];
            $udata=M('User','tab_')
            ->field('count(id) as register_num')
            ->where($umap)
            ->find();
            $data[$key]['count']=$udata['register_num'];
            
            $smap['pay_status']=1;
            $sdata=M('Spend','tab_')
                ->field('ifnull(sum(pay_amount),0) as sum')
                ->where($smap)
                ->find();
            $data[$key]['sum']=$sdata['sum'];
        }
        if($_REQUEST['data_order']==2){
            $data_order_type='sum';
            $data_order=3;//倒序
        }else{
            $data_order_type='count';
            $data_order=3;
        }
        $data=my_sort($data,$data_order_type,(int)$data_order);
        $data = array_slice($data, 0, 12);
        // 前台显示
        // X轴游戏
            $xAxis="[";
            foreach ($data as $tk => $tv) {
                $xAxis.="'".$tv['game_name']."',";
            }
            $xAxis.="]";
        //x轴注册数据
        $xzdate="[";
        foreach ($data as $key => $value) {
            $xzdate.="'".$value['count']."',";
        }
        $xzdate.="]";
        //x轴充值数据
        $xsdate="[";
        foreach ($data as $key => $value) {
            $xsdate.="'".$value['sum']."',";
        }
        $xsdate.="]";
        $this->meta_title = '游戏分析';
        $this->assign('xzdate',$xzdate);
        $this->assign('xsdate',$xsdate);
        $this->assign('xAxis',$xAxis);
        $this->assign('start',$start);
        $this->assign('end',$end);
        $this->assign('data',$data);
        $this->display();
    }
    function promote_analysis(){
        if($_REQUEST['time-start']!=''&&$_REQUEST['time-end']!=''){
            $start=$_REQUEST['time-start'];
            $end=$_REQUEST['time-end'];
        }else{
            $start=get_lastweek_name(6);
            $end=date('Y-m-d');
        }
        $umap['register_time']=array('BETWEEN',array(strtotime($start),strtotime($end)+24*60*60-1));
        $smap['pay_time']=array('BETWEEN',array(strtotime($start),strtotime($end)+24*60*60-1));
        if($_REQUEST['game_id']!=''){
            $umap['game_id']=$_REQUEST['game_id'];
            $smap['game_id']=$_REQUEST['game_id'];
        }
        $data=M('Promote','tab_')->field('id as promote_id,account as promote_name')->order('id desc')->select();
        foreach ($data as $key => $value) {
            $umap['promote_id']=$value['promote_id'];
            $smap['promote_id']=$value['promote_id'];
            $udata=M('User','tab_')
            ->field('count(id) as register_num')
            ->where($umap)
            ->find();
            $data[$key]['count']=$udata['register_num'];
            
            $smap['pay_status']=1;
            $sdata=M('Spend','tab_')
                ->field('ifnull(sum(pay_amount),0) as sum')
                ->where($smap)
                ->find();
            $data[$key]['sum']=$sdata['sum'];
        }
        if($_REQUEST['data_order']==2){
            $data_order_type='sum';
            $data_order=3;//倒序
        }else{
            $data_order_type='count';
            $data_order=3;
        }
        $data=my_sort($data,$data_order_type,(int)$data_order);
        $data = array_slice($data, 0, 12);
        // 前台显示
        // X轴游戏
            $xAxis="[";
            foreach ($data as $tk => $tv) {
                $xAxis.="'".$tv['promote_name']."',";
            }
            $xAxis.="]";
        //x轴注册数据
        $xzdate="[";
        foreach ($data as $key => $value) {
            $xzdate.="'".$value['count']."',";
        }
        $xzdate.="]";
        //x轴充值数据
        $xsdate="[";
        foreach ($data as $key => $value) {
            $xsdate.="'".$value['sum']."',";
        }
        $xsdate.="]";
        $this->meta_title = '渠道分析';
        $this->assign('xzdate',$xzdate);
        $this->assign('xsdate',$xsdate);
        $this->assign('xAxis',$xAxis);
        $this->assign('start',$start);
        $this->assign('end',$end);
        $this->assign('data',$data);
        $this->display();
    }
}
