<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Media\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class BaseController extends HomeController {

	/* public function __construct(){
	  	parent::__construct();
    	// 右上角广告
		$single_img = M('adv','tab_')->where('pos_id=2 and status=1')->find();
		$single_img['data'] =__ROOT__. get_cover($single_img['data'],'path'); 
		$this->assign("single_img",$single_img);
    } */
	/**
	*搜索
	*/
	public function search(){ 
	}

	protected function lists($model=null,$p=0){
		header("Content-type: text/html; charset=utf-8");
		$page = intval($p);
                $page = $page ? $page : 1; //默认显示第一页数据
		$game  = M($model['m_name'],$model['prefix']);
		$map = $model['map'];
		$row = 10;
		$data  = $game->where($map)->order($model['order'])->group($model['group'])->page($page, $row)->select();
		$count = count($game->where($map)->group($model['group'])->select());
		//分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        if(isset($model['group'])&&$model['group']='relation_game_id'){
        	unset($model['map']['sdk_version']);
            $data=game_merge($data,$model['map']);
        }
        $this->assign("count",$cuont);
        $this->assign('list_data', $data);
        $this->display($model['tmeplate_list']);
	}

	protected function join_list($model,$p){
		$page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $row=10;
		$game  = M($model['m_name'],$model['prefix']);
		$map = $model['map'];
		$data  = $game
		->field($model['field'])
		->join($model['join'])
		->where($map)
		->order($model['order'])
		->page($page,$row)
		->select();
		$count = $game->join($model['join'])->where($map)->count();
		//分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $this->assign("count",$cuont);
        $this->assign('list_data', $data);
        $this->display($model['tmeplate_list']);
	}

	protected function list_data($model){
		$game  = M($model['m_name'],$model['prefix']);
		$map = $model['map'];
		$data  = $game
		->field($model['field'])
		->limit($model['limit'])
		->where($map)
		->group($model['group'])
		->order($model['order'])
		->select();
		return $data;
	}

	protected function join_data($model){
		$game  = M($model['m_name'],$model['prefix']);
		$map = $model['map'];
		$data  = $game
		->field($model['field'])
		->join($model['join'])
		->limit($model['limit'])
		->where($map)->group($model['group'])->order($model['order'])->select();
		return $data;
	}



	protected function is_login(){
		$user = session('member_auth');
		if (empty($user)) {
		    return 0;
		} else {
		    return session('member_auth_sign') == data_auth_sign($user) ? $user['mid'] : 0;
		}
	}

	protected function entity($id){
		$data = M('User','tab_')->find($id);
		if(empty($data)){
			return false;
		}
		return $data;
	}

	

	protected function autoLogin($uid){
		$user =$this->entity($uid);
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'mid'             => $user['id'],
            'account'		  => $user['account'],
            'nickname'        => $user['nickname'],
            'balance'         => $user['balance'],
            'last_login_time' => $user['login_time'],
        );
        session('member_auth', $auth);
        session('member_auth_sign', data_auth_sign($auth));
	}

	public function showlist($model,$num=10) {
		if ($num==-1) $num="";
		if($model['field']) $field = $model['field'];
		else $field = true;
		$mo = D($model['model']);
		$list = $mo->field($field)->join($model['joins'])->where($model['where'])->order($model['order'])->limit($num)->select();
		return $list;
	}

	public function detail($model) {
		$mo = D($model['model']);
		$data = $mo->where($model['dwhere'])->order($model['dorder'])->find();
		return $data;
	}

}
