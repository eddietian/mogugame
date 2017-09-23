<?php

/* 获取日期间隔数组 @author 鹿文学 */
function get_date_list($d1='',$d2='',$flag=1) {
    if ($flag == 1){/* 天 形如：array('2017-03-10','2017-03-11','2017-03-12','2017-03-13')*/
        $d1 = $d1?$d1:mktime(0,0,0,date('m'),date('d')-6,date('Y'));
        $d2 = $d2?$d2:mktime(0,0,0,date('m'),date('d'),date('Y'));
        $date = range($d1,$d2,86400);
        $date = array_map(create_function('$v','return date("Y-m-d",$v);'),$date);       
    } elseif ($flag == 2) {/* 月 形如：array('2017-01','2017-02','2017-03','2017-04')*/
        $d1 = $d1?$d1:mktime(0,0,0,date('m')-5,1,date('Y'));
        $d2 = $d2?$d2:mktime(0,0,0,date('m'),date('d'),date('Y'));
        $i = false;
        while($d1<$d2) {
            $d1 = !$i?$d1:strtotime('+1 month',$d1);
            $date[]=date('Y-m',$d1);$i=true;
        }
        array_pop($date);
    } elseif ($flag == 3) {/* 周 形如：array('2017-01','2017-02','2017-03','2017-04')*/
        $d1 = $d1?$d1:mktime(0,0,0,date('m')-2,1,date('Y'));
        $d2 = $d2?$d2:mktime(0,0,0,date('m'),date('d'),date('Y'));

        $i = false;
        while($d1<$d2) {
            $d1 = !$i?$d1:strtotime('+1 week',$d1);
            $date[]=date('Y-W',$d1);$i=true;
        }
    }
    
    return $date;
}

/**
 * 获取对应游戏类型的状态信息
 * @param int $group 状态分组
 * @param int $type  状态文字
 * @return string 状态文字 ，false 未获取到
 * @author
 */
function get_info_status($type=null,$group=0){
    if(!isset($type)){
        return false;
    }
    $arr=array(
        0 =>array(0=>'关闭'   ,1=>'开启'),
        1 =>array(0=>'不推荐' ,1=>'推荐',2=>"热门",3=>'最新'),//游戏设置状态
        2 =>array(0=>'否'     ,1=>'是'),
        3 =>array(0=>'未审核' ,1=>'正常',2=>'锁定'),//推广员状态
        4 =>array(0=>'锁定'   ,1=>'正常'),//用户状态
        5 =>array(0=>'未审核' ,1=>'已审核'   ,2=>'驳回'),//游戏审核状态
        6 =>array(0=>'未修复' ,1=>'已修复'),//纠错状态
        7 =>array(0=>'失败'   ,1=>'成功'),//纠错状态
        8 =>array(0=>'禁用'   ,1=>'启用'),//显示状态
        9 =>array(0=>'下单未付款' ,1=>'充值成功'),//显示状态
        10 =>array(0=>'正常'   ,1=>'拥挤',2=>'爆满'),//区服状态
        12 =>array(0=>'未支付',1=>'成功'),
        30=>array(0=>'待审核',1=>'通过',2=>'未通过'),
        13 =>array(1=>'已读',2=>'未读'),
        14 =>array(0=>'通知失败',1=>'通知成功'),
        15 =>array(0=>'未充值',1=>'已充值'),
        16 =>array(0=>'未回复',1=>'已回复'),
        17 =>array(0=>'平台币',1=>'绑定平台币'),
        29=>array(0=>'待审核',1=>'已通过',2=>'未通过'),
        18 => ['平台币','支付宝','微信','聚宝云'],
        19 => ['审核中','已通过','拒绝'],
        20 => ['','一级','二级'],
        21 => ['','支付宝','微信','聚宝云','平台币','竣付通'],
        22 => ['双平台','安卓','苹果'],
        23 => ['','安卓','苹果'],
        24 => ['','系统分配','自主添加'],
        25 => ['','通过','未审核'],
        26 => ['','开启','关闭'],
        27 => ['','实物','虚拟物品'],
        28 => ['','已签署','待签署','待签署','已确认'],
    );
    return $arr[$group][$type];
}

     
     /**
     * 获取申请游戏列表
     * @return array，false
     * @author yyh 
     */
    function get_apply_game_list2($promote_id=0)
     {
        $apply = M('Apply',"tab_");
        $map['promote_id'] = $promote_id;
        $lists = $apply
                ->field("tab_apply.*,tab_game.icon,tab_game.short,tab_game.game_appid,tab_game.discount")
                ->join("left join tab_game on tab_apply.game_id = tab_game.id")
                ->where($map)
                ->order("id DESC")
                ->select();
        
        if(empty($lists)){return false;}
        return $lists;
     }


