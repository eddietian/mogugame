<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Media\Widget;
use Think\Controller;

/**
 * 分类widget
 * 用于动态调用分类信息
 */

class BaseWidget extends Controller{
	
	/* 显示指定分类的同级分类或子分类列表 */
	public function ranking(){
		$map = array('game_status'=>1);
		$game  = M('Game','tab_');
		$data  = $game->field(true)->limit(10)->where($map)->group('sibling_id')->order('sort asc')->select();
                $data=game_merge($data,$data['map']);
                foreach ( $data as $key => $vlaue){
                    $data[$key]['recommend_level']=$vlaue['recommend_level']*10;
                }
		$this->assign("list_rank",$data);
		$this->display('Base/ranking');
	}
	
}
