<?php

namespace Media\Controller;
use OT\DataDictionary;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class ShareController extends BaseController {

	//系统首页
    public function index(){

       if(get_device_type()=="ios"){
          $map['id']=3;
          $app=M('app','tab_')->where($map)->find();
          $file = "https://" . $_SERVER['HTTP_HOST'] . ltrim($app['plist_url'], ".");
          $file = "itms-services://?action=download-manifest&url=$file";
         $this->assign('url',$file);
       }else{
	       $map['id']=2;
    	   $app=M('app','tab_')->where($map)->find();
       	  $this->assign('url',"http://".$_SERVER['HTTP_HOST'].$app['file_url']);
       }
       $this->display();
    }
}