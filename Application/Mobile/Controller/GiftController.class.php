<?php
namespace Mobile\Controller;
use Think\Controller;

/**
* 礼包 鹿文学
*/
class GiftController extends BaseController {
    
	protected function _initialize(){

        $config = api('Config/lists');
        
        C($config); 
        
    }

    public function index() {
		
		$this->display();
	}
    
    public function lists($name='',$id='',$p=1) {
        $gamem = D('Game');
        if (!empty($id) && is_numeric($id) && $id>0) {
            $giftbag = D("Giftbag");
            $time = time();
            $display = "game";
            $game = $gamem->find($id);
            $this->assign('category',array('title'=>$game['game_name'].'礼包'));
            $this->assign('game',$game);
            
            $map1['end_time']=array(array('egt',time()),array('eq',0),'or');
            $map1['game_id']=$id;
            $map1['status']=1;
            $lists = $giftbag->where($map1)->page($p,10)->select();
            
            $count = $giftbag->where("game_id=$id")->count();
            $total = ceil($count/10);
            $this->assign('total',$total);
            
        } else {
            if (!empty($name) && is_numeric($name) && $name>=0 && $name <= 3) {
                $map['map']['tab_giftbag.giftbag_type'] = $name;
                unset($_REQUEST['name']);
                $display = "lists";
                
                $lists = A("Game","Event")->gift_lists($p,$map,true);
                
            } else {
                $display = "all";
                $lists = $gamem->giftbygame($p);
                
                $count = $gamem->where("game_status=1")->count();
                $total = ceil($count/10);
                $this->assign('total',$total);
            }
            $this->assign('category',array('title'=>get_recommend_status($name).'礼包','n'=>$name));
        }
        
        $this->assign('lists',$lists);
        $this->assign('page',1);
        
        $this->display($display);
    }
    
    public function ajaxlists($name='',$id='',$p = 2) {
        $status=0;
        if (!empty($id) && is_numeric($id) && $id>0) {
            $time = time();
            $lists = D("Giftbag")->where("game_id=$id and status=1 and end_time>$time")->page($p,10)->select();            
        } else {
            if (!empty($name) && is_numeric($name) && $name>=0 && $name <= 3) {
                $map['map']['tab_giftbag.giftbag_type'] = $name;
                unset($_REQUEST['name']);
                
                $lists = A("Game","Event")->gift_lists($p,$map);
            } else {
                $lists = D('Game')->giftbygame($p);
            }
        }        
        if (!empty($lists) && is_array($lists)) {
            $status = 1;
        }
        
        echo json_encode(array('status'=>$status,'page'=>$p,'lists'=>$lists));
    }
 
    public function today() {
        $map['tab_giftbag.status'] = 1;
        $start = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $end = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        // var_dump($start);
        // var_dump($end);exit;
        $map['tab_giftbag.start_time'] = array('between',array($start,$end));
        $map['tab_giftbag.end_time'] = array(array('eq','0'),array('egt',time()),'or');
        
        $data = D("Giftbag")
            ->field("tab_giftbag.*,tab_game.icon,tab_game.cover,tab_game.game_name")
            ->join("tab_game on tab_game.id=tab_giftbag.game_id",'left')
            ->where($map)->order("tab_giftbag.start_time")->select();
        if (!empty($data) && is_array($data)) {
            foreach ($data as $k => $v) {
                $count = D("GiftRecord")->where(array('game_id'=>$v['game_id'],'gift_id'=>$v['id']))->count();
                $number = $data[$k]['number'] = count(explode(',',$v['novice']));
                $data[$k]['total'] = $count + $number;
            }
            $this->assign('lists',$data);
        }

        $this->display();
    }
    
    public function detail($id) {
        
        if(!($id && is_numeric($id))){
            
			$this->error('文档ID错误！');
            
		}
        
        $data = D('Giftbag')->detail1($id);
        
        if (empty($data)) {
            
            $this->error('此礼包不存在！！！');
            
        }
        
        $this->assign('category',array('title'=>$data['game_name'].'礼包','url'=>U('detail?id='.$data['id'])));           
        
        $this->assign('data',$data);
		
        $this->display();
    }

}