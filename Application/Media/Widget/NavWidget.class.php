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
 * 导航widget
 */

class NavWidget extends Controller{

	/**
	 * 显示导航
	 */
	public function showNav($navParams) {
		$defaultParams = array();
		if ($navParams) {
			array_push($defaultParams, $navParams);
		}

		$navstr = "";
		foreach ($defaultParams as $v) {
			$navstr .= "&nbsp;>&nbsp;";
			$navstr .= '<a href="'.$v['navlink'].'">'.$v['navname'].'</a>';
		}

		$this->assign('navstr', $navstr);
		$this->display('Base/nav');
	}

}
