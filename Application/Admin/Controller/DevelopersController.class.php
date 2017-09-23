<?php

namespace Admin\Controller;
use Admin\Model\DevelopersModel;
use Open\Model\ContractModel;
use Open\Model\OpenMessageModel;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class DevelopersController extends ThinkController {

    const model_name = 'Developers';

    public function lists(){
        if(isset($_GET['account'])){
        $map['account']=I('get.account');
        }
        if(isset($_GET['status'])){
        	$map['status']=I('get.status');
        }
        $model = array(
            'm_name' => 'Developers',
            'map'    => $map,
            'title'  => '开发者',
            'template_list' =>'lists',
            'order'=>'id desc',
        );
        $user = A('User','Event');
        $user->user_join_($model,$_GET['p']);
    }

    public function record(){
        parent::lists('GiftRecord',$_GET["p"],$extend);
    }

    public function edit($id=0){

		$_REQUEST['id'] || $this->error('请选择要编辑的用户！');
		$model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
		//获取模型信息
        $model = M('Model')->find($model['id']);
        $model || $this->error('模型不存在！');
	    if(empty(I('post.password'))){
		    unset($_POST['password']);
	    }
        if(IS_POST){
            $Model  =   new DevelopersModel();
            // 获取模型的字段信息
            $Model  =   $this->checkAttr($Model,$model['id']);
            $data = $Model->create();
            if($data){
	            if(empty(I('post.password'))){
		            unset($data['password']);
	            }
                $Model->save($data);
                $this->success('保存'.$model['title'].'成功！', U('lists?model='.$model['name']));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields     = get_model_attribute($model['id']);
            //获取数据
            $data       = D("developers")->getUserData($id);
            $data || $this->error('数据不存在！');

            $this->assign('model', $model);
            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->meta_title = '编辑开发者列表';
            $this->display($model['template_edit']?$model['template_edit']:'');
        }
    }

    public function del($model = null, $ids=null){
        if(isset($_GET['model'])&&$_GET['model']==20){
            $this->delServer($id);exit;
        }
        $model = M('Model')->getByName(self::model_name); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }
    public function get_ajax_area_list(){
        $area = D('Server');
        $map['game_id'] = I('post.game_id',1);
        $list = $area->where($map)->select();
        $this->ajaxReturn($list);
    }
    /**
     * [game 游戏列表]
     * @return [type] [description]
     */
    public function game(){
        if(isset($_REQUEST['game_name'])){
                $extend['game_name'] = $_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
        }
        if(isset($_REQUEST['sdk_version1'])){
            $extend['sdk_version']=$_REQUEST['sdk_version1'];
            unset($_REQUEST['sdk_version1']);
        }
        $extend['developers']=array('gt',0);
        $extend['order']='id desc';
        $extend['for_show_pic_list']='icon';//列表显示图片
        parent::lists('Game',$_GET["p"],$extend);
    }
    /**
     * [gameEdit 编辑游戏]
     * @return [type] [description]
     */
    public function gameEdit($id = null){

       if(IS_POST) {
            /*if($_POST['apply_status']==0&&$_POST['game_status']==1){
               $this->error('游戏未审核不允许显示');//游戏添加完成
        }*/
            $game = D('Game');//M('$this->$model_name','tab_');
            $_POST['discount'] ==''?$_POST['discount'] = 10:$_POST['discount'];
            $before=$game->find($_POST['id']);
            if($before['game_status']==0&&$_POST['game_status']==1){
                $_POST['online_time']=strtotime($_POST['online_time'])?strtotime($_POST['online_time']):time();
            }else{
                unset($_POST['online_time']);
            }
            $res = $game->update();
            $id = $res["id"];
            $sibling = D("Game")->find($id);
            $map['relation_game_id'] = $sibling['relation_game_id'];
            $sid=$sibling['id'];
            $map['id'] = array('neq',$sid);
            $another=D("Game")->where($map)->find();  //获取另一个所有
            $phone['game_type_id'] =$sibling['game_type_id'];
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
                $this->success($res['id'] ? '更新成功' : '新增成功', U('game'));
            }

        } else {
            $id || $this->error('id不能为空');
            $data = D('Game')->detailback($id);
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
    /**
     * [get_game_set 获取对接参数]
     * @return [type] [description]
     */
    public function get_game_set(){
        $map["game_id"] =$_REQUEST['game_id'];
        $find=M('game_set','tab_')->where($map)->find();
        echo json_encode(array("status"=>1,"data"=>$find));
    }
    /**
     * [delGame 删除游戏]
     * @param  [type] $model [description]
     * @param  [type] $ids   [description]
     * @return [type]        [description]
     */
    public function delGame($model = null, $ids=null){
        $model = M('Model')->getByName('Game'); /*通过Model名称获取Model完整信息*/
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
        }
        $del_map['game_id'] = ['in',$ids];
        M('giftbag','tab_')->where($del_map)->delete();
        M('server','tab_')->where($del_map)->delete();

        parent::remove($model["id"],'Set',$ids);
    }
    /**
     * [set_status 审核游戏]
     * @param string $model [description]
     */
    public function set_status($model='Game'){
        $ids      = I('request.ids');
        $status   = I('request.status');
        $msg_type = I('request.msg_type',1);
        $field    = I('request.field');
        if($field=="apply_status"){
            foreach ($ids as $key => $value) {
	            $game = M("game","tab_")->find($value);
	            $msg = new OpenMessageModel();
	            if($status == 1 && !empty($game['developers'])){//审核通过
		            $map['game_id']=$value;
		            M('contract','tab_')->where($map)->setField('status',2);
		            $msg->sendMsg($game['developers'],"游戏审核通过","恭喜您，游戏：‘{$game['game_name']}’ 已通过审核。");
	            }elseif($status == 2 && !empty($game['developers'])){//驳回
		            $msg->sendMsg($game['developers'],"游戏审核未通过","抱歉，游戏：‘{$game['game_name']}’ 未通过审核，请联系客服。");
	            }
            }
        }elseif($field=="game_status"){
                $game=M('game','tab_')->find($ids);
                if($game['game_status']==0&&$game['online_time']==""&&$status==1){
                    $tmap['id']=$ids;
                    $tsave['online_time']=time();
                    M('game','tab_')->where($tmap)->save($tsave);
                }
        }
        parent::set_status($model);
    }
    public function set_gift_status($model='Giftbag'){
        parent::set_status($model);
    }
    /**
     * [addGame 新增游戏]
     */
        public function addGame(){
        if(IS_POST){
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
            $game   =   D('Game');//M('$this->$model_name','tab_');
            $res = $game->update();
            if(!$res){
                $this->error($game->getError());
            }else{
                $this->success($res['id']?'更新成功':'新增成功',U('game'));
            }
        }else{
            $this->meta_title = '新增游戏';
            $this->display();
        }
    }
    /**
     * [relation 关联游戏]
     * @return [type] [description]
     */
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
            $game   =   D('Game');//M('$this->$model_name','tab_');
            $res = $game->update();
            if(!$res){
                $this->error($game->getError());
            }else{
            	$game_id = M("game","tab_")->field("id")->where(['game_name'=>$_POST['game_name']])->find()['id'];
	            //增加合同信息
	            $Contract = new ContractModel();
	            $Contract->addContract($game_id,$res['developers']);
                $this->success($res['id']?'更新成功':'新增成功',U('game'));
            }
        }else{
            $_REQUEST['id'] || $this->error('id不能为空');
            $map['relation_game_id']=$_REQUEST['id'];
            $map['id']=$_REQUEST['id'];
            $map1=$map;
            $map1['id']=array('neq',$_REQUEST['id']);
            $inv=D('Game')->where($map)->find();
            $invalid=D('Game')->where($map1)->find();
            if($invalid||$inv==''){
               $this->error('关联数据错误');
            }
            $this->assign('data',$inv);
            $this->meta_title = '关联游戏';
            $this->display();
        }
    }
    function refuse_reason(){
        if($_POST['reason']==""||$_POST['ids']==""){
            $this->ajaxReturn(array("status"=>0,"msg"=>"缺少必要参数！"));exit;
        }else{
            $refArr=explode(",",$_POST['ids']);
            unset($refArr[count($refArr)-1]);
            foreach ($refArr as $key => $value) {
                $map['id']=$value;
                $save['status']=3;
                $save['refuse_reason']=$_POST['reason'];
                $res[]=M('developers','tab_')->where($map)->save($save);
                if($res[$key]){
                    $Message = new OpenMessageModel();
                    $Message->sendMsg($value,"资料未通过审核",$_POST['reason']);
                }
            }
            foreach ($res as $k=>$v){
                if($v){
                    $this->ajaxReturn(array("status"=>1,"msg"=>"驳回成功！"));exit;
                }
            }
            $this->ajaxReturn(array("status"=>2,"msg"=>"驳回失败！"));exit;

        }
    }
    /**
     * [source 原包列表]
     * @return [type] [description]
     */
    public function source(){
        if(isset($_REQUEST['game_name'])){
            $extend['game_name']=array('like','%'.$_REQUEST['game_name'].'%');
            unset($_REQUEST['game_name']);
        }
        if(isset($_REQUEST['sdk_version'])){
            $extend['file_type']=$_REQUEST['sdk_version'];
            unset($_REQUEST['sdk_version']);
        }
        $extend['develop_id']=array('gt',0);
        parent::lists('GameSource',$_GET["p"],$extend);
    }
    /**
     * [addSource 增加原包]
     * @param string $value [description]
     */
        public function addSource($value='')
    {
        if(IS_POST){
//             if(empty($_POST['version'])){
//                $this->error('原包版本不能为空');
//            }
            if(empty($_POST['game_id'])){
                $this->error('游戏名称不能为空');
            }
            $game=M('Game','tab_')->where(array('id'=>$_POST['game_id']))->find();
            $_POST['game_name']=$game['game_name'];
            if(empty($_POST['file_name'])){
                $this->error('未上传游戏原包');
            }else{
                $extend=substr($_POST['file_name'],strlen($_POST['file_name'])-3,3);
                if($_POST['file_type']==1&&$extend!='apk'){
                    $this->error('游戏原包格式不正确！');
                }else if($_POST['file_type']==2&&$extend!='ipa'){
                    $this->error('游戏原包格式不正确！');
                }
            }
            if($_POST['file_type']==2&&empty($_REQUEST['file_name2'])){
                $this->error('未上传描述文件');
            }
            $map['game_id']=$_POST['game_id'];
            $map['file_type'] = $_POST['file_type'];
            $d = D('Game_source')->where($map)->find();
            $source = A('Source','Event');
            if(empty($d)){
                $source->add_source();
            }
            else{
            $this->error('游戏已存在原包',U('Developers/source'));
            }
        }
        else{
            $this->meta_title = '新增游戏原包';
            $this->display();
        }

    }
    /**
     * [delSource 删除原包]
     * @param  [type] $model [description]
     * @param  [type] $ids   [description]
     * @return [type]        [description]
     */
    public function delSource($model = null, $ids=null){
        if ( empty($ids) ) {
            $this->error('请选择要操作的数据!');
        }
        $model = M('Model')->find($model);
        $model || $this->error('模型不存在！');
        $id = array_unique((array)I('ids',0));   //var_dump($id);exit;
        foreach ($id as $key => $value) {
            $arr=explode(',',$value);
            $ids=reset($arr);
            $file_type[]=next($arr);
            $game_id[]=end($arr);
        }
        $game=D('Game');
        $Model = D(get_table_name($model['id']));
        $map = array('id' => array('in', $ids) );
        for ($i=0; $i <count($game_id) ; $i++) {
            $maps['id']=$game_id[$i];
            if($file_type[$i]==1){
                $dell = array('and_dow_address'=>'');
                $game->where($maps)->setField($dell);
            }else{
                $dell = array('ios_dow_address'=>'');
                $game->where($maps)->setField($dell);
            }
        }
        $souce=M("GameSource","tab_");
        $mapp['id']=array("in",$ids);
        $list=$souce->where($mapp)->select();
        foreach ($list as $key => $value) {
            @unlink($value['file_url']);
            @unlink($value['description_url']);
            @unlink($value['plist_url']);
        }
        if($Model->where($map)->delete()){
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    /**
     * [editSource 编辑原包]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function editSource($id){
        $map['id']=$id;
        if(IS_POST){
            if(empty($_POST['file_name'])){
                $this->error('未上传游戏原包');
            }else{
                $extend=substr($_POST['file_name'],strlen($str)-3,3);
                if($_POST['file_type']==1&&$extend!='apk'){
                    $this->error('游戏原包格式不正确！');
                }else if($_POST['file_type']==2&&$extend!='ipa'){
                    $this->error('游戏原包格式不正确！');
                }
            }
              if($_POST['file_type']==2&&empty($_REQUEST['file_name2']) && empty(I('edit_description'))){
                $this->error('未上传描述文件');
            }
            $map['file_type'] = $_POST['file_type'];
            $d = D('Game_source')->where($map)->find();
            $source = A('Source','Event');
            if(empty($d)){
                $source->add_source();
            }
            else{
                $source->update_source($d['id'],$d['file_name'],"dev");
            }
        }
        else{
            $d = M('GameSource',"tab_")->where($map)->find();
            $this->meta_title = '更新游戏原包';
            $this->assign("data",$d);
            $this->display('editSource');
        }

    }
    /**
     * [server 区服列表]
     * @return [type] [description]
     */
    public function server(){
        if(isset($_REQUEST['show_status'])){
            $extend['show_status']=$_REQUEST['show_status'];
            unset($_REQUEST['show_status']);
        }
        if(isset($_REQUEST['server_version'])){
            $extend['server_version']=$_REQUEST['server_version'];
            unset($_REQUEST['server_version']);
        }
        if(isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])){
            $extend['start_time']  =  array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }elseif(isset($_REQUEST['time-start'])){
            $extend['start_time']=array('EGT',strtotime($_REQUEST['time-start']));
        }elseif(isset($_REQUEST['time-end'])){
            $extend['start_time']=array('ELT',strtotime($_REQUEST['time-end']));
        }
        if(isset($_REQUEST['start']) && isset($_REQUEST['end'])){
            $extend['start_time']  =  array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $extend['game_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
         $extend['developers']=array('gt',0);
        parent::order_lists('Server',$_GET["p"],$extend);
    }

    public function addServer(){
        if(IS_POST){
            $model = M('Model')->getByName('Server');
           parent::add($model["id"]);
        }else{
            $this->meta_title = '新增区服';
            $this->display('addServer');
        }
    }

    public function editServer($id=0){
        $id || $this->error('请选择要编辑的用户！');
        $model = M('Model')->getByName('Server'); /*通过Model名称获取Model完整信息*/
        if(IS_POST){
            $Model  =   D(parse_name(get_table_name($model['id']),1));
            // 获取模型的字段信息
            $Model  =   $this->checkAttr($Model,$model['id']);
            if($Model->create() && $Model->save() !== false){
                $this->success('保存'.$model['title'].'成功！', U('Developers/server'));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $map['id']=$id;
            $d = M('Server',"tab_")->where($map)->find();
            $this->meta_title = '更新游戏原包';
            $this->assign("data",$d);
            $this->display('editServer');
        }
    }

    public function delServer($model = null, $ids=null){
        $model = M('Model')->getByName('Server'); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }

    //批量新增
    public function batch(){
        if(IS_POST){
            $server_str = str_replace(array("\r\n", "\r", "\n"), "", I('server'));
            $server_ar1 = explode(';',$server_str);
            array_pop($server_ar1);
            $num = count($server_ar1);
            if($num > 100 ){
                $this->error('区服数量过多，最多只允许添加100个！');
            }
            $verify = ['game_id','server_name','time'];
            foreach ($server_ar1 as $key=>$value) {
                $arr = explode(',',$value);
                foreach ($arr as $k=>$v) {
                    $att = explode('=',$v);
                    if(in_array($att[0],$verify)){
                        switch ($att[0]){
                            case 'time' :
                                $time = $server[$key]['start_time'] = strtotime($att[1]);
                                if($time < time()){
                                    $this->error('开服时间不能小于当前时间');
                                }
                                break;
                            case 'game_id':
                                $game = M('Game','tab_')->find($att[1]);
                                if(empty($game)){
                                    $this->error('游戏ID不存在');
                                }
                                $server[$key]['game_id'] = $att[1];
                                break;
                            default:
                                $server[$key][$att[0]] = $att[1];
                        }
                    }
                }
                $server[$key]['game_name'] = get_game_name($server[$key]['game_id']);
                $server[$key]['server_num'] = 0;
                $server[$key]['recommend_status'] = 1;
                $server[$key]['show_status'] = 1;
                $server[$key]['stop_status'] = 0;
                $server[$key]['server_status'] = 0;
                $server[$key]['parent_id'] = 0;
                $server[$key]['create_time'] = time();
                $version = get_sdk_version($server[$key]['game_id']);
                $server[$key]['server_version'] = empty($version) ? 0 : $version;
            }
            $res = M('server','tab_')->addAll($server);
            if($res !== false){
                $this->success('添加成功！',U('Developers/server'));
            }else{
                $this->error('添加失败！'.M()->getError());
            }
        }else{
            $this->meta_title = '新增区服管理';
            $this->display();
        }
    }
    /**
     * 礼包列表
     */
    public function gift(){
        $extend = array('key'=>'gift_name');
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $extend['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }elseif(isset($_REQUEST['time-start'])){
            $extend['create_time']=array('egt',strtotime($_REQUEST['time-start']));
            unset($_REQUEST['time-start']);
        }elseif(isset($_REQUEST['time-end'])){
            $extend['create_time']=array('elt',strtotime($_REQUEST['time-end'])+24*60*60-1);
            unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $extend['game_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        if(isset($_REQUEST['giftbag_name'])){
            $extend['giftbag_name']=array('like','%'.$_REQUEST['giftbag_name'].'%');
            unset($_REQUEST['giftbag_name']);
        }
        if(isset($_REQUEST['status'])){
            if($_REQUEST['status']=='全部'){
                unset($_REQUEST['status']);
            }else{
                $extend['status']=$_REQUEST['status'];
                unset($_REQUEST['status']);
            }
        }
        if(isset($_REQUEST['giftbag_version'])){
            if($_REQUEST['giftbag_version']=='全部'){
                unset($_REQUEST['giftbag_version']);
            }else{
                $extend['giftbag_version']=$_REQUEST['giftbag_version'];
                unset($_REQUEST['giftbag_version']);
            }
        }
        $extend['for_show_pic_list']='novice';
        $extend['developers']=array('gt',0);
        parent::order_lists('Giftbag',$_GET["p"],$extend);

    }
    public function recordGift(){
        if(isset($_REQUEST['game_name'])){
                $extend['game_name']=trim($_REQUEST['game_name']);
                unset($_REQUEST['game_name']);
        }
        if (isset($_REQUEST['user_account'])) {
            $extend['user_account']=array('like','%'.trim($_REQUEST['user_account']).'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['sdk_version'])){
            if($_REQUEST['sdk_version'] ==''){
                unset($_REQUEST['sdk_version']);
            }else{
                $map['sdk_version'] = $_REQUEST['sdk_version'];
                $game_ids = M('game','tab_')->field('id')->where($map)->select();
                $game_ids = array_column($game_ids,'id');
                $extend['sdk_version'] = ['in',$game_ids];
                unset($_REQUEST['sdk_version']);
            }
        }
        parent::lists('GiftRecord',$_GET["p"],$extend);
    }

    public function addGift(){
        if(IS_POST){
            $Model  =   D('Giftbag');
            // 获取模型的字段信息
            $Model  =   $this->checkAttr($Model,$model['id']);
            if($_REQUEST['end_time']!=''){
                if(strtotime($_REQUEST['start_time'])>strtotime($_REQUEST['end_time'])){
                    $this->error('请输入正确开始结束时间');
                }
            }
            $data = $Model->create();
            if($data){
                $data['novice'] = str_replace(array("\r\n", "\r", "\n"), ",", $_POST['novice']);
                $data['server_name']=get_server_name($data['server_id']);
                $Model->add($data);
                $this->success('添加'.$model['title'].'成功！', U('Developers/gift'));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $this->meta_title = '新增礼包';
            $this->display('addGift');
        }
    }

    public function editGift($id=0){
        $_REQUEST['id'] || $this->error('请选择要编辑的用户！');
        $model = M('Model')->getByName('Giftbag'); /*通过Model名称获取Model完整信息*/
        //获取模型信息
        $model = M('Model')->find($model['id']);
        $model || $this->error('模型不存在！');

        if(IS_POST){
            $Model  =   D(parse_name(get_table_name($model['id']),1));
            // 获取模型的字段信息
            $Model  =   $this->checkAttr($Model,$model['id']);
            if($_REQUEST['end_time']!=''){
                if(strtotime($_REQUEST['start_time'])>strtotime($_REQUEST['end_time'])){
                    $this->error('请输入正确开始结束时间');
                }
            }
            $data = $Model->create();
            if($data){
                if($data['apply_status']!=1){
                    $data['status']=0;
                }else{
                    $data['status']=1;
                }
                $data['novice'] = str_replace(array("\r\n", "\r", "\n"), ",", $_POST['novice']);
                $Model->save($data);
                $this->success('保存'.$model['title'].'成功！', U('Developers/gift'));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields     = get_model_attribute($model['id']);
            //获取数据
            $data       = D(get_table_name($model['id']))->find($id);
            $data || $this->error('数据不存在！');

            $this->assign('model', $model);
            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->meta_title = '编辑礼包';
            $this->display($model['template_edit']?$model['template_edit']:'');
        }
    }

    public function delGift($model = null, $ids=null){
        $model = M('Model')->getByName('Giftbag'); /*通过Model名称获取Model完整信息*/
        parent::del($model["id"],$ids);
    }

	/**
	 * 审核/锁定/解锁用户
	 * @param $ids
	 * author: xmy 280564871@qq.com
	 */
    public function lock_user($ids,$status){
    	$model = new DevelopersModel();
    	$res = $model->lockUser($ids,$status);
    	if ($res !== false){
    		$this->success("操作成功");
	    }else{
    		$this->success("操作失败");
	    }
    }
}
