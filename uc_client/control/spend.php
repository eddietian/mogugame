<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1082 2011-04-07 06:42:14Z svn_project_zhangjie $
*/

!defined('IN_UC') && exit('Access Denied');

class spendcontrol extends base {


	function __construct() {
		$this->spendcontrol();
	}

	function spendcontrol() {
		parent::__construct();
		$this->load('spend');
		$this->app = $this->cache['apps'][UC_APPID];
	}

	function onadd() {
		$this->init_input();
		$user_id = $this->input('user_id');
		$user_account =  $this->input('user_account');
		$user_nickname =  $this->input('user_nickname');
		$game_id =  $this->input('game_id');
		$game_appid =  $this->input('game_appid');
		$game_name =  $this->input('game_name');
		$server_id =  $this->input('server_id');
		$server_name = $this->input('server_name');
		$promote_id = $this->input('promote_id');
		$promote_account = $this->input('promote_account');
		$order_number = $this->input('order_number');
		$pay_order_number = $this->input('pay_order_number');
		$pay_amount = $this->input('pay_amount');
		$pay_time = $this->input('pay_time');
		$extend = $this->input('extend');
		$pay_way = $this->input('pay_way');
		$spend_ip = $this->input('spend_ip');
		$sdk_version = $this->input('sdk_version');
		$version = $this->input('version');
		$platform=$this->input('platform');
		$pay_notify_url=$this->input('pay_notify_url');
		$game_key=$this->input('game_key');
		$id = $_ENV['spend']->add_spend($user_id, $user_account,$user_nickname,$game_id,$game_appid,$game_name,$server_id,$server_name,$promote_id, $promote_account, $order_number, $pay_order_number,$pay_amount,$pay_time,$extend,$pay_way,$spend_ip,$sdk_version,$version,$platform,$pay_notify_url,$game_key);
		return $id;
	}
	function onfind(){
		$this->init_input();
		$pay_order_number=$this->input('pay_order_number');
		$data=$_ENV['spend']->find_spend($pay_order_number);
		return $data;
	}
	function onchange(){
		$this->init_input();
		$pay_order_number=$this->input('pay_order_number');
		$status=$this->input('status');
		$result=$_ENV['spend']->changeGamePayStatus($pay_order_number,$status);
		return $result;
	}
	function onedit() {
		$this->init_input();
		$pay_order_number = $this->input('pay_order_number');
		$pay_status=$this->input('pay_status');
		$pay_game_status=$this->input('pay_game_status');
		$status = $_ENV['spend']->edit_spend($pay_order_number, $pay_status,$pay_game_status);
		return $status;
	}
	function onselect(){
		$this->init_input();
		$page=$this->input('page');
		$ppp=$this->input('ppp');
		$param=$this->input('param');
		$data=$_ENV['spend']->select_spend($page,$ppp,$param);
		return $data;
	}
}

?>