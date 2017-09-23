<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/8
 * Time: 15:21
 */

namespace App\Controller;

use Admin\Model\PictureModel;

class FilesController extends BaseController{

	/**
	 * 上传图片
	 * @author huajie <banhuajie@163.com>
	 */
	private function uploadPicture(){

		/* 调用文件上传组件上传文件 */
		$Picture = new PictureModel();
		$pic_driver = C('PICTURE_UPLOAD_DRIVER');
		$info = $Picture->upload(
			$_FILES,
			C('PICTURE_UPLOAD'),
			C('PICTURE_UPLOAD_DRIVER'),
			C("UPLOAD_{$pic_driver}_CONFIG")
		); //TODO:上传到远程服务器
		/* 记录图片信息 */
		if($info){
			$return['status'] = 1;
			$return['file'] = $info;
		} else {
			$return['status'] = 0;
			$return['info']   = $Picture->getError();
		}
		ob_clean();
		/* 返回JSON数据 */
		return $return;
	}


	/**
	 * 上传头像
	 * @param $token
	 * author: xmy 280564871@qq.com
	 */
	public function upload_head($token=""){
		$this->auth($token);
		$result = $this->uploadPicture();
		if($result['status'] == 1){
			$file = $result['file'];
			if(isset($file['head_img']['id'])){
				$head = $file['head_img']['id'];
			}else{
				$map['md5'] = $file['head_img']['md5'];
				$picture = M('Picture')->where($map)->find();
				$head = $picture['id'];
			}
			//上传成功，写入用户数据
			D("User")->where(["account"=>USER_ACCOUNT])->setField(['head_img'=>$head]);
			$this->set_message(1,"上传成功",get_img_url($head));

		}else{
			$this->set_message(-1,"上传失败：".$result['info']);
		}
	}
}