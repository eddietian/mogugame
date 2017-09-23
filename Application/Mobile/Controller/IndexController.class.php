<?php
namespace Mobile\Controller;
use Think\Controller;

/**
* 首页
*/
class IndexController extends BaseController {
    
	protected function _initialize(){

        $config = api('Config/lists');
        
        C($config); 
        
    }

    public function index(){
        $model = M('Game','tab_');
        //热门游戏
        $maph['recommend_status'] = 2;
        $maph['game_status'] = 1;
        $hot=$model
            ->where($maph)
            ->group('relation_game_id')
            ->order('sort desc')
            ->limit(8)
            ->select();
        $this->assign('hot',$hot);
        //最新游戏
        $mapn['recommend_status'] = 3;
        $mapn['game_status'] = 1;
        $new=$model
            ->where($mapn)
            ->group('relation_game_id')
            ->order('sort desc')
            ->limit(8)
            ->select();
        $this->assign('new',$new);
        //推荐游戏
        $mapt['recommend_status'] = 1;
        $mapt['game_status'] = 1;
        $tui=$model
            ->where($mapt)
            ->group('relation_game_id')
            ->order('sort desc')
            ->limit(10)
            ->select();
        $this->assign('tui',$tui);
		$this->display();
	}
    
    
    
	
}