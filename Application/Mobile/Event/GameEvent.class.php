<?php
namespace Mobile\Event;
use Think\Controller;
/**
 * 事件控制器
 */
class GameEvent extends BaseEvent {

    public function gift_lists($p=0,$extend=array(),$flag = false) {

        $page = intval($p);
        
        $page = $page ? $page : 1; //默认显示第一页数据
        

        //获取模型信息
        
        $model = M('Model')->getByName("Giftbag");
        
        $model || $this->error('模型不存在！');
        
        
        $dbFields = D($model['name'])->getDbFields();
        
        array_walk(
            $dbFields, 
            function(&$value, $key, $prefix){$value = $prefix.$value;}, 
            'tab_giftbag.'
        );
        
        $fields = empty($extend) || empty($extend['fields']) ?array():$extend['fields'];
        
        if ($fields) {
            foreach ($fields as $k => $v) {           
                if (!array_search($v,$dbFields)) {               
                    unset($fields[$k]);                
                }            
            }                
            $fields = array_unique(array_merge($fields,$dbFields)); 
        } else {
            $fields = $dbFields;
        }
        
        $fields[] = "tab_game.icon";

        $map = empty($extend) || empty($extend['map']) ?array():$extend['map'];
                
        foreach($_REQUEST as $name=>$val){
            
            if(in_array('tab_giftbag.'.$name,$fields)){
                
                $map['tab_giftbag.'.$name]	=	$val;
                
            }
            
        }
        
        $row  = empty($extend) || empty($extend['num']) ? 50 : $extend['num'];
        
        $name = parse_name(get_table_name($model['id']), true);
        
        $entity = D($name);
        
        $data = $entity

            ->field($fields)
            
            ->join("tab_game on(tab_giftbag.game_id=tab_game.id)","left")
            
            ->where($map)
            
            ->order("tab_giftbag.create_time desc")
            
            ->page($page, $row)
            
            
            ->select();
        
        if ($flag) {
            
            $count = $entity
                ->join("tab_game on(tab_giftbag.game_id=tab_game.id)","left")
                ->where($map)->count(); 
        
            $total = intval(ceil($count/$row));  
            
            $this->assign('total',$total);
        }
        
        $this->assign('model', $model);
        $ng = array();
        if($data) {
			$this->jump($data,$ng);
			$key = end(array_keys($ng));
		}
        $lists['data'] = $ng;
        $lists['key'] = $key;
        return $lists;
    }
        
    protected function jump(&$ga,&$ng,$n=0,$field='create_time') {
		$num = count($ga);
		if($num==0 || $n == $num) {
			return ;
		} else {
			$t = date('Y-m-d',$ga[0][$field]);
			foreach($ga as $k => $g) {
				$st = date('Y-m-d',$g[$field]);
                $g['picurl'] = get_cover($g['icon'],'path');
                if ($field == 'start_time')
                    $g['datetime'] = date('m-d H:i',$g[$field]);
                if (isset($g['novice'])) {
                $count = D("GiftRecord")->where(array('game_id'=>$g['game_id'],'gift_id'=>$g['id']))->count();                        
                $number = $g['number'] = count(explode(',',$g['novice']));
                $g['total']=$count + $number; 
                }                
				if($t==$st) {	
                    $ty = $this->setTime($t,$field);				
					$ng[$ty][]=$g;
					unset($ga[$k]);
				}
			}
			$ga = array_values($ga);			
			return $this->jump($ga,$ng,$num,$field);
		} 
	}
    
    private function setTime($t,$f) {
        $time = date('Ymd',time()) - str_replace('-','',$t);
        if ($f == 'create_time') {
            switch($time) {
                case 0: $text = '今天';break;
                case 1: $text = '昨天';break;
                case 2: $text = '前天';break;
                case 3:
                case 4:
                case 5:
                case 6: $text = $t;break;
                default: $text = '一周以前';
            }
        }else {
            if ($time == 0) {
                $text = '今天';
            } elseif ($time == 1) {
                $text = '昨天';
            } elseif ($time < 0) {
                $text = '即将开始';
            } else {
               $text = $t; 
            }
        }
        return $text;
    }
    
