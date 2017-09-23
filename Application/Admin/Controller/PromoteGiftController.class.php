<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PromoteGiftController extends ThinkController {

    const model_name = 'PromoteGift';

    public function lists(){
        $extend = array('key'=>'gift_name');
        if(isset($_REQUEST['game_name'])){
                $extend['game_name']=trim($_REQUEST['game_name']);
                unset($_REQUEST['game_name']);
        }
        if(isset($_REQUEST['status'])){
             if($_REQUEST['status']=='全部'){
                unset($_REQUEST['status']);
            }else{
            $extend['status']=$_REQUEST['status'];
            unset($_REQUEST['status']);
            }
        }
        if(isset($_REQUEST['giftbag_version'])){
             if($_REQUEST['giftbag_version']=='全部'){
                unset($_REQUEST['giftbag_version']);
            }else{
            $extend['giftbag_version']=$_REQUEST['giftbag_version'];
            unset($_REQUEST['giftbag_version']);
            }
        }
    	parent::lists(self::model_name,$_GET["p"],$extend);
    }

    public function record(){
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $extend['game_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        if(isset($_REQUEST['user_account'])){
            $extend['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        parent::lists('GiftRecord',$_GET["p"],$extend);
    }

    public function add(){
        if(IS_POST){
            if($_POST['game_name']==''){
                $this->error('请选择游戏');
            }
            if($_POST['giftbag_name']==''){
                $this->error('请输入礼包名称');
            }
            $Model  =   M('promote_gift','tab_');
            // 获取模型的字段信息
            $data = $Model->create();
            if(IS_POST){
                $data['giftbag_type'] = 4;
                $data['novice'] = str_replace(array("\r\n", "\r", "\n"), ",", $_POST['novice']);  
                $data['server_name']=get_server_name($data['server_id']);
                $data['create_time'] = time();
                $Model->add($data);
                $this->success('添加'.$model['title'].'成功！', U('lists?model='.$model['name']));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $this->meta_title = '新增渠道礼包';
            $this->display('add');
        }
    }

    public function edit($id=0){
        /* if($_POST['game_name']==''){
            $this->error('请选择游戏');
        }
        if($_POST['giftbag_name']==''){
            $this->error('请输入礼包名称');
        } */
		$_REQUEST['id'] || $this->error('请选择要编辑的用户！');
		$model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
		//获取模型信息
        $model = M('Model')->find($model['id']);
        $model || $this->error('模型不存在！');

        if(IS_POST){
            $Model  =   M('promote_gift','tab_');
            // 获取模型的字段信息
            $Model  =   $this->checkAttr($Model,$model['id']);
            $data = $Model->create();
            if($data){
                $data['novice'] = str_replace(array("\r\n", "\r", "\n"), ",", $_POST['novice']);
                $Model->save($data);
                $this->success('保存'.$model['title'].'成功！', U('lists?model='.$model['name']));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields     = get_model_attribute($model['id']);
            //获取数据
            $data       = D(get_table_name($model['id']))->find($id);
            $data || $this->error('数据不存在！');

            $this->assign('model', $model);
            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->meta_title = '编辑渠道礼包';
            $this->display($model['template_edit']?$model['template_edit']:'');
        }
    }

    public function del($model = null, $ids=null){
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }

    public function get_ajax_area_list(){
    	$area = D('Server');
    	$map['game_id'] = I('post.game_id',1);
    	$list = $area->where($map)->select();
    	$this->ajaxReturn($list);
    }

}
