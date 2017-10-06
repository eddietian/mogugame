<?php

namespace Media\Controller;
use OT\DataDictionary;
use User\Api\MemberApi;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends BaseController {

	//系统首页
    public function index(){
        $this->nav();
        $this->slide();//轮播图
        $this->recommend();
        $this->hot();
        $this->area();
        $this->slide(2);//中部广告
        $this->newzixun(9);
        $this->slide(4);//资讯广告
        $this->gift();
        
        
        $user_id=session('member_auth.mid');
        $user=get_user_entity($user_id)==false?session('member_auth'):get_user_entity($user_id);
        C(api('Config/lists'));
        if (C('UC_SET') == 1 && session('member_auth.nickname') == 'Uc用户') {
            $user_play = null;
            $sqltype = 2;
            $ucuser = M('User','tab_',C('DB_CONFIG2'))->where(array('account'=>session('member_auth.account')))->find();
            if($ucuser==''){
                $sqltype = 3;
                $ucuser = M('User','tab_',C('DB_CONFIG3'))->where(array('account'=>session('member_auth.account')))->find();
            }
            $this->assign('uc_balance', $ucuser['balance']);
        } else{
            $user_play=M('User_play as u','tab_')
                    ->field('g.relation_game_id,g.relation_game_name,u.server_name,icon')
                    ->join('tab_game g on u.game_id = g.id ')
                    ->where(array('user_id'=>$user['id']))
                    ->group('g.relation_game_id')
                    ->limit(2)
                    ->select();
        }
        $this->assign('user_play',$user_play);
        $this->assign('user',$user);
        $this->display();
    }

    public function nav() {
        $navinfo = $this->getHtmlContent(array("HOME_NAV_NEWGAME", "HOME_NAV_ANDROIDGAME", "HOME_NAV_IOSGAME"));

        if (empty($navinfo)) {
            return false;
        }

        $gameinfoids = array();
        //拿到gameid
        foreach ($navinfo as $key => $v) {
            $ids = explode(",", $v['content']);
            $navinfo[$key]['ids'] = $ids;
            $gameinfoids = array_merge($gameinfoids,$ids);
        }

        //拿到游戏信息
        $gameinfo = $this->getGameByGameids($gameinfoids);

        foreach ($navinfo as $key => $v) {
            foreach ($v['ids'] as $kk => $gameid) {
                $navinfo[$key]['ids'][$kk] = $gameinfo[$gameid];
            }
        }

        $this->assign("nav_newgame", array_slice($navinfo['HOME_NAV_NEWGAME']['ids'], 0, 10));
        $this->assign("nav_newgame_other", array_slice($navinfo['HOME_NAV_NEWGAME']['ids'], 10, 20));


        $this->assign("nav_androidgame", array_slice($navinfo['HOME_NAV_ANDROIDGAME']['ids'], 0, 10));
        $this->assign("nav_androidgame_other", array_slice($navinfo['HOME_NAV_ANDROIDGAME']['ids'], 10, 20));


        $this->assign("nav_iosgame", array_slice($navinfo['HOME_NAV_IOSGAME']['ids'], 0, 10));
        $this->assign("nav_iosgame_other", array_slice($navinfo['HOME_NAV_IOSGAME']['ids'], 10, 20));

    }


    public function slide($pos_id=1){
        $adv = M("Adv","tab_");
        $map['status'] = 1;
        $map['pos_id'] = $pos_id; #首页轮播图广告id
        $map['start_time']=array(array('lt',time()),array('eq',0), 'or') ;
        $map['end_time']=array(array('gt',time()),array('eq',0), 'or') ;
        $carousel = $adv->where($map)->order('sort desc')->select();
        if($pos_id==2){
            // 设置媒体中部广告为一张 随机显示
        $carousel = $adv->where($map)->limit(1)->select();
            $this->assign("midcarousel",$carousel);
        }elseif($pos_id==4){
            $this->assign("zixuncarousel",$carousel);
        }else{
            $this->assign("carousel",$carousel);
        }
        
    }
    /***
	*推荐游戏
    */
    public function recommend(){
    	$model = array(
    		'm_name'=>'Game',
    		'prefix'=>'tab_',
    		'map'   =>array('game_status'=>1,'recommend_status'=>1),
    		'field' =>'*,min(id) as id',
    		'order' =>'RAND()',
            'group' =>'relation_game_id',
    		'limit' =>4,
    	);
    	$reco = parent::list_data($model);
        $reco=game_merge($reco,$model['map']);
    	$this->assign('recommend',$reco);
    }
    /***
    *推荐游戏
    */
    public function recommend1(){
        $model = array(
            'm_name'=>'Game',
            'prefix'=>'tab_',
            'map'   =>array('game_status'=>1,'recommend_status'=>1),
            'field' =>'*,min(id) as id',
            'order' =>'RAND()',
            'group' =>'relation_game_id',
            'limit' =>4,
        );
        $reco = parent::list_data($model);
        $reco=game_merge($reco,$model['map']);
        foreach ($reco as $key=>$value){
            $reco[$key]['game_detail']=U('Game/game_detail',array('id'=>$value['relation_game_id']));
            $reco[$key]['dow_url_generate']=U('Game/dow_url_generate',array('id'=>$value['relation_game_id']));
            $reco[$key]['relation_game_id']=U('Game/game_detail',array('id'=>$value['relation_game_id']));
            $reco[$key]['cover']=get_cover($value['cover'],'path');
            $reco[$key]['percent']=$value['game_score']*10;
            $reco[$key]['down_total']=$value['anddow']+$value['iosdow'];

        }
        if($reco){
            $this->ajaxReturn(array('status'=>1,'data'=>$reco));
        }else{
            $this->ajaxReturn(array('status'=>0,'data'=>"没有更多了~"));
        }
    }

    /***
	*热门游戏
    */
    public function hot($limit = 9){
    	$model = array(
    		'm_name'=>'Game',
    		'prefix'=>'tab_',
    		'map'   =>array('game_status'=>1,'recommend_status'=>2),
    		'field' =>'*,min(id) as id',
    		'order' =>'id desc',
            'group' =>'relation_game_id',
    		'limit' =>9,
    	);
    	$hot = parent::list_data($model);
        $hot = game_merge($hot,$model['map']);
    	$this->assign('hot',$hot);
    }

    /***
    *最新游戏
    */
    public function newgame($recommend_status=3,$order='sort desc'){
        $model = array(
            'm_name'=>'Game',
            'prefix'=>'tab_',
            'map'   =>array('game_status'=>1,'recommend_status'=>$recommend_status),
            'field' =>'*,min(id) as id,@counter:=@counter+1 AS Rank',
            'order' =>$order,
            'group' =>'relation_game_id',
            'limit' =>9,
        );
        $newgame = parent::list_data($model);
        $newgame = array_order(game_merge($newgame,$model['map']));
        // var_dump($newgame);exit;
        $this->assign('new',$newgame);
    }
    
    /***
	*游戏礼包
    */
    public function gift(){
        $map['game_status']=1;
        $map['end_time']=array(array('gt',time()),array('eq',0),'or');
        $map['giftbag_type']=2;
    	$model = array(
    		'm_name'=>'Giftbag',
    		'prefix'=>'tab_',
    		'field' =>'tab_giftbag.id as gift_id,relation_game_name,game_id,tab_giftbag.game_name,giftbag_name,giftbag_type,tab_game.icon,tab_giftbag.create_time',
    		'join'	=>'tab_game on tab_giftbag.game_id = tab_game.id',
    		'map'   =>$map,
    		'order' =>'create_time desc',
    		'limit' =>10,
    	);
    	$gift = parent::join_data($model);
        foreach ($gift as $key => $value) {
            $gift[$key]['gift_num']=gift_recorded($value['game_id'],$value['gift_id']);
        }
    	$this->assign('gift',$gift);
    }

    /***
	*游戏区服
    */
    public function area(){
    	$model = array(
            'm_name'=>'server',
            'prefix'=>'tab_',
            'field' =>'tab_server.*,tab_game.relation_game_name,tab_game.relation_game_id,tab_game.icon,tab_game.cover,tab_giftbag.id as gift_id',
            'join'	=>' left join tab_game on tab_server.game_id = tab_game.id left join tab_giftbag on tab_server.game_id = tab_giftbag.game_id',
            'map'   =>array('game_status'=>1,'show_status'=>1,'tab_server.start_time'=>array('lt',time())),
            'group' =>'tab_server.id',
            'order' =>'start_time desc',
            'limit'=>45,
    	);
    	$area = parent::join_data($model);
    	$this->assign('area',$area);
    }
    
    
    public function newzixun($limit=9){
        $map['sys_category.name']=array('in','media_gg,media_activity,media_zx');//显示公告和资讯
        $map['sys_document.status']=1;
        $map['sys_document.display']=1;
        $map['deadline']=array(array('gt',time()),array('eq',0),'or');
        $data=M('document')
                ->field('sys_category.name,cover_id,category_id,sys_document.description,sys_document.title,sys_document.update_time,sys_document.id')
                ->join('sys_category on sys_document.category_id = sys_category.id and sys_category.pid = 39')
                ->where($map)
                // ->limit($limit)
                ->order('level desc')
                ->select();
        foreach ($data as $key => $value) {
            if($value['name']=='media_activity'){
                $huodong[]=$value;
                unset($data[$key]);
            }
        } 
        $this->assign('firhuodong',$huodong[0]);
        unset($huodong[0]);  
        $this->assign('huodong',$huodong);    
        $this->assign('newzixun',$data);
    }



    public function download(){
    	$app_map['name'] = ['like','%联运APP%'];
    	$app_map['version'] = 1;
	    $android = M('app', 'tab_')->where($app_map)->find();
	    $app_map['version'] = 2;
	    $ios = M('app', 'tab_')->where($app_map)->find();
        $this->assign('android',$android);
        $this->assign('ios',$ios);
        $this->display();
    }

    public function qrcode($url='pc.vlcms.com',$level=3,$size=4){
        $url = base64_decode(base64_decode($url));
        Vendor('phpqrcode.phpqrcode');
        $errorCorrectionLevel =intval($level) ;//容错级别 
        $matrixPointSize = intval($size);//生成图片大小 
        //生成二维码图片
        //echo $_SERVER['REQUEST_URI'];
        ob_clean();
        $object = new \QRcode();
        echo $object->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);   
    }

    public function down_app(){

    }
}