    public function lists($model = null, $p = 0,$extend = array(),$flag = false) {
        
        $model || $this->error('模型名标识必须！');
        
        $page = intval($p);
        
        $page = $page ? $page : 1; //默认显示第一页数据
        

        //获取模型信息
        
        $model = M('Model')->getByName($model);
        
        $model || $this->error('模型不存在！');
        
        $prefix = $model['prefix']?$model['prefix']:C("DB_PREFIX");
        
        $dbFields = M($model['name'],$prefix)->getDbFields();
        
        $fields = !empty($extend) && !empty($extend['fields']) ?$extend['fields']:array();
        
        if ($fields) {
            foreach ($fields as $k => $v) {
                if (!array_search($v,$dbFields)) {
                    unset($fields[$k]);
                }
            }
            $fields = array_unique(array_merge($fields,$dbFields));                
        } else {
            $fields = $dbFields;
        }
        
        // 条件搜索
        
        $map = !empty($extend) && !empty($extend['map']) ?$extend['map']:array();
        
        unset($_REQUEST['name']);
        
        foreach($_REQUEST as $name=>$val){
            
            if(in_array($name,$fields)){
                
                $map[$name]	= array('like',	'%'.$val.'%');
                
            }
            
        }
        
        $row    = !empty($extend) && !empty($extend['num']) ? $extend['num']:50;
        
        $name = parse_name(get_table_name($model['id']), true);
        
        $entity = M($name,$prefix);
        
        $data = $entity

            ->field(empty($fields) ? true : $fields)

            ->where($map)
            
            ->order(empty($extend['order'])?"id desc":$extend['order'])
            
            ->page($page, $row)
            
            ->select();
                
        if ($flag) {
            
            $count = $entity->where($map)->count(); 
        
            $total = intval(ceil($count/$row));  
            
            $this->assign('total',$total);
        }
        
        $this->assign('model', $model);

        return $data;
    }
    
    public function listed($model = null, $p = 0,$extend = array(),$flag = false) {
        $model || $this->error('模型名标识必须！');
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        //获取模型信息
        $model = M('Model')->getByName($model);
        $model || $this->error('模型不存在！');
        $prefix ='tab_';
        $dbFields = M($model['name'],$prefix)->getDbFields(); 
        $fields = !empty($extend) && !empty($extend['fields']) ?$extend['fields']:array();  
        if ($fields) {
            foreach ($fields as $k => $v) {
                if (!array_search($v,$dbFields)) {
                    unset($fields[$k]);
                }
            }
            $fields = array_unique(array_merge($fields,$dbFields));                
        } else {
            $fields = $dbFields;
        }
        // 条件搜索
        $map = !empty($extend) && !empty($extend['map']) ?$extend['map']:array();
        unset($_REQUEST['name']);
        foreach($_REQUEST as $name=>$val){
            if(in_array($name,$fields)){
                $map[$name]	= array('like',	'%'.$val.'%');
            }
        }
        
        $row    = !empty($extend) && !empty($extend['num']) ? $extend['num']:50; 
        $name = parse_name(get_table_name($model['id']), true); 
        $entity = M($name,$prefix);
        $data = $entity
            ->field(empty($fields) ? true : $fields)
            ->where($map)
            ->group('relation_game_id')
            ->order(empty($extend['order'])?"id desc":$extend['order'])
            ->page($page, $row)
            ->select();

        if ($flag) {
            $count = $entity->where($map)->count(); 
            $total = intval(ceil($count/$row));  
            $this->assign('total',$total);
        }
        $this->assign('model', $model);
        $data=game_merge($data,$data['map']);
        return $data;
    }
    
    
    public function game_lists($model = null, $p = 0,$extend = array(),$flag = false) {

        $lists = $this->listed($model,$p,$extend,$flag);
        
        if (empty($lists) || !is_array($lists)) {return '';}
        
        /* $fields = M('Attribute')->where("name in ('device') and model_id=4")->find(); 
        
        $pfa = parse_field_attr($fields['extra']); 
        $pfa = array(1=>'Android',2=>'iPhone',3=>'iPad');
        */
        
        
        foreach ($lists as $k => $v) {
            $lists[$k]['picurl'] = get_cover($v['icon'],'path');
            $lists[$k]['open_type'] = get_open_type($v['category']);
            /* $plat = '';
            foreach ($pfa as $p => $a) {
                if (check_field_value($v['device'],$p)) {
                    $plat .= $a.'/';
                }
            }
            $lists[$k]['plat'] = substr($plat,0,-1); */
        }
        
        return $lists;
    }
    
