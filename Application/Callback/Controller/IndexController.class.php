<?php
namespace Callback\Controller;
use Common\Api\GameApi;
class IndexController extends BaseController {
    /**
    *通知方法
    */
    public function index()
    {
    	$order_info = array(
    		"out_trade_no"=>"SP_20160818205430AYu1",
    		"status"=>1,
    		"money"=>10
    	);
        $game = new GameApi();
        $result = $game->game_pay_notify($order_info,1);
        echo $result;
    }
}