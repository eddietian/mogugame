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


/*
*获取游戏设置信息
*/
function get_game_set_info($game_id = 0){
	$game = M('GameSet','tab_');
	$map['game_id'] = $game_id;
	$data = $game->where($map)->find();
	return $data;
}

/**  
 * 对数据进行编码转换  
 * @param array/string $data       数组  
 * @param string $output    转换后的编码  
 */  
function array_iconv($data,  $output = 'utf-8') {  
    $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');  
    $encoded = mb_detect_encoding($data, $encode_arr);  
  
    if (!is_array($data)) {  
        return mb_convert_encoding($data, $output, $encoded);  
    }  
    else {  
        foreach ($data as $key=>$val) {  
            $key = array_iconv($key, $output);  
            if(is_array($val)) {  
                $data[$key] = array_iconv($val, $output);  
            } else {  
            $data[$key] = mb_convert_encoding($data, $output, $encoded);  
            }  
        }  
    return $data;  
    }  
}

/**
 * 获取游戏appstor上线状态
 * @param $game_id 游戏id
 * @return mixed appstatus 上线状态
 * @author zhaochao
 */
function get_game_appstatus($game_id){
    $map['id']=$game_id;
    $game=M('game','tab_')->where($map)->find();
    if($game['sdk_version']==2&&$game['appstatus']==1){
        return true;
    }elseif($game['sdk_version']==2&&$game['appstatus']==0){
        return false;
    }elseif($game['sdk_version']==1){
        return true;
    }

}