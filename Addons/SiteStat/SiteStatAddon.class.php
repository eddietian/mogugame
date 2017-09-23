<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------
namespace Addons\SiteStat;
use Common\Controller\Addon;

/**
 * 系统环境信息插件
 * @author thinkphp
 */
class SiteStatAddon extends Addon{

    public $info = array(
        'name'=>'SiteStat',
        'title'=>'站点统计信息',
        'description'=>'统计站点的基础信息',
        'status'=>1,
        'author'=>'thinkphp',
        'version'=>'0.1'
    );

    public function install(){
        return true;
    }

    public function uninstall(){
        return true;
    }

    //实现的AdminIndex钩子方法
    public function AdminIndex($param){
        $config = $this->getConfig();
        $this->assign('addons_config', $config);
        if($config['display']){
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
            
            $info['game'] = $game->count();
            $info['tadd'] = $game->where("create_time".$today)->count();
            $info['yadd'] = $game->where("create_time".$yesterday)->count();
                   
            $samount = $spend->field('sum(pay_amount) as amount')->where("pay_status=1 and pay_way <> 0")->select();
            if($samount[0]['amount']){
                $info['samount']=$this->huanwei($samount[0]['amount']);
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
            $info['asamount'] = $asamount['amount'];
            $info['tsamount'] = $tsamount['amount'];
            $info['ysamount'] = $ysamount['amount'];
            
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
            $info['document'] = $this->huanwei($blog + $media);
            $info['blog']=$this->huanwei($blog);
            $info['media']=$this->huanwei($media);
            //待办事项
            $this->daiban();
            //提示事项indexcontroller
            
            // 图表
            $day=$this->every_day(7);
             $spend_map['pay_status']=1;
                $data_spend=$spend
                    ->field('date_format(FROM_UNIXTIME( pay_time),"%Y-%m-%d") AS time,sum(pay_amount) as cg')
                    ->where(array('game_id'=>array('gt',0)))
                    ->where($spend_map)
                    ->group('time')
                    ->order('cg desc')
                    ->select();
                $data_user=$user
                    ->field('date_format(FROM_UNIXTIME( register_time),"%Y-%m-%d") AS time,count(id) as cg')
                    ->group('time')
                    ->select();
            $spendd=$this->foreach_data($day,$data_spend);   
            $userdd=$this->foreach_data($day,$data_user);   
            asort($spendd);
            asort($userdd);
            foreach ($spendd as $key => $value) {
                $dday[]=$value['time'];
                $cgg[]=$value['count'];

            }
            foreach ($userdd as $key => $value) {
                $user_dday[]=$value['time'];
                $user_cgg[]=$value['count'];

            }

            $mix_pay=max($cgg);
            $min_pay=min($cgg);
            $ii['min']=$min_pay;
            $ii['mix']=$mix_pay;
            $ii['data']= rtrim($this->foreach_stat($cgg,2),",");
            $ii['day']=rtrim($this->foreach_stat($dday),",");


            $mix_user=max($user_cgg);
            $min_user=min($user_cgg);
            $user_ii['min']=$min_user;
            $user_ii['mix']=$mix_user;
            $user_ii['data']= rtrim($this->foreach_stat($user_cgg,2),",");
            $user_ii['day']=rtrim($this->foreach_stat($user_dday),",");
   
            $mix_tu=$user_ii['mix']>$ii['mix']?$user_ii['mix']:$ii['mix'];
            $this->assign('mix_tu',$mix_tu);
            $this->assign('ii',$ii);
            $this->assign('user_ii',$user_ii);
            $this->assign('info',$info);
            $this->display('info');
        }
    }

   public function foreach_stat($dday,$type=1){
        foreach ($dday as $key => $value) {
            if($type==1){
                  $ss.='"'.$value.'",';
            }else{
                  $ss.=$value.',';
            }
          
        }
        return $ss;
    }


     public function foreach_data($day,$data,$type=1){
        foreach ($day as $s => $d) {
            $spendd[$s]['time']=$d;
            $spendd[$s]['count']=0;
        }
        if($type==1){
            foreach ($spendd as $s => $d) {
              foreach ($data as $key => $value) {
                  if($value['time']==$d['time']){
                        $spendd[$s]['count']=(int)$value['cg'];
                     }
            }
            }
        }else{
            foreach ($spendd as $s => $d) {
                $a=0;
              foreach ($data as $key => $value) {
                  if($value['time']==$d['time']){
                    $a++;
                        $spendd[$s]['count']=$a;
                     }
            }
            }
        }

        return $spendd;
    }


    private function daiban(){
        $user = M("User","tab_");
        $game = M("Game","tab_");
        $spend = M('Spend',"tab_");
        $deposit = M('Deposit',"tab_");
        $apply = M('Apply',"tab_");
        $applyapp = M('app_apply',"tab_");
        $promote = M("Promote","tab_");

        $pregist=$promote->where(array('status'=>0))->count();//渠道申请待审核数
        $daiban['pcount']=$pregist;

        $map_appc['sdk_version'] = 1;
        $appc=$apply->where($map_appc)->where('ISNULL(pack_url)')->count();//渠道分包待打包数
        $daiban['appc']=$appc;

        $withc=M('Withdraw','tab_')->where(array('status'=>0,'promote_id'=>array('gt',0)))->count();//渠道提现待审核数
        $daiban['withc']=$withc;

        $spenc=$spend->where(array('pay_game_status'=>0,'pay_status'=>1))->count();//游戏充值待补单数
        $daiban['spenc']=$spenc;
        
        $applyapp=$applyapp->where(array('dow_url'=>''))->count();//APP分包待打包数
        $daiban['applyapp']=$applyapp;

        $msgc=M('Msg','tab_')->where(array('user_id'=>UID,'status'=>2))->count();//站内通知
        $daiban['msgc']=$msgc;
        $this->assign('daiban',$daiban);
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

    private function idata($data,$flag=false,$field) {
        $day=array_flip($this->every_day(7));//七天日期
        $data=array_merge($day,$data);
        $d = $c = '';
        $max = 0;
        $min = 0;
        if (!empty($data)) {
            ksort($data);
            // $data = array_reverse($data);
            if ($flag) {
                foreach ($data as $k => $v) {
                    if (!empty($v)) {
                        foreach($v as $j => $u) {
                            $total += $u[$field];
                        }
                        $toto[]=$total;
                        
                    } else {
                        $toto[]=$total = 0;
                    }         
                    if ($min>$total){$min = $total;}
                    if ($max<$total){$max = $total;}
                    $c .= '"'.$k.'",';   
                    $total=0;       
                } 
                $d =implode(',', $toto).',';         
            } else {
                foreach ($data as $k => $v) {
                    $count = empty($v)?0:count($v);        
                    if ($min>$count){$min = $count;}
                    if ($max<$count){$max = $count;}
                    $d .= $count.',';
                    $c .= '"'.$k.'",';          
                }
            }
            $d = substr($d,0,-1);
            $c = substr($c,0,-1);           
        }
        $max++;
        $pay = array(
            'min' => $min,
            'max' => $max,
            'data' => $d,
            'cate' => $c
        );
        return $pay;
    }
    private function linepay() {
        $spend = M('Spend',"tab_");
        $deposit = M('Deposit',"tab_");
        $week = $this->total(9);
        $samount = $spend->field("pay_amount,pay_time as time")->where("pay_status=1 and pay_time $week")->select();
        $damount = $deposit->field("pay_amount,create_time as time")->where("pay_status=1 and create_time $week")->select();
        if (!empty($samount) && !empty($damount) )
            $data = array_merge($samount,$damount);
        else {
            if (!empty($samount))
                $data = $samount;
            else if (!empty($damount))
                $data = $damount;
            else 
                $data = '';
        }

        $result = array();
        $this->jump($data,$result,8);
        return $result;
    }
    
    private function lineregister() {
        $week = $this->total(9);
        $user = M("User","tab_")->field("account,register_time as time")->where("lock_status=1 and register_time $week")->select();

        if (!empty($user))
            $data = $user;
        else 
            $data = array(0,0,0,0,0,0,0);
        
        $result = array();
        $this->jump($data,$result,8);
        return $result;
    }
    
    protected function jump(&$a,&$b,$m,$n=0) {
        $num = count($a);
        if($m == 1) {
            return ;
        } else {
            $time = time();    
            if ($m < 8) {
                $c = 8 - $m;
                $time = $time - ($c * 86400);
            }
            $m -= 1;
            $t = date("Y-m-d",$time);
            if (empty($a) && count($b)<8) {
                $b[$t]= "";
            } else {
                foreach($a as $k => $g) {
                    $st = date("Y-m-d",$g['time']);
                    if($t===$st) {              
                        $b[$t][]=$g;
                        unset($a[$k]);
                    }
                    if($b[$t]==''){
                        $b[$t] = 0;
                    }
                }
                $a = array_values($a);      
            }
            return $this->jump($a,$b,$m,$num);
        } 
    }
    private function total($type) {
        switch ($type) {
            case 1: { // 今天
                $start=mktime(0,0,0,date('m'),date('d'),date('Y'));
                $end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
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
                $end=mktime(0,0,0,date('m'),date('d'),date('Y'));
            };break;
            case 9: { // 前七天
                $start = mktime(0,0,0,date('m'),date('d')-6,date('Y'));
                $end=mktime(date('H'),date('m'),date('s'),date('m'),date('d'),date('Y'));
            };break;
            default:
                $start='';$end='';
        }
        
        return " between $start and $end ";
    }
    //以当前日期 默认前七天 
    private function every_day($m=7){
        $time=array();
        for ($i=0; $i <$m ; $i++) { 
            $time[]=date('Y-m-d',mktime(0,0,0,date('m'),date('d')-$i,date('Y')));
        }
        return $time;
    }
    private function huanwei($total) {
        if(!strstr($total,'.')){
            $total=$total.'.00';
        }
        $total = empty($total)?'0':trim($total.' ');
        $len = strlen($total);
        if ($len>8) { // 亿
           $len = $len-12;
           $total = $len>0?(round(($total/1e12),2).'万亿'):round(($total/1e8),2).'亿';            
        } else if ($len>4) { // 万
            $total = (round(($total/10000),2)).'w';
        }
        return $total;
    }
}