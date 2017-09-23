<?php
namespace Mobile\Model;
use Think\Model;
use Mobile\Logic\SetLogic;

/**
 * 文档基础模型
 */
class GameModel extends Model{

    

    /* 自动验证规则 */
    protected $_validate = array(
        array('game_name', 'require', '游戏名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('game_name', '1,30', '游戏名称不能超过30个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
        array('game_appid', 'require', '游戏APPID不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', self::MODEL_BOTH,'callback'),
        array('recommend_level', 0, self::MODEL_BOTH),
        array('game_score', 0, self::MODEL_BOTH),
    );

    //protected $this->$tablePrefix = 'tab_'; 
    /**
     * 构造函数
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($name = '', $tablePrefix = '', $connection = '') {
        /* 设置默认的表前缀 */
        $this->tablePrefix ='tab_';
        /* 执行构造方法 */
        parent::__construct($name, $tablePrefix, $connection);
    }

    /**
     * 获取详情页数据
     * @param  integer $id 文档ID
     * @return array       详细数据
     */
    public function detail($id){
        /* 获取基础数据 */$map['relation_game_id']=$id;
        $info = $this->field(true)->where($map)->group('relation_game_id')->select();
        $info= game_merge($info,$map);
        $info=reset($info);
        
        if(!(is_array($info) || 1 !== $info['status'])){
            $this->error = '游戏被禁用或已删除！';
            return false;
        }
        $info=game_merge($info,$info['map']);
        /* 获取模型数据 */
        $logic  = new SetLogic();
        $detail = $logic->detail($id); //获取指定ID的数据
        if(!$detail){
            $this->error = $logic->getError();
            return false;
        }
        $info = array_merge($info, $detail);
        return $info;
    }
    
    public function getinfo($id) {
        $info = $this->field(true)->find($id);
        if(!(is_array($info) || 1 !== $info['status'])){
            $this->error = '游戏被禁用或已删除！';
            return false;
        }
        
        return $info;
    }
    
    
    
    public function giftbygame($p=1,$limit=10,$order="g.id desc") {
        $start = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $end = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $time= time();
        $map['_string'] = "g.game_status=1 and gb.status=1 and (gb.end_time>=$time or gb.end_time=0)";
        $lists = $this->table("__GAME__ as g")
            ->field("g.id,g.game_name,g.icon,count(gb.id) as count")
            ->join("__GIFTBAG__ as gb on (gb.game_id = g.id)","left")
            ->where($map)
            ->page($p,$limit)
            ->group("gb.game_id")
            ->order($order)
            ->select();
        
        if (empty($lists) || !is_array($lists)) {return '';}
        
        foreach ($lists as $k=>$v) {
            $ids[] = $v['id'];
        }

        $map['gb.create_time'] = array('between',array($start,$end));
        $map['g.id'] = array('in',$ids);       
        
        $lists2 = $this->table("__GAME__ as g")
            ->field("g.id,count(gb.id) as tcount")
            ->join("__GIFTBAG__ as gb on (gb.game_id = g.id)","left")
            ->where($map)
            ->group("gb.game_id")
            ->order($order)
            ->select(); 
            
        if (!empty($lists2) && is_array($lists2)) 
            foreach ($lists as $k => $v) {
                foreach ($lists2 as $v2) {
                    if ($v2['id'] == $v['id']) {
                        $lists[$k]['tcount'] = $v2['tcount'];
                    }
                }
                $lists[$k]['picurl'] = get_cover($v['icon'],'path');
            }    
        
        return $lists;
    }