    public function server_lists($p = 0,$extend = array(),$flag = false) {
        $page = intval($p);
        
        $page = $page ? $page : 1; //默认显示第一页数据
        //获取模型信息
        $model = M('Model')->getByName("Server");
        
        $model || $this->error('模型不存在！');
                
        $dbFields = M($model['name'],"tab_")->getDbFields();
        
        array_walk(
            $dbFields, 
            function(&$value, $key, $prefix){$value = $prefix.$value;}, 
            'tab_server.'
        );
        
        $fields = empty($extend) || empty($extend['fields']) ?array():$extend['fields'];
        
        if ($fields) {
            foreach ($fields as $k => $v) {           
                if (!array_search($v,$dbFields)) {               
                    unset($fields[$k]);                
                }            
            }                
            $fields = array_unique(array_merge($fields,$dbFields)); 
        } else {
            $fields = $dbFields;
        }
        
        $fields[] = "tab_game.icon";
        $fields[] = "tab_game.game_type_name";
        $fields[] = "tab_game.relation_game_id";
        
        $map = empty($extend) || empty($extend['map']) ?array():$extend['map'];

        foreach($_REQUEST as $name=>$val){
            
            if(in_array('tab_server.'.$name,$fields)){
                
                $map['tab_server.'.$name]	=	$val;
                
            }
            
        }
        $row  = empty($extend) || empty($extend['num']) ? 50 : $extend['num'];
        
        $name = parse_name(get_table_name($model['id']), true);
        
        $entity = M($name,"tab_");
        
        $data = $entity

            ->field($fields)
            
            ->join("tab_game on(tab_server.game_id=tab_game.id)","left")
            
            ->where($map)
                
            ->group('relation_game_id')
            
            ->order("tab_server.start_time desc")
            
            ->page($page, $row)
            
            ->select();
        
        if ($flag) {
            
            $count = $entity
                ->join("tab_game on(tab_server.game_id=tab_game.id)","left")
                ->where($map)->count(); 
        
            $total = intval(ceil($count/$row));  
            
            $this->assign('total',$total);
        }
        
        $this->assign('model', $model);
        $ng = array();
        if($data) {
			$this->jump($data,$ng,0,'start_time');
			$key = end(array_keys($ng));
		}
        $lists['data'] = $ng;
        $lists['key'] = $key;

        return $lists;
    }
    
