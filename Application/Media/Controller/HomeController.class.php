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
class HomeController extends Controller {

	public $_NAV_PARAMS = array();

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}


    protected function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
    }
    
    public function __construct(){
	  	parent::__construct();
    	// 右上角广告
		$single_img = M('adv','tab_')->where('pos_id=2 and status=1')->find();
		$single_img['data'] =__ROOT__. get_cover($single_img['data'],'path'); 
		$this->assign("single_img",$single_img);
    }

	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
	}

	/**
	 * 获取html=>content并格式化数组
	 * @params array $keyList
	 */
	protected function getHtmlContent($keyList) {
		if (empty($keyList)) {
			return array();
		}

		$keyList = implode(",", $keyList);

		$htmlModel = M("html", "sys_");
		$map = array();
		$map['key']  = array('in',$keyList);

		$navinfo = $htmlModel->where($map)->select();
		if (empty($navinfo)) {
			return array();
		}
		$result = array();
		foreach ($navinfo as $v) {
			$result[$v['key']] = $v;
		}

		return $result;

	}

	/**
	 * 通过游戏id获取信息
	 */
	protected function getGameByGameids($gameids) {
		if (empty($gameids)) {
			return array();
		}
		$gameids = implode(",", $gameids);
		$gameModel = M("Game", "tab_");
		$map = array();
		$map['id'] = array('in', $gameids);

		$gameinfo = $gameModel->where($map)->select();

		if (empty($gameids)) {
			return array();
		}

		$result = array();
		foreach ($gameinfo as $v) {
			$result[$v['id']] = $v;
			$result[$v['id']]['gamename'] = preg_replace('/(\(.+)/i','',$v['game_name']);
		}

		return $result;
	}
}
