<?php
namespace Admin\Controller;

class QuestionController extends ThinkController
{
    public function lists($p = 0)
    {
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $row = 10;
        if (!empty(I('account'))) {
            $map['account'] = I('account');
        }
        if (isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])) {
            $map['create_time'] = array('BETWEEN', array(strtotime($_REQUEST['time-start']), strtotime($_REQUEST['time-end']) + 24 * 60 * 60 - 1));
            unset($_REQUEST['time-start']);
            unset($_REQUEST['time-end']);
        }
        if (isset($_REQUEST['start']) && isset($_REQUEST['end'])) {
            $map['update_time'] = array('BETWEEN', array(strtotime($_REQUEST['start']), strtotime($_REQUEST['end']) + 24 * 60 * 60 - 1));
            unset($_REQUEST['start']);
            unset($_REQUEST['end']);
        }
        if (isset($_REQUEST['status']) && $_REQUEST['status'] != '') {
            $map['status'] = I('status');
        }
        $data = M('question', 'tab_')->where($map)->page($page, $row)->order('create_time desc,id')->group('game_id,user_id')->select();
        $count = M('question', 'tab_')->where($map)->count();
        if ($count > $row) {
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $this->assign('list_data', $data);
        $this->meta_title = "用户留言列表";
        $this->display();
    }

    public function show()
    {
        $map['account'] = $_REQUEST['account'];
        $data = M('question', 'tab_')->where($map)->select();
        $this->assign('user', $data);
        $this->display();
    }

//回复留言
    public function reply()
    {
        $map['id'] = $_REQUEST['id'];
        $data = M('question', 'tab_')->where($map)->find();
        $this->assign('data', $data);
        $this->display();
    }

    public function reply_add()
    {
        $map['id'] = $_REQUEST['id'];
        if (empty($_REQUEST['answer'])) {
            $this->ajaxReturn(array("status" => 2, "msg" => "留言不能为空"));
        }
        $data = array(
            'answer' => trim($_REQUEST['answer']),
            'update_time' => time(),
            'status' => 1
        );
        $res = M('question', 'tab_')->where($map)->save($data);
        if ($res > 0) {
            $this->ajaxReturn(array("status" => 1, "msg" => "留言成功"));
        } else {
            $this->ajaxReturn(array("status" => 0, "msg" => "留言失败"));
        }
    }

}