<?php
/**
 * 定时自动完成
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/22
 * Time: 11:40
 */
namespace Admin\Controller;
use Admin\Model\SpendModel;
use Think\Think;

class AutoController extends Think {

    public function ttt(){
        $a = get_promote_list(111);
        var_dump($a);
    }
    public function t(){
       var_dump(zimu26());
    }

    public function run(){
        //自动补单
        $repair = new SpendModel();
        $repair::auto_repair();
    }

    public function stat(){
        $info = $this->sitestat();
        $stat = $this->statistics();
//        $rank = $this->ranktop();
//        $zhexian = $this->zhexiantop();

        //玩家注册
        $data['player_regist_yes'] = $info['yesterday'];
        $data['player_regist_tod'] = $info['today'];
        $data['player_regist_week'] = $stat['realtime_data']['thisweek_user'];
        $data['player_reigst_mon'] = $stat['realtime_data']['thismounth_user'];
        $data['player_regist_all'] = $info['user'];
        //玩家活跃
        $data['act_yes'] = $info['ylogin'];
        $data['act_tod'] = $info['tlogin'];
        $data['act_seven'] = $info['ulogin'];
        $data['act_week'] = $stat['realtime_data']['thisweek_active'];
        $data['act_mon'] = $stat['realtime_data']['thismounth_active'];
        //充值人数
        $data['payer_yes'] = $info['yfufei'];
        $data['payer_tod'] = $info['tfufei'];
        $data['payer_all'] = $info['afufei'];
        //充值
        $data['pay_add_yes'] = $info['ysamount'];
        $data['pay_add_tod'] = $info['tsamount'];
        $data['pay_add_all'] = $info['asamount'];
        //流水
        $data['pay_tod'] = $stat['realtime_data']['today_pay'];
        $data['pay_week'] = $stat['realtime_data']['thisweek_pay'];
        $data['pay_mon'] = $stat['realtime_data']['thismounth_pay'];
        $data['pay_all'] = $stat['platform_data']['all_pay'];
        //游戏接入数量
        $data['game_add_yes'] = $info['yadd'];
        $data['game_add_tod'] = $info['tadd'];
        $data['game_add_all'] = $info['game'];
        $data['game_and_all'] = $stat['platform_data']['all_android'];
        $data['game_ios_all'] = $stat['platform_data']['all_ios'];
        //渠道增加数量
        $data['pro_add_yes'] = $info['ypadd'];
        $data['pro_add_tod'] = $info['tpadd'];
        $data['pro_add_all'] = $info['promote'];
        $data['pro_complete'] = $stat['platform_data']['all_promote'];
        //渠道支付总数
        $data['pro_pay_all'] = $stat['platform_data']['all_tpay'];
        //渠道玩家总数
        $data['pro_player_all'] = $stat['platform_data']['all_tuser'];

        //首页七天流水 七天注册统计
        $data['seven_pay_min'] = $info['pay']['min'];
        $data['seven_pay_max'] = $info['pay']['max'];
        $data['seven_pay_data'] = $info['pay']['data'];
        $data['seven_pay_cate'] = $info['pay']['cate'];
        $data['seven_reg_min'] = $info['reg']['min'];
        $data['seven_reg_max'] = $info['reg']['max'];
        $data['seven_reg_data'] = $info['reg']['data'];
        $data['seven_reg_cate'] = $info['reg']['cate'];

        //统计页面三个排行 共计九个字段
        /*$data['day_rank_sy'] = $rank['day_rank_sy'];
        $data['week_rank_sy'] = $rank['week_rank_sy'];
        $data['mon_rank_sy'] = $rank['mon_rank_sy'];
        $data['day_rank_h5'] = $rank['day_rank_h5'];
        $data['week_rank_h5'] = $rank['week_rank_h5'];
        $data['mon_rank_h5'] = $rank['mon_rank_h5'];
        $data['day_rank_all'] = $rank['day_rank_all'];
        $data['week_rank_all'] = $rank['week_rank_all'];
        $data['mon_rank_all'] = $rank['mon_rank_all'];

        //统计页面的注册 活跃 充值
        $data['reg_quxian'] = json_encode($zhexian['reg']);
        $data['act_quxian'] = json_encode($zhexian['act']);
        $data['pay_quxian'] = json_encode($zhexian['pay']);*/


        $data['create_time'] = time();

        M('Data','tab_')->add($data);
    }

