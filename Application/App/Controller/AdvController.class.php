<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/8
 * Time: 11:25
 */

namespace App\Controller;

use App\Model\AdvModel;

class AdvController extends BaseController{

	/**
	 * 广告轮播图
	 * author: xmy 280564871@qq.com
	 */
	public function get_slider(){
		$model = new AdvModel();
		$data = $model->getAdv("slider_app",5);
		if(empty($data)){
			$this->set_message(-1,"暂无数据");
		}else{
			$this->set_message(1,1,$data);
		}
	}
}