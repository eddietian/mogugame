<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class StatisticsController extends ThinkController {
	// const model_name = 'Spend';
    public function overview(){
        $shuju = M('Data','tab_')->order('create_time desc')->find();
        $this->assign('shuju',$shuju);

        $this->assign('openegretmain','openegretmain');//模板还样式使用
        $this->meta_title = '总览';
       /* //定义表名
        $user = M("User","tab_");
        $userlogin = M("user_login_record","tab_");
        $game = M("Game","tab_");
        $spend = M('Spend',"tab_");
        $deposit = M('Deposit',"tab_");
        $promote = M("Promote","tab_");
        $game = M("Game","tab_");
        $gamesource = M("Game_source","tab_");

        //平台数据概况
        $platform_data['all_user']=$user->count();//累计注册玩家人数


        //全部平台币充值
        $adfufei=$deposit
                ->field('user_id')
                ->where(array('pay_status'=>1))
                ->group('user_id')
                ->select(false);
        //两表并集 
        $afufei=$spend
                ->field('user_id')
                ->union($adfufei)
                ->where(array('pay_status'=>1))
                ->group('user_id')
                ->select();
        $platform_data['all_pay_user']=count($afufei);//累计付费玩家人数包括平台币

        $spay=$spend->where(array('pay_status'=>1))->sum('pay_amount');
        //$dpay=$deposit->where(array('pay_status'=>1))->sum('pay_amount');

        $platform_data['all_pay']=$this->huanwei($spay);//累计流水

        $platform_data['all_promote']=$promote->where(array('status'=>1))->count();//累计渠道

        $platform_data['all_game']=$game->where(['apply_status'=>1])->count();//累计游戏

        $platform_data['all_android']=$gamesource->where(array('file_type'=>1))->count();//累计安卓包

        $platform_data['all_ios']=$gamesource->where(array('file_type'=>2))->count();//累计苹果包

        $platform_data['all_tuser']=$user->where(array('promote_id'=>array('gt',0)))->count();//累计渠道注册玩家
        
        $tspay=$spend->where(array('promote_id'=>array('gt',0)))->where(array('pay_status'=>1))->sum('pay_amount');//累计渠道充值玩家
        //$dspay =$deposit->where(array('promote_id'=>array('gt',0)))->where(array('pay_status'=>1))->sum('pay_amount');
        $platform_data['all_tpay']=$this->huanwei($tspay);//累计渠道总流水

        $this->assign('platform_data',$platform_data);

        //实时数据概况
        $today = $this->total(1);
        $thisweek = $this->total(2);
        $thismounth = $this->total(3);
        //注册
        $realtime_data['today_user']=$user->where(array('register_time'.$today))->count();//今日注册

        $realtime_data['thisweek_user']=$user->where(array('register_time'.$thisweek))->count();//本周注册

        $realtime_data['thismounth_user']=$user->where(array('register_time'.$thismounth))->count();//本月注册

        //活跃
      
        //今日活跃
        $turel=$user
            ->field('id as user_id')
            ->where('register_time'.$today)
            ->select(false);
        $tlogin=$userlogin
            ->field('user_id')
            ->where("login_time".$today)
            ->union($turel)
            ->group('user_id')
            ->select();
        //本周活跃
        $wurel=$user
            ->field('id as user_id')
            ->where('register_time'.$thisweek)
            ->select(false);
        $wlogin=$userlogin
            ->field('user_id')
            ->where("login_time".$thisweek)
            ->union($wurel)
            ->group('user_id')
            ->select();
        //本月活跃
        $murel=$user
            ->field('id as user_id')
            ->where('register_time'.$thismounth)
            ->select(false);
        $mlogin=$userlogin
            ->field('user_id')
            ->where("login_time".$thismounth)
            ->union($murel)
            ->group('user_id')
            ->select();
        $realtime_data['today_active']=count($tlogin);
        $realtime_data['thisweek_active']=count($wlogin);
        $realtime_data['thismounth_active']=count($mlogin);
        //充值
        //今日流水
        $todayspay=$spend->where(array('pay_time'.$today))->where(array('pay_status'=>1))->sum('pay_amount');
        //$todaydpay=$deposit->where(array('create_time'.$today))->where(array('pay_status'=>1))->sum('pay_amount');
        //$realtime_data['today_pay']=$this->huanwei($todayspay+$todaydpay);
        $realtime_data['today_pay']=$this->huanwei($todayspay);
        //本周流水
        $weekspay=$spend->where(array('pay_time'.$thisweek))->where(array('pay_status'=>1))->sum('pay_amount');
        //$weekdpay=$deposit->where(array('create_time'.$thisweek))->where(array('pay_status'=>1))->sum('pay_amount');
        $realtime_data['thisweek_pay']=$this->huanwei($weekspay);
        //本月流水
        $mounthspay=$spend->where(array('pay_time'.$thismounth))->where(array('pay_status'=>1))->sum('pay_amount');
        //$mounthdpay=$deposit->where(array('create_time'.$thismounth))->where(array('pay_status'=>1))->sum('pay_amount');
        $realtime_data['thismounth_pay']=$this->huanwei($mounthspay);
        $this->assign('realtime_data',$realtime_data);*/

        //实时数据概况
        $today = $this->total(1);
        $thisweek = $this->total(2);
        $thismounth = $this->total(3);
        //排行
        $yesterday=$this->total(5);
        $lastweek=$this->total(6);
        $lastmounth=$this->total(7);
        $type=$_REQUEST['type'];
        if($type==1 || $type==''){
            $list_data=$this->data_order($today,$yesterday);
        }elseif($type==2){
            $list_data=$this->data_order($thisweek,$lastweek);
        }elseif($type==3){
            $list_data=$this->data_order($thismounth,$lastmounth);
        }
        $this->assign('zhuce',$list_data['zhuce']);
        $this->assign('active',$list_data['active']);
        $this->assign('pay',$list_data['pay']);
    	$this->display();
    }
    private function data_order($nowtime,$othertime){
        $user = M("User","tab_");
        $spend = M('Spend',"tab_");
        //今日注册排行
        $ri_ug_order=$user->field('fgame_id,fgame_name,count(tab_user.id) as cg')->where(array('register_time'.$nowtime))->where(array('fgame_id'=>array('gt',0)))->group('fgame_id')->order('cg desc')->limit(10)->select();
        $ri_ug_order=array_order($ri_ug_order);

        $yes_ug_order=$user->field('fgame_id,fgame_name,count(tab_user.id) as cg')->where(array('register_time'.$othertime))->where(array('fgame_id'=>array('gt',0)))->group('fgame_id')->order('cg desc')->select();
        $yes_ug_order=array_order($yes_ug_order);
        foreach ($ri_ug_order as $key => $value) {
            $ri_ug_order[$key]['change']=$value['rand'];
            foreach ($yes_ug_order as $k => $v) {
                if($value['fgame_id']==$v['fgame_id']){
                    $ri_ug_order[$key]['change']=$value['rand']-$v['rand'];
                }else{
                    $ri_ug_order[$key]['change']=$value['rand'];
                }
            }
        }
        // //今日活跃排行
        $ri_active_order=$user->field('game_id,game_name,count(tab_user.id) as cg')->join('tab_user_login_record as uu on tab_user.id = uu.user_id')->where(array('uu.login_time'.$nowtime))->where(array('game_id'=>array('gt',0)))->group('game_id')->order('cg desc')->limit(10)->select();
        $ri_active_order=array_order($ri_active_order);

        $yes_active=$user->field('game_id,game_name,count(tab_user.id) as cg')->join('tab_user_login_record as uu on tab_user.id = uu.user_id')->where(array('uu.login_time'.$othertime))->where(array('game_id'=>array('gt',0)))->group('game_id')->order('cg desc')->select();
        $yes_active=array_order($yes_active);
        foreach ($ri_active_order as $key => $value) {
            $ri_active_order[$key]['change']=$value['rand'];
            foreach ($yes_active as $k => $v) {
                if($value['game_id']==$v['game_id']){
                    $ri_active_order[$key]['change']=$value['rand']-$v['rand'];
                }else{
                    $ri_active_order[$key]['change']=$value['rand'];
                }
            }
        }

        // //充值排行
        //spend
        $ri_spay_order=$spend->field('game_id,game_name,sum(pay_amount) as cg')->where(array('pay_time'.$nowtime))->where(array('game_id'=>array('gt',0)))->where(array('pay_status'=>1))->group('game_id')->order('cg desc')->limit(10)->select();
        $ri_spay_order=array_order($ri_spay_order);

        $yes_spay=$spend->field('game_id,game_name,sum(pay_amount) as cg')->where(array('pay_status'=>1))->where(array('pay_time'.$othertime))->where(array('game_id'=>array('gt',0)))->group('game_id')->order('cg desc')->select();
        $yes_spay=array_order($yes_spay);
        foreach ($ri_spay_order as $key => $value) {
            $ri_spay_order[$key]['change']=$value['rand'];
            foreach ($yes_spay as $k => $v) {
                if($value['game_id']==$v['game_id']){
                    $ri_spay_order[$key]['change']=$value['rand']-$v['rand'];
                }
            }
        }
        $data['zhuce']=$ri_ug_order;
        $data['active']=$ri_active_order;
        $data['pay']=$ri_spay_order;
        return $data;
    }

    public function zhexian(){
        $day=$this->every_day(7);
        $time=$this->total(9);
        $key=$_REQUEST['key'];
        $user = M("User","tab_");
        $spend = M('Spend',"tab_");
        $deposit = M('Deposit','tab_');
        if($key==1){
            //注册数据
            $data=$user->field('fgame_id,fgame_name,date_format(FROM_UNIXTIME( `register_time`),"%Y-%m-%d") AS time,count(id) as cg')->where(array('register_time'.$time))->where(array('fgame_id'=>array('gt',0)))->group('time,fgame_id')->order('cg desc')->select();
            $title=$user->field('fgame_name,count(id) as cg')->where(array('register_time'.$time))->where(array('fgame_id'=>array('gt',0)))->group('fgame_id')->order('cg desc')->select();
            $title=array_column($title,'fgame_name');
            $data=array_group_by($data,'time');
            foreach ($day as $key => $value) {
                if(array_key_exists($value, $data)){
                    foreach ($data[$value] as $kk => $vv) {
                        $game_name=$vv['fgame_name'];
                        $dayy[$value][$game_name]=$vv['cg'];
                    }
                }
            }
        }elseif($key==2){
            //活跃数据
            $data=$user->field('game_id,game_name,date_format(FROM_UNIXTIME( uu.login_time),"%Y-%m-%d") AS time,count(tab_user.id) as cg')->join('tab_user_login_record as uu on tab_user.id = uu.user_id')->where(array('uu.login_time'.$time))->where(array('game_id'=>array('gt',0)))->group('time,game_id')->order('cg desc')->select();
            $title=$user->field('game_name,count(tab_user.id) as cg')->join('tab_user_login_record as uu on tab_user.id = uu.user_id')->where(array('uu.login_time'.$time))->where(array('game_id'=>array('gt',0)))->group('game_id')->order('cg desc')->select();
            $title=array_column($title,'game_name');
            $data=array_group_by($data,'time');
            foreach ($day as $key => $value) {
                if(array_key_exists($value, $data)){
                    foreach ($data[$value] as $kk => $vv) {
                        $game_name=$vv['game_name'];
                        $dayy[$value][$game_name]=$vv['cg'];
                    }
                }
            }
        }elseif($key==3){
            //充值数据
            $data=$spend->field('game_id,game_name,date_format(FROM_UNIXTIME( pay_time),"%Y-%m-%d") AS time,sum(pay_amount) as cg')->where(array('pay_time'.$time))->where(array('game_id'=>array('gt',0)))->where(array('pay_status'=>1))->group('time,game_id')->order('cg desc')->select();
            $title=$spend->field('game_name,sum(pay_amount) as cg')->where(array('pay_time'.$time))->where(array('game_id'=>array('gt',0)))->where(array('pay_status'=>1))->group('game_id')->order('cg desc')->select();
            $title=array_column($title,'game_name');
            $data=array_group_by($data,'time');
            foreach ($day as $key => $value) {
                if(array_key_exists($value, $data)){
                    foreach ($data[$value] as $kk => $vv) {
                        $game_name=$vv['game_name'];
                        $dayy[$value][$game_name]=$vv['cg'];
                    }
                }
            }
        }
        $this->assign('day0',$day[0]);
        $this->assign('day1',$day[1]);
        $this->assign('day2',$day[2]);
        $this->assign('day3',$day[3]);
        $this->assign('day4',$day[4]);
        $this->assign('day5',$day[5]);
        $this->assign('day6',$day[6]);
        $this->assign('dayy',$dayy);
        $this->assign('title1',$title[1]);
        $this->assign('title0',$title[0]);
        $this->assign('title2',$title[2]);
        $this->assign('title3',$title[3]);
        $this->assign('title4',$title[4]);
        $this->display();
    }

    //数据概况
    public function data_profile(){
        $keytype=$_REQUEST['key']==""?1:$_REQUEST['key'];
        $user=M('User','tab_');
        $spend=M('Spend','tab_');
        $deposit= M('Deposit','tab_');
        if($keytype==1){
            $time=$this->time2other();
            $tt=$this->total(1);
            //注册数据
            $udata=$user->field('date_format(FROM_UNIXTIME( register_time),"%H") AS time,count(id) as count')->where('register_time'.$tt)->group('time')->select();
            $xtime=$this->for_every_time_point($time,$udata,'time','count');

            //充值数据
            //spend
            $sdata=$spend->field('date_format(FROM_UNIXTIME( pay_time),"%H") AS time,sum(pay_amount) as sum')->where('pay_time'.$tt)->where(array('pay_status'=>1))->group('time')->select();
            $xstime=$this->for_every_time_point($time,$sdata,'time','sum');
            foreach ($xstime as $key => $value) {
                $stime[$key]['sum']=$value['sum'];
            }
        }elseif($keytype==2){//7天
            $time=$this->time2other('7day');
            $tt=$this->total(9);
            //注册数据
            $udata=$user->field('date_format(FROM_UNIXTIME( `register_time`),"%Y-%m-%d") AS time,count(id) as count')->where(array('register_time'.$tt))->group('time')->order('time asc')->select();
            $xtime=$this->for_every_time_point($time,$udata,'time','count');

            //充值数据
            //spend
            $sdata=$spend->field('date_format(FROM_UNIXTIME( pay_time),"%Y-%m-%d") AS time,sum(pay_amount) as sum')->where(array('pay_time'.$tt))->where(array('game_id'=>array('gt',0)))->where(array('pay_status'=>1))->group('time')->order('time asc')->select();
            $xstime=$this->for_every_time_point($time,$sdata,'time','sum');

            foreach ($xstime as $key => $value) {
                $stime[$key]['sum']=$value['sum'];
            }        
        }elseif($keytype==3){//30天
            $time=$this->time2other('30day');
            // var_dump($time);exit;
            $tt=$this->total(10);
            //注册数据
            $udata=$user->field('date_format(FROM_UNIXTIME( `register_time`),"%Y-%m-%d") AS time,count(id) as count')->where(array('register_time'.$tt))->group('time')->order('time asc')->select();
            $xtime=$this->for_every_time_point($time,$udata,'time','count');

            //充值数据
            //spend
            $sdata=$spend->field('date_format(FROM_UNIXTIME( pay_time),"%Y-%m-%d") AS time,sum(pay_amount) as sum')->where(array('pay_time'.$tt))->where(array('game_id'=>array('gt',0)))->where(array('pay_status'=>1))->group('time')->order('time asc')->select();
            $xstime=$this->for_every_time_point($time,$sdata,'time','sum');

            foreach ($xstime as $key => $value) {
                $stime[$key]['sum']=$value['sum'];
            }       
        }elseif($keytype==4){//1年
            $time=$this->time2other('12mounth');
            $tt=$this->total(8);
            //注册数据
            $udata=$user->field('date_format(FROM_UNIXTIME( `register_time`),"%Y-%m") AS time,count(id) as count')->where(array('register_time'.$tt))->group('time')->order('time asc')->select();
            $xtime=$this->for_every_time_point($time,$udata,'time','count');

            //充值数据
            //spend
            $sdata=$spend->field('date_format(FROM_UNIXTIME( pay_time),"%Y-%m") AS time,sum(pay_amount) as sum')->where(array('pay_time'.$tt))->where(array('game_id'=>array('gt',0)))->where(array('pay_status'=>1))->group('time')->order('time asc')->select();
            $xstime=$this->for_every_time_point($time,$sdata,'time','sum');
            foreach ($xstime as $key => $value) {
                $stime[$key]['sum']=$value['sum'];
            }
        }
        // 前台显示
        // X轴日期
        if($keytype==1){
            $xAxis="[";
            foreach ($time as $tk => $tv) {
                $xAxis.="'".$tk.":00',";
            }
            $xAxis.="]";
        }elseif($keytype==2){
            sort($time);
            $xAxis="[";
            foreach ($time as $tk => $tv) {
                $xAxis.="'".$tv."',";
            }
            $xAxis.="]";
        }elseif($keytype==3){
            sort($time);
            $xAxis="[";
            foreach ($time as $tk => $tv) {
                $xAxis.="'".$tv."',";
            }
            $xAxis.="]";
        }elseif($keytype==4){
            sort($time);
            $xAxis="[";
            foreach ($time as $tk => $tv) {
                $xAxis.="'".$tv."',";
            }
            $xAxis.="]";
        }
        //x轴注册数据
        $xzdate="[";
        foreach ($xtime as $key => $value) {
            $xzdate.="'".$value['count']."',";
        }
        $xzdate.="]";
        //x轴充值数据
        $xsdate="[";
        foreach ($stime as $key => $value) {
            $xsdate.="'".$value['sum']."',";
        }
        $xsdate.="]";
        $this->assign('xzdate',$xzdate);
        $this->assign('xsdate',$xsdate);
        $this->assign('xAxis',$xAxis);
        $this->assign('qingxie',count($time));
        $this->meta_title = '数据概况';
        $this->display();
    }
    /**
     * [数据折线 分配每个时间段]
     * @param  [type] $time [时间点]
     * @return [type]       [description]
     */
    private function for_every_time_point($time,$data,$key1,$key2){
        foreach ($time as $key => $value) {
            $newdata[$key][$key2]=0;
            foreach ($data as $k => $v) {
                if($v[$key1]==$key){
                    $newdata[$key][$key2]=$v[$key2];
                }
            }
        }
        return $newdata;
    }
    //把时间戳 当前时间一天分成24小时  前七天 前30天  前12个月
    function time2other($type='day'){
        if($type=='day'){//一天分成24小时
            $start = mktime(0,0,0,date("m"),date("d"),date("y"));
            for($i = 0; $i < 24; $i++){
                static $x=0;
                $xx=$x++;
                if($xx<10){
                    $xxx='0'.$xx;
                }else{
                    $xxx=$xx;
                }
                
                $b = $start + ($i * 3600);
                $e = $start + (($i+1) * 3600)-1;
                $time[$xxx]="between $b and $e";
            }
        }
        if($type=='7day'){
            $ttime=array_reverse($this->every_day());
            foreach ($ttime as $key => $value) {
                $time[$value]=$value;
            }
        }
        if($type=='30day'){
            $ttime=array_reverse($this->every_day(30));
            foreach ($ttime as $key => $value) {
                $time[$value]=$value;
            }
        }
        if($type=='12mounth'){
            $ttime=array_reverse(before_mounth());
            foreach ($ttime as $key => $value) {
                $time[$value]=$value;
            }
        }
        
        return $time;
    }
    //以当前日期 默认前七天 
    private function every_day($m=7){
        $time=array();
        for ($i=0; $i <$m ; $i++) { 
            $time[]=date('Y-m-d',mktime(0,0,0,date('m'),date('d')-$i,date('Y')));
        }
        return $time;
    }
    private function total($type) {
        switch ($type) {
            case 1: { // 今天
                $start=mktime(0,0,0,date('m'),date('d'),date('Y'));
                $end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            };break;
             case 2: { // 本周
            //当前日期
            $sdefaultDate = date("Y-m-d");
            //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
            $first=1;
            //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
            $w=date('w',strtotime($sdefaultDate));
            //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
            $week_start=date('Y-m-d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
            //本周结束日期
            $week_end=date('Y-m-d',strtotime("$week_start +6 days"));
                        //当前日期
            $sdefaultDate = date("Y-m-d");
            //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
            $first=1;
            //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
            $w=date('w',strtotime($sdefaultDate));
            //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
            $start=strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days');
            //本周结束日期
            $end=$start+7*24*60*60-1;
        };break;
            case 3: { // 本月
                $start=mktime(0,0,0,date('m'),1,date('Y'));
                $end=mktime(0,0,0,date('m')+1,1,date('Y'))-1;
            };break;
            case 4: { // 本年
                $start=mktime(0,0,0,1,1,date('Y'));
                $end=mktime(0,0,0,1,1,date('Y')+1)-1;
            };break;
            case 5: { // 昨天
                $start=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
                $end=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
            };break;
            case 6: { // 上周
                $start=mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y"));
                $end=mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y"));
            };break;
            case 7: { // 上月
                $start=mktime(0, 0 , 0,date("m")-1,1,date("Y"));
                $end=mktime(23,59,59,date("m") ,0,date("Y"));
            };break;
            case 8: { // 上一年
                $start=mktime(0,0,0,date('m')-11,1,date('Y'));
                $end=mktime(0,0,0,date('m')+1,1,date('Y'))-1;
            };break;
            case 9: { // 前七天
                $start = mktime(0,0,0,date('m'),date('d')-6,date('Y'));
                $end=mktime(23,59,59,date('m'),date('d'),date('Y'));
            };break;
            case 10: { // 前30天
                $start = mktime(0,0,0,date('m'),date('d')-29,date('Y'));
                $end=mktime(23,59,59,date('m'),date('d'),date('Y'));
            };break;
            default:
                $start='';$end='';
        }
        return " between $start and $end ";
    }
    private function huanwei($total) {
        $total = empty($total)?'0':trim($total.' ');
        if(!strstr($total,'.')){
            $total=$total.'.00';
        }
        $len = strlen($total);
        if ($len>7) { // 万
            $total = (round(($total/10000),2)).'w';
        }
        return $total;
    }
}
