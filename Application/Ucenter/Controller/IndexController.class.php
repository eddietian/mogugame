<?php
/* Ucenter接口API入口文件  */
// +----------------------------------------------------------------------+
// | PHP version 5.3+                                                    
// +----------------------------------------------------------------------+
// | Copyright (c) 2013-2015  铭扬致远                                          
// +----------------------------------------------------------------------+
// | Authors: Author <262877348@qq.com>                        
// |          金奎   <QQ:262877348>    QQ群：【332560336】                                   
// |          本群只为喜爱ThinkPHP的用户，一直研究、探讨THINKPHP5/3.23 RBAC AUTH UCenter整合而提供服务                         
// +----------------------------------------------------------------------+
namespace Ucenter\Controller;
use Think\Controller;
use Ucenter\Api\UcenterLib;
class IndexController extends Controller {
    public function index(){
    	UcenterLib::back();
    }
}