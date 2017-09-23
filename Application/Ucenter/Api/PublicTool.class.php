<?php
/* 公共工具类  */
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

class PublicTool {

    public static $mail_suffix = array('@hotmail.com',
        '@msn.com',
        '@yahoo.com',
        '@gmail.com',
        '@aim.com',
        '@aol.com',
        '@mail.com',
        '@walla.com',
        '@inbox.com',
        '@126.com',
        '@163.com',
        '@sina.com',
        '@21cn.com',
        '@sohu.com',
        '@yahoo.com.cn',
        '@tom.com',
        '@qq.com',
        '@etang.com',
        '@eyou.com',
        '@56.com',
        '@x.cn',
        '@chinaren.com',
        '@sogou.com',
        '@citiz.com',
    );

    /**
     * 获取邮箱登陆URL
     * @param type $email
     * @return type
     */
    public static function getMailLoginUrl($email) {
        $domain = strstr($email, '@');
        $url = "http://www.baidu.com/s?wd={$domain}+邮箱登陆&ie=utf-8";
        if (in_array($domain, self::$mail_suffix)) {
            $domain = 'http://' . $domain;
            $url = str_replace("@", 'mail.', $domain);
        }
        return $url;
    }

    /**
     * 获取随机字符串
     * @param type $count
     * @param type $str
     * @return type
     */
    public static function getRandomChar($count, $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") {
        $key = "";
        $len = strlen($str) - 1;
        for ($i = 0; $i < $count; $i++) {
            $key .= $str{mt_rand(0, $len)};    //生成php随机数
        }
        return $key;
    }

    /**
     * 获取16位唯一随机数字串
     * @return type
     */
    public static function getUniqueId() {
        $mtime = microtime();
        $key = __FUNCTION__ . time();
        $aExistData = S($key);
        if (empty($aExistData)) {
            $aExistData = array();
        }
        preg_match('/0\.(\d{4})\d{4}\s(\d{10})/', $mtime, $match);
        $sUniqueId = $match[2] . $match[1] . mt_rand(10, 99);
        if (in_array($sUniqueId, $aExistData)) {
            $sUniqueId = self::getUniqueId();
        } else {
            $aExistData[] = $sUniqueId;
            S($key, $aExistData, 1);
        }
        return $sUniqueId;
    }

    /**
     * 根据IP地址获取当前城市详情
     * @param type $ip
     * @return type
     */
    public static function getIpCity($ip = '') {
        if ($ip == '')
            $ip = Net_Net_GetIp();
        $ip_url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
        $message = file_get_contents($ip_url);
        $data = json_decode($message, true);
        return $data;
    }

    /**
     * 获取当前IP地址
     * @return string
     */
    public static function getIp() {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = "";
        }
        return $cip;
    }

    /**
     * 根据IP地址获取当前城市
     * @return boolean
     */
    public static function getCityByIp() {
        $ip = get_client_ip();
        $ip = $ip == "127.0.0.1" ? "122.226.178.42" : $ip;
        //122.226.178.42 60.194.13.0
        $city_info = PublicTool::getIpCity($ip);
        //未匹配ip
        $data = array();
        if ($city_info['data']['country'] == "未分配或者内网IP") {
            return false;
        }
        //北京市 省级市
        $data['region'] = $city_info['data']['region'];
        $data['city'] = $city_info['data']['city'];
        return $data;
    }

    /**
     * 删除文件
     * @param type $filePath
     */
    public static function delFile($filePath) {
        if (trim($filePath) != "") {
            unlink($filePath);
        }
    }

    /**
     * 删除文件夹
     * @param type $dir
     * @return boolean
     */
    public static function deldir($dir) {
//先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    self::deldir($fullpath);
                }
            }
        }

        closedir($dh);
