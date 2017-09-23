<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class GameTypeController extends ThinkController {
    const model_name = 'GameType';

    public function lists(){
        if(isset($_REQUEST['status_show'])){
            if($_REQUEST['status_show']!='all'){
                $extend['status_show'] = $_REQUEST['status_show'];
            }
            unset($_REQUEST['status_show']);
        }
        parent::lists(self::model_name,$_GET["p"],$extend);
    }

    public function add($model='')
    {
        parent::add($model);
    }
    
    public function edit($model='',$id=0)
    {
        parent::edit($model,$id);
    }

    public function del($model = null, $ids=null)
    {
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }

    public function set_status()
    {
        parent::set_status(self::model_name);
    }
}
