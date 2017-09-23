<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class CertController extends ThinkController {

    const model_name = 'Applecert';

    public function lists(){
        $extend = array('key'=>'gift_name');
    	parent::lists(self::model_name,$_GET["p"],$extend);
    }

    public function record(){
        parent::lists('GiftRecord',$_GET["p"],$extend);
    }

    public function add(){
        if(IS_POST){
            if($_REQUEST['name']==''){
                $this->error('证书名称不能为空');
            }
            if(IS_POST){
                $data['name']=$_REQUEST['name'];
                $data['updatetime'] = time();
                M('Applecert')->add($data);
                $this->success('添加'.$model['title'].'成功！', U('lists?model='.$model['name']));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $this->meta_title = '新增ios证书';
            $this->display('add');
        }
    }

    public function del($model = null, $ids=null){
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }

}
