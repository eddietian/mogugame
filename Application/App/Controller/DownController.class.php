<?php

namespace App\Controller;

use App\Model\GameModel;
use Think\Controller;

class DownController extends Controller {

	/**
	 * 游戏下载
	 * @param int $game_id
	 * @param int $promote_id
	 * author: xmy 280564871@qq.com
	 */
    public function down($game_id,$promote_id=0){
    	$data = D("Game")->getGameDownInfo($game_id);
    	$game_info = $data['game_info'];
    	if(empty($game_info)){
		    $this->set_message('-1','游戏不存在');
	    }
    	$packet = $data['packet'];

    	//判断系统
    	if(GameModel::ANDROID == $game_info['sdk_version']){
		    $down_url = $game_info['add_game_address'];
		    $file_url = $packet['file_url'];
	    }elseif(GameModel::IOS == $game_info['sdk_version']){
		    $down_url = $game_info['ios_game_address'];
		    $file_url = $packet['plist_url'];
	    }else{
		    $this->set_message('-1','游戏版本错误');
	    }

	    //优先第三方地址下载
	    //渠道下载 下载原包
	    if(!empty($down_url) && empty($promote_id)){
		    if(varify_url($down_url)){
		    	D('Game')->addGameDownNum($game_id);
			    $this->set_message('1',1,$down_url);
		    }else{
			    $this->set_message('-1','地址错误');
		    }

	    }elseif(!empty($packet)){//原包下载
		    D('Game')->addGameDownNum($game_id);
		    if(!empty($promote_id)){
			    $file_url = $this->package($packet,$promote_id);
		    }
		    $this->set_message(1,1,"http://".$_SERVER['HTTP_HOST'].$file_url);

	    }else{
		    $this->set_message(-1,"未上传原包");
	    }

    }

	/**
	 * 安卓打包渠道信息
	 * @param $source_info  原包信息
	 * @param $promote_id
	 * @return string
	 * author: xmy 280564871@qq.com
	 */
	public function package($source_info, $promote_id)
	{
		$file_path = $source_info['file_url'];
		//验证原包是否存在
		if (!file_exists($file_path)) {
			$this->set_message(-1,"原包不存在");
		} else {
			if($source_info['file_type']==1){
				$str_ver=".apk";
				$url_ver="META-INF/mch.properties";
			}else{
				$str_ver=".ipa";
			    $url_ver="Payload/TestSdkDemo.app/_CodeSignature/mch.txt";
			}
			$new_name = "game_package" . $source_info['game_id'] . "-" . $promote_id . $str_ver;
			$to = "./Uploads/TmpGamePack/" . $new_name;
			copy($source_info['file_url'], $to);
			#打包新路径
			$zip = new \ZipArchive();
			$zip_res = $zip->open($to, \ZipArchive::CREATE);
			if ($zip_res == TRUE) {
				#打包数据
				$pack_data = array( 
					"game_id" => $source_info["game_id"],
					"game_name" => $source_info['game_name'],
					"game_appid" => get_game_appid($source_info["game_id"], "id"),
					"promote_id" => $promote_id,
					"promote_account" => get_promote_account($promote_id),
				);
				$zip->addFromString($url_ver, json_encode($pack_data));
				$zip->close();
			}

			if($source_info['file_type']==2){
			$to = A('Plist')->create_plist($pack_data['game_id'],$pack_data['promote_id'],'',$to);
			}
			return $to;
		}
	}

	/**
	 * 返回输出
	 * @param int $status 状态
	 * @param string $return_msg   错误信息
	 * @param array $data           返回数据
	 * author: xmy 280564871@qq.com
	 */
	public function set_message($status, $return_msg = 0, $data = [])
	{
		$msg = array(
			"status" => $status,
			"return_code" => $return_msg,
			"data" => $data
		);
		echo json_encode($msg);
		exit;
	}

	/**
	 * 安卓下载
	 * author: xmy 280564871@qq.com
	 */
	public function android_app_down(){
		$app = M("app","tab_")->find(1);
		$this->down_file($app);
	}


	/**
	 * 下载
	 * @param $file
	 * author: xmy 280564871@qq.com
	 */
	protected function down_file($file){
		header('Content-Description: File Transfer');
		header("Content-Type: application/force-download;");
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename={$file['file_name']}");
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize(__ROOT__.$file['file_url']));
		header("Pragma: no-cache"); //不缓存页面
		ob_clean();
		flush();
		readfile($file['file_url']);
	}

}
