<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1078 2011-03-30 02:00:29Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class spendmodel {

	var $db;
	var $base;

	function __construct(&$base) {
		$this->spendmodel($base);
	}

	function spendmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function add_spend($user_id, $user_account,$user_nickname,$game_id,$game_appid,$game_name,$server_id,$server_name,$promote_id, $promote_account, $order_number, $pay_order_number,$pay_amount,$pay_time,$extend,$pay_way,$spend_ip,$sdk_version,$version,$platform,$pay_notify_url,$game_key) { 
		$this->db->query("INSERT INTO ".UC_DBTABLEPRE."spend SET user_id='$user_id', user_account='$user_account',user_nickname='$user_nickname',game_id='$game_id',game_appid='$game_appid',game_name='$game_name',server_id='$server_id',server_name='$server_name', promote_id='$promote_id', promote_account='$promote_account',order_number='$order_number',pay_order_number='$pay_order_number',pay_amount='$pay_amount',pay_time='$pay_time',pay_status=0,pay_game_status=0,extend='$extend',pay_way='$pay_way',spend_ip='$spend_ip',sdk_version='sdk_version',version='$version',platform='$platform',pay_notify_url='$pay_notify_url',game_key='$game_key' "); 
		$uid = $this->db->insert_id(); 

		return $uid; 
	}
	function wite_text($txt,$name){
    $myfile = fopen($name, "w") or die("Unable to open file!");
    fwrite($myfile, $txt);
    fclose($myfile);
	}
	function changeGamePayStatus($pay_order_number,$status=0){
		$pay_data = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."spend WHERE pay_order_number='$pay_order_number'");
		if(empty($pay_data)) {
			return false;
		}elseif($pay_data['pay_status']==0||$pay_data['version']!=1||$pay_data['pay_game_status']==1){
			return false;
		}
		$this->db->query("UPDATE ".UC_DBTABLEPRE."spend SET pay_game_status=1 WHERE pay_order_number='$pay_order_number'");
		return $this->db->affected_rows();
	}
	function edit_spend($pay_order_number,$pay_status=0) {
		$pay_data = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."spend WHERE pay_order_number='$pay_order_number'");
		if(empty($pay_data)) {
			return false;
		}elseif($pay_data['pay_status']==1||$pay_data['version']!=1){
			return false;
		}
		$sqladd="pay_status=".$pay_status;
		if($pay_status){
			$this->db->query("UPDATE ".UC_DBTABLEPRE."spend SET $sqladd WHERE pay_order_number='$pay_order_number'");
			if($this->db->affected_rows()){
				$md5_sign = md5($pay_data['pay_order_number'].$pay_data['pay_amount']."1".$pay_data['extend'].$pay_data['game_key']);
				$data = array(
					"out_trade_no" => $pay_data['pay_order_number'],
					"price"        => $pay_data['pay_amount'],
					"pay_status"   => 1,
					"extend"       => $pay_data['extend'],
					"signType"     => "MD5",
					"sign"         => $md5_sign
				);

				$result = $this->post($data,$pay_data['pay_notify_url']);
				

				if($result == "success"){
					$this->db->query("UPDATE ".UC_DBTABLEPRE."spend SET pay_game_status=1 WHERE pay_order_number='$pay_order_number'");
				}else{
					// \Think\Log::record("游戏支付通知信息：".$result.";游戏通知地址：".$game_data['pay_notify_url']);
				}
			}
		}
	}
	function find_spend($orderNo){
		$pay_data = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."spend WHERE pay_order_number='$orderNo'");
		if($pay_data){
			return $pay_data;
		}else{
			return false;
		}
	}
	function select_spend($page=1,$ppp=10,$param){
		$param=str_replace("\\",'',$param); 
		$totalnum= $this->db->fetch_all("select count(*) FROM ".UC_DBTABLEPRE."spend WHERE $param");
		$start = $this->base->page_get_start($page, $ppp, $totalnum[0]['count(*)']);
		$data = $this->db->fetch_all("SELECT * FROM ".UC_DBTABLEPRE."spend WHERE $param  order by id desc LIMIT $start, $ppp");
		$data['count']=$totalnum[0]['count(*)'];
		$data['totalpage']= $this->db->fetch_all("SELECT sum(pay_amount) as totalpage FROM".UC_DBTABLEPRE."spend WHERE $param and pay_status=1 order by id desc LIMIT $start, $ppp");

		$data['total']= $this->db->fetch_all("SELECT sum(pay_amount) as total FROM".UC_DBTABLEPRE."spend WHERE $param and pay_status=1");
		$data['total']=$data['total'][0]['total'];
		if(empty($data)) {
			return false;
		}else{
			return $data;
		}
	}

	 function post($param,$url){
    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置等待时间
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
    }
}

?>