<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/5
 * Time: 19:47
 */

namespace Admin\Controller;

use Admin\Model\PointShopModel;

class PointShopController extends ThinkController
{


	public function _initialize()
	{
		$this->meta_title = "积分商城";
		return parent::_initialize(); // TODO: Change the autogenerated stub
	}

	public function lists($p=1)
	{
		$model = new PointShopModel();
		$data = $model->getLists("","create_time desc",$p);
		$this->assign("data",$data['data']);

		//分页
		$count = $data['count'];
		$row = 10;
		if($count > $row){
			$page = new \Think\Page($count, $row);
			$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
			$this->assign('_page', $page->show());
		}

		$this->display();
	}

	public function add()
	{
		if (IS_POST) {
			$model = new PointShopModel();
			$result = $model->saveData();
			if ($result !== false) {
				$this->success("添加成功!", U('lists'));
			} else {
				$this->error("添加失败：" . $model->getError());
			}
		} else {
			$this->display();
		}
	}


	public function edit($id)
	{
		$model = new PointShopModel();
		if (IS_POST) {
			$result = $model->saveData($id);
			if ($result !== false) {
				$this->success("编辑成功!", U('lists'));
			} else {
				$this->error("编辑失败：" . $model->getError());
			}
		} else {
			$data = $model->getData($id);
			$this->assign("data",$data);
			$this->display();
		}
	}

	public function delete($ids){
		$model = new PointShopModel();
		$map['id'] = ['in',$ids];
		$result = $model->where($map)->delete();
		if ($result !== false){
			$this->success("删除成功!", U('lists'));
		}else{
			$this->error("删除失败");
		}
	}
}