    public function giftrecord($p = 0,$extend = array(),$flag = false) {
        $page = intval($p);
        
        $page = $page ? $page : 1; //默认显示第一页数据
        

        //获取模型信息
        
        $model = M('Model')->getByName("GiftRecord");
        
        $model || $this->error('模型不存在！');
                
        $dbFields = D($model['name'])->getDbFields();
        
        array_walk(
            $dbFields, 
            function(&$value, $key, $prefix){$value = $prefix.$value;}, 
            'tab_gift_record.'
        );
        
        $fields = empty($extend) || empty($extend['fields']) ?array():$extend['fields'];
        
        if ($fields) {
            foreach ($fields as $k => $v) {           
                if (!array_search($v,$dbFields)) {               
                    unset($fields[$k]);                
                }            
            }                
            $fields = array_unique(array_merge($fields,$dbFields)); 
        } else {
            $fields = $dbFields;
        }
        
        $fields[] = "tab_game.icon";
        $fields[] = "tab_game.game_type_name";
        $fields[] = "tab_giftbag.end_time";

        $map = empty($extend) || empty($extend['map']) ?array():$extend['map'];
                
        foreach($_REQUEST as $name=>$val){
            
            if(in_array('tab_gift_record.'.$name,$fields)){
                
                $map['tab_gift_record.'.$name]	=	$val;
                
            }
            
        }
        
        $row  = empty($extend) || empty($extend['num']) ? 50 : $extend['num'];
        
        $name = parse_name(get_table_name($model['id']), true);
        
        $entity = D($name);
        
        $data = $entity

            ->field($fields)
            
            ->join("tab_giftbag on(tab_gift_record.gift_id=tab_giftbag.id)","left")
            
            ->join("tab_game on(tab_game.id = tab_gift_record.game_id)","left")
            
            ->where($map)
            
            ->order("tab_gift_record.id desc")
            
            ->page($page, $row)
            
            
            ->select();

        
        if ($flag) {
            
            $count = $entity
                ->join("tab_giftbag on(tab_gift_record.gift_id=tab_giftbag.id)","left")
                ->join("tab_game on(tab_game.id = tab_gift_record.game_id)","left")
                ->where($map)->count(); 
        
            $total = intval(ceil($count/$row));  
            
            $this->assign('total',$total);
        }
        
        $this->assign('model', $model);
        
        if (empty($data) || !is_array($data)) {return '';}
        
        $time = time();
        
        foreach ($data as $k => $v) {
            if ($v['end_time']<$time) {
                $data[$k]['status'] = 2;
            }
            $data[$k]['datetime'] = date('Y-m-d H:i:s',$v['create_time']);
            $data[$k]['picurl'] = get_cover($v['icon'],'path');
        }
        
        return $data;
    }
    
    
    public function play_lists($p = 0,$extend = array(),$flag = false) {
        $page = intval($p);
        
        $page = $page ? $page : 1; //默认显示第一页数据
        

        //获取模型信息
        
        $model = M('Model')->getByName("UserPlay");
        
        $model || $this->error('模型不存在！');
                
        $dbFields = M($model['name'],"tab_")->getDbFields();
        
        array_walk(
            $dbFields, 
            function(&$value, $key, $prefix){$value = $prefix.$value;}, 
            'tab_user_play.'
        );
        
        $fields = empty($extend) || empty($extend['fields']) ?array():$extend['fields'];
        
        if ($fields) {
            foreach ($fields as $k => $v) {           
                if (!array_search($v,$dbFields)) {               
                    unset($fields[$k]);                
                }            
            }                
            $fields = array_unique(array_merge($fields,$dbFields)); 
        } else {
            $fields = $dbFields;
        }
        
        $fields[] = "tab_game.icon";
        $fields[] = "tab_game.game_type_name";

        $map = empty($extend) || empty($extend['map']) ?array():$extend['map'];
                
        foreach($_REQUEST as $name=>$val){
            
            if(in_array('tab_user_play.'.$name,$fields)){
                
                $map['tab_user_play.'.$name]	=	$val;
                
            }
            
        }
        
        $row  = empty($extend) || empty($extend['num']) ? 50 : $extend['num'];
        
        $name = parse_name(get_table_name($model['id']), true);
        
        $entity = M($name,"tab_");
        
        $data = $entity

            ->field($fields)
            
            ->join("tab_game on(tab_user_play.game_id=tab_game.id)","left")
            
            ->where($map)
            
            ->order("tab_user_play.id desc")
            
            ->page($page, $row)
            
            
            ->select();
        
        if ($flag) {
            
            $count = $entity
                ->join("tab_game on(tab_user_play.game_id=tab_game.id)","left")
                ->where($map)->count(); 
        
            $total = intval(ceil($count/$row));  
            
            $this->assign('total',$total);
        }
        
        $this->assign('model', $model);
        
        foreach ($data as $k => $v) {

            $data[$k]['picurl'] = get_cover($v['icon'],'path');
        }
        
        return $data;
    }
    
}
