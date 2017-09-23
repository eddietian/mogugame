<?php
namespace Mobile\Event;
use Think\Controller;
/**
 * 事件控制器
 */
class SearchEvent extends BaseEvent {
    
    public function game_lists($w='',$p=0,$limit=10,$flag=false) {
        $page = intval($p);
        
        $page = $page ? $page : 1;
        
        $model = D("Game");
        
        $replace = preg_match("/^[\x4e00-\x9fa5]+$/",$w)?',game_name,game_name as rgame_name':",replace(game_name,'$w','<em style=color:red>$w</em>') as rgame_name,game_name ";               
                
        // $where = "game_status=1 and game_name like '%$w%' or short like '%$w%' ";
        $where = "game_status=1 and (game_name like '%$w%' or short like '%$w%') ";
        
        $data = $model->field("game_type_id $replace,id,game_type_name,game_score,icon,create_time,and_dow_address,ios_dow_address,features")
            ->where($where)
            ->page($page,$limit)
            ->order("game_name")
            ->select();

        if ($flag) {
            $count = $model
                ->where($where)
                ->count();
                
            $total = intval(ceil($count/$limit));
            
            $this->assign('total',$total);
        }
        
        if (!empty($data) && is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k]['picurl'] = get_cover($v['icon'],'path');
                $data[$k]['describe'] = mb_substr($v['features'],0,80,'utf-8');
                $data[$k]['url'] = U('Game/detail?id='.$v['id']);
            }
        }
        
        return $data;    
        
    }
    
    public function gift_lists($w='',$p=0,$limit=10,$flag=false) {
        $page = intval($p);
        
        $page = $page ? $page : 1;
        
        $model = D("Giftbag");
        
        $replace = preg_match("/^[\x4e00-\x9fa5]+$/",$w)?',g.game_name as rgame_name,g.game_name,gb.giftbag_name,gb.giftbag_name as rgiftbag_name':",g.game_name,replace(g.game_name,'$w','<em style=color:red>$w</em>') as rgame_name,gb.giftbag_name,replace(gb.giftbag_name,'$w','<em style=color:red>$w</em>') as rgiftbag_name ";               
                        
        $where = " g.game_status=1 and gb.status=1 and g.game_name like '%$w%' or g.short like '%$w%' and gb.giftbag_name like '%$w%' ";
        
        $data = $model->field("gb.id,gb.start_time,gb.end_time,gb.novice,gb.level,gb.giftbag_type,gb.desribe,g.game_type_id $replace,g.game_type_name,g.icon")
            ->table("__GIFTBAG__ as gb")
            ->join("__GAME__ as g on(g.id=gb.game_id) ","left")
            ->where($where)
            ->page($page,$limit)
            ->order("g.game_name")
            ->select();
        
        if ($flag) {
            $count = $model->table("__GIFTBAG__ as gb")
                ->join("__GAME__ as g on(g.id=gb.game_id) ","left")
                ->where($where)
                ->count();
                
            $total = intval(ceil($count/$limit));
            
            $this->assign('total',$total);
        }
        
        if (!empty($data) && is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k]['picurl'] = get_cover($v['icon'],'path');
                $data[$k]['desribe'] = mb_substr($v['desribe'],0,80,'utf-8');
                $data[$k]['url'] = U('Gift/detail?id='.$v['id']);
            }
        }
        
        return $data;
    }
    
    public function document_lists($w='',$p=0,$limit=10,$flag=false) {
        $page = intval($p);
        
        $page = $page ? $page : 1;
        
        $time = time();
        
        $model = D("Document");
        
        $replace = preg_match("/^[\x4e00-\x9fa5]+$/",$w)?',d.title,d.title as rtitle':",replace(d.title,'$w','<em style=color:red>$w</em>') as rtitle,d.title  ";                    
                
        $where = " (d.deadline > $time or d.deadline = 0 )and d.status = 1 and d.title like '%$w%' and category_id in('47','48','49')";
        
        $data = $model->field("c.name as cname,c.title as ctitle,d.cover_id,d.id $replace,d.description,d.category_id,d.create_time")
            ->table("__DOCUMENT__ as d")
            ->join("__CATEGORY__ as c on(c.id=d.category_id) ","left")
            ->where($where)
            ->page($page,$limit)
            ->order("d.title")
            ->select();
        
        if ($flag) {
            $count = $model->table("__DOCUMENT__ as d")
                ->join("__CATEGORY__ as c on(c.id=d.category_id) ","left")
                ->where($where)
                ->count();
                
            $total = intval(ceil($count/$limit));
            
            $this->assign('total',$total);
        }
        
        if (!empty($data) && is_array($data)) {
            $arr = array(42=>'Strategy',43=>'News',44=>'Activity');
            foreach ($data as $k => $v) {
                $data[$k]['picurl'] = get_cover($v['cover_id'],'path');
                $data[$k]['description'] = mb_substr($v['description'],0,80,'utf-8');
                $data[$k]['datetime'] = date('y-m',$v['create_time']);
                
                $url = $arr[$data[$k]['category_id']].'/detail?';
                $data[$k]['url'] = U($url.'id='.$v['id']);
            }
        }
        
        return $data;
    }

    public function search_lists($t='tag',$w='',$p=1,$flag=false) {
        $time = time(); $a="news"; $lists = "";  
        switch($t) {
            case 'news':
            case 'strategy':
            case 'activity':
                $a = $t;
                $t = 'news';
                $lists = $this->document_lists($w,$p,10,$flag);
                break;
            case 'game':                
                $lists = $this->game_lists($w,$p,10,$flag);
                break;
            case 'gift':
                $lists = $this->gift_lists($w,$p,10,$flag);               
                break;
            default: 
               
                $news = $this->document_lists($w,$p,10);

                $game = $this->game_lists($w,$p,10);
               
                $gift = $this->gift_lists($w,$p,10); 
                
                $t = 'tag';
                $this->assign('news',$news);
                $this->assign('game',$game);
                $this->assign('gift',$gift);                               
        }
        
        $this->assign('t',$t);
        $this->assign('w',$w);
        $this->assign('a',$a);
     
        return $lists;
    }
    
    
    
}
