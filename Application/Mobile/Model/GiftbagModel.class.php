<?php
namespace Mobile\Model;
use Think\Model;

/**
 * 文档基础模型
 */
class GiftbagModel extends Model{

    

    /* 自动验证规则 */
    protected $_validate = array(
        array('giftbag_name', 'require', '礼包名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('giftbag_name', '1,30', '礼包名称不能超过30个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
        array('giftbag_type', 'require', '请选择礼包类型', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('start_time', 'require', '开始时间不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('end_time', 'require', '结束时间不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', self::MODEL_BOTH,'callback'),
        array('area_num', 0, self::MODEL_BOTH),
        array('start_time', 'strtotime', self::MODEL_BOTH, 'function'),
        array('end_time', 'strtotime', self::MODEL_BOTH, 'function'),
        //array('game_score', 0, self::MODEL_BOTH),
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
     * 创建时间不写则取当前时间
     * @return int 时间戳
     * @author huajie <banhuajie@163.com>
     */
    protected function getCreateTime(){
        $create_time    =   I('post.create_time');
        return $create_time?strtotime($create_time):NOW_TIME;
    }
    
    public function detail($id) {
    
        $time = time();
        $data = $this->table("__GIFTBAG__ as gb")
            ->field("gb.*,g.icon,g.game_name,g.icon,g.screenshot,g.introduction")
            ->join("__GAME__ as g on(g.id=gb.game_id )","left")
            ->where("gb.id = $id and gb.status=1 and (gb.end_time>$time) ")
            ->find();
            
        if (empty($data) || !is_array($data)) {
            return "";
        }
        
        $count = D("GiftRecord")->where("gift_id=$id")->count();
        $number = $data['number'] = count(explode(',',$data['novice']));
        $data['novicepercent'] = round($number/($count+$number)*100,1);

        return $data;
    }

    public function detail1($id) {

        $time = time();
        $data = $this->table("__GIFTBAG__ as gb")
            ->field("gb.*,g.icon,g.game_name,g.icon,g.screenshot,g.introduction")
            ->join("__GAME__ as g on(g.id=gb.game_id )","left")
            ->where("gb.id = $id and gb.status=1")
            ->find();

        if (empty($data) || !is_array($data)) {
            return "";
        }

        $count = D("GiftRecord")->where("gift_id=$id")->count();
        $number = $data['number'] = count(explode(',',$data['novice']));
        $data['novicepercent'] = round($number/($count+$number)*100,1);

        return $data;
    }

    
    public function index($giftbag_type="",$page=1,$sort="tab_giftbag.id desc",$limit=10,$field=true,$isrecord=false) {
        $map = array();
        if (!empty($giftbag_type)) {
            $map['tab_giftbag.giftbag_type'] = array('in',explode(',',$giftbag_type));
        }
        
        $map['tab_giftbag.status'] = 1;
        /* $map['tab_giftbag.start_time'] = array('elt',time()); */
        $map['tab_giftbag.end_time'] = array(array('egt',time()),array('eq',0),'or');
        
        $field = $field?"tab_giftbag.*,tab_game.icon,tab_game.cover,tab_game.game_name":$field;
        
        $data = $this->field($field)
            ->join("tab_game on tab_game.id=tab_giftbag.game_id","left")
            ->where($map)->page($page,$limit)->order($sort)->select();
        
        if (empty($data) || !is_array($data)) {
            return '';
        }
        
        if ($isrecord) {
            foreach ($data as $k => $v) {
                $record = D("GiftRecord")->where(array('game_id'=>$v['game_id'],'gift_id'=>$v['id']))->count();
                $number = $data[$k]['number'] = count(explode(',',$v['novice']));
                $data[$k]['total']=$record + $number;
            }
        }
        
        return $data;
        
    }   
    
    public function record($giftbag_type="",$limit=10) {
        $map = array();
        if (!empty($giftbag_type) ) {
            $map['tab_giftbag.giftbag_type'] = array('in',explode(',',$giftbag_type));
        }
        
        $map['tab_giftbag.status'] = 1;
        /* $map['tab_giftbag.start_time'] = array('elt',time()); */
        $map['tab_giftbag.end_time'] = array('egt',time());
        
        $record = D("GiftRecord")
            ->field("count(tab_gift_record.id) as record,tab_giftbag.*,tab_game.icon,tab_game.cover,tab_game.game_name")
            ->join("tab_giftbag on tab_giftbag.id = tab_gift_record.gift_id","left")
            ->join("tab_game on tab_game.id = tab_gift_record.game_id","left")
            ->limit($limit)
            ->where($map)
            ->group("tab_giftbag.id")
            ->order("record desc")
            ->select();
        
        if (!empty($record) && is_array($record)) {
            $fieldarray = array();
            foreach ($record as $k => $v) {
                $number = $record[$k]['number'] = count(explode(',',$v['novice']));
                $fieldarray[] = $record[$k]['total'] = $v['record'] + $number;
            }
            array_multisort($fieldarray,SORT_DESC,$record);
        }
        
        return $record;
    }

    
    public function multiple($model,$num=10,$flag = false) {
        
        $join=$dire="";
        
        if ($flag) {
            
            $join = "__GAME__ as g on(g.id=gb.game_id) ";
            
            $dire = "left";
       
            $gf = ",g.icon,g.cover,g.game_name";
        }
        
        $field = isset($model['field'])?$model['field']:"gb.* ".$gf;
        $order = isset($model['order'])?$model['order']:" gb.id DESC ";
        $where = isset($model['where'])?$model['where']:"";
        
        $data = $this->field($field)->table("__GIFTBAG__ as gb ")
            ->join($join,$dire)->where($where)
            ->order($order)->limit($num)->select();
        
        return $data;        
    }
    
    /**
     * 生成不重复的name标识
     * @author huajie <banhuajie@163.com>
     */
    private function generateName(){
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789';  //源字符串
        $min = 10;
        $max = 39;
        $name = false;
        while (true){
            $length = rand($min, $max); //生成的标识长度
            $name = substr(str_shuffle(substr($str,0,26)), 0, 1);   //第一个字母
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