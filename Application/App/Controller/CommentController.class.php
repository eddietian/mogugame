<?php
/**
 * 评论控制器
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/28
 * Time: 16:53
 */
namespace App\Controller;

class CommentController extends BaseController{


	/**
	 * 添加评论
	 * @param $comment  评论
	 * @param $game_id
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function add_comment($comment,$game_id,$token){
		$this->auth($token);
		$model = D("Comment");
		$result = $model->add_comment(USER_ACCOUNT,$game_id,$comment);
		if($result !== false){
			$this->set_message(1,"添加成功");
		}else{
			$this->set_message(-1,$model->getError());
		}
	}


	/**
	 * 获取评论
	 * @param $game_id
	 * @param int $p
	 * author: xmy 280564871@qq.com
	 */
	public function get_comment($game_id,$p=1){
		$model = D("Comment");
		$map['game_id'] = $game_id;
		$data = $model->getComment($map,$p);
		if(empty($data)){
			$this->set_message(-1,"暂无评论");
		}else{
			$this->set_message(1,1,$data);
		}
	}

	/**
	 * 我的评论
	 * @param $token
	 * @param int $p
	 * author: xmy 280564871@qq.com
	 */
	public function my_comment($token,$p=1,$game_name=""){
		$this->auth($token);
		if(!empty($game_name)){
			$game = D("Game")->where(['game_name'=>['like',"%".$game_name."%"]])->select();
			$map['game_id'] = ['in',array_column($game,"id")];
		}
		$model = D("Comment");
		$map['account'] = USER_ACCOUNT;
		$data = $model->getComment($map,$p);
		if(empty($data['data'])){
			$this->set_message(-1,"暂无评论");
		}else{
			$this->set_message(1,1,$data);
		}
	}
}