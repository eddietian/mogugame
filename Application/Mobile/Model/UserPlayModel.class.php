<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mobile\Model;
use Think\Model;

/**
 * 用户模型
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */

class UserPlayModel extends Model
{

	protected $_validate = array();

	/* 自动完成规则 */
	protected $_auto = array(
		array('server_id', 0, self::MODEL_INSERT),
		array('server_name', 0, self::MODEL_INSERT),
		array('role_id', 0, self::MODEL_INSERT),
		array('role_name', "", self::MODEL_INSERT),
		array('role_level', 0, self::MODEL_INSERT),
		array('bind_balance', 0, self::MODEL_INSERT),
	);

	/**
	 * 构造函数
	 * @param string $name 模型名称
	 * @param string $tablePrefix 表前缀
	 * @param mixed $connection 数据库连接信息
	 */
	public function __construct($name = '', $tablePrefix = '', $connection = '')
	{
		/* 设置默认的表前缀 */
		$this->tablePrefix = 'tab_';
		/* 执行构造方法 */
		parent::__construct($name, $tablePrefix, $connection);
	}

	/**
	 * 获取开放平台游戏 累计玩家
	 * @param string $map
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getTotalPlayerOfOpen($map = "")
	{
		$sql = $this->alias("p")->field("user_id")
			->join("left join tab_game g on g.id = p.game_id")
			->group("p.game_id,p.user_id")
			->where($map)
			->select(false);
		$sql = "select count(res.user_id) as num from ({$sql}) as res";
		$data = M()->query($sql);
		return $data[0]['num'];
	}


}
