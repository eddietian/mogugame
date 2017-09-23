<?php
namespace Mobile\Model;
use Think\Model;

/**
 * 分类模型
 */
class CategoryModel extends Model{

    protected $_validate = array(
        array('name', 'require', '标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    	array('meta_title', '1,50', '网页标题不能超过50个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
    	array('keywords', '1,255', '网页关键字不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
    	array('meta_title', '1,255', '网页描述不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('model', 'arr2str', self::MODEL_BOTH, 'function'),
        array('model', null, self::MODEL_BOTH, 'ignore'),
        array('model_sub', 'arr2str', self::MODEL_BOTH, 'function'),
        array('model_sub', null, self::MODEL_BOTH, 'ignore'),
        array('type', 'arr2str', self::MODEL_BOTH, 'function'),
        array('type', null, self::MODEL_BOTH, 'ignore'),
        array('reply_model', 'arr2str', self::MODEL_BOTH, 'function'),
        array('reply_model', null, self::MODEL_BOTH, 'ignore'),
        array('extend', 'json_encode', self::MODEL_BOTH, 'function'),
        array('extend', null, self::MODEL_BOTH, 'ignore'),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_BOTH),
    );


    /**
     * 获取分类详细信息
     * @param  milit   $id 分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     分类信息
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function info($id, $field = true){
        /* 获取分类信息 */
        $map = array();
        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array          分类树
     */
    
    public function getTree($id = 0, $field = true,$order='sort'){
        /* 获取当前分类信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status'=>1);
        $list = $this->field($field)->where($map)->order($order)->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);
        
        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }
    
    public function getAllTree($id = '',$field=true,$order='sort',$identify='') {
        $map  = array('status'=>1);
            
        $list = $this->field($field)->where($map)->order($order)->select();
        
        if ($identify)
            foreach ($list as $k => $v) {
                if (strstr($v['name'],$identify)) {
                    unset($list[$k]);
                }
            }
        
        if (!empty($id)) {
            $ids = explode(',',$id);
            foreach ($ids as $k => $v) {
                $info = $this->info($v,$field);
                $id   = $info['id'];
                
                $in = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);
                if ($in) $info['_']=$in;
                $data[] = $info;
            }     
                        
            return $data;
        } else {
            
            $id = 0;

            $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);

            return $list;
        }
        
    }

    /**
     * 获取指定分类子分类ID
     * @param  string $cate 分类ID
     * @return string       id列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getChildrenId($cate,$flag=false) {
        $field    = 'id,name,pid,title,link_id';
        $category = $this->getTree($cate, $field);
        $ids[]    = $cate;
        foreach ($category['_'] as $key => $value) {
            $ids[] = $value['id'];
        }
        if ($flag) {
            return $ids;
        }
        return implode(',', $ids);
    }
    
    public function getChildrenAllId($cate,$flag=false) {
        $field    = 'id,name,pid,title,link_id';
        $category = $this->getAllTree($cate, $field,'sort,id'); 

        $ids = array();
        foreach ($category as $k => $v) {
            $result = $this->resetarray($v); 
            if (!empty($result['ids']))
                $ids = array_merge($ids,$result['ids']);
        }
        
        if ($flag) {
            return $ids;
        }
        
        return implode(',', $ids);
    }
    
    public function getChildrenIdByAdhesion($cate,$name=''){
        $field = 'id,name,pid,title,link_id';
        
        $category = $this->getTree($cate, $field);
        
        $ids[]    = $cate; 
        
        if ($name) {   
        
            foreach ($category['_'] as $key => $value) {  
            
                if (strstr($value['name'],$name))
                    
                    $ids[] = $value['id'];
                    
            }  
            
        } else {   
        
            foreach ($category['_'] as $key => $value) {  
            
                $ids[] = $value['id'];   
                
            }        
            
        }
            
        return implode(',', $ids);
        
    }
    
    public function getChildrenIdSelect($cate,$identify=''){
        $field = 'id,name,pid,title,link_id';
        
        $category = $this->getTree($cate, $field);
        
        $ids[]    = $cate;
        
        if ($identify) {
            
            foreach ($category['_'] as $key => $value) {
                
                if (in_array($value['name'],$identify))
                    
                    $ids[] = $value['id'];
                    
            }
            
        } else {             
            foreach ($category['_'] as $key => $value) {  
            
                $ids[] = $value['id'];   
                
            }        
        }
        
        return implode(',', $ids);
        
    }


    public function getChildrenAllInfo($cate,$identify='') {
        $field = 'id,name,pid,title,link_id,sort';    
        
        $category = $this->getAllTree($cate, $field,'sort,id',$identify); 
        
        $result ='';
        
        $data = array('_'=>array(),'ids'=>array());
        
        foreach ($category as $k => $v) {
            $result = $this->resetarray($v); 
            
            $data['_'] = $data['_'] + $result['_'];
            if (!empty($result['ids']))
            $data['ids'] = array_merge($data['ids'],$result['ids']);
        }
        
        return $data;
    }
    
    private function resetarray($category,$identify='') {
        
        $result['_'][$category['id']] = array(
            'id' => $category['id'],
            'name' => $category['name'],
            'title' => $category['title'],
            'pid' => $category['pid'],
            'link_id' => $category['link_id'],
            'sort' => $category['sort'],
        );
        
        $result['ids'][] = $category['id'];
        
        foreach ($category['_'] as $key => $value) {  
        
            $id = $value['id'];
        
            $result['_'][$id] = $value;
            
            $result['ids'][] = $id;
            
        }
        
        return $result;
    }
    
    /* public function getChildrenIdInfo($cate,$identify='') {
        $field = 'id,name,pid,title,link_id,sort';    
        
        $category = $this->getTree($cate, $field,'sort,id'); 
        
        $result = array();
        
        
        $result['_'][$category['id']] = array(
            'id' => $category['id'],
            'name' => $category['name'],
            'title' => $category['title'],
            'pid' => $category['pid'],
            'link_id' => $category['link_id'],
            'sort' => $category['sort'],
        );
        
        
        if ($identify) {
            
            foreach ($category['_'] as $key => $value) {
                
                if (in_array($value['name'],$identify)) {
                    
                    $id = $value['id'];
                    
                    $result['_'][$id] = $value;
                    
                    $result['ids'][] = $id;
                    
                }
                    
            }
            
        } else {
            foreach ($category['_'] as $key => $value) {  
            
                $id = $value['id'];
            
                $result['_'][$id] = $value;
                
                $result['ids'][] = $id;
                
            }
        }
        
        return $result; 
    } */
    
    
    public function getChildrenInfo($cate,$identify=''){  
    
        $field = 'id,name,pid,title,link_id,sort';    
        
        $category = $this->getTree($cate, $field,'sort,id'); 
        
        
        if ($identify) {
            
            foreach ($category['_'] as $key => $value) {
                
                if (!in_array($value['name'],$identify))
                    
                    unset($category['_'][$key]);
                    
            }
            
        }
        
        return $category['_']; 
    }

    /**
     * 获取指定分类的同级分类
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getSameLevel($id, $field = true){
        $info = $this->info($id, 'pid');
        $map = array('pid' => $info['pid'], 'status' => 1);
        return $this->field($field)->where($map)->order('sort')->select();
    }

    /**
     * 更新分类信息
     * @return boolean 更新状态
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }

        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add();
        }else{
            $res = $this->save();
        }

        //更新分类缓存
        S('sys_category_list', null);

        //记录行为
        action_log('update_category', 'category', $data['id'] ? $data['id'] : $res, UID);

        return $res;
    }

    /**
     * 查询后解析扩展信息
     * @param  array $data 分类数据
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    protected function _after_find(&$data, $options){
        /* 分割模型 */
        if(!empty($data['model'])){
            $data['model'] = explode(',', $data['model']);
        }

        if(!empty($data['model_sub'])){
            $data['model_sub'] = explode(',', $data['model_sub']);
        }

        /* 分割文档类型 */
        if(!empty($data['type'])){
            $data['type'] = explode(',', $data['type']);
        }

        /* 分割模型 */
        if(!empty($data['reply_model'])){
            $data['reply_model'] = explode(',', $data['reply_model']);
        }

        /* 分割文档类型 */
        if(!empty($data['reply_type'])){
            $data['reply_type'] = explode(',', $data['reply_type']);
        }

        /* 还原扩展数据 */
        if(!empty($data['extend'])){
            $data['extend'] = json_decode($data['extend'], true);
        }
    }

}
