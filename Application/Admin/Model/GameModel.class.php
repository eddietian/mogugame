<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Open\Model\ContractModel;
use Think\Model;
use Admin\Logic\SetLogic;

/**
 * 文档基础模型
 */
class GameModel extends Model{

    

    /* 自动验证规则 */
    protected $_validate = array(
        array('game_name',  'require', '游戏名称不能为空',         self::MUST_VALIDATE,  'regex',  self::MODEL_BOTH),
        array('game_name', 'checkGame', '游戏名称已存在', 1, 'unique', 1), // 新增时候验证是否唯一
        array('game_name',  '1,30',    '游戏名称不能超过30个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
        array('game_appid', 'require', '游戏APPID不能为空',        self::MUST_VALIDATE,  'regex',  self::MODEL_BOTH),
        array('game_coin_ration', '/^[0-9]*$/',             '游戏比例必须是数字',             self::VALUE_VALIDATE,  'regex',  self::MODEL_BOTH),
        array('ratio', 'ratio_c',             '分成比例不正确',             self::MUST_VALIDATE,  'callback',  self::MODEL_BOTH),
        array('money', '/^[0-9]*$/'/*非负整数或者1-2位小数'/^\d+(\.\d{1,2})?$/'*/,             '注册单价不正确',             self::MUST_VALIDATE,  'regex',  self::MODEL_BOTH),
        array('sort',             '/^[0-9]*$/',             '排序必须是数字',                 self::VALUE_VALIDATE,  'regex',  self::MODEL_BOTH),
        array('game_score',       '/^(\d(\.\d)?|10)$/',     '游戏评分输入格式不正确',         self::VALUE_VALIDATE,  'regex',  self::MODEL_BOTH),
        array('recommend_level',  '/^(\d(\.\d)?|10)$/',     '推荐指数输入格式不正确',         self::VALUE_VALIDATE,  'regex',  self::MODEL_BOTH),
        array('discount',[1,10],           '代充折扣错误',             self::MUST_VALIDATE,  'between',  self::MODEL_BOTH),
        array('bind_recharge_discount',[1,10],           '绑币充值折扣错误',             self::MUST_VALIDATE,  'between',  self::MODEL_BOTH),

    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time',       'getCreateTime',         self::MODEL_INSERT,  'callback'),
    );

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
     * 获取详情页数据(前端)
     * @param  integer $id 文档ID
     * @return array       详细数据
     */
    public function detail($id){
        /* 获取基础数据 */
        $map['relation_game_id']=$id;
        $info = $this->field(true)->where($map)->group('relation_game_id')->select();
        $info= game_merge($info,$map);
        $info=reset($info);
        if(!(is_array($info) || $info['game_status']!=1)){
            $this->error = '游戏被禁用或已删除！';
            return false;
        }
        /* 获取模型数据 */
        $logic  = new SetLogic();
        $detail = $logic->detail($id); //获取指定ID的数据
        if(!$detail){
            $this->error = $logic->getError();
            return false;
        }
        $info = array_merge( $detail,$info);

        return $info;
    }
    
     /**
     * 获取详情页数据(后端)
     * @param  integer $id 文档ID
     * @return array       详细数据
     */
    public function detailback($id){
        /* 获取基础数据 */
        $info = $this->field(true)->find($id);
        if(!(is_array($info) || 1 !== $info['status'])){
            $this->error = '游戏被禁用或已删除！';
            return false;
        }
        /* 获取模型数据 */
        $logic  = new SetLogic();
        $detailback = $logic->detail($id); //获取指定ID的数据
        if(!$detailback){
            $this->error = $logic->getError();
            return false;
        }
        $info = array_merge( $detailback,$info);
        return $info;
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
            $id = $this->add($data); //添加基础内容
            if(!$id){
                $this->error = '新增基础内容出错！';
                return false;
            }else{
                if(!isset($data['relation_game_id'])){
                    $relation=M('Game','tab_')->where(array('id'=>$id))->save(array('relation_game_id'=>$id));
                    if(!$relation){
                        $this->error('关联id添加失败');//游戏添加完成
                    }
                }
            }
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = '更新基础内容出错！';
                return false;
            }
        }
         // 添加或新增扩展内容
        $logic = $this->logic('Set');
        $logic->checkModelAttr(5);
        if(!$logic->update($id)){
            if(isset($id)){ //新增失败，删除基础数据
                $this->delete($id);
            }
            $this->error = $logic->getError();
            return false;
        }
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
    protected function ratio_c(){
        $ratio=I('post.ratio');
        if((0<=$ratio)&&(100>=$ratio)){
            return true;
        }else{
            
            return false;
        }
    }
    protected function checkGame(){
        $game_name=I('post.game_name');
        if(I('post.sibling_id')){
             $map['game_name']=$game_name;
             $game=M('Game','tab_')->where($map)->find();
            if(empty($game)){
                return true;
            }else{
                return false;
            }
        }else{
            $map['game_name']=array('like',substr_replace($game_name,'___',-10,9));
            $game=M('Game','tab_')->where($map)->find();
            if(empty($game)){
                return true;
            }else{
                return false;
            }
        }
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