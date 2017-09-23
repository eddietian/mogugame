<?php

namespace Admin\Controller;
use Think\Controller;


class Ks3Controller extends Controller {//金山云存储
    
   public function upload_ks3($return_data=null){

        /**
        * 根据Config配置，得到一个ks3Client实例
        */
        try {
            Vendor('ks3.Ks3Client','','.class.php');
            $client = new \Ks3Client(C("ks3_storage.AccessKey"), C("ks3_storage.SecretKey"), C("ks3_storage.domain"));
        } catch (OssException $e) {
            $this->error($e->getMessage());
        }
// dump($client);die();
        $args = array(
            "Bucket"=>C('ks3_storage.bucket'),
            "Key"=>"php",  
            "Content"=>"D:\wamp\www\a.php",//要上传的内容//可以是文件路径或者resource,如果文件大于2G，请提供文件路径
            "ACL"=>"public-read",//可以设置访问权限,合法值,private、public-read
            "ObjectMeta"=>array(//设置object的元数据,可以设置"Cache-Control","Content-Disposition","Content-Encoding","Content-Length","Content-MD5","Content-Type","Expires"。当设置了Content-Length时，请勿大于实际长度，如果小于实际长度，将只上传部分内容。
                //"Content-Type"=>"binay/ocet-stream",
                "Content-Length"=>4
                ),
            "UserMeta"=>array(//可以设置object的用户元数据，需要以x-kss-meta-开头
               "x-kss-meta-test"=>"test"
               )
        );
        $res=$client->putObjectByContent($args);
        dump($res);
   }
   public function test(){

        $this->upload_ks3();
   }
   

}
