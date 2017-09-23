<?php
namespace Mobile\Controller;
use Think\Controller;

/**
* 首页
*/
class BaseController extends Controller {
    
    public function _empty(){
        
		$this->redirect('Index/index');
        
	}
    
	protected function _initialize(){
        
        $config = api('Config/lists');
        
        C($config); 
        
        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        
    }
    
    
	public function __construct() {
		parent::__construct();	
	}
    
    
    public function islogin() {  
    
        $user = session('user_auth');
        
        if ($user) {  
        
            return true;        
        } else {  
        
            return false;        
        }    
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
    
    
}