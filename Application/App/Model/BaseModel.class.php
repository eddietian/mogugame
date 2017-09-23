<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/24
 * Time: 10:57
 */
namespace App\Model;

use Think\Model;

class BaseModel extends Model{

	protected function _initialize()
	{
		$this->tablePrefix = "tab_";
		C(api('Config/lists'));//加载配置变量
	}
}