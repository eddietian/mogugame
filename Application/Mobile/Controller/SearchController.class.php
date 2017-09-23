<?php
namespace Mobile\Controller;
use Think\Controller;

class SearchController extends BaseController {
    
	public function index($t='tag',$w='',$p=1) {
        
		header("Content-type: text/html;charset=utf-8"); 
        
        $lists = A("Search","Event")->search_lists($t,$w,$p,true);
        
        $this->assign('lists',$lists);
        
        $this->assign('page',1);
        
        $this->display();
	}
    
    public function ajaxlists($t='',$w='',$p=2) {
        
        $lists = A("Search","Event")->search_lists($t,$w,$p);
        
        $status = 0;
        
        if (!empty($lists) && is_array($lists)) {
            
            $status = 1;
            
        }
        
        echo json_encode(array('status'=>$status,'page'=>$p,'lists'=>$lists));
    }
    
}