//计算文件夹所有文件代销
function dirsize($dir) {
@$dh = opendir($dir);
$size = 0;
while ($file = @readdir($dh)) {
if ($file != "." and $file != "..") {
$path = $dir."/".$file;
if (is_dir($path)) {
$size += dirsize($path);
} elseif (is_file($path)) {
$size += filesize($path);
}
}
}
@closedir($dh);
return $size;
} 



    // lwx 2017-01-07
    function get_systems_name($id,$lan='cn') {
        $list = get_systems_list($lan);
        return $list[$id];
    }

    // lwx 2017-01-07
    function get_systems_list($lan='cn') {
        switch($lan) {
            case 'en':{$list = array(0=>'double',1=>'Android',2=>'IOS');}break;
            default:$list = array(1=>'安卓',2=>'苹果');
        }
        return $list;
    }

    // lwx 
    function seo_replace($str='',$array=array(),$site='media') {
        if ($site=='channel') {$title = C('CH_SET_TITLE');}
        else {$title = C('PC_SET_TITLE');}
        if (empty($str)) {return $title;}
        $find = array('%webname%','%gamename%','%newsname%','%giftname%','%gametype%');
        $replace = array($title,$array['game_name'],$array['title'],$array['giftbag_name'],$array['game_type_name']);
        $str =  str_replace($find,$replace,$str);
        
        return preg_replace('/((-|_)+)?((%[0-9A-Za-z_]*%)|%+)((-|_)+)?/','',$str);
    }
    function auto_get_access_token($dirname){
        $appid     = C('wx_login.appid');
        $appsecret = C('wx_login.appsecret');
        $access_token_validity=file_get_contents($dirname);
        if($access_token_validity){
            $access_token_validity=json_decode($access_token_validity,true);
            $is_validity=$access_token_validity['expires_in_validity']-1000>time()?true:false;
        }else{
            $is_validity=false;
        }
        $result['is_validity']=$is_validity;
        $result['access_token']=$access_token_validity['access_token'];
        return $result;
    }
    function write_text($txt,$name){
        $myfile = fopen($name, "w") or die("Unable to open file!");
        fwrite($myfile, $txt);
        fclose($myfile);
    }
    //判断客户端是否是在微信客户端打开
    function is_weixin(){ 
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }   
        return false;
    }
    function wite_text($txt,$name){
        $myfile = fopen($name, "w") or die("Unable to open file!");
        fwrite($myfile, $txt);
        fclose($myfile);
    }
        /**
     * [获取开放类型名称]
     * @param  string $id [description]
     * @return [type]     [description]
     */
    function get_one_opentype_name($id=''){
        if($id==''){
            return '';
        }
        if($id==0){
             return '公测';
        }
        $data=M('Opentype','tab_')->where(array('id'=>$id))->find();
        if($data==''){
            return false;
        }else{
            return $data['open_name'];
        }
}

    /**
     * 获取游戏列表
     * @return array，false
     * @author yyh 
     */
    function get_game_list()
     {
        $game = M("game","tab_");
        $map['game_status'] = 1;
        // $map['apply_status'] = 1;
        $lists = $game->where($map)->select();
        if(empty($lists)){return false;}
        return $lists;
     }
    /**
     * 获取游戏类型列表
     * @return array，false
     * @author yyh 
     */
    function get_game_type_all() {
        $list = M("Game_type","tab_")->where("status_show=1")->select();
        if (empty($list)) {return '';}
        return $list;
    }
    function get_opentype_all() {    
        $list = M("Opentype","tab_")->where("status=1")->select();
        if (empty($list)) {return '';}
        return $list;
    }
        /**
    * 生成唯一的APPID
    * @param  $str_key 加密key
    * @return string
    * @author 小纯洁 
    */
    function generate_game_appid($str_key=""){
        $guid = '';  
        $data = $str_key;  
        $data .= $_SERVER ['REQUEST_TIME'];     
        $data .= $_SERVER ['HTTP_USER_AGENT']; 
        $data .= $_SERVER ['SERVER_ADDR'];       
        $data .= $_SERVER ['SERVER_PORT'];      
        $data .= $_SERVER ['REMOTE_ADDR'];     
        $data .= $_SERVER ['REMOTE_PORT'];     
        $hash = strtoupper ( hash ( 'MD4', $guid . md5 ( $data ) ) ); //ABCDEFZHIJKLMNOPQISTWARY
        $guid .= substr ( $hash, 0, 9 ) . substr ( $hash, 17, 8 ) ; 
        return $guid;
    }
    // 获取游戏名称
    function get_game_name($game_id=null,$field='id'){
        $map[$field]=$game_id;
        $data=M('Game','tab_')->where($map)->find();
        if(empty($data)){return ' ';}
        return $data['game_name'];
    }
    //获取游戏cp比例
    function get_game_selle_ratio($game_id=null,$field='id'){
        $map[$field]=$game_id;
        $data=M('game','tab_')->where($map)->find();
        if(empty($data)){return '';}
        return $data['dratio'];
    }

   /**
    * [获取管理员昵称]
    * @param  integer $id [description]
    * @return [yyh]      [description]
    */
    function get_admin_name($id=0){
        if($id==null){
            return '';
        }
        $data = M("Member")->find($id);
        if(empty($data)){return "";}
        return $data['nickname'];
    }
    /**
     * [获取原包类型]
     * @param  integer $type [description]
     * @return [type]        [description]
     */
    function file_type($file_type='',$type=0){
        if($file_type==''){
            return false;
        }
        if($type){
            $file_type==1?$file_type_name='Android':$file_type_name='IOS';
        }else{
            $file_type==1?$file_type_name='安卓':$file_type_name='苹果';
        }
        return $file_type_name;
    }
    /**
     * [获取游戏appid]
     * @param  [type] $game_name [description]
     * @param  string $field     [description]
     * @param  string $md5       [16位加密]
     * @return [type]            [description]
     * @author yyh <[email address]>
     */
    function get_game_appid($game_name=null,$field='game_name',$md5=false){
        if($game_name==null){
            return false;
        }
        $map[$field]=$game_name;
        $data=M('Game','tab_')->where($map)->find();
        if(empty($data)){return false;}
        if($md5){
            return md5($data['game_appid']);
        }else{
            return $data['game_appid'];
        }
    }
    /**
     * [字符串转数组  计算个数]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    function arr_count($string){
        if($string){
            $arr=explode(',',$string);
            $cou=count($arr);
        }else{
            $cou=0;
        }
        return $cou;
    }
    function get_operation_platform($id,$table,$field='id'){
        if(!$table||!$id){
            return false;
        }
        $model=M($table,'tab_');
        $map[$field]=$id;
        $data = $model->where($map)->find();
        if(empty($data)){
            return false;
        }else{
            if($data['giftbag_version']==0){
                return '双平台';
            }elseif($data['giftbag_version']==1){
                return '安卓';
            }elseif($data['giftbag_version']==2){
                return '苹果';
            }
        }
    }
    /**
     * [获取渠道名称]
     * @param  integer $prmote_id [description]
     * @return [type]             [description]
     */
    function get_promote_name($prmote_id=0){
        if($prmote_id==0){
            return '自然注册';
        }
        $promote = M("promote","tab_");
        $map['id'] = $prmote_id;
        $data = $promote->where($map)->find();
        if(empty($data)){return '自然注册';}
        if(empty($data['account'])){return "未知推广";}
        $result = $data['account'];
        return $result;
    }
    /**
     * [获取开发商名称]
     * @param  integer $prmote_id [description]
     * @return [type]             [description]
     */
    function get_developer_name($develop_id=0){
        if($develop_id==0){
            return '无';
        }
        $promote = M("developers","tab_");
        $map['id'] = $develop_id;
        $data = $promote->where($map)->find();
        if(empty($data)){return '无';}
        if(empty($data['nickname'])){return "无";}
        $result = $data['nickname'];
        return $result;
    }
    function get_developer_account($develop_id=0){
        if($develop_id==0){
            return '无';
        }
        $promote = M("developers","tab_");
        $map['id'] = $develop_id;
        $data = $promote->where($map)->find();
        if(empty($data)){return '无';}
        if(empty($data['account'])){return "无";}
        $result = $data['account'];
        return $result;
    }
    /**
     * [根据渠道id 获取上线渠道id]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    function get_parent_id($param,$type='id'){
        $map[$type]=$param;
        $pdata=M('Promote','tab_')->where($map)->find();
        if(empty($pdata)){
            return false;
        }else{
            $p_id=$pdata['parent_id'];
            return $p_id;
        }
    }
/**
 * [pay_way description]
 * @param  [type] $pay_way [description]
 * @return [type]          [description]
 */
    function pay_way($pay_way){
        if($pay_way==-1){
            return '绑定平台币';
        }
        if($pay_way==0){
            return '平台币';
        }
        if($pay_way==1){
            return '支付宝';
        }
        if($pay_way==2){
            return '微信';
        }
        if($pay_way==3){
            return "苹果支付";
        }
    }

    function get_subordinate_promote($param,$type="parent_id"){
        if($param==''){
            return false;
        }
        $map[$type]=$param;
        $data=M('Promote','tab_')
            ->field('account')
            ->where($map)
            ->select();
        return array_column($data,'account');
    }
    /**
     * [二维数组 按照某字段排序]
     * @param  [type] $arrays     [description]
     * @param  [type] $sort_key   [description]
     * @param  [type] $sort_order [description]
     * @param  [type] $sort_type  [description]
     * @return [type]             [description]
     */
    function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){ 
        if(is_array($arrays)){   
            foreach ($arrays as $array){   
                if(is_array($array)){   
                    $key_arrays[] = $array[$sort_key];   
                }else{   
                    return false;   
                }   
            }   
        }else{   
            return false;   
        }  
        array_multisort($key_arrays,$sort_order,$sort_type,$arrays);   
        return $arrays;   
    } 
    /**
     * [判断结算时间是否合法]
     * @param  [type] $start      [description]
     * @param  [type] $promote_id [description]
     * @param  [type] $game_id    [description]
     * @return [type]             [description]
     */
    function get_settlement($start,$end,$promote_id,$game_id){
        $start=strtotime($start);
        $end=strtotime($end)+24*60*60-1;
        $map['promote_id']=$promote_id;
        $map['game_id']=$game_id;
        $data=M('settlement','tab_')
            ->where($map)
            ->select();
        foreach ($data as $key => $value) {
            if($start<$value['endtime']){
                if($end>$value['starttime']){
                    return true;//开始时间<结算 不可结算
                }else{
                    return false;
                }
            }
        }
    }
