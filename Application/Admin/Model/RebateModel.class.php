<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 文档基础模型
 */
class RebateModel extends Model{

    

    /* 自动验证规则 */
    protected $_validate = array(
        array('game_id', 'require', '游戏不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('starttime', 'require', '开始时间不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('endtime', 'check_endtime', '结束时间不能小于开始时间', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('ratio', [1,100], '返利比例输入错误', self::MUST_VALIDATE, 'between', self::MODEL_BOTH),
        array('money', [0,99999999], '单笔金额限制输入错误', self::MUST_VALIDATE, 'between', self::MODEL_BOTH),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('starttime', 'strtotime', self::MODEL_INSERT, 'function'),
        array('endtime', 'strtotime', self::MODEL_INSERT, 'function'),
        array('game_name', 'get_game_name', self::MODEL_INSERT, 'callback'),
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
     * 检查返利对象是否合法
     * @return bool
     */
    public function check_promote(){
        $promote_id = I('promote_id');
        $game_id = I('game_id');
        /*if(!empty(I('id'))){ //编辑
            $rebate = M('rebate','tab_')->find(I('id'));
            if($rebate['game_id'] == $game_id && $rebate['promote_id'] == $promote_id){
                return true;
            }
        }*/
//        $time = empty(I('starttime')) ? $rebate['starttime'] : strtotime(I('starttime'));
        $time = strtotime(I('starttime'));
        $map = "((endtime > {$time}) or (endtime = 0)) and game_id = {$game_id} and promote_id = {$promote_id}";
        $rebate = M('rebate','tab_')->where($map)->find();
        if(!empty($rebate)){
            $this->error = "该游戏已设置返利对象";
            return false;
        }
        $map = "((endtime > {$time}) or (endtime = 0)) and game_id = {$game_id} ";
        switch ($promote_id){
            case -1 :
                $map .= "and (promote_id = 0 or promote_id = 1)";
                $error = "该游戏已设置其他返利对象";
                break;
            case 0:
                $map .= "and promote_id = -1";
                $error = "该游戏已设置全站用户返利";
                break;
            case 1:
                $map .= "and promote_id = -1";
                $error = "该游戏已设置全站用户返利";
                break;
        }
        $rebate = M('rebate','tab_')->where($map)->find();
        if(!empty($rebate)){
            $this->error = $error;
            return false;
        }else{
            return true;
        }
    }

    /**
     * 获取游戏名称
     * @param $game_id
     * @return string
     */
    public function get_game_name(){
        $game_id = I('game_id');
        $game_name = get_game_name($game_id);
        return $game_name;
    }

    /**
     * 检查结束时间是否大于开始时间
     * @param $endtime
     * @return bool
     */
    public function check_endtime($endtime){
        if(!empty($endtime)){
            $end = strtotime($endtime);
            $start = strtotime(I('starttime'));
            if($end < $start){
                return false;
            }
        }
        return true;
    }

}