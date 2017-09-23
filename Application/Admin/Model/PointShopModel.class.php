<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/5
 * Time: 21:00
 */

namespace Admin\Model;

class PointShopModel extends TabModel{

	protected $_validate = [
		['good_name', 'require', '商品名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT],
		['price', 'require', '商品价格不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT],
		['price', 'number', '商品价格错误', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT],
		['number', 'number', '商品数量错误', self::VALUE_VALIDATE , 'regex', self::MODEL_INSERT],
		['good_info', 'require', '商品信息不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT],
		['good_type', 'require', '商品类型不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT],
	];

	protected $_auto = [
		['good_key', 'formatStr', self::MODEL_BOTH,'callback'],
		['create_time', 'time', self::MODEL_INSERT,'function'],
	];


	/**
	 * 获取列表
	 * @param string $map
	 * @param string $order
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getLists($map = "", $order = "create_time desc", $p = 1)
	{
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$data['data'] = $this
			->where($map)
			->order($order)
			->page($page, $row)
			->select();
		$data['count'] = $this->where($map)->count();
		return $data;
	}


	/**
	 * 数据保存
	 * @param string $id
	 * @return bool|mixed
	 * author: xmy 280564871@qq.com
	 */
	public function saveData($id=""){
		$data = $this->create();
		if(!$data){
			return false;
		}
		//计算激活码数量
		if($data['good_type'] == 2 && !empty($data['good_key'])){
			$data['number'] = $this->countJson($data['good_key']);
		}
		if(empty($id)){
			return $this->add($data);
		}else{
			return $this->where(['id'=>$id])->save($data);
		}
	}

	/**
	 * 数据格式化
	 * @param $str
	 * @return array|string
	 * author: xmy 280564871@qq.com
	 */
	public function formatStr($str){
		if (empty($str)){
			return $str;
		}
		$data = str2arr($str,"\r\n");
		$data = array_filter($data);//去空
		$result = json_encode($data);
		return $result;
	}


	/**
	 * 获取数据
	 * @param $id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getData($id){
		$data = $this->find($id);
		$good_key = json_decode($data['good_key']);
		$data['good_key'] = arr2str($good_key,"\r\n");
		return $data;
	}


	public function countJson($str){
		$good_key = json_decode($str);
		$num = count($good_key);
		return $num;
	}
}