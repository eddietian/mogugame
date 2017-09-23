<?php
namespace Mobile\Controller;
use Think\Controller;

/**
* 首页
*/
class GameController extends BaseController {
    
	protected function _initialize(){

        $config = api('Config/lists');
        
        C($config); 
        
    }

    public function index() {

		$this->display();
	}
    
    public function lists($name='',$type='',$p=1) {

        $map = $this->condition($name,$type);
        
        $list = A("Game","Event")->game_lists('Game',$p,$map,true);
        
//        if (empty($list) || !is_array($list)) {$this->error('暂无数据');}
        
        $this->assign('lists',$list);
        
        $this->assign('category',array('title'=>$map['title']));
        
        $this->assign('name',$name);
        
        $this->assign('type',$type);
        
        $this->assign('page',$p);
        
        $this->display();
    }
    
    public function ajaxlists($name='',$type='',$condition='',$p=2) {
        
        $status = 0;
        
        $map = $this->condition($name,$type,$condition);
        
        $list = A("Game","Event")->game_lists('Game',$p,$map);
        
        if (!empty($list) && is_array($list)) {$status=1;}
        
        echo json_encode(array('status'=>$status,'page'=>$p,'lists'=>$list));
    }
    
    public function detail($id='') {
        if (!empty($id) && is_numeric($id)) {
            
            $data = D('Game')->detail($id);
            
            if (empty($data) || !is_array($data)) {$this->error('此游戏不存在！！');}
//            var_dump($data);exit;
            $this->assign('data',$data);
            
            $this->assign('category',array('title'=>$data['game_name'].'专区','url'=>U('detail?id='.$data['id'])));
                    
            $this->display();
            
        } else {
            echo "<script>javascript:history.go(-1);</script>";exit;
        }

    }
    
    
    public function category() {
        $this->assign('category',array('title'=>'分类'));
        $this->display();
    }
    
    public function open($p=1) {
        $map['show_status'] = 1;
        $extend['map'] = $map;
        $lists = A("Game","Event")->server_lists($p,$extend,true);
//        var_dump($lists);
        $lists=game_merge($lists,$lists['map']);
        if (empty($lists) || !is_array($lists)) {$this->error('暂无数据');}
        
        
        $this->assign('lists',$lists);
        
        $this->assign('category',array('title'=>'开服'));
        
        $this->assign('name',$name);
        
        $this->assign('page',$p);
        
        $this->display('open');
    }
    
    public function ajaxopen($p=2) {
        
        $status=0;
    
        $list = A("Game","Event")->server_lists($p);
        
        if (!empty($list) && is_array($list)) {$status=1;}
        
        echo json_encode(array('status'=>$status,'page'=>$p,'lists'=>$list));
    }

    public function rank($name='',$p=1) {
        $map = $this->condition($name,'','rank');

        $list = A("Game","Event")->game_lists('Game',$p,$map,true);
        
        if (empty($list) || !is_array($list)) {$this->error('暂无数据');}
        
        $this->assign('lists',$list);
        
        $this->assign('category',array('title'=>'游戏排行榜'));
        $this->assign('name',$name);
        $this->assign('page',$p);
        $this->display();
    } 
    
    
    public function condition($name='',$type='',$condition='') {
        $where = " game_status=1 and apply_status = 1";
        if ($condition == 'rank') {
            switch ($name) {
                case 'hp':
                    $map['order'] = "game_score desc";
                    break;
                default:
                    $name = '';
                    $map['order'] = "dow_num desc";
                    $map['map']['recommend_status'] = 2;
            }
            $map['map']['_string'] = $where;
        } else { 
            if ($type && is_numeric($type)) 
                switch($name) {
                    case 'category':$where .= " and category = $type ";$title = get_open_type($type);break;
                    default:$where .= " and game_type_id = $type ";$title = get_game_type_name($type);
                }
            $map['map']['_string'] = $where;
            $map['title'] = empty($title)?'全部':$title;
        }
        return $map;
    }
	
}