    /**
     * 新增或更新一个游戏
     * @param array  $data 手动传入的数据
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     * @author 王贺 
     */
    public function update($data = null){
        /* 检查文档类型是否符合要求 */
        // $res = $this->checkDocumentType( I('type',2), I('pid') );
        // if(!$res['status']){
        //     $this->error = $res['info'];
        //     return false;
        // }
        //if(true){$this->error("000000"); return fasle;}

        /* 获取数据对象 */
        $data = $this->token(false)->create($data);
        if(empty($data)){
            return false;
        }

        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增数据
            $id = $this->add(); //添加基础内容
            if(!$id){
                $this->error = '新增基础内容出错！';
                return false;
            }
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = '更新基础内容出错！';
                return false;
            }
        }

        /* 添加或新增扩展内容 */
        $logic = $this->logic('Info');
        $logic->checkModelAttr(5);
        if(!$logic->update($id)){
            if(isset($id)){ //新增失败，删除基础数据
                $this->delete($id);
            }
            $this->error = $logic->getError();
            return false;
        }

        //hook('documentSaveComplete', array('model_id'=>$data['model_id']));

        //行为记录
        //if($id){
        //    action_log('add_document', 'document', $id, UID);
        //}

        //内容添加或更新完成
        return $data;
    }
    

    /**
     * 创建时间不写则取当前时间
     * @return int 时间戳
     * @author huajie <banhuajie@163.com>
     */
    protected function getCreateTime(){
        $create_time    =   I('post.create_time');
        return $create_time?strtotime($create_time):NOW_TIME;
    }

    /**
     * 获取扩展模型对象
     * @param  integer $model 模型编号
     * @return object         模型对象
     */
    private function logic($model){
        $name  = $model;//parse_name(get_document_model($model, 'name'), 1);
        $class = is_file(MODULE_PATH . 'Logic/' . $name . 'Logic' . EXT) ? $name : 'Base';
        $class = MODULE_NAME . '\\Logic\\' . $class . 'Logic';
        return new $class($name);
    }
    
    
    
    public function index($show_status='',$page=1,$order='id desc',$limit=20,$field=true,$where='') {
        $map = array();
        if (!empty($show_status))
            $map['show_status'] = array('in',explode(',',$show_status));
        if ($where) {
            $w = explode(',',$where);
            foreach ($w as $v) {
                if (strstr($v,'!=')) {
                    $k=explode('!=',$v);
                    $map[$k[0]] = array('neq',$k[1]);
                    continue;
                }
                $k=explode('=',$v);
                $map[$k[0]] = $k[1];
            }
        }
        $data = $this->field($field)->where($map)->group('relation_game_id')->order($order)->page($page,$limit)->select();
        $data=game_merge($data,$data['map']);
        return $data;
    }
    
    public function type($type='',$gamenumber=false,$isall=false) {
        $sum=0;
        $result = array();
        if (empty($type)) {return '';}
        switch($type) {
            case 'open_type':              
                $data = $this->field("gt.id,gt.open_name,count(DISTINCT tab_game.relation_game_id) as count")
                    ->join("tab_opentype as gt on (tab_game.category=gt.id and tab_game.game_status=1)","right")
                    ->where("gt.status=1 ")
                    ->group("gt.id")->order("gt.id")->select();
                if ($gamenumber) {
                    $arr = ['id'=>0,'open_name'=>'公测','count'=>$this->where('category = 0')->count()];
                    $data[] = $arr;
                    foreach ($data as $k=> $v) {
                        $id = $v['id'];
                        $result[$id]['id'] = $id;
                        $result[$id]['type_name'] = $v['open_name'];
                        $sum += $result[$id]['count'] = $v['count'];
                    }                   
                } else {
                    foreach ($data as $k=> $v) {
                        $id = $v['id'];
                        $result[$id] = $v['open_name'];
                    }
                }
            ;break;
            case 'game_type': 
            default:
                $data = M()
                    ->table('tab_game_type as gt')
                    ->field("gt.id,gt.type_name,count(DISTINCT tab_game.relation_game_id) as count,gt.icon")
                    ->join("tab_game on (tab_game.game_type_id=gt.id and tab_game.game_status=1)","left")
                    ->where("gt.status_show=1")
                    ->group("gt.id")
                    ->order("gt.id")
                    ->select();

                if ($gamenumber) {
                    
                    foreach ($data as $k=> $v) {
                        $id = $v['id'];
                        $result[$id]['id'] = $id;
                        $result[$id]['type_name'] = $v['type_name'];
                        $sum += $result[$id]['count'] = $v['count'];
                        $result[$id]['icon'] = $v['icon'];
                    }                   
                } else {
                    foreach ($data as $k=> $v) {
                        $id = $v['id'];
                        $result[$id] = $v['type_name'];
                    }
                }
        }
        if ($isall)
            if ($gamenumber) {
                $result[0]['id']=0;
                $result[0]['type_name']='全部';
                $result[0]['count']=$sum;
            } else {
                $result[0]="全部";
            }
        ksort($result);
        return $result;
    }
    
    
    public function server($show_status='',$page=1,$order='id desc',$limit=10,$field=true) {
        $map = array();
        if (!empty($show_status))
            $map['show_status'] = array('in',explode(',',$show_status));
        
        $field = $field?"s.*,g.game_name,g.icon,g.cover,g.relation_game_id":$field;
        
        $data = M('Server','tab_')->table("__SERVER__ as s")
            ->field($field)
            ->join("__GAME__ as g on (g.id=s.game_id) ","left")
            ->where($map)->group('relation_game_id')->order($order)->page($page,$limit)->select();
        $data=game_merge($data,$data['map']);
        return $data;
    }
    
    
    public function multiple($model,$num=10) {
        
        $field = isset($model['field'])?$model['field']:true;
		$order = isset($model['order'])?$model['order']:" id DESC ";
		$where = isset($model['where'])?$model['where']:"";
		
        $data = $this->field($field)
            ->where($where)
            ->order($order)->limit($num)->select();
        
        return $data;   
    }
    

    /**
     * 检查标识是否已存在(只需在同一根节点下不重复)
     * @param string $name
     * @return true无重复，false已存在
     * @author huajie <banhuajie@163.com>
     */
    protected function checkName(){
        $name        = I('post.name');
        $category_id = I('post.category_id', 0);
        $id          = I('post.id', 0);

        $map = array('name' => $name, 'id' => array('neq', $id), 'status' => array('neq', -1));

        $category = get_category($category_id);
        if ($category['pid'] == 0) {
            $map['category_id'] = $category_id;
        } else {
            $parent             = get_parent_category($category['id']);
            $root               = array_shift($parent);
            $map['category_id'] = array('in', D("Category")->getChildrenId($root['id']));
        }

        $res = $this->where($map)->getField('id');
        if ($res) {
            return false;
        }
        return true;
    }

    /**
     * 生成不重复的name标识
     * @author huajie <banhuajie@163.com>
     */
    private function generateName(){
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789';	//源字符串
        $min = 10;
        $max = 39;
        $name = false;
        while (true){
            $length = rand($min, $max);	//生成的标识长度
            $name = substr(str_shuffle(substr($str,0,26)), 0, 1);	//第一个字母
            $name .= substr(str_shuffle($str), 0, $length);
            //检查是否已存在
            $res = $this->getFieldByName($name, 'id');
            if(!$res){
                break;
            }
        }
        return $name;
    }
}