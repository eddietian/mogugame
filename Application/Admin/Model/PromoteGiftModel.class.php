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
class PromoteGiftModel extends Model{

    

    /* 自动验证规则 */
    protected $_validate = array(
        array('game_id','require','游戏名称不能为空',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
        // array('server_id','require','区服名称不能为空',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
        array('giftbag_name', 'require', '礼包名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('giftbag_name', '1,30', '礼包名称不能超过30个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
        // array('giftbag_type', 'require', '请选择礼包类型', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('area_num', 0, self::MODEL_BOTH),
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
}