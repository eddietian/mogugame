<?php

namespace Mobile\Controller;
use Think\Controller;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class DownController extends Controller {
    
    public function down_file($game_id=0,$type=1){
        $model = M('Game','tab_');
        $map['tab_game.relation_game_id'] = $game_id;
        // $map['file_type'] = $type;
        $data = $model
            ->field('tab_game_source.*,tab_game.game_name,tab_game.id as game_id,tab_game.and_dow_address,tab_game.ios_dow_address,tab_game.add_game_address,tab_game.ios_game_address')
            ->join("left join tab_game_source on tab_game.id = tab_game_source.game_id")
            ->where($map)
            ->order('file_type asc,tab_game.game_name asc')
            ->select();
        $first_data=reset($data);
        $end_data=end($data);
        if(empty($first_data) || empty($end_data)){
            $this->error('暂无原包！');
        }
        if(substr($first_data['and_dow_address'], 0 , 2)==".."){
            $first_data['and_dow_address']=substr($first_data['and_dow_address'],'1',strlen($first_data['and_dow_address']));
        }
        if(substr($end_data['ios_dow_address'], 0 , 2)==".."){
            $end_data['ios_dow_address']=substr($end_data['ios_dow_address'],'1',strlen($end_data['ios_dow_address']));
        }
        if($type==1){
            M('Game','tab_')->where('id='.$first_data['game_id'])->setInc('dow_num');
            $this->add_down_stat($first_data['game_id']);
            switch ($first_data['file_type']) {
                case 1://如果设置了安卓包
                    if($first_data['add_game_address'] != ''){
                        if(varify_url($first_data['add_game_address'])){
                            Header("HTTP/1.1 303 See Other");
                            Header("Location: ".$first_data['add_game_address']);
                        }else{
                            $this->error('下载地址错误！');
                        }break;
                    }  else {
                        if($first_data['add_game_address']!=''){
                            Header("HTTP/1.1 303 See Other");
                            Header("Location: ".$first_data['add_game_address']);
                        }elseif($first_data['and_dow_address']!=''){
                            $this->down($first_data['and_dow_address'],$type);
                        }else{
                            $this->error('下载地址未设置！');
                        }break;
                    }
                default :
                    if(varify_url($first_data['add_game_address'])){
                        Header("HTTP/1.1 303 See Other");
                        Header("Location: ".$first_data['add_game_address']);
                        break;
                    }else{
                        $this->error('下载地址错误！');
                    }
            }
        }else if($type==2){
            M('Game','tab_')->where('id='.$end_data['game_id'])->setInc('dow_num');
            $this->add_down_stat($end_data['game_id']);
            switch ($end_data['file_type']) {
                case 2://如果设置了苹果包
                    if($end_data['ios_game_address'] != ''){
                        if(varify_url($end_data['ios_game_address'])){
                            Header("HTTP/1.1 303 See Other");
                            Header("Location: ".$end_data['ios_game_address']);
                        }else{
                            $this->error('下载地址错误！');
                        }break;
                    }  else {
                        if($end_data['ios_game_address'] != ''){
                            Header("HTTP/1.1 303 See Other");
                            Header("Location: ".$end_data['ios_game_address']);
                        }if($end_data['ios_dow_address']!=''){
                            $this->down($end_data['ios_dow_address'],$type);
                        }else{
                            $this->error('下载地址未设置！');
                        } break;
                    }
                 default :
                    if(varify_url($end_data['ios_game_address'])){
                            Header("HTTP/1.1 303 See Other");
                            Header("Location: ".$end_data['ios_game_address']);break;
                    }else{
                        $this->error('下载地址错误！');
                    }
            }
        }        
    }

    function access_url($url) {    
        if ($url=='') return false;    
        $fp = fopen($url, 'r') or exit('Open url faild!');    
        if($fp){  
        while(!feof($fp)) {    
            $file.=fgets($fp)."";  
        }  
        fclose($fp);    
        }  
        return $file;  
    }  
    // public function down1($file, $type,$isLarge = false, $reload=ture,$rename = NULL)
    // {
    //     if(headers_sent())return false;
    //     if(!$file&&$type==1) {
    //         $this->error('安卓文件不存在哦 亲!');
    //         //exit('Error 404:The file not found!');
    //     }
    //     if(!$file&&$type==2) {
    //         $this->error('苹果文件不存在哦 亲!');
    //         //exit('Error 404:The file not found!');
    //     }
    //     if(file_exists($file)){
    //         if($name==''){
    //             $name = basename($file);
    //         }
    //         $fp = fopen($file, 'rb+');
    //         $file_size = filesize($file);
    //         $ranges = $this->getRange($file_size);
    //         header('cache-control:public');
    //         header('Accenpt-Ranges: bytes');
    //         header('content-type:application/octet-stream');
    //         header('content-disposition:attachment; filename='.$name);
    //         if($reload && $ranges!=null){ // 使用断点下载，暂不能用
    //             header('HTTP/1.1 206 Partial Content');
    //             header('Accept-Ranges:bytes');
    //             // 剩余长度
    //             header(sprintf('content-length:%u',$ranges['end']-$ranges['start']));
    //             // range信息
    //             header(sprintf('content-range:bytes %s-%s/%s', $ranges['start'], $ranges['end'], $file_size));
    //             // fp指针跳到断点位置
    //             fseek($fp, sprintf('%u', $ranges['start']));
    //         }else{
    //             // header('HTTP/1.1 200 OK');//数据流下载
    //             // header('content-length:'.$file_size);
    //             // ob_clean();
    //             // flush();
    //             // readfile($file);
    //             header("Location:$file");//大文件下载
    //         }
    //         ($fp!=null) && fclose($fp);
    //     }else{
    //         $this->error('文件丢失了!');
    //     }
    // }
    /**
     * [断点下载 需要服务器支持]
     * @param  [type] $file   [description]
     * @param  [type] $type   [description]
     * @param  [type] $rename [description]
     * @return [type]         [description]
     * @author [yyh] <[email address]>
     */
    public function down($file,$type,$rename = NULL)
    {
        if(headers_sent())return false;//检查 HTTP 标头是否已被发送，可以避免与 HTTP 标头有关的错误信息。
        if(!$file&&$type==1) {
            $this->error('安卓文件不存在哦 亲!');
            //exit('Error 404:The file not found!');
        }
        if(!$file&&$type==2) {
            $this->error('苹果文件不存在哦 亲!');
            //exit('Error 404:The file not found!');
        }
        $sourceFile = $file; //要下载的临时文件名  
        $outFile = $rename;
        $file_extension = strtolower(substr(strrchr($sourceFile, "."), 1)); //获取文件扩展名 
        //检测文件是否存在  
        if (!is_file($sourceFile)) {  
            die("<b>404 File not found!</b>");  
        }  
        $len = filesize($sourceFile); //获取文件大小  
        $filename = basename($sourceFile,'.'.$file_extension); //获取文件名字  
        $outFile_extension = $file_extension; //获取文件扩展名  
        //根据扩展名 指出输出浏览器格式  
        switch ($outFile_extension) {  
            case "exe" :  
                $ctype = "application/octet-stream";  
                break;  
            case "zip" :  
                $ctype = "application/zip";  
                break;  
            case "mp3" :  
                $ctype = "audio/mpeg";  
                break;  
            case "mpg" :  
                $ctype = "video/mpeg";  
                break;  
            case "avi" :  
                $ctype = "video/x-msvideo";  
                break;  
            default :  
                $ctype = "application/force-download";  
        }  
        //Begin writing headers  允许缓存 把页面缓存
        header("Cache-Control:");
        header("Cache-Control: public");         
        //设置输出浏览器格式  
        //告诉浏览器强制下载
        header("Content-Type: $ctype"); 
        //下载后的名字以及后缀  
        header("Content-Disposition: attachment; filename=" . $filename.".".$outFile_extension); 
        //接受的范围单位
        header("Accept-Ranges: bytes");  
        $size = filesize($sourceFile);
        //$_SERVER['HTTP_RANGE'] HTTP协议是否设置支持断点下载  
        //如果有$_SERVER['HTTP_RANGE']参数 
        if (isset ($_SERVER['HTTP_RANGE'])) {  
            if (!preg_match('^bytes=\d*-\d*(,\d*-\d*)*$', $_SERVER['HTTP_RANGE'])) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header('Content-Range: bytes */' . $size); // Required in 416.
                exit;
            }
            $ranges = explode(',', substr($_SERVER['HTTP_RANGE'], 6));
            foreach ($ranges as $range) {
                $parts = explode('-', $range);
                $start = $parts[0]; // If this is empty, this should be 0.
                $end = $parts[1]; // If this is empty or greater than than filelength - 1, this should be filelength - 1.

                if ($start > $end) {
                    header("HTTP/1.1 206 Partial Content");  
                    header("Content-Length: $new_length"); //输入总长  
                    header("Content-Range: bytes $range$size2/$size"); //Content-Range: bytes 4908618-4988927/4988928   95%的时候  
                    exit;
                }
            }
        } else {  
            //HTTP协议未设置断点下载 尝试设置  
            //即第一次连接
            $size2 = $size -1; 
            header("Content-Range: bytes 0-$size2/$size"); //Content-Range: bytes 0-4988927/4988928  
            header("Content-Length: " . $size); //输出总长  
        }  
        //打开文件  
        $fp = fopen("$sourceFile", "rb+");  
        //设置指针位置  
        fseek($fp, $range);  
        //虚幻输出  
        while (!feof($fp)) {  
            //设置文件最长执行时间  
            set_time_limit(0);  
            print (fread($fp, 1024 * 8)); //输出文件  
            flush(); //输出缓冲  
            ob_flush();  
        }  
        fclose($fp);  
        exit ();
    }
    /** 获取header range信息
    * @param  int   $file_size 文件大小
    * @return Array
    */
    private function getRange($file_size){
        if(isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])){
            $range = $_SERVER['HTTP_RANGE'];
            $range = preg_replace('/[\s|,].*/', '', $range);
            $range = explode('-', substr($range, 6));
            if(count($range)<2){
                $range[1] = $file_size;
            }
            $range = array_combine(array('start','end'), $range);
            if(empty($range['start'])){
                $range['start'] = 0;
            }
            if(empty($range['end'])){
                $range['end'] = $file_size;
            }
            return $range;
        }
        return null;
    }
    /**
    *游戏下载统计
    */
    public function add_down_stat($game_id=null){
        $model = M('down_stat','tab_');
        $data['promote_id'] = 0;
        $data['game_id'] = $game_id;
        $data['number'] = 1;
        $data['type'] = 0;
        $data['create_time'] = NOW_TIME;
        $model->add($data);
    }
}
