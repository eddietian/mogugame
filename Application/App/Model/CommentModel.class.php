<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/28
 * Time: 17:51
 */
namespace App\Model;

class CommentModel extends BaseModel{

	protected $_validate = [
		['password','255','评论太长',self::MODEL_BOTH,'length']
	];

	protected $_auto = [
		['create_time', 'time', self::MODEL_INSERT, 'function'],
		['status', 2, self::MODEL_INSERT],
	];

	/**
	 * 添加评论
	 * @param $account
	 * @param $game_id
	 * @param $comment
	 * @return bool|mixed
	 * author: xmy 280564871@qq.com
	 */
	public function add_comment($account,$game_id,$comment){
		$data['account'] = $account;
		$data['game_id'] = $game_id;
		$data['comment'] = $comment;
		$data = $this->create($data);
		if(empty($data)){
			return false;
		}
		$result = $this->add($data);
		return $result;
	}

	/**
	 * 获取评论
	 * @param $game_id
	 * @param int $p
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getComment($map,$p=0){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 10;
		$map['status'] = 1;
		$data = $this->field("account,create_time,comment,game_id")->where($map)->order("create_time desc")->page($page,$row)->select();
		foreach ($data as $key => $val){
			$user = D("User")->getUserInfo($val['account']);
			$data[$key]['nickname'] = $user['nickname'];
			$data[$key]['head_img'] = $user['head_img'];
		}
		$result['data'] = $data;
		$result['count'] = $this->where($map)->count();
		return $result;
	}
}