<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class RebateController extends ThinkController {
    //private $table_name="Game";
    const model_name = 'rebate';

    /**
    *返利设置列表
    */
    public function lists(){
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $extend['game_name'] = $_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        if(isset($_REQUEST['status'])){
            if($_REQUEST['status']=='全部'){
                unset($_REQUEST['status']);
            }else{
                $extend['status'] = $_REQUEST['status'];
                unset($_REQUEST['status']);
            }
        }
        /*if(isset($_REQUEST['game_appid'])){
            $extend['game_appid'] = array('like','%'.$_REQUEST['game_appid'].'%');
            unset($_REQUEST['game_appid']);
        }*/
        parent::order_lists(self::model_name,$_GET["p"],$extend);
    }

    public function add()
    {
        if (IS_POST) {
            $rebate = D('rebate');
            $data = $rebate->create();
            if(!$data || !$rebate->check_promote()){
                $this->error($rebate->getError());
            }
            !empty(I('endtime')) || $data['endtime'] = 0;
            $res = $rebate->add($data);
            if($res !== false){
                $this->success('添加成功!',U('lists'));
            }else{
                $this->error('添加失败!');
            }
        } else {
            $this->meta_title = '新增游戏返利';
            $this->display();
        }
    }

    public function edit()
    {
        $rebate = D('rebate');
        $id = $_REQUEST['id'];
        if (IS_POST) {
            if ($rebate->create() &&  $rebate->save()) {
                $this->success("编辑成功", U("lists"));
            } else {
                $this->error("编辑失败".$rebate->getError());
            }
        } else {
            $map['id'] = $id;
            $lists = $rebate->where($map)->find();
            $this->assign("data", $lists);
            $this->meta_title = '编辑游戏返利';
            $this->display();
        }
    }
        
    public function del($model = null, $ids=null) {
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }

}
