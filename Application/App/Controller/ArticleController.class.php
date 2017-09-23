<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/3/30
 * Time: 13:53
 */
namespace App\Controller;

use App\Model\DocumentModel;

class ArticleController extends BaseController{

	/**
	 * 获取文章列表
	 * @param int $p
	 * @param $category
	 * author: xmy 280564871@qq.com
	 */
	public function get_article_lists($p=1,$category){
		switch ($category) {
			case 1://资讯
				$category_name = "APP_INFO";
				break;
			case 2://公告
				$category_name = "APP_NOTICE";
				break;
			case 3://活动
				$category_name = "APP_ACTIVITY";
				break;
			default:
				$category_name = "APP_ARTICLE";
		}
		$data = D("Document")->getArticleListsByCategory($category_name,$p);
		if(empty($data)){
			$this->set_message(-1,"暂无文章");
		}else{
			$this->set_message(1,1,$data);
		}
	}

	/**
	 * 文章显示
	 * @param string $id
	 * author: xmy 280564871@qq.com
	 */
	public function show($id){
		$data = D("Document")->getArticle($id);
		$this->assign("data",$data);
		$this->display("index");
	}


	/**
	 * 用户协议
	 * author: xmy 280564871@qq.com
	 */
	public function agreement()
	{
		$data = D("Document")->getArticleListsByCategory("agreement");
		$data = D("Document")->getArticle($data[0]['id']);
		$this->assign("data",$data);
		$this->display();
	}

	/**
	 * 获取分享信息
	 * @param $game_id
	 * author: xmy 280564871@qq.com
	 */
	public function get_share_info($article_id){
		$model = new DocumentModel();
		$article = $model->getArticle($article_id);
		if(empty($article)){
			$this->set_message(-1,"文章不存在");
		}
		$result['title'] = $article['title'];
		$result['icon'] = $article['cover_id'];
		$result['content'] = $article['description'];
		$result['url'] = U('Article/show',['id'=>$article_id],true,true);
		$this->set_message(1,1,$result);
	}

}