<?php
/* 打印日志文件  */
// +----------------------------------------------------------------------+
// | PHP version 5.3+                                                    
// +----------------------------------------------------------------------+
// | Copyright (c) 2013-2015  铭扬致远                                          
// +----------------------------------------------------------------------+
// | Authors: Author <262877348@qq.com>                        
// |          金奎   <QQ:262877348>    QQ群：【332560336】                                   
// |          本群只为喜爱ThinkPHP的用户，一直研究、探讨THINKPHP5/3.23 RBAC AUTH UCenter整合而提供服务                         
// +----------------------------------------------------------------------+

namespace Ucenter\Api;
use Ucenter\Api\PublicTool;

define('UCENTER_MODULE_PATH', rtrim(dirname(dirname(__FILE__)), '/\\') . DIRECTORY_SEPARATOR);

require_cache(UCENTER_MODULE_PATH . '/Conf/config.php');

abstract class Api{

    public static $logIndex = 1;

    public static function log($text) {
        if(!APP_DEBUG){
            return ;
        }
        
        if (self::$logIndex == 1) {
            $log = '*************************测试分割线*************************' . PHP_EOL;
            $log .= date("Y-m-d H:i:s", time()) . PHP_EOL;
        }

        $logPath = RUNTIME_PATH . 'Debug/';

        if (!is_dir($logPath)) {
            PublicTool::mkdirs($logPath);
        }

        $logFile = $logPath . 'log'.date("Y-m-d", time()).'.log';

        if (is_array($text)) {
            $log = self::$logIndex . ', array==>';
            $text = var_export($text, true);
        } else {
            $log .= self::$logIndex . ', str==>';
        }

        $log .= $text . PHP_EOL;
        file_put_contents($logFile, $log, FILE_APPEND);
        self::$logIndex++;
    }
}

