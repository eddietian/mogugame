<?php
// +----------------------------------------------------------------------
// | 徐州梦创信息科技有限公司—专业的游戏运营，推广解决方案.
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.vlcms.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: kefu@vlcms.com QQ：97471547
// +----------------------------------------------------------------------


/**
*写入txt文件
*/
/*function wite_text($txt,$name){
    $myfile = fopen($name, "w") or die("Unable to open file!");
    fwrite($myfile, $txt);
    fclose($myfile);
}*/
function think_md5($str, $key = 'ThinkUCenter'){
    return '' === $str ? '' : md5($str.$key);
}

/*
*获取游戏设置信息
*/
function get_game_set_info($game_id = 0){
    $game = M('GameSet','tab_');
    $map['game_id'] = $game_id;
    $data = $game->where($map)->find();
    return $data;
}

function get_game_entity($game_appid){
    $model = M('game','tab_');
    $map['game_appid'] = $game_appid;
    $data = $model->where($map)->find();
    return $data;
}

function sdk_game_entity($game_appid){
    $model = M('game','tab_');
    $map['game_appid'] = $game_appid;
    $data = $model->where($map)->find();
    return $data['id'];
}

/**
*根据推广员id获取推广员名称
*/
function get_promote_ParentID($promote_id){
    $model = M("Promote",'tab_');
    $map["id"] = $promote_id; 
    $reg = $model->where($map)->find();
    return $reg["parent_id"];
}

/**
*根据用户名获取用户id
*/
function get_user_id($account){
    $model = M("user",'tab_');
    $map["account"] = $account; 
    $reg = $model->where($map)->find();
    if($reg){
     return $reg["id"];    
    }else{
        return 0;
    }
    
}
//通过手机号获取用户id
function get_user_id_phone($phone)
{
    $map['phone']=$phone;
    $user=M("user","tab_")->where($map)->find();
    if($user){
   return $user['id'];
    }else{
    return false;
    }
}

/*function wite_text($txt,$name){
    $myfile = fopen($name, "w") or die("Unable to open file!");
    fwrite($myfile, $txt);
    fclose($myfile);
}
*/
//根据id获取游戏原包路径
function get_source_path($game_id){
    $model = M('gamesource');
    $map['game_id'] = $game_id;
    $res = $model->where($map)->find();
    return $res['path'];
}
 function get_cname($id)
{
     $model = M('opentype','tab_');
    $map['id'] = $id;
    $res = $model->where($map)->find();
    return $res['open_name'];
}
function check_order($order_number,$pay_order_number){
    if(empty($order_number)||empty($pay_order_number)){
          return false;
    }   
    $map['order_number']=$order_number;
    $map['pay_order_number']=$pay_order_number;
    $pri=M("deposit","tab_")->where($map)->find();
    if($pri){
        return false;
    }else{
        return true;
    }

}

function get_game_icon_id($id)
{
    $map['id']=$id;
    $data=M("game","tab_")->where($map)->find();
    return $data['icon'];
}
/**
 * [获取游戏版本]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function game_version($id){
    $game=M('game','tab_');
    $map['id']=$id;
    $data=$game->where($map)->find();
    if($data['id']==null){
        return false;
    }
    return $data['version'];
}
// 获取IOS游戏名称
function get_ios_game_name($game_id=null,$field='id'){
    $map[$field]=$game_id;
    $map['game_version']=0;
    $data=M('Game','tab_')->where($map)->find();
    if(empty($data)){return false;}
    $game_name=explode("(", $data['game_name']);
    return $game_name[0];
}
function get_img_url($cover_id){
	if(empty($cover_id)){
		return "";
	}
	$picture = M('Picture')->where(array('status'=>1))->getById($cover_id);
	if (get_tool_status("oss_storage") == 1) {
		if(!empty($picture['oss_url'])){
			return $picture['oss_url'];
		}else{
			return 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__.$picture['path'];
		}
	}elseif(get_tool_status("qiniu_storage") == 1){
		if(!empty($picture['url'])){
			return $picture['url'];
		}else{
			return 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__.$picture['path'];
		}
	}elseif(get_tool_status("cos_storage") == 1){
		if(!empty($picture['url'])){
			return $picture['url'];
		}else{
			return 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__.$picture['path'];
		}

	}elseif(get_tool_status("bos_storage") == 1){
          if(!empty($picture['bos_url'])){
           return $picture['bos_url'];
        }else{
            return 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__.$picture['path'];
        }
      }else{
		return 'http://' . $_SERVER['HTTP_HOST'] .__ROOT__.$picture['path'];
	    }
}