/**
*随机生成字符串
*@param  $len int 字符串长度
*@return string
*@author 小纯洁
*/
function sp_random_string($len = 6) {
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}
/**
*随机生成字符串
*@param  $len int 字符串长度
*@return string
*@author 小纯洁
*/
function sp_random_num($len = 5) {
    $chars = array(
        "0", "1", "2","3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}
/**
 * [获取合作模式]
 * @param  [type] $type [description]
 * @return [type]       [description]
 */
function get_pattern($type){
   if($type==0){
        return "CPS";
    }else{
        return "CPA";
    } 
}
/**
 * [获取日期时间戳]
 * @param  [type] $type [description]
 * @return [type]       [description]
 */
function total($type,$str=true) {
    switch ($type) {
        case 1: { // 今天
            $start=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        };break;
        case 2: { // 本周
            // $start=mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
            // $end=mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
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
    if($str){
        return " between $start and $end ";
    }else{
        return ['between',[$start,$end]];
    }
}
/**
 * [null_to_0 description]
 * @param  [type] $num [description]
 * @return [type]      [description]
 */
function null_to_0($num){
    if($num){
        return sprintf("%.2f",$num);
    }else{
        return sprintf("%.2f",0);
    }
}
function get_game_limit_name($game_id){
    if (empty($game_id)) {
        return '全部游戏';
    } else {
        $game = M("game", "tab_");
        $map['game_status'] = 1;
        $lists = $game->where($map)->find();
        if (empty($lists)) {
            return '';
        }
        return $lists['game_name'];
    }
}
 /**
  * [获取用户实体]
  * @param  integer $id        [description]
  * @param  boolean $isAccount [description]
  * @return [yyh]             [description]
  */
 function get_user_entity($id=0,$isAccount = false){
    if($id =='' ){
        return false;
    }
    $user = M('user',"tab_");
    if($isAccount){
        $map['account'] = $id;
        $data = $user->where($map)->find();
    }
    else{
        $data = $user->find($id);
    }
    if(empty($data)){
        return false;
    }
    return $data;
 }
 /**
  * [两个运营平台的游戏合并]
  * @param  [type] $data [最新添加的游戏]
  * @param  [type] $map  [description]
  * @return [type]       [游戏信息]
  */
 function game_merge($data,$map){
    $model=M('Game','tab_');
    for ($i=0; $i <count($data); $i++) { 
        if($data[$i]['sdk_version']==2){
            $data[$i]['and_id']='';
            $data[$i]['and_dow_address']='';
            $data[$i]['add_game_address']='';
            $data[$i]['ios_id']=$data[$i]['id'];
            $data[$i]['iosdow']=$data[$i]['dow_num'];
        }else if($data[$i]['sdk_version']==1){
            $data[$i]['ios_id']='';
            $data[$i]['ios_dow_address']='';
            $data[$i]['ios_game_address']='';
            $data[$i]['and_id']=$data[$i]['id'];
            $data[$i]['anddow']=$data[$i]['dow_num'];
        }
        if($data[$i]['relation_game_id']!=$data[$i]['id']){
            //最新添加的游戏id和关联游戏id不一致 即不止一个游戏
            $sibling_id=$data[$i]['relation_game_id'];
            $map['id']=array('eq',$sibling_id);//id不等于关联id(第一个)
            $map['relation_game_id']=$sibling_id;
        }else{
            //最新添加的游戏和关联游戏一致  即只有一个游戏 下面代码可以屏蔽
            $sibling_id=$data[$i]['id'];
            $map['id']=array('neq',$sibling_id);
            $map['relation_game_id']=$data[$i]['relation_game_id'];
        }
        $game_data=$model->where($map)->find();
        if($game_data['sdk_version']==2){
            $data[$i]['ios_id']=$game_data['id'];
            $data[$i]['ios_dow_address']=$game_data['ios_dow_address'];
            $data[$i]['ios_game_address']=$game_data['ios_game_address'];
            $data[$i]['iosdow']=$data[$i]['dow_num'];
        }else if ($game_data['sdk_version']==1){
            $data[$i]['and_id']=$game_data['id'];
            $data[$i]['and_dow_address']=$game_data['and_dow_address'];
            $data[$i]['add_game_address']=$game_data['add_game_address'];
            $data[$i]['anddow']=$data[$i]['dow_num'];
        }
    }
    return $data;
}
/**
 * [获取文本 超过字数显示..]
 * @param  [type] $title [description]
 * @return [type]        [description]
 */
function get_title($title,$len=30){
    if(mb_strlen($title,'utf8') > $len){
         $title = mb_substr($title,0,$len,'utf-8').'...';
    }else{
        $title = $title;
    }
    if(empty($title)){return false;}
    return $title;
}
// function get_title($title,$len =30){
//     if(strlen($title) > $len){
//          $title = substr($title, 0,$len).'...';
//     }else{
//         $title = $title;
//     }
//     if(empty($title)){return false;}
//     return $title;
// }
/**
 * [游戏某一礼包所有激活码个数（未领取和已领取）]
 * @param  [type] $gid     [游戏id]
 * @param  [type] $gift_id [礼包 id]
 * @return [type]          [description]
 */
function gift_recorded($gid,$gift_id){
    $wnovice=M('giftbag','tab_')->where(array('game_id'=>$gid,'id'=>$gift_id))->find();
    if($wnovice['novice']!=''){
        $wnovice=count(explode(',',$wnovice['novice']));
    }else{
        $wnovice=0;
    }
    $ynpvice=M('gift_record','tab_')->where(array('game_id'=>$gid,'gift_id'=>$gift_id))->select();
    if($ynpvice!=''){
        $ynpvice=count($ynpvice);
    }else{
        $ynpvice=0;
    }
    $return['all']=$wnovice+$ynpvice;
    $return['wei']=$wnovice;
    return $return;
}
function zimu26(){
    for($i=0;$i<26;$i++){
        $zimu[]['value']=chr($i+65);
    } 
    return $zimu;
}
function get_gift_list($game_id='all',$limit=""){
    $map['status'] = 1;
    $map['game_status']=1;
    // $map['giftbag_type']=1;//推荐状态
    $map['end_time']=array(array('gt',time()),array('eq',0),'or');
    if($game_id!='all'){
        $map['game_id']=$game_id;
    }
    $model = array(
        'm_name'=>'Giftbag',
        'prefix'=>'tab_',
        'field' =>'tab_giftbag.id as gift_id,relation_game_name,game_id,tab_giftbag.game_name,giftbag_name,giftbag_type,tab_game.icon,tab_giftbag.create_time',
        'join'  =>'tab_game on tab_giftbag.game_id = tab_game.id',
        'order' =>'create_time desc',
    );
    if(!empty($limit)){
        $map_list=$limit;
    }
    $game  = M($model['m_name'],$model['prefix']);
    $data  = $game
    ->field($model['field'])
    ->join($model['join'])
    ->where($map)
    ->limit($map_list)
    ->order($model['order'])
    ->select();
    return $data;
}
function shuffle_assoc($list) {   
  if (!is_array($list)) return $list;   
  $keys = array_keys($list);   
  shuffle($keys);   
  $random = array();   
  foreach ($keys as $key)   
    $random[$key] = $list[$key];   
  return $random;   
}   
//获取推广员父类id
function get_fu_id($id){
    $map['id']=$id;
    $pro=M("promote","tab_")->where($map)->find();
    if(null==$pro||$pro['parent_id']==0){
        return 0;
    }else{
        return $pro['parent_id'];
    }
}
function get_parent_name($id){
    $map['id']=$id;
    $pro=M("promote","tab_")->where($map)->find();
    if(null!=$pro&&$pro['parent_id']>0){
        $pro_map['id']=$pro['parent_id'];
        $pro_p=M("promote","tab_")->where($pro_map)->find();
        return $pro_p['account'];
    }else if($pro['parent_id']==0){
        return $pro['account'];
    }else{
        return false;
    }
}
/**
 * [用于统计排行  给根据某一字段倒序 获得的结果集插入排序字段 ]
 * @param  [type] $arr [description]
 * @return [type]      [description]
 */
function array_order($arr){
    foreach ($arr as $key => $value) {
        $arr[$key]['rand']=++$i;
    }
    return $arr;
}
/**
*将时间戳装成年月日(不同格式)
*@param  int    $time 要转换的时间戳 
*@param  string $date 类型 
*@return string 
*/
function set_show_time($time = null,$type='time',$tab_type=''){
    $date = "";
    switch ($type) {
        case 'date':
            $date = date('Y-m-d ',$time);
            break;
        case 'time':
            $date = date('Y-m-d H:i',$time);
            break;
        default:
            $date = date('Y-m-d H:i:s',$time);
            break;
    }
    if(empty($time)){//若为空  根据不同情况返回
        if($tab_type==''){
            return "暂无登陆";
        }
        if($tab_type=='forever'){
            return "永久";
        }
        if($tab_type=='other'){
            return "";
        }
    }
    return $date;
}
//判断支付设置
//yyh
function pay_set_status($type){
    $sta=M('tool','tab_')->field('status')->where(array('name'=>$type))->find();
    return $sta['status'];
}
//获取微信支付类型 0官方 1 威富通
function get_wx_type(){
    if(MODULE_NAME=='Media'||MODULE_NAME=='Media2'||MODULE_NAME=='Home'){
        $map['name']=array('in',array('wei_xin','weixin','weixin_gf'));
    }elseif(MODULE_NAME=='Sdk'){
        $map['name']=array('in',array('wei_xin_app','weixin','weixin_gf'));
    }elseif(MODULE_NAME=='App'){
        $map['name']=array('in',array('wei_xin_apps','weixin_app'));
    }
    $type=M('Tool','tab_')->where($map)->select();
    foreach ($type as $k => $v) {
    if($v['status']==1){
    $name=$v['name'];
    }
    }
    if($name=="weixin"){
        return 1;
    }elseif($name=="weixin_gf"){
        return 1;
    }else{
        return 0;
    }
}


/**
 * 威富通选择
 * @return [type] [中信 1 官方 2]
 */
function wft_status(){
        $map['name']=array('like','%weixin%');
        $map['status']=1;
        $type=M('Tool','tab_')->where($map)->find();
        if($type['name']=="weixin"){
            return 1;
        }else{
            return 2;
        }

}

//app微信支付类型
function get_weixin_type(){
    $map['name']=array('like','%wei%');
    $type=M('Tool','tab_')->where($map)->select();
    foreach ($type as $k => $v) {
        if($v['status']==1){
            $name=$v['name'];
        }
    }
    return $name=="wei_xin_apps"?0:1;
}
//查询uc用户是否存在该平台
function find_uc_account($name){
    $map['account']=trim($name);
    $user=M('user','tab_')->where($map)->find();
    if(null==$user){
        return false;
    }else{
        return $user;
    }   
}

 /**
*检查链接地址是否有效
*/
function varify_url($url){  
    $check = @fopen($url,"r");  
    if($check){  
     $status = true;  
    }else{  
     $status = false;  
    }    
    return $status;  
} 
//获取当前推广员id
function get_pid()
{
    return $_SESSION['onethink_home']['promote_auth']['pid'];
}

// //计算数组个数用于模板
function arr_count1($string){
    if($string){
        $arr=explode(',',$string);
        $cou=count($arr);
    }else{
        $cou=0;
    }
    return $cou;
}

/**
 *获取推广员子账号
 */
function get_prmoote_chlid_account($id=0){
    $promote = M("promote","tab_");
    $map['status'] = 1;
    $map["parent_id"] = $id;
    $data = $promote->where($map)->select();
    if(empty($data)){return "";}
    return $data;
}

/**
 *获取推广员父类账号  改写
 *@param  $promote_id 推广id
 *@param  $isShow bool
 *@return string
 *@author yyh
 */
function get_parent_promote_($prmote_id=0,$isShwo=true)
{
    $promote = M("promote","tab_");
    $map['id'] = $prmote_id;//本推广员的id
    $data1 = $promote->where($map)->find();//本推广员的记录
    if(empty($data1)){return false;}
    if($data1['parent_id']==0){return false;}
    if($data1['parent_id']){
        $map1['id']=$data1['parent_id'];
    }
    $data = $promote->where($map1)->find();//父类的记录
    $result = "";
    if($isShwo){
        $result = "[{$data['account']}]";
    }
    else{
        $result = $data['account'];
    }
    return $result;
}

//获取当前子渠道
function get_zi_promote_id($id){
    $map['parent_id']=$id;
    $pro=M("promote","tab_")->field('id')->where($map)->select();
    if(null==$pro){
        return 0;
    }else{
        for ($i=0; $i <count($pro); $i++) {
            $sd[]=implode(",", $pro[$i]);
        }
        return  implode(",", $sd);
    }
}

/**
 * 折扣信息
 * @param $data
 * @return mixed
 */
function discount_data($data){
    if($data['recharge_status'] != 1){
        $game = M('promote_welfare','tab_')->find($data['game_id']);
        $game_discount = $game['discount'];
        $data['promote_discount'] = $game_discount;
    }
    if($data['promote_status'] != 1 || empty($data['first_discount'])){
        $data['first_discount'] = 10;
    }
    if($data['cont_status'] != 1 || empty($data['continue_discount'])){
        $data['continue_discount'] = 10;
    }
    return $data;
}

/**
 *设置状态文本
 */
function get_status_text($index=1,$mark=1){
    $data_text = array(
        0  => array( 0 => '失败' ,1 => '成功'),
        1  => array( 0 => '锁定' ,1 => '正常'),
        2  => array( 0 => '未申' ,1 => '已审' , 2 => '拉黑'),
        3  =>array(0=>'不参与',1=>'已参与'),
        4 => ['系统','上级渠道'],
    );
    return $data_text[$index][$mark];
}


//获取推广员帐号
function get_promote_account($promote_id){
    if($promote_id == 0){
        return '自然注册';
    }elseif ($promote_id==-1){
        return '全平台用户';
    }
    $data = M('promote','tab_')->find($promote_id);
    $account = empty($data['account']) ? '系统' : $data['account'];
    return $account;
}

/**
 * 获取渠道等级
 * @param $promote_id
 * @return mixed
 */
function get_promote_level($promote_id){
    $model = M('promote','tab_');
    $map['id'] = $promote_id;
    $data = $model->where($map)->find();
    if(empty($data)){
        return '';
    }
    if($data['parent_id'] == 0) {
        return '1';
    }else{
        return 2;
    }
}
/**
 * [获取游戏原包数据]
 * @param  [type] $and_id [安卓版本id]
 * @param  [type] $ios_id [苹果版本id]
 * @return [type]         [description]
 * @author [yyh] <[email address]>
 */
function get_game_source($and_id,$ios_id){
    $model = M('Game_source','tab_');
    if($and_id&&$ios_id){
        $map['game_id']=array('in',$and_id.','.$ios_id);
    }else if($and_id||$ios_id){
        if($and_id){
            $map['game_id']=$and_id;
        }else{
            $map['game_id']=$ios_id;
        }
    }else{
        return false;
    }
    $data=$model->where($map)->select();
    foreach ($data as $key => $value) {
        if($value['game_id']==$and_id){
            $dataa['and_id']=$data[$key];
        }
        if($value['game_id']==$ios_id){
            $dataa['ios_id']=$data[$key];
        }
    }
    return $dataa;
}

/**
 * [获取扩展状态]
 * @param  [type] $name [description]
 * @return [type]       [description]
 */
function get_tool_status($name){
    $map['name']=$name;
    $tool=M("tool","tab_")->where($map)->find();
    return $tool['status'];
}
//二级推广员id
function get_child_ids($id){
        $map1['parent_id']=$id;
        $map1['id']=$id;
        $map1['_logic']='OR';
        $arr1=M('promote','tab_')->where($map1)->field('id')->select();
        if($arr1){
            return $arr1;
        }else{
            return false;
        }
        
    }
//获取图片连接
function icon_url($value){
   if (get_tool_status("oss_storage") == 1){
    $url=get_cover($value, 'path');
    }elseif(get_tool_status("qiniu_storage") == 1){
    $url=get_cover($value, 'path');
    }elseif(get_tool_status("cos_storage") == 1){
    $url=get_cover($value, 'path');
    }else{
    $url='http://' . $_SERVER['HTTP_HOST'] . get_cover($value, 'path');
    }
    return $url;
}

//获取广告数据
function get_adv_data($pos_name){
    $adv = M("Adv","tab_");
    $map['tab_adv.status'] = 1;
    $map['tab_adv.start_time']=array(array('lt',time()),array('eq',0), 'or') ;
    $map['tab_adv.end_time']=array(array('gt',time()),array('eq',0), 'or') ;
    $data = $adv
        ->field('tab_adv.*,p.width,p.height')
        ->where($map)
        ->join("right join  tab_adv_pos as p on p.name = '{$pos_name}' and p.id = tab_adv.pos_id")
        ->order('sort desc')
        ->select();
    return $data;
}

//获取指定文件夹内文件数量
function getfilecounts($ff){
 $dir = './'.$ff;
 $handle = opendir($dir);
 $i = 0;
 while(false !== $file=(readdir($handle))){
  if($file !== '.' && $file != '..')
  {
    $i++;
   }
 }
 closedir($handle);
return $i;
}


function get_iospack_status($game_id,$promote_id){
    /*$map['channelid']=$promote_id;
    $map['game_id']=$game_id;
    $find=M('iospacket')->where($map)->find();
    if(null==$find){
        return 0;
    }elseif ($find['status']!=="0"&&$find['status']!=="2") {
        return 0;
    }elseif ($find['status']!=="0"&&$find['status']=="2") {
        return 2;
    }
    elseif ($find['status']=="0") {
        return 1;
    }*/
    $map['game_id'] = $game_id;
    $map['promote_id'] = $promote_id;

    $find = M('Apply','tab_')->where($map)->find();
    if (null == $find){
        return 0;
    }elseif ($find['down_status'] == 0){
        return 1;
    }elseif ($find['down_status'] == 1){
        return 2;
    }
}



/**
 * 获取所有开发者信息
 * @return [type] [description]
 */
function get_developers_list(){
    $list = M("developers","tab_")->where("status=1")->select();
    if (empty($list)){return '';}
    return $list;
}


//sdk加密方法
function get_ss($key){
    $verfy_key="gnauhcgnem";
    $len=strlen($key);
    $res="";
    for ($i=0; $i <$len; $i++) { 
        if($i<11){
            $a=0;
        }else{
            $a=-1;
        }
        $res.=chr(ord($key[$i])^ord($verfy_key[$i%10+$a]));
    }
    return base64_encode($res);
}


// lwx 获取游戏类型名称

function get_game_type_name($type = null){
	if(!isset($type) || empty($type)){
		return '全部';
	}
	$cl = M("game_type","tab_")->where("id={$type}")->limit(1)->select();
	return $cl[0]['type_name'];
}

/**
 * 获取退款商户订单
 * @param  [type] $order [description]
 * @return [type]        [description]
 */
function get_refund_pay_order_number($order)
{
    $map['order_number']=$order;
    $find=M('refund_record','tab_')->where($map)->find();
    return $find['pay_order_number'];
}

/* 获取时间闭合区间 @author 鹿文学 */
function period($flag=0,$opposite=true) {/* 0:今日，1：昨天 4:本周 5:上周 8:本月 9:上月*/
    switch($flag) {
        case 0:
        case 1:{
            $start = mktime(0,0,0,date('m'),date('d')-$flag,date('Y'));
            $end = mktime(0,0,0,date('m'),date('d')-$flag+1,date('Y'))-1;
        };break;
        case 3:
        case 7:{
            $start = mktime(0,0,0,date('m'),date('d')-$flag,date('Y'));
            $end = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        };break;
        case 4:{
            $start = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
            $end = mktime(0,0,0,date('m'),date('d')-date('w')+8,date('Y'))-1;
        };break;
        case 5:{
            $start = mktime(0,0,0,date('m'),date('d')-date('w')-6,date('Y'));
            $end = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'))-1;
        };break;
        case 8: {
            $start = mktime(0,0,0,date('m'),1,date('Y'));
            $end = mktime(0,0,0,date('m')+1,1,date('Y'))-1;
        };break;
        case 9: {
            $start = mktime(0,0,0,date('m')-1,1,date('Y'));
            $end = mktime(0,0,0,date('m'),1,date('Y'))-1;
        };break;
    }
    if ($opposite)
        return array(array('egt',$start),array('elt',$end));
    else
        return array(array('elt',$start),array('egt',$end));
}



/**
 * 获取游戏原包路径
 * @return [type] [description]
 * @author zc 894827077@qq.com
 */
function get_game_source_file_url($game_id){
    $map['game_id']=$game_id;
    $find=M('game_source','tab_')->field('file_url')->where($map)->find();
    return ROOTTT.ltrim($find['file_url'],'./');
}



 /**
  * 获取正在打包的数量
  * @return [type] [description]
  * @author zc 894827077@qq.com
  */
 function get_enable_status_count(){
    $map['enable_status']=3;
    $count=M('apply','tab_')->where($map)->count();
    return $count;
 }





?>