    public function sitestat(){

        $user = M("User","tab_");
        $userlogin = M("UserLoginRecord","tab_");
        $game = M("Game","tab_");
        $spend = M('Spend',"tab_");
        $deposit = M('Deposit',"tab_");
        $promote = M("Promote","tab_");
        $yesterday = $this->total(5);
        $today = $this->total(1);
        $week = $this->total(9);
        $month = $this->total(3);
        $info['user'] = $user->count();
        $info['yesterday']= $user->where("register_time".$yesterday)->count();
        $info['today']= $user->where("register_time".$today)->count();
        $info['login']= $user->where("login_time".$today)->count();

        $info['game'] = $game->where(['apply_status'=>1])->count();
        $info['tadd'] = $game->where("create_time".$today)->count();
        $info['yadd'] = $game->where("create_time".$yesterday)->count();

        $samount = $spend->field('sum(pay_amount) as amount')->where("pay_status=1 and pay_way <> 0")->select();
        if($samount[0]['amount']){
            $info['samount']=$this->test($samount[0]['amount']);
        }else{
            $info['samount']=0;
        }
        $damount = $deposit->field('sum(pay_amount) as amount')->where("pay_status=1 and pay_way<>0")->select();
        if($damount[0]['amount']){
            $info['damount']=$damount[0]['amount']==''?0:$damount[0]['amount'];
        }else{
            $info['damount']=0;
        }

        //七日活跃
        $wurel=$user
            ->field('id as user_id')
            ->where('register_time'.$week)
            ->select(false);
        $ulogin=$userlogin
            ->field('user_id')
            ->where("login_time".$week)
            ->union($wurel)
            ->group('user_id')
            ->select();
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
        //昨日活跃
        $yurel=$user
            ->field('id as user_id')
            ->where('register_time'.$yesterday)
            ->select(false);
        $ylogin=$userlogin
            ->field('user_id')
            ->where("login_time".$yesterday)
            ->union($yurel)
            ->group('user_id')
            ->select();
        $ulogin=count($ulogin);
        $tlogin=count($tlogin);
        $ylogin=count($ylogin);
        $info['ulogin'] = $ulogin;
        $info['tlogin'] = $ylogin;
        $info['ylogin'] = $tlogin;
        //
        // 付费玩家  游戏付费+平台币充值
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
        //昨日平台币充值
        $ydfufei=$deposit
            ->field('user_id')
            ->where("pay_status=1 and create_time".$yesterday)
            ->group('user_id')
            ->select(false);
        //两表并集
        $yfufei=$spend
            ->field('user_id')
            ->union($ydfufei)
            ->where("pay_status=1 and pay_time".$yesterday)
            ->group('user_id')
            ->select();
        //今日平台币充值
        $tdfufei=$deposit
            ->field('user_id')
            ->where("pay_status=1 and create_time".$today)
            ->group('user_id')
            ->select(false);
        //两表并集
        $tfufei=$spend
            ->field('user_id')
            ->union($tdfufei)
            ->where("pay_status=1 and pay_time".$today)
            ->group('user_id')
            ->select();
        $info['afufei']=count($afufei);
        $info['yfufei']=count($yfufei);
        $info['tfufei']=count($tfufei);
        //
        // 游戏充值
        $asamount = $spend->field('sum(pay_amount) as amount')->where("pay_status=1 and pay_way >= 0")->find();
        $tsamount = $spend->field('sum(pay_amount) as amount')->where("pay_status=1 and pay_way >= 0 and pay_time".$today)->find();
        $ysamount = $spend->field('sum(pay_amount) as amount')->where("pay_status=1 and pay_way >= 0 and pay_time".$yesterday)->find();
        $info['asamount'] = $asamount['amount']?$asamount['amount']:0;
        $info['tsamount'] = $tsamount['amount']?$tsamount['amount']:0;
        $info['ysamount'] = $ysamount['amount']?$ysamount['amount']:0;

        $info['promote'] = $promote->count();
        $info['tpadd'] = $promote->where("create_time".$today)->count();
        $info['ypadd'] = $promote->where("create_time".$yesterday)->count();

        $doc = D("Document");
        $b =$this->cate("blog");
        $m =$this->cate("media");
        $blog = $doc->table("__DOCUMENT__ as d")
            ->where("d.status=1 and d.display=1 and d.category_id in (".$b.")")->count();
        $media = $doc->table("__DOCUMENT__ as d")
            ->where("d.status=1 and d.display=1 and d.category_id in (".$m.")")->count();
        $info['document'] = $this->test($blog + $media);
        $info['blog']=$this->test($blog);
        $info['media']=$this->test($media);

        return $info;
    }


    public function statistics(){

        //定义表名
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

        $platform_data['all_pay']=$this->test($spay);//累计流水

        $platform_data['all_promote']=$promote->where(array('status'=>1))->count();//累计渠道

        $platform_data['all_game']=$game->where(['apply_status'=>1])->count();//累计游戏

        $platform_data['all_android']=$gamesource->where(array('file_type'=>1))->count();//累计安卓包

        $platform_data['all_ios']=$gamesource->where(array('file_type'=>2))->count();//累计苹果包

        $platform_data['all_tuser']=$user->where(array('promote_id'=>array('gt',0)))->count();//累计渠道注册玩家

        $tspay=$spend->where(array('promote_id'=>array('gt',0)))->where(array('pay_status'=>1))->sum('pay_amount');//累计渠道充值玩家
        //$dspay =$deposit->where(array('promote_id'=>array('gt',0)))->where(array('pay_status'=>1))->sum('pay_amount');
        $platform_data['all_tpay']=$this->test($tspay);//累计渠道总流水

        $result['platform_data'] = $platform_data;

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
        //$realtime_data['today_pay']=$this->test($todayspay+$todaydpay);
        $realtime_data['today_pay']=$todayspay?$todayspay:0;
        //本周流水
        $weekspay=$spend->where(array('pay_time'.$thisweek))->where(array('pay_status'=>1))->sum('pay_amount');
        //$weekdpay=$deposit->where(array('create_time'.$thisweek))->where(array('pay_status'=>1))->sum('pay_amount');
        $realtime_data['thisweek_pay']=$weekspay?$weekspay:0;
        //本月流水
        $mounthspay=$spend->where(array('pay_time'.$thismounth))->where(array('pay_status'=>1))->sum('pay_amount');
        //$mounthdpay=$deposit->where(array('create_time'.$thismounth))->where(array('pay_status'=>1))->sum('pay_amount');
        $realtime_data['thismounth_pay']=$mounthspay?$mounthspay:0;

        $result['realtime_data'] = $realtime_data;

        return $result;

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

    public function test($test){
        return $test;
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

    private function cate($name) {
        $cate = M("Category");
        $c = $cate->field('id')->where("status=1 and display=1 and name='$name'")->buildSql();
        $ca = $cate->field('id')->where("status=1 and display=1 and pid=$c")->select();
        foreach($ca as $c) {
            $d[]=$c['id'];
        }
        return "'".implode("','",$d)."'";
    }
}