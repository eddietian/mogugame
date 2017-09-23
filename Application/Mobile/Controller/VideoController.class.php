<?php
namespace Mobile\Controller;
use Think\Controller;

/**
* 视频 鹿文学
*/
class VideoController extends BaseController {
    
	protected function _initialize(){

        $config = api('Config/lists');
        
        C($config); 
        
    }

    var $id = 50;

    public function index($p=1){
        $category = $this->category($this->id);
        
		$this->assign('category', $category);

        $map = array(
            'map' => array(
                'status' => 1,
                'display' => 1,
                'category_id'=> $category['id'],
            ),
            'num' => 10
        );

        $lists = parent::lists('Document',$p,$map,true);
 
        $this->assign('page',$p);
        
        $this->assign('catetitle',"视频");
        
        $this->assign('lists',$lists);
        
        $this->display();
	}
    
    public function lists() {
        $this->redirect('index');
    }
    
    public function ajaxlists($p = 2) {
        
        $category = $this->category($this->id);

        $map = array(
            'map' => array(
                'status' => 1,
                'display' => 1,
                'category_id'=> $category['id'],
            ),
            'num' => 10
        );

        $lists = parent::lists('Document',$p,$map);
        
        if (!empty($lists) && is_array($lists)) {
            
            foreach ($lists as $k => $v) {
                $lists[$k]['picurl'] = get_cover($v['cover_id'],'path');
                $lists[$k]['update_time'] = date('Y-m-d H:i',$v['update_time']);
            }
            
            echo json_encode(array('lists'=>$lists,'status'=>1,'page'=>$p));
            
        } else {
            
            echo json_encode(array('status'=>0));
            
        }
    }
    
    public function detail($id) {
        if(!($id && is_numeric($id))){
			$this->error('文档ID错误！');
		}
        
        
        $Document = D('Document');
        
		$data = $Document->detail($id);
        
		if(!$data){
            
			$this->error($Document->getError());
            
		}
        
        $category = $this->category($this->id);
        
        $map = array('id' => $id);
        
		$Document->where($map)->setInc('view');
        
        if ($data['game_id']) {
            $game = D('Game')->getinfo($data['game_id']);
           
            $this->assign('game',$game);
        }

        
        $category['n']=str_replace('media_news_','',$category['name']);
         
		$this->assign('category', $category);
        
		$this->assign('data', $data);
        
        $this->assign('data',$data);
        
        $this->display();
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