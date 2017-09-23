<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Mobile\Model;
use Think\Model;

/**
 * 文档基础模型
 */
class AdvModel extends Model{

    

    /* 自动验证规则 */
    protected $_validate = array(
        
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('start_time',  'strtotime', self::MODEL_INSERT, 'function'),
        array('end_time',  'strtotime', self::MODEL_INSERT, 'function'),
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
    
    public function carousel($cate,$sort='id desc',$limit=10,$field=true) {
        $dire = $join = "";
        if (!is_numeric($cate)) {
            $join = "tab_adv_pos on tab_adv.pos_id = tab_adv_pos.id";
            $dire = "left";
            $map['tab_adv_pos.name'] = $cate;
        } else {
            $map['tab_adv.pos_id'] = $cate;
        }
        
        $map['tab_adv.status'] = 1;
        $map['tab_adv.data'] = array('neq','');
        
        $time = time();
        $map['_string'] = "(tab_adv.start_time <= $time or tab_adv.start_time = 0 ) and (tab_adv.end_time >= $time or tab_adv.end_time = 0)";
        $data = $this->field($field)

                     ->join($join,$dire)

                     ->where($map)
                     
                     ->order($sort)

                     ->limit($limit)

                     ->select();
        
        if (count($data) < $limit) {
            $count = count($data);
            $len = $limit - $count;
            while ($len > 0) {
                $data[] = $data[$limit-$len-1];
                $len--;
            }
        }
        
        return $data;
    }






}