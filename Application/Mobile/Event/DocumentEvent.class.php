<?php
namespace Mobile\Event;
use Think\Controller;
/**
 * 事件控制器
 */
class DocumentEvent extends BaseEvent {
    
    public function lists($p = 0,$extend = array(),$flag = false) {
        
        
        $page = intval($p);
        
        $page = $page ? $page : 1; //默认显示第一页数据
        

        //获取模型信息
        
        $model = M('Model')->getByName("Document");
        
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
        unset($_REQUEST['id']);
        
        foreach($_REQUEST as $name=>$val){
            
            if(in_array($name,$fields)){
                
                $map[$name]	= array('like',	'%'.$val.'%');
                
            }
            
        }
        
        $row    = !empty($extend) && !empty($extend['num']) ? $extend['num']:10;
        
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
    
    public function news_lists($name='',$id='',$p=0,$flag = false) {
        if (empty($id)) {return '';}
        $category = $this->category('media_'.$name);
        $cate = D("Category")->getChildrenId($category['id'],true);    
        $map = array(
            'map' => array(
                'status' => 1,
                'display' => 1,
                'category_id'=> array('in',$cate),
                'game_id' => $id,
            ),
            'num' => 10
        );
        
        $lists = $this->lists($p,$map,$flag);
        
        foreach ($lists as $k => $v) {
            $lists[$k]['datetime'] = date('Y-m-d H:i:s',$v['update_time']);
        }
        
        return $lists;
        
    }
    
    public function special_lists($name='',$p=0,$flag = false) {
        $category = $this->category('media_'.$name);
        $cate = D("Category")->getChildrenId($category['id'],true);    
        $map = array(
            'map' => array(
                'status' => 1,
                'display' => 1,
                'category_id'=> array('in',$cate),
                'game_id' => array('neq',''),
            ),
            'num' => 10
        );
        
        $lists = $this->lists($p,$map,$flag);
        
        foreach ($lists as $k => $v) {
            $lists[$k]['datetime'] = date('Y-m-d H:i:s',$v['update_time']);
            $lists[$k]['picrul'] = get_cover($v['cover_id'],'path');
        }
        
        return $lists;
        
    }
    
    public function news_detail($name='',$id='') {
        if(!($id && is_numeric($id))){
			$this->error('文档ID错误！');
		}
        
        $Document = D('Document');
        
		$data = $Document->detail($id);
        
        if(!$data){
            
			$this->error($Document->getError());
            
		}
        
        $category = $this->category($data['category_id']);
        
        $map = array('id' => $id);
        
		$Document->where($map)->setInc('view');
        
        if ($data['tag'])
            $data['taga']=explode(',',$data['tag']);
        
        if ($data['taga']) {
            foreach ($data['taga'] as $v) {
                $where .= " d.title like '%$v%' or d.tag like '%$v%' or";
            }
            $where = 'and ( '.substr($where,0,-2).")"; 
            $model = array(
                'field' => "d.id,d.title,d.category_id,c.name,c.title as ctitle,d.game_id",
                'where' => "d.status=1 and d.display=1 and d.id <> ".$data['id']." $where  and c.name like 'media_%'",
            );
            
            $this->assign('article',D('Document')->multiple($model,5,true));
        }
        
        $game_name = get_game_name($data['game_id']);
        $arr = array('news' => '游戏新闻','video'=>'游戏视频','strategy'=>'游戏攻略','activity'=>'游戏活动');
        //$category['subtitle'] = $category['title'];
        
        $category['subtitle'] = $arr[$name];
        
        $category['game_name'] = $game_name;
        
        $category['title'] = $game_name.'专区'; 
        
        $category['url'] = U('Game/detail?id='.$data['game_id']);   


        $scate = D("Category")->getChildrenInfo(50);     
        shuffle($scate);
        array_splice($scate,3);
        $category['scate'] = $scate;
		$this->assign('category', $category);
        
		$this->assign('data', $data);
        
		/* $this->assign('game_id', $data['game_id']); */
        
		$this->assign('name', $name);  
        
        return ;
    }
    
    
    private function category($id = 0){
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			$this->error('没有指定文档分类！'.$id);
		}
		/* 获取分类信息 */
		$category = D('Category')->info($id);
		if($category && 1 == $category['status']){
			switch ($category['display']) {
				case 0:
					$this->error('该分类禁止显示！');
					break;
                    
                    
				//TODO: 更多分类显示状态判断
                
				default:
					return $category;
			}
		} else {
			$this->error('分类不存在或被禁用！');
		}
	}
    
}
