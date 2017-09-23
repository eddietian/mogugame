<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Media\Controller;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ArticleController extends HomeController {
	protected function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
    }
    /* 文档模型频道页 */
	public function index(){
		/* 分类信息 */
		$category = $this->category();
        $this->hot();
		//频道页只显示模板，默认不读取任何内容
		//内容可以通过模板标签自行定制

		/* 模板赋值并渲染模板 */
		$gift=new GameController();
		$gift_list=get_gift_list('all');
		if(count($gift_list)<6){
			$limit=count($gift_list);
		}else{
			$limit=6;
		}
        $gift_keys=array_rand($gift_list,$limit);
        // 当只有一个数数组对象时  返回0 
		if( $gift_keys != 0){
	        foreach ($gift_keys as $val) {
	            $gift_like[]=$gift_list[$val];
	        }
		}else{
			$gift_like= $gift_list;
		}
		$sc=new IndexController();
		$sc->newgame(3,'dow_num desc');
		$adv = M("Adv","tab_");
        $map['status'] = 1;
        $map['pos_id'] = $pos_id; #首页轮播图广告id
        $map['start_time']=array(array('lt',time()),array('eq',0), 'or') ;
        $map['end_time']=array(array('gt',time()),array('eq',0), 'or') ;
        $map['pos_id']=5;
        $carousel = $adv->where($map)->order('sort desc')->select();
		$this->assign('gift_like', $gift_like);
		$this->assign('carousel', $carousel);
		$this->assign('category', $category);
		$this->display($category['template_index']);
	}
	 /***
	*热门游戏
    */
    public function hot(){
    	$model = array(
            'm_name'=>'Game',
            'prefix'=>'tab_',
            'map'   =>array('game_status'=>1,'recommend_status'=>2),
            'field' =>'*,min(id) as id,@counter:=@counter+1 AS Rank',
            'order' =>$order,
            'group' =>'relation_game_id',
            'limit' =>9,
        );
    	$hot = $this->list_data($model);
        $hot=game_merge($hot,$model['map']);
    	$this->assign('hot',$hot);
    }

	/* 文档模型列表页 */
	public function lists($p = 1){
		/* 分类信息 */
		$category = $this->category();

		/* 获取当前分类列表 */
		$Document = D('Document');
		$list = $Document->page($p, $category['list_row'])->lists($category['id']);
		if(false === $list){
			$this->error('获取列表数据失败！');
		}

		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('list', $list);
		$this->display($category['template_lists']);
	}

	public function news($type='') {
        if (empty($type)) {return;}
        $name = 'media_'.$type;
        $news = M("Document")->field("d.id")->table("__DOCUMENT__ as d")
            ->join("__CATEGORY__ as c on(c.id=d.category_id and c.name='$name')",'right')
            ->where("d.status>0 and d.display=1")->find();
        $this->detail($news['id']);
    }

	/* 文档模型详情页 */
	public function detail($id = 0, $p = 1){
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))){
			$this->error('文档ID错误！');
		}

		/* 页码检测 */
		$p = intval($p);
		$p = empty($p) ? 1 : $p;

		/* 获取详细信息 */
		$Document = D('Document');
		$info = $Document->detail($id);
		if(!$info){
			$this->error($Document->getError());
		}

		/* 分类信息 */
		$category = $this->category($info['category_id']);

		/* 获取模板 */
		if(!empty($info['template'])){//已定制模板
			$tmpl = $info['template'];
		} elseif (!empty($category['template_detail'])){ //分类已定制模板
			$tmpl = $category['template_detail'];
		} else { //使用默认模板
			$tmpl = 'Article/'. get_document_model($info['model_id'],'name') .'/detail';
		}

		/* 更新浏览数 */
		$map = array('id' => $id);
		$Document->where($map)->setInc('view');
		/* 模板赋值并渲染模板 */
		$map['id']=array('lt',$info['id']);
		$map['status']=1;
		$map['display']=1;
		$map['deadline']=array(array('gt',time()),array('eq',0), 'or') ;
		$map['category_id']=$info['category_id'];
		// $map['id']
		$data=M('document')->where($map)->order('id desc')->find();
		if(!$data){
			$map['id']=array('lt',$info['id']);
			$map['status']=1;
			$map['display']=1;
			$map['deadline']=array(array('gt',time()),array('eq',0), 'or') ;
			$data=M('document')->where($map)->order('id asc')->find();
			$data['datatype']=0;
		}else{
			$data['datatype']=1;

		}
		$gift=new GameController();
		$gift_list=get_gift_list('all');
		if(count($gift_list)<6){
			$limit=count($gift_list);
		}else{
			$limit=6;
		}
        
        // 当只有一个数数组对象时  返回0 
		if( $gift_keys != 0){
	        foreach ($gift_keys as $val) {
	            $gift_like[]=$gift_list[$val];
	        }
		}else{
			$gift_like= $gift_list;
		}
		$sc=new IndexController();
		$sc->newgame(3,'dow_num desc');
		$adv = M("Adv","tab_");
        $map['status'] = 1;
        $map['pos_id'] = $pos_id; #首页轮播图广告id
        $map['start_time']=array(array('lt',time()),array('eq',0), 'or') ;
        $map['end_time']=array(array('gt',time()),array('eq',0), 'or') ;
        $map['pos_id']=5;
        $carousel = $adv->where($map)->order('sort desc')->select();
        $this->hot();
		$this->assign('gift_like', $gift_like);
		$this->assign('carousel', $carousel);
		$this->assign('category', $category);
		$this->assign('info', $info);
		$this->assign('data', $data);
		$this->assign('page', $p); //页码
		$this->display($tmpl);
	}

	public function news1($type='') {
        if (empty($type)) {return;}
        $name = 'media_'.$type;
        $news = M("Document")->field("d.id")->table("__DOCUMENT__ as d")
            ->join("__CATEGORY__ as c on(c.id=d.category_id and c.name='$name')",'right')
            ->where("d.status>0 and d.display=1")->find();
        $this->detail1($news['id']);
    }

	/* 文档模型详情页 */
	public function detail1($id = 0, $p = 1){
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))){
			$this->error('文档ID错误！');
		}

		/* 页码检测 */
		$p = intval($p);
		$p = empty($p) ? 1 : $p;

		/* 获取详细信息 */
		$Document = D('Document');
		$info = $Document->detail($id);
		if(!$info){
			$this->error($Document->getError());
		}

		/* 分类信息 */
		$category = $this->category($info['category_id']);

		/* 获取模板 */
		if(!empty($info['template'])){//已定制模板
			$tmpl = $info['template'];
		} elseif (!empty($category['template_detail'])){ //分类已定制模板
			$tmpl = $category['template_detail'];
		} else { //使用默认模板
			$tmpl = 'Article/'. get_document_model($info['model_id'],'name') .'/detail1';
		}

		/* 更新浏览数 */
		$map = array('id' => $id);
		$Document->where($map)->setInc('view');
		/* 模板赋值并渲染模板 */
		$map['id']=array('gt',$info['id']);
		$map['status']=1;
		$map['display']=1;
		$map['deadline']=array(array('gt',time()),array('eq',0), 'or') ;
		$data=M('document')->where($map)->order('id asc')->find();
		if(!$data){
			$map['id']=array('lt',$info['id']);
			$map['status']=1;
			$map['display']=1;
			$map['deadline']=array(array('gt',time()),array('eq',0), 'or') ;
			$data=M('document')->where($map)->order('id asc')->find();
			$data['datatype']=0;
		}else{
			$data['datatype']=1;

		}
		$gift=new GameController();
		$gift_list=get_gift_list('all');
		if(count($gift_list)<6){
			$limit=count($gift_list);
		}else{
			$limit=6;
		}
        $gift_keys=array_rand($gift_list,$limit);
        foreach ($gift_keys as $val) {
            $gift_like[]=$gift_list[$val];
        }
		$sc=new IndexController();
		$sc->newgame(3,'dow_num desc');
		$adv = M("Adv","tab_");
        $map['status'] = 1;
        $map['pos_id'] = $pos_id; #首页轮播图广告id
        $map['start_time']=array(array('lt',time()),array('eq',0), 'or') ;
        $map['end_time']=array(array('gt',time()),array('eq',0), 'or') ;
        $map['pos_id']=5;
        $carousel = $adv->where($map)->order('sort desc')->select();
		// $this->assign('gift_like', $gift_like);
		// $this->assign('carousel', $carousel);
		// $this->assign('category', $category);
		// var_dump($info);
		$this->assign('info', $info);
		$this->assign('data', $data);
		$this->assign('page', $p); //页码
		$this->display($tmpl);
	}
	/* 文档模型详情页 */
	public function supervise(){
		$category=D('category')->where(array('name'=>'media_supervise'))->find();
		$map['status']=1;
		$map['category_id']=$category['id'];
		$map['display']=1;
		$map['deadline']=array(array('gt',time()),array('eq',0), 'or') ;
        $docu=D('document')->where($map)->find();
    	$document_article=D('document_article')->where(array('id'=>$docu['id']))->find();
		$gift=new GameController();
		$gift_list=get_gift_list('all');
		if(count($gift_list)<6){
			$limit=count($gift_list);
		}else{
			$limit=6;
		}
        $gift_keys=array_rand($gift_list,$limit);
        foreach ($gift_keys as $val) {
            $gift_like[]=$gift_list[$val];
        }
		$sc=new IndexController();
		$sc->newgame(3,'dow_num desc');
		$adv = M("Adv","tab_");
        $map['status'] = 1;
        $map['pos_id'] = $pos_id; #首页轮播图广告id
        $map['start_time']=array(array('lt',time()),array('eq',0), 'or') ;
        $map['end_time']=array(array('gt',time()),array('eq',0), 'or') ;
        $map['pos_id']=5;
        $carousel = $adv->where($map)->order('sort desc')->select();
		$this->assign('gift_like', $gift_like);
		$this->assign('carousel', $carousel);
		$this->assign('article',$document_article['content']); 
    	$this->assign('docu',$docu); 
		$this->display();
	}

	/* 文档分类检测 */
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
    
    public function agreement() {
    	$category=D('category')->where(array('name'=>'agreement'))->find();
        $docu=D('document')->where(array('category_id'=>$category['id'],'status'=>1))->find();
    	$document_article=D('document_article')->where(array('id'=>$docu['id']))->find();
    	$this->assign('article',$document_article['content']); 
        $this->display();
    }
    protected function list_data($model){
		$game  = M($model['m_name'],$model['prefix']);
		$map = $model['map'];
		$data  = $game
		->field($model['field'])
		->limit($model['limit'])
		->where($map)
		->group($model['group'])
		->order($model['order'])
		->select();
		return $data;
	}
}
