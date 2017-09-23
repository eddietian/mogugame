<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class SiteController extends ThinkController {

    public function config_index(){
        R('Config/group');
    }
    
     // 获取某个标签的配置参数
    public function group($cate_id=0,$group_id=0,$type="PC_CONFIG_GROUP_LIST") {
        $type   =   C($type);
        $map['status'] = 1;
        $map['category'] = $cate_id;
        $map['group'] = $group_id;
        $list   =   M("Config")->where($map)->field('id,name,title,extra,value,remark,type')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
        $this->assign('id',$group_id);
        $this->meta_title = $type[$group_id].'设置';
        $this->display();
    }


    /**
    *保存设置
    */
    public function saveTool($value='')
    {
        $name   = $_POST['name'];
        $config = I('config');
        $data   = array('config'=>json_encode($config),'template'=>$_POST['template'],'status'=>$_POST['status']);
        $map['name']=$name;
        if($_POST['status']==1&&$name=="weixin_app"){
            $map_['name']=array("in",'wei_xin_apps');
            M('tool','tab_')->where($map_)->setField('status','0');
        }
        if($_POST['status']==1&&$name=="wei_xin_apps"){
            $map_['name']="weixin_app";
            M('tool','tab_')->where($map_)->setField('status','0');
        }
        $flag   = M('Tool','tab_')->where($map)->setField($data);
        if($flag !== false){
            $this->set_config($name,$config);
            $this->success('保存成功');
        }else{
            $this->error('保存失败');
        }

    }

  /**
    *显示扩展设置信息
    */
    protected function BaseConfig($name='')
    {   
        $map['name'] = array('in',$name);
        $tool = M('tool',"tab_")->where($map)->select();
        if(empty($tool)){$this->error('没有此设置');}
        foreach ($tool as $key => $val) {
            $this->assign($tool[$key]['name'],json_decode($tool[$key]['config'],true));
            unset($tool[$key]['config']);
            $this->assign($tool[$key]['name']."_data",$tool[$key]);
        }
    }

    /**
    *支付设置
    */
    public function app_pay($value='')
    {
        $str = "weixin_app,wei_xin_apps";
        $this->BaseConfig($str);
        $this->meta_title = '支付设置';
        $this->display();
    }


    /**
     * 批量保存配置
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function save($config){
        if($config && is_array($config)){
            $Config = M('Config');
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $Config->where($map)->setField('value', $value);
            }
        }
        S('DB_CONFIG_DATA',null);
        $this->success('保存成功！');
    }

    /**
    *设置config
    */
    private function set_config($name="",$config=""){
        $config_file ="./Application/Common/Conf/pay_config.php";
        if(file_exists($config_file)){
            $configs=include $config_file;
        }else {
            $configs=array();
        }
        #定义一个数组
        $data = array();
        #给数组赋值
        $data[$name] = $config;
        $configs=array_merge($configs,$data);
        $result = file_put_contents($config_file, "<?php\treturn " . var_export($configs, true) . ";");
    }

    public function media($cate_id=0,$group_id=0){
        $cate_id  = I('cate_id',1);
        $group_id = I('group_id',1);
        $this->group($cate_id,$group_id);
    }

    public function channel(){
        $cate_id = I('cate_id',2);
        $group_id = I('group_id',1);
        $type = "CHANNEL_CONFIG_GROUP_LIST";
        $this->group($cate_id,$group_id,$type);
    }

    public function app(){
        $cate_id = I('cate_id',3);
        $group_id = I('group_id',1);
        $type = "APP_CONFIG_GROUP_LIST";
        $this->group($cate_id,$group_id,$type);
    }
    public function wap(){
        $cate_id = I('cate_id',5);;
        $group_id = I('group_id',1);
        $this->group($cate_id,$group_id);
    }

    public function security(){
        $cate_id = I('cate_id',6);;
        $group_id = I('group_id',1);
        $type = "APP_CONFIG_GROUP_LIST";
        $this->group($cate_id,$group_id,$type);
    }

    public function open(){
	    $cate_id = I('cate_id',7);;
	    $group_id = I('group_id',1);
	    $type = "APP_CONFIG_GROUP_LIST";
	    $this->group($cate_id,$group_id,$type);
    }

    //友情链接
    /**
     * 编辑配置
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $Config = D('Config');
            $data = $Config->create();
            if($data){
                if($Config->save()){
                    S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_log('update_config','config',$data['id'],UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($Config->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Config')->field(true)->find($id);

            if(false === $info){
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
            $this->meta_title = '编辑配置';
            $this->display();
        }
    }
}
