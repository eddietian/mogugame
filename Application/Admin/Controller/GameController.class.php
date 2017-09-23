<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use OSS\OssClient;
use OSS\Core\OSsException;
use Qiniu\Storage\BucketManager;
use Qiniu\Auth;
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
/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class GameController extends ThinkController {
    //private $table_name="Game";
    const model_name = 'game';

    /**
    *游戏信息列表
    */
     public function lists(){
        if(isset($_REQUEST['game_name'])){
                $extend['game_name'] = $_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
        }
        if(isset($_REQUEST['sdk_version1'])){
            $extend['sdk_version']=$_REQUEST['sdk_version1'];
            unset($_REQUEST['sdk_version1']);
        }
        $extend['apply_status']=1;
        $extend['order']='id desc';
        $extend['for_show_pic_list']='icon';//列表显示图片
        parent::lists(self::model_name,$_GET["p"],$extend);
    }


    public function get_game_set(){
        $map["game_id"] =$_REQUEST['game_id'];
        $find=M('game_set','tab_')->where($map)->find();
        $find['mdaccess_key']=get_ss($find['access_key']);
        echo json_encode(array("status"=>1,"data"=>$find));
    }


    /**
    *游戏原包列表
    */
    public function source(){
        $extend = array('field_time'=>'create_time');
        parent::lists('Source',$_GET["p"],$extend);
    }

    /**
    *游戏更新列表
    */
    public function update(){
        parent::lists('Update',$_GET["p"]);
    }

    /**
    *添加游戏原包
    */
    public function add_source(){
        if(IS_POST){
            if(empty($_POST['game_id']) || empty($_POST['file_type'])){
                $this->error('游戏名称或类型不能为空');
            }
            $map['game_id']=$_POST['game_id'];
            $map['file_type'] = $_POST['file_type'];
            $d = D('Source')->where($map)->find();
            $source = A('Source','Event');
            if(empty($d)){
                $source->add_source();
            }
            else{
                $source->update_source($d['id']);
            }
        }
        else{

            $this->display();
        }
    }

    /**
    *删除原包
    */
    public function del_game_source($model = null, $ids=null){
        $source = D("Source");
        $id = array_unique((array)$ids);
        $map = array('id' => array('in', $id) );
        $list = $source->where($map)->select();
        foreach ($list as $key => $value) {
            $file_url = APP_ROOT.$value['file_url'];
            unlink($file_url);
        }
        $model = M('Model')->getByName("source"); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids,"tab_game_");
    }

    public function add(){
    	if(IS_POST){
            $_POST['introduction']=str_replace(array("\r\n", "\r", "\n"), "~", $_POST['introduction']);
            if($_POST['game_name']==''){
                $this->error('游戏名称不能为空！');exit;
            }
             if($_POST['marking']==''){
                $this->error('游戏标示不能为空！');exit;
            }
            $_POST['relation_game_name']=$_POST['game_name'];
            if($_POST['sdk_version']==1){
                unset($_POST['ios_game_address']);
                $_POST['game_name']=$_POST['game_name'].'(安卓版)';
            }else{
                unset($_POST['add_game_address']);
                $_POST['game_name']=$_POST['game_name'].'(苹果版)';
            }
            $pinyin = new \Think\Pinyin();
            $num=mb_strlen($_POST['game_name'],'UTF8');
            $short = '';
            for ($i=0; $i <$num ; $i++) { 
                $str=mb_substr( $_POST['game_name'], $i, $i+1, 'UTF8'); 
                $short.=$pinyin->getFirstChar($str);  
            }
            $_POST['material_url'] = $_POST['file_url'].$_POST['file_name'];
            $_POST['discount'] ==''?$_POST['discount'] = 10:$_POST['discount'];
            $_POST['short']=$short;
    		$game   =   D(self::model_name);//M('$this->$model_name','tab_');
	        $res = $game->update();  
	        if(!$res){
	            $this->error($game->getError());
	        }else{
	            $this->success($res['id']?'更新成功':'新增成功',U('lists'));
	        }
    	}else{
            $this->meta_title = '新增游戏';
            $this->display();
    	}
    }

    public function relation(){
        if(IS_POST){
            if($_POST['game_name']==''){
                $this->error('游戏名称不能为空！');exit;
            }
            $_POST['relation_game_name']=$_POST['game_name'];
            if($_POST['sdk_version']==1){
                $_POST['game_name']=$_POST['game_name'].'(安卓版)';
            }else{
                $_POST['game_name']=$_POST['game_name'].'(苹果版)';
            }
            $pinyin = new \Think\Pinyin();
            $num=mb_strlen($_POST['game_name'],'UTF8');
            for ($i=0; $i <$num ; $i++) { 
                $str=mb_substr( $_POST['game_name'], $i, $i+1, 'UTF8'); 
                $short.=$pinyin->getFirstChar($str);  
            }
            $_POST['short']=$short;
            $game   =   D(self::model_name);//M('$this->$model_name','tab_');
            $res = $game->update();  
            if(!$res){
                $this->error($game->getError());
            }else{
                $this->success($res['id']?'更新成功':'新增成功',U('lists'));
            }
        }else{
            $_REQUEST['id'] || $this->error('id不能为空');
            $map['relation_game_id']=$_REQUEST['id'];
            $map['id']=$_REQUEST['id'];
            $map1=$map;
            $map1['id']=array('neq',$_REQUEST['id']);
            $inv=D(self::model_name)->where($map)->find();
            $invalid=D(self::model_name)->where($map1)->find();
            if($invalid||$inv==''){
               $this->error('关联数据错误'); 
            }
            $this->assign('data',$inv);
            $this->meta_title = '关联游戏';
            $this->display();
        }
    }

    public function edit($id = null)
    {
        if (IS_POST) {
            /*if($_POST['apply_status']==0&&$_POST['game_status']==1){
               $this->error('游戏未审核不允许显示');//游戏添加完成
        }*/
            $_POST['introduction']=str_replace(array("\r\n", "\r", "\n"), "~", $_POST['introduction']);
            $game = D(self::model_name);//M('$this->$model_name','tab_');
            $_POST['discount'] ==''?$_POST['discount'] = 10:$_POST['discount'];
            $res = $game->update();
            $id = $res["id"];
            $sibling = D("Game")->find($id);
            $map['relation_game_id'] = $sibling['relation_game_id'];
            $sid=$sibling['id'];
            $map['id'] = array('neq',$sid);
            $another=D("Game")->where($map)->find();  //获取另一个所有
            $phone['game_type_id'] =$sibling['game_type_id'];
            $phone['dow_num'] = $sibling['dow_num'];
            $phone['game_type_name'] =$sibling['game_type_name'];
            $phone['category']=$sibling['category'] ;
            $phone['recommend_status']= $sibling['recommend_status'];
            $phone['game_status']= $sibling['game_status'];
            $phone['sort']= $sibling['sort'];
            $phone['game_score']=$sibling['game_score'] ;
            $phone['features']= $sibling['features'];
            $phone['introduction']= $sibling['introduction'];
            $phone['icon']= $sibling['icon'];
            $phone['cover']= $sibling['cover'];
            $phone['screenshot']=$sibling['screenshot'] ;
            $phone['material_url']=$sibling['material_url'] ;
            $phone['game_detail_cover']=$sibling['game_detail_cover'] ;
            M('Game','tab_')->data($phone)->where(array('id'=>$another['id']))->save();
            if(!$res){
                $this->error($game->getError());
            } else {
                $this->success($res['id'] ? '更新成功' : '新增成功', U('lists'));
            }

        } else {
            $id || $this->error('id不能为空');
            $data = D(self::model_name)->detailback($id);
            $data || $this->error('数据不存在！');
            if (!empty($data['and_dow_address'])) {
                $data['and_dow_address'] = ltrim($data['and_dow_address'], '.');
            }
            if (!empty($data['ios_dow_address'])) {
                $data['ios_dow_address'] = ltrim($data['ios_dow_address'], '.');
            }
            $this->assign('data', $data);
            $this->meta_title = '编辑游戏';
            $this->display();
        }
    }

    public function set_status($model='Game'){
        parent::set_status($model);
    }

    public function del($model = null, $ids=null){
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        $model = M('Model')->find($model["id"]);
        $model || $this->error('模型不存在！');
        $ids = array_unique((array)I('request.ids',null));
        if ( empty($ids) ) {
            $this->error('请选择要操作的数据!');
        }
        foreach ($ids as $key => $value) {
           $id=$value;
           $gda=M('Game','tab_')->where(array('id'=>$id))->find();
           $map['id']=array('neq',$id);
           $map['relation_game_id']=$gda['relation_game_id'];
           $anogame=M('Game','tab_')->where($map)->find();
           if($anogame){
                M('Game','tab_')->where($map)->data(array('relation_game_id'=>$anogame['id']))->save();
           }
           
           $pic = M('Picture')->find($gda['icon']);
           $count=M('Game','tab_')->where(array('icon'=>$gda['icon']))->count();//统计icon是否为多个游戏的图标
           if($pic!='' && $count ==1){  //只有一个游戏指向这个图标 
                if($pic['oss_url']!=''){
                    $this -> del_oss($gda['icon']);   //删除oss里图片
                }elseif($pic['bos_url']!=''){
                    $this ->del_bos($gda['icon']); //删除bos里的图片
                }
                unlink('.'.$pic['path']);  //删除图片
                M('Picture')->where(array('id'=>$gda['icon']))->delete();
           }


           $gs= M('GameSource','tab_')->where(array('game_id'=>$id))->find();
           if($gs){
                unlink($gs['file_url']);   //删除原包
                M('GameSource','tab_')->where(array('game_id'=>$id))->delete();
           }

           $apply=M('apply','tab_')->where(array('game_id'=>$id))->find();
           if($apply){
                if(substr($apply['pack_url'],0,4)=='http'){
                    if(strpos($apply['pack_url'],'bcebos')!== false){  //$value['pack_url']这个字符串是否包含'bcebos'.'bcebos'可以判断是否为bos存储
                        $objectname=basename($apply['pack_url']);
                        $this->delete_bos($objectname);   //删除bos里的原包
                    }elseif(strpos($apply['pack_url'],'oss')!== false){
                        $objectname=basename($apply['pack_url']);
                        $this->delete_oss($objectname);
                    }
                }

                $file_url = "./Uploads/GamePack/".basename($apply['pack_url']);//删除本地原包
                unlink($file_url);
                M('Apply','tab_')->where(array('game_id'=>$id))->delete();
           }


        }
        $del_map['game_id'] = ['in',$ids];
        M('giftbag','tab_')->where($del_map)->delete();
        M('server','tab_')->where($del_map)->delete();

        parent::remove($model["id"],'Set',$ids);
    }
    //开放类型
    public function openlist(){
        $extend = array(
        );
        parent::lists("opentype",$_GET["p"],$extend);
    }
    //新增开放类型
    public function addopen(){
        if(IS_POST){
            $game=D("opentype");
        if($game->create()&&$game->add()){
            $this->success("添加成功",U('openlist'));
        }else{
            $this->error("添加失败",U('openlist'));
        }
        }else{
            $this->display();
        }
        
    }
    //编辑开放类型
    public function editopen($ids=null){
          $game=D("opentype");
        if(IS_POST){
        if($game->create()&&$game->save()){
             $this->success("修改成功",U('openlist'));
        }else{
           $this->error("修改失败",U('openlist'));
        }
        }else{  
         $map['id']=$ids;
            $date=$game->where($map)->find();
            $this->assign("data",$date);
            $this->display();
        }
    }
    //删除开放类型
    public function delopen($model = null, $ids=null){
       $model = M('Model')->getByName("opentype"); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }
    /**
     * 文档排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort(){
        //获取左边菜单$this->getMenus()
       
        if(IS_GET){
            $map['status'] = 1;
            $list = D('Game')->where($map)->field('id,game_name')->order('sort DESC, id DESC')->select();

            $this->assign('list', $list);
            $this->meta_title = '游戏排序';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = array_reverse(explode(',', $ids));
            foreach ($ids as $key=>$value){
                $res = D('Game')->where(array('id'=>$value))->setField('sort', $key+1);
            }
            if($res !== false){
                $this->success('排序成功！');
            }else{
                $this->error('排序失败！');
            }
        }else{
            $this->error('非法请求！');
        }
    }

    /*
    删除oss里的object   
    param  id   图片id
     */
    public function del_oss($id){
        $data=M('picture')->where("id=$id")->find();
        if(!empty($data)){
            if(!empty($data['oss_url'])){
                $objectname= basename($data['oss_url']);  //返回路径中的文件名部分(带后缀)
                $oss = A('Admin/Oss');
                $res = $oss ->delete_game_pak_oss($objectname);
            }
            
        }
        
    }

    /**
    *删除OSS原包
    * param name 原包名
    */
    public function delete_oss($objectname){
         /**
        * 根据Config配置，得到一个OssClient实例
        */
        try {
            Vendor('OSS.autoload');
            $ossClient = new \OSS\OssClient(C("oss_storage.accesskeyid"), C("oss_storage.accesskeysecr"), C("oss_storage.domain"));
        } catch (OssException $e) {
            $this->error($e->getMessage());
        }
        $bucket = C('oss_storage.bucket');
        $objectname="GamePak/".$objectname;
        try {

            $ossClient->deleteObject($bucket, $objectname);
        return true;
        } catch (OssException $e) {
            /* 返回JSON数据 */
           $this->error($e->getMessage());
        }
    }


    /*
    删除bos里的object   
    param  id   图片id
     */
    public function del_bos($id){
        $data=M('picture')->where("id=$id")->find();
        if(!empty($data)){
            if(!empty($data['bos_url'])){
                $objectname= basename($data['bos_url']); //返回路径中的文件名部分(带后缀)
                $oss = A('Admin/Bos');
                $res = $oss ->delete_bos($objectname);
            }
            
        }
        
    }
    /*
    删除bos的原包
    param name 原包名
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
        //$path ="icon/". $name; //在bos的路径
        $path ="GamePak/". $name;
       
        
        $client->deleteObject($bucket, $path);
            
        
    }

}
