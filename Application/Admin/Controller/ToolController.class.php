<?php

namespace Admin\Controller;
/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ToolController extends ThinkController {
    
    /**
    *保存设置
    */
    public function saveTool($value='')
    {
        $name   = $_POST['name'];
        $config = I('config');
        $data   = array('config'=>json_encode($config),'template'=>$_POST['template'],'status'=>$_POST['status']);
        $map['name']=$name;
        if($_POST['status']==1&&$name=="weixin"){
            $map_['name']=array("in",'wei_xin,wei_xin_app,weixin_gf');
            M('tool','tab_')->where($map_)->setField('status','0');
        }
        if($_POST['status']==1&&$name=="wei_xin"||$name=="wei_xin_app"){
            $map_['name']=['in','weixin,weixin_gf'];
            M('tool','tab_')->where($map_)->setField('status','0');
        }
        if($_POST['status']==1&&$name=="weixin_gf"){
            $map_['name']=array("in",'wei_xin,wei_xin_app,weixin');
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

    public function save($config){
        $name   = $_POST['name'];
        $a = $this->set_config($name,$config);
        if ($a){
            return true;
        }else{
            return false;
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
    *短信设置
    */
    public function smsset($value='')
    {
        $this->BaseConfig("sms_set,alidayu,jiguang,alidayunew,alidayumsg");
        $this->meta_title = '短信设置';
        $this->display();
    }

    /**
     *防沉迷设置
     */
    public function age($value='')
    {
        $this->BaseConfig("age");
        $this->meta_title = '实名认证';
        $this->display();
    }

    /**
    *文件存储
    */
    public function storage($value='')
    {
        $str = "oss_storage,qiniu_storage,cos_storage,bos_storage,ks3_storage";
        $this->BaseConfig($str);
        $this->meta_title = '文件存储';
        $this->display();
    }

    /**
    *动态密保
    */
    public function cooperate($value='')
    {
        $str = "set_cooperate";
        $this->BaseConfig($str);
        $this->meta_title = '动态密保';
        $this->display();
    }

    /**
    *支付设置
    */
    public function payset($value='')
    {
        $str = "alipay,weixin,wei_xin,wei_xin_app,jubaobar,weixin_gf,jft";
        $this->BaseConfig($str);
        $this->meta_title = '支付设置';
        $this->display();
    }

    /**
    *邮件设置
    */
    public function email($value='')
    {
        $str = "email_set";
        $this->BaseConfig($str);
        $this->meta_title = '邮件设置';
        $this->display();
    }

    /**
    *第三方登陆设置
    */
    public function thirdparty($value='')
    {
        $str = "qq_login,wx_login,wb_login,bd_login,fb_login,gg_login";
        $this->BaseConfig($str);
        $this->meta_title = '第三方登录';
        $this->display();
    }

    /**
    *畅言设置
    */
    public function changyan($value=''){
        $str = "changyan";
        $this->BaseConfig($str);
        $this->meta_title = '畅言设置';
        $this->display();
    }
    /**
    *聚宝云
    */
    public function saveTool_jubaobar(){
        $name   = $_POST['name'];
        $config = I('config');
        $data   = array('config'=>json_encode($config),'template'=>$_POST['template'],'status'=>$_POST['status']);
        $map['name']=$name;  
        $flag   = M('Tool','tab_')->where($map)->setField($data);
        if($flag !== false){
            $this->set_config($name,$config);
            $this->update_xml($config['key']);
            $this->success('保存成功');
        }else{
            $this->error('保存失败');
        }
    }

    /**
    *修改 聚宝云 安全口令
    */
    private function update_xml($key=""){
        // $file = $_SERVER['DOCUMENT_ROOT'].'Application/Sdk/SecretKey/jubaopay/jubaopay.ini';
        $file = './Application/Sdk/SecretKey/jubaopay/jubaopay.ini';
        //创建DOMDocument的对象
        $dom = new \DOMDocument('1.0');
        //载入mainchart.xml文件
        $dom->load($file);
        $dom->getElementsByTagName('psw')->item(0)->nodeValue = $key;
        $dom->save($file);
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

}
