<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}
//获取友情链接
function get_links(){
    $link=M('links','tab_');
    $data=$link->where(array('status'=>1,'mark'=>0))->select();
    if(empty($data)){return false;}
    return $data;
}
/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}
/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}



function get_game_introduction($g_id,$sum){
    $game = M('Info','tab_game_');
    $str = $game->field('introduction')->find($g_id);
    return msubstr(strip_tags($str['introduction']),0,$sum,'utf-8',false);
}


function get_gift_type($type=null){
    if(empty($type)){
        return false;
    }
    switch ($type) {
        case 1:  return '新手包'; break;
        case 2:  return '媒体包'; break;
        case 3:  return '其他包'; break;
        case 4:  return '公众礼包'; break;
        default: return false; break;
    }
}


/**
*获取用户账号
*/
function get_user_account($uid=null){
    if(empty($uid)){return false;}
    $user = M("user","tab_");
    $map['id'] = $uid;
    $data = $user->where($map)->find();
    if(empty($data['account'])){return false;}
    return $data['account'];
}
function get_user_play($id,$game_id='',$limit=''){
    if($id==''){
        return false;
    }
    $map['user_id']=$id;
    if($game_id!='')$map['game_id']=$game_id;
    if($limit!='')$limit=$limit;
    $map['user_id']=$id;
    $data=M('User_play','tab_')->where($map)->limit($limit)->select();
    if(empty($data)){
        return false;
    }else{
        return $data;
    }
}
