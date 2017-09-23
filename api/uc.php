<?php
/* Uc通信入口文件  */
// +----------------------------------------------------------------------+
// | PHP version 5.3+                                                    
// +----------------------------------------------------------------------+
// | Copyright (c) 2013-2015  铭扬致远                                          
// +----------------------------------------------------------------------+
// | Authors: Author <262877348@qq.com>                        
// |          金奎   <QQ:262877348>    QQ群：【332560336】                                   
// |          本群只为喜爱ThinkPHP的用户，一直研究、探讨THINKPHP5/3.23 RBAC AUTH UCenter整合而提供服务                         
// +----------------------------------------------------------------------+
// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
ob_start();

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);
define('BIND_MODULE','Ucenter');

//网站根目录
define('WEB_ROOT_PATH', rtrim(dirname(dirname(__FILE__)), '/\\') . DIRECTORY_SEPARATOR);

//API根目录
define('API_PATH', realpath(WEB_ROOT_PATH . './api') . DIRECTORY_SEPARATOR);

//项目根目录
define('APP_PATH', realpath(WEB_ROOT_PATH . './Application') . DIRECTORY_SEPARATOR);

//项目安全文件
define('DIR_SECURE_FILENAME', 'index.html');

//运行时文件目录
define('RUNTIME_PATH', APP_PATH . "Runtime" . DIRECTORY_SEPARATOR);

//应用静态目录
define('HTML_PATH', WEB_ROOT_PATH . 'Html' . DIRECTORY_SEPARATOR);

// 引入ThinkPHP入口文件
require WEB_ROOT_PATH . './ThinkPHP/ThinkPHP.php';