//删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 创建文件夹
     * @param type $dir
     * @return boolean
     */
    public static function mkdirs($dir) {
        if (!is_dir($dir)) {
            if (!self::mkdirs(dirname($dir))) {
                return false;
            }
            if (!mkdir($dir, 0777)) {
                return false;
            }
        }
        return true;
    }

    // Vendor("PHPExcel", APP_PATH . 'Extend/Lib/PHPExcel/');
    // Vendor("IOFactory", APP_PATH . 'Extend/Lib/PHPExcel/PHPExcel/');
    // $objExcel = new \PHPExcel();
    // $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    // $objWriter->_phpExcel->setActiveSheetIndex(0);
    // $map = $arr['map']; //array('A'=>array('key'=>'title','title'=>'标题'),...)
    // $list = $arr['list']; //array(array(),...);
    // foreach ($list as $k => $v) {
    //     $k++;
    //     if ($k == 1) {
    //         //init header
    //         foreach ($map as $kk => $vv) {
    //             $objWriter->_phpExcel->getActiveSheet()->setCellValue($kk . $k, $vv['title']);
    //         }
    //     }
    //     $k++;
    //     foreach ($map as $kk => $vv) {
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue($kk . $k, $v[$vv['key']]);
    //     }
    // }
    // $objWriter->save($outFileName);
    // return $outFileName;

    // Vendor("PHPExcel", APP_PATH . 'Extend/Lib/PHPExcel/');
    // Vendor("IOFactory", APP_PATH . 'Extend/Lib/PHPExcel/PHPExcel/');
    // $objExcel = new \PHPExcel();
    // $objReader = \PHPExcel_IOFactory::createReader('Excel5');
    // $objPHPExcel = $objReader->load($inputFilename);
    // $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    // $objWriter->_phpExcel->setActiveSheetIndex(0);
    // $arr = $objWriter->_phpExcel->getActiveSheet()->toArray(null, true, true, true);

    // $fixTime = array(
    //     12 * 3600, //中午时刻0
    //     8 * 3600, //上班时刻1
    //     17 * 3600 + 1800, //下班时刻2
    //     60 * 15, //15分钟3
    //     60 * 60, //迟到1小时4
    //     21 * 3600, //加班基准时间9
    //     1 * 60, //
    // );
    
    // $day_daka = array();
    // foreach ($arr as $k => $v) {
    //     $objWriter->_phpExcel->getActiveSheet()->setCellValue('A' . $k, $v['A']);
    //     $objWriter->_phpExcel->getActiveSheet()->setCellValue('B' . $k, $v['B']);
    //     $objWriter->_phpExcel->getActiveSheet()->setCellValue('C' . $k, $v['C']);
    //     $objWriter->_phpExcel->getActiveSheet()->setCellValue('D' . $k, $v['D']);
    //     $objWriter->_phpExcel->getActiveSheet()->setCellValue('E' . $k, $v['E']);
    //     $objWriter->_phpExcel->getActiveSheet()->setCellValue('F' . $k, $v['F']);
    //     $objWriter->_phpExcel->getActiveSheet()->setCellValue('G' . $k, $v['G']);
    //     $objWriter->_phpExcel->getActiveSheet()->setCellValue('H' . $k, $v['H']);
    //     $time = $v['C'];
    //     $time = strtotime($time);
    //     if ($time <= 0) {
    //         continue;
    //     }
    //     if ($k == 1) {
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('I' . $k, '未打卡情况');
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('J' . $k, '上班基准时间');
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('K' . $k, '上班迟到15分钟基准时间');
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('L' . $k, '上班迟到60分钟基准时间');
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('M' . $k, '下班基准时间');
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('N' . $k, '加班基准时间');
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('O' . $k, '基准日期');
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('P' . $k, '迟到早退详情');
    //     } else {
    //         $day = date("Y-m-d", $time);
    //         $daytime = strtotime($day);
    //         $zfTime = $daytime + $fixTime[0]; //正午时间
    //         $sbTime = $daytime + $fixTime[1]; //上班时间
    //         $sbryTime = $daytime + $fixTime[1] + $fixTime[6]; //上班时间
    //         $xbTime = $daytime + $fixTime[2]; //下班时间
    //         $cd15Time = $daytime + $fixTime[1] + $fixTime[3]; //15
    //         $cd60Time = $daytime + $fixTime[1] + $fixTime[4]; //60
    //         $jbTime = $daytime + $fixTime[5]; //9:00
    //         $jcString = "";
    //         $overtime = 0;
    //         if ($time < $zfTime) {//上午打卡
    //             $overtime = $time - $sbTime;
    //             $day_daka[$daytime] = 1;
    //             if ($time <= $cd15Time && $time > $sbryTime) {
    //                 $objWriter->_phpExcel->getActiveSheet()->setCellValue('D' . $k, 1);
    //             }
    //             if ($time <= $cd60Time && $time > $cd15Time) {
    //                 $objWriter->_phpExcel->getActiveSheet()->setCellValue('E' . $k, 1);
    //             }
    //             if ($time > $cd60Time) {
    //                 $objWriter->_phpExcel->getActiveSheet()->setCellValue('F' . $k, 1);
    //             }
    //             if ($overtime > 0) {
    //                 $jcString = "上班迟到 %s 小时 %d 分钟 %d 秒";
    //                 $jcString = sprintf($jcString, intval($overtime / 3600), intval($overtime % 3600 / 60), $overtime % 60);
    //                 $objWriter->_phpExcel->getActiveSheet()->setCellValue('P' . $k, $jcString);
    //             }
    //         } else {//下午打卡
    //             $overtime = $xbTime - $time;
    //             if (empty($day_daka[$daytime])) {//上午未打卡
    //                 $objWriter->_phpExcel->getActiveSheet()->setCellValue('I' . $k, '上午未打卡');
    //             }
    //             if ($time < $xbTime) {
    //                 $objWriter->_phpExcel->getActiveSheet()->setCellValue('G' . $k, 1);
    //             }
    //             if ($time >= $jbTime) {
    //                 $objWriter->_phpExcel->getActiveSheet()->setCellValue('H' . $k, 1);
    //             }
    //             if ($overtime > 0) {
    //                 $jcString = "下班早退 %s 小时 %d 分钟 %d 秒";
    //                 $jcString = sprintf($jcString, intval($overtime / 3600), intval($overtime % 3600 / 60), $overtime % 60);
    //                 $objWriter->_phpExcel->getActiveSheet()->setCellValue('P' . $k, $jcString);
    //             }
    //         }
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('J' . $k, date("Y/m/d H:i:s", $sbryTime)); //上班基准时间
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('K' . $k, date("Y/m/d H:i:s", $cd15Time)); //上班迟到15分钟基准时间
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('L' . $k, date("Y/m/d H:i:s", $cd60Time)); //上班迟到60分钟基准时间
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('M' . $k, date("Y/m/d H:i:s", $xbTime)); //下班基准时间
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('N' . $k, date("Y/m/d H:i:s", $jbTime)); //加班基准时间
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('O' . $k, date("Y/m/d", $daytime)); //基准日期
    //         $objWriter->_phpExcel->getActiveSheet()->setCellValue('P' . $k, $jcString); //异常打卡记录
    //     }
    //     if ($k > 2000) {
    //         break;
    //     }
    // }
    // $objWriter->save($outFileName);
    // return $outFileName;



    /**
     * Unix时间戳转日期
     * echo date_to_unixtime("1900-1-31 00:00:00"); //输出-2206425952
      echo unixtime_to_date(date_to_unixtime("1900-1-31 00:00:00")); //输出1900-01-31 00:00:00
     * @param type $unixtime
     * @param type $timezone
     * @return type
     */
    public static function unixtimeToDate($unixtime, $timezone = 'PRC') {
        $datetime = new \DateTime("@$unixtime"); //DateTime类的bug，加入@可以将Unix时间戳作为参数传入
        $datetime->setTimezone(new \DateTimeZone($timezone));
        return $datetime->format("Y-m-d H:i:s");
    }

    /**
     * 日期转Unix时间戳
     * @param type $date
     * @param type $timezone
     * @return type
     */
    public static function dateToUnixtime($date, $timezone = 'PRC') {
        $datetime = new \DateTime($date, new \DateTimeZone($timezone));
        return $datetime->format('U');
    }

}
