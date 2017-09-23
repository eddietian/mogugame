<?php
namespace Mobile\Controller;
use Think\Controller;

/**
* 新闻 鹿文学
*/
class ActivityController extends BaseController {
    
	protected function _initialize(){

        $config = api('Config/lists');
        
        C($config); 
        
    }
    
    var $id=49;

    public function index($p=1){
        
		$category = $this->category($this->id);
        
		$this->assign('category', $category);

        $map = array(
            'map' => array(
                'status' => 1,
                'display' => 1,
                'category_id'=> $this->id,
            ),
            'num' => 10
        );

        $lists = parent::lists('Document',$p,$map,true);
               
        $this->assign('page',$p);
        
        $this->assign('catetitle',$category['title']);
        
        $this->assign('lists',$lists);
        
        $this->display();
	}
    
    public function ajaxlists($p = 2) {
        
        $category = $this->category($this->id);

        $map = array(
            'map' => array(
                'status' => 1,
                'display' => 1,
                'category_id'=> $this->id,
            ),
            'num' => 10
        );

        $lists = parent::lists('Document',$p,$map);
        
        if (!empty($lists) && is_array($lists)) {
            
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
        
        $category = $this->category($data['category_id']);
        
        $map = array('id' => $id);
        
		$Document->where($map)->setInc('view');
        
        
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