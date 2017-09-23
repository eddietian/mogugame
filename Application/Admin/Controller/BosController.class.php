<?php

namespace Admin\Controller;
use Think\Controller;
use BaiduBce\BceClientConfigOptions;
use BaiduBce\Util\Time;
use BaiduBce\Util\MimeTypes;
use BaiduBce\Http\HttpHeaders;
use BaiduBce\Services\Bos\BosClient;
use BaiduBce\Services\Bos\CannedAcl;
use BaiduBce\Services\Bos\BosOptions;
use BaiduBce\Auth\SignOptions;
use BaiduBce\Log\LogFactory;

class BosController extends Controller {
    
   
    /**
    *上传到BOS
    */
    public function upload_bos($return_data=null){
         /**
        * 根据Config配置，得到一个OssClient实例
        */
        try {
            $BOS_TEST_CONFIG =
            array(
                'credentials' => array(
                    'accessKeyId' => C("bos_storage.AccessKey"),
                    'secretAccessKey' => C("bos_storage.SecretKey"),
                ),
                'endpoint' => C("bos_storage.domain"),
            );
            require VENDOR_PATH.'BOS/BaiduBce.phar';
            $client = new BosClient($BOS_TEST_CONFIG);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $bucket = C('bos_storage.bucket');
        $bos_file_path ="icon/". $return_data["savename"]; //图片在bos的路径
        $avatar = $return_data["path"];   
        
        try {
            
            $client->putObjectFromFile($bucket,$bos_file_path,$avatar);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

    }    

    /*
    删除bos的object
     */
    public function delete_bos($name){
         /**
        * 根据Config配置，得到一个OssClient实例
        */
        try {
            $BOS_TEST_CONFIG =
            array(
                'credentials' => array(
                    'accessKeyId' => C("bos_storage.AccessKey"),
                    'secretAccessKey' => C("bos_storage.SecretKey"),
                ),
                'endpoint' => C("bos_storage.domain"),
            );
            require VENDOR_PATH.'BOS/BaiduBce.phar';
            $client = new BosClient($BOS_TEST_CONFIG);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $bucket = C('bos_storage.bucket');
        $path ="icon/". $name; //在bos的路径
        //$path ="GamePak/". $name;
       
        
        $client->deleteObject($bucket, $path);
            
        
    }


}
