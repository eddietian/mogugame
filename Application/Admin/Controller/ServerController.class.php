<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ServerController extends ThinkController {
	const model_name = 'Server';

    public function lists(){
        if(isset($_REQUEST['show_status'])){
            $extend['show_status']=$_REQUEST['show_status'];
            unset($_REQUEST['show_status']);
        }
        if(isset($_REQUEST['server_version'])){
            $extend['server_version']=$_REQUEST['server_version'];
            unset($_REQUEST['server_version']);
        }
        if(isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])){
            $extend['start_time']  =  array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }elseif(isset($_REQUEST['time-start'])){
            $extend['start_time']=array('EGT',strtotime($_REQUEST['time-start']));
        }elseif(isset($_REQUEST['time-end'])){
            $extend['start_time']=array('ELT',strtotime($_REQUEST['time-end']));
        }
        if(isset($_REQUEST['start']) && isset($_REQUEST['end'])){
            $extend['start_time']  =  array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $extend['game_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        $extend['show_status']=1;
    	parent::order_lists(self::model_name,$_GET["p"],$extend);
    }

    public function add(){
    	$model = M('Model')->getByName(self::model_name);
    	parent::add($model["id"]);
    }

    public function edit($id=0){
		$id || $this->error('请选择要编辑的用户！');
        // var_dump($_REQUEST);exit;
		$model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
		parent::edit($model['id'],$id);
    }

    public function del($model = null, $ids=null){
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }

    //批量新增
    public function batch1(){
        if(IS_POST){
            $server_str = str_replace(array("\r\n", "\r", "\n"), "", I('server'));
            $server_ar1 = explode(';',$server_str);
            array_pop($server_ar1);
            $num = count($server_ar1);
            if($num > 100 ){
                $this->error('区服数量过多，最多只允许添加100个！');
            }
            $verify = ['game_id','server_name','time'];
            foreach ($server_ar1 as $key=>$value) {
                $arr = explode(',',$value);
                foreach ($arr as $k=>$v) {
                    $att = explode('=',$v);
                    if(in_array($att[0],$verify)){
                        switch ($att[0]){
                            case 'time' :
                                $time = $server[$key]['start_time'] = strtotime($att[1]);
                                if($time < time()){
                                    $this->error('开服时间不能小于当前时间');
                                }
                                break;
                            case 'game_id':
                                $game = M('Game','tab_')->find($att[1]);
                                if(empty($game)){
                                    $this->error('游戏ID不存在');
                                }
                                $server[$key]['game_id'] = $att[1];
                                break;
                            default:
                                $server[$key][$att[0]] = $att[1];
                        }
                    }
                }
                $server[$key]['game_name'] = get_game_name($server[$key]['game_id']);
                $server[$key]['server_num'] = 0;
                $server[$key]['recommend_status'] = 1;
                $server[$key]['show_status'] = 1;
                $server[$key]['stop_status'] = 0;
                $server[$key]['server_status'] = 0;
                $server[$key]['parent_id'] = 0;
                $server[$key]['create_time'] = time();
                $version = get_sdk_version($server[$key]['game_id']);
                $server[$key]['server_version'] = empty($version) ? 0 : $version;
            }
            $res = M('server','tab_')->addAll($server);
            if($res !== false){
                $this->success('添加成功！',U('Server/lists'));
            }else{
                $this->error('添加失败！'.M()->getError());
            }
        }else{
            $this->meta_title = '新增区服管理';
            $this->display();
        }
    }

    //批量新增
    public function batch(){
        if(IS_POST){
            $server_str = str_replace(array("\r\n", "\r", "\n"), ";", I('server'));
            $server_str = str_replace(array(";;"), ";", $server_str);
            $server_ar1 = explode(';',$server_str);
            array_pop($server_ar1);
            $num = count($server_ar1);
            if($num > 100 ){
                $this->error('区服数量过多，最多只允许添加100个！');
            }

            $server = array();
            //小精灵物语	2017-10-02    11:00:00	15	打猎区
            foreach($server_ar1 as $key => $value) {
                $value = str_replace("    ", "	", $value);
                $arr = explode("	", $value);

                $gameinfo = M('Game','tab_')->where(array('game_name'=> $arr[0]."(安卓版)"))->find();

                if (empty($gameinfo)) {
                    continue;
                }
                $gameid = $gameinfo['id'];
                $game_name = $gameinfo['game_name'];
                $start_time = trim($arr[1])." ".trim($arr[2]);
                $desride = trim($arr[3]);
                $server_name = trim($arr[4]);

                $server[$key]['game_id'] = $gameid;
                $server[$key]['start_time'] = strtotime($start_time);
                $server[$key]['desride'] = $desride;

                $server[$key]['game_name'] = $game_name;
                $server[$key]['server_name'] = $server_name;
                $server[$key]['server_num'] = 0;
                $server[$key]['recommend_status'] = 1;
                $server[$key]['show_status'] = 1;
                $server[$key]['stop_status'] = 0;
                $server[$key]['server_status'] = 0;
                $server[$key]['parent_id'] = 0;
                $server[$key]['create_time'] = time();
                $version = get_sdk_version($server[$key]['game_id']);
                $server[$key]['server_version'] = empty($version) ? 0 : $version;


            }
            $server = array_values($server);

            $res = M('server','tab_')->addAll($server);
            if($res !== false){
                $this->success('添加成功！',U('Server/lists'));
            }else{
                $this->error('添加失败！'.M()->getError());
            }
        }else{
            $this->meta_title = '新增区服管理';
            $this->display();
        }
    }
}
