<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/30
 * Time: 13:56
 */

namespace App\Model;

use Think\Model;

class DocumentModel extends Model {

	public function getArticleListsByCategory($name,$p=1){
		$page = intval($p);
		$page = $page ? $page : 1; //默认显示第一页数据
		$row = 5;
		$category = M("category")->where(['name'=>$name])->find();
		$map['category_id'] = $category['id'];
		$map['display'] = 1;
		$map['status'] = 1;
		$time = NOW_TIME;
		$map['_string'] = "deadline < {$time} or deadline = 0";
		$data = $this->field("id,title,description,create_time,cover_id as cover")
			->where($map)
			->order("level desc,id desc")
			->page($page,$row)->select();
		foreach ($data as $key => $val) {
			$data[$key]['cover'] = get_img_url($val['cover']);
			$data[$key]['url'] = $this->generate_url($val['id']);
		}
		return $data;
	}

	/**
	 * 生成文章链接
	 * @param $id
	 * @return string
	 * author: xmy 280564871@qq.com
	 */
	public function generate_url($id){
		return U("Article/show",['id'=>$id],'',true);
	}

	public function getArticle($id){
		$map['d.id'] = $id;
		$data = $this->table("sys_document as d")
			->field("d.title,a.content,create_time,d.cover_id,d.description")
			->join("sys_document_article a on a.id = d.id")
			->where($map)
			->find();
		$data['cover_id'] = get_img_url($data['cover_id']);
		return $data;

	}

}