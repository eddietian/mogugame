<?php
namespace admin\Controller;

use Think\Controller;

class ExportController extends Controller
{
    public function exportExcel($expTitle, $expCellName, $expTableData)
    {
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称  
//        $fileName = session('user_auth.username').date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $fileName = $expTitle;
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        Vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
        }
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        ob_end_clean();//清除缓冲区,避免乱码
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    //导出Excel
    function expUser($id)
    {
        switch ($id) {
            case 1:
                $xlsName = "角色数据";
                $xlsCell = array(
                    array('user_account', '玩家账号'),
                    array('game_name', '游戏名称'),
                    array('server_name', '游戏区服'),
                    array('role_name', '角色名'),
                    array('role_level', '游戏等级'),
                    array('play_time', '最后登录时间', 'time_format', '*'),
                    array('play_ip', '最后登录IP'),
                );
                if (isset($_REQUEST['game_name'])) {
                    $map['game_name'] = trim($_REQUEST['game_name']);
                    unset($_REQUEST['game_name']);
                }
                if (isset($_REQUEST['user_account'])) {
                    $map['user_account'] = trim($_REQUEST['user_account']);
                    unset($_REQUEST['user_account']);
                }
                if (isset($_REQUEST['server_name'])) {
                    $map['server_name'] = trim($_REQUEST['server_name']);
                    unset($_REQUEST['server_name']);
                }
                if (isset($_REQUEST['role_name'])) {
                    $map['role_name'] = trim($_REQUEST['role_name']);
                    unset($_REQUEST['role_name']);
                }
                $xlsData = M('user_play', 'tab_')
                    ->where($map)
                    ->select();
                break;
            case 2:
                $xlsName = "渠道注册";
                $xlsCell = array(
                    array('account', '玩家账号'),
                    array('fgame_name', '注册游戏'),
                    array('promote_account', '所属渠道'),
                    array('register_time', '注册时间', 'time_format', '*'),
                    array('register_ip', '注册IP'),
                    array('parent_id', '上线渠道'),
                    array('promote_id', '平台专员', 'get_belong_admin', '*'),
                );
                if (isset($_REQUEST['game_name'])) {
                    if ($_REQUEST['game_name'] == '全部') {
                        unset($_REQUEST['game_name']);
                    } else {
                        $map['fgame_name'] = $_REQUEST['game_name'];
                        unset($_REQUEST['game_name']);
                    }
                }
                $map['tab_user.promote_id'] = array("neq", 0);
                if (isset($_REQUEST['promote_name'])) {
                    if ($_REQUEST['promote_name'] == '全部') {
                        unset($_REQUEST['promote_name']);
                    } else if ($_REQUEST['promote_name'] == '自然注册') {
                        $map['tab_user.promote_id'] = array("elt", 0);
                        unset($_REQUEST['promote_name']);
                    } else {
                        $map['tab_user.promote_id'] = get_promote_id($_REQUEST['promote_name']);
                        unset($_REQUEST['promote_name']);
                    }
                }
                if (isset($_REQUEST['is_check']) && $_REQUEST['is_check'] != "全部") {
                    $map['is_check'] = check_status($_REQUEST['is_check']);
                    unset($_REQUEST['is_check']);
                }
                if (isset($_REQUEST['account'])) {
                    $map['tab_user.account'] = array('like', '%' . $_REQUEST['account'] . '%');
                    unset($_REQUEST['account']);
                }
                if (isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])) {
                    $map['register_time'] = array('BETWEEN', array(strtotime($_REQUEST['time-start']), strtotime($_REQUEST['time-end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['time-start']);
                    unset($_REQUEST['time_end']);
                }
                if (isset($_REQUEST['start']) && isset($_REQUEST['end'])) {
                    $map['register_time'] = array('BETWEEN', array(strtotime($_REQUEST['start']), strtotime($_REQUEST['end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['start']);
                    unset($_REQUEST['end']);
                }
                $model = array(
                    'm_name' => 'User',
                    'fields' => array('id', 'tab_user.account', 'tab_user.fgame_name', 'nickname', 'email', 'phone', 'promote_id', 'parent_id', 'register_time', 'register_way', 'register_ip', 'promote_account', 'parent_name', 'is_check'),
                    'key' => array('tab_user.account', 'tab_game.fgame_name'),
                    'map' => $map,
                    'order' => 'id desc',
                    'title' => '渠道注册',
                    'template_list' => 'ch_reg_list',
                );
                $name = $model['m_name'];
                $xlsData = M($name, "tab_")
                    ->field($model['fields'])
                    ->join($model['join'])
                    ->join($model['joins'])
                    ->join($model['joinss'])
                    ->where($model['map'])
                    ->order($model['order'])
                    ->group($model['group'])
                    ->select();
                foreach ($xlsData as $key => &$value) {
                    $xlsData[$key]['parent_id'] = get_top_promote($value['promote_id'], $value['parent_id']);
                }
                break;
            case 3:
                $xlsName = "玩家列表";
                $xlsCell = array(
                    array('id', '账号ID'),
                    array('account', '玩家账号'),
                    array('promote_account', '所属渠道'),
                    array('balance', '账户余额'),
                    array('cumulative', '充值金额'),
                    array('lock_status', '账号状态', 'get_info_status', '*', '4'),
                    array('register_way', '注册来源', "get_registertype", '*'),
                    array('register_time', '注册时间', 'time_format', '*'),
                    array('register_ip', '注册IP'),
                    array('login_time', '最后登录时间', 'time_format', '*'),
                );
                if(isset($_REQUEST['promote_account'])){
                    $map['promote_id'] = get_promote_id(trim(I('promote_account')));
                    unset($_REQUEST['promote_account']);
                }
                if(isset($_REQUEST['register_way'])){
                    $map['register_way']  = I('register_way');
                    unset($_REQUEST['register_way']);
                }
                if(isset($_REQUEST['status'])){
                    $map['lock_status']  = I('status');
                    unset($_REQUEST['status']);
                }
                if(!empty(I('time_start')) && !empty(I('time_end'))){
                    $map['register_time'] = array('BETWEEN',array(strtotime(I('get.time_start')),strtotime(I('time_end'))+24*60*60-1));
                    unset($_REQUEST['time_start']);unset($_REQUEST['time_end']);
                }
                empty(I('account')) || $map['account'] = ['like','%'.I('account').'%'];
                //排序
                if (I('total_status') == 1) {
                    $order = 'cumulative asc';
                } elseif (I('total_status') == 2) {
                    $order = 'cumulative desc';
                } else {
                    $order = 'id desc';
                }
                //数据
                $xlsData = M('user', 'tab_')
                    ->where($map)
                    ->order($order)
                    ->select();

                break;
            case 4:
                $xlsName = "渠道充值";

                if(isset($_REQUEST['game_name'])){
                    if($_REQUEST['game_name']=='全部'){
                        unset($_REQUEST['game_name']);
                    }else{
                        $map['game_name']=$_REQUEST['game_name'];
                        unset($_REQUEST['game_name']);
                    }
                }
                if(!empty($_REQUEST['server_id'])){
                    $map['server_id']=$_REQUEST['server_id'];
                    unset($_REQUEST['server_id']);
                }
                if(isset($_REQUEST['promote_name'])){
                    if($_REQUEST['promote_name']=='全部'){
                        unset($_REQUEST['promote_name']);
                    }else if($_REQUEST['promote_name']=='自然注册'){
                        $map['promote_id']=array("lte",0);

                        unset($_REQUEST['promote_name']);
                    }else{
                        $map['promote_id']=get_promote_id($_REQUEST['promote_name']);
                        unset($_REQUEST['promote_name']);
                    }
                }else{
                    $map['promote_id']=array("gt",0);
                }

                if(isset($_REQUEST['pay_way'])){
                    $map['pay_way']=$_REQUEST['pay_way'];
                    unset($_REQUEST['pay_way']);
                }
                if(isset($_REQUEST['user_account'])){
                    $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
                    unset($_REQUEST['user_account']);
                }
                if(isset($_REQUEST['spend_ip'])){
                    $map['spend_ip']=array('like','%'.$_REQUEST['spend_ip'].'%');
                    unset($_REQUEST['spend_ip']);
                }
                if(isset($_REQUEST['promote_name'])){
                    $map['promote_account']=$_REQUEST['promote_name'];
                    unset($_REQUEST['user_account']);
                }
                if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
                    $map['pay_time']=array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
                    unset($_REQUEST['time-start']);unset($_REQUEST['time_end']);
                }
                if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
                    $map['pay_time']=array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
                    unset($_REQUEST['start']);unset($_REQUEST['end']);
                }
                $model = array(
                    'm_name' => 'Spend',
                    'map' => $map,
                    'order' => 'id desc',
                    'title' => '渠道充值',
                    'template_list' => 'spend_list',
                );
                $map1 = $map;
                $map1['pay_status'] = 1;
                $total = M('Spend', 'tab_')->where($map1)->sum('pay_amount');
                $total = sprintf("%.2f", $total);

                $xlsCell = array(
                    array('pay_order_number', '订单号'),
                    array('pay_time', '充值时间', 'time_format', '*'),
                    array('user_account', '玩家账号'),
                    array('promote_account', '所属渠道'),
                    array('game_name', '游戏名称'),
                    array('server_name', '游戏区服'),
                    array('game_player_name', '角色名'),
                    array('spend_ip', '充值IP'),
                    array('pay_amount', '充值金额'),
                    array('pay_way', '充值方式', 'get_info_status', '*', 18),
                    array('parent_id', '上级渠道'),
                    array('promote_id', '平台专员', 'get_belong_admin', '*'),
                    array("", "共计充值{$total}"),
                );
                $name = $model['m_name'];
                $xlsData = $data = D($name)
                    ->where($map)
                    ->order($model['order'])
                    ->group($model['group'])
                    ->select();
                foreach ($xlsData as $key => &$value) {
                    $xlsData[$key]['parent_id'] = get_top_promote($value['promote_id'], $value['parent_id']);
                }
                break;
            case 5:
                $map['promote_id'] = array("neq", 0);
                if (isset($_REQUEST['user_account'])) {
                    $map['user_account'] = array('like', '%' . $_REQUEST['user_account'] . '%');
                    unset($_REQUEST['user_account']);
                }
                if (!empty($_REQUEST['pay_way'])) {
                    $map['pay_way'] = $_REQUEST['pay_way'];
                    unset($_REQUEST['pay_way']);
                }
                if (isset($_REQUEST['promote_name'])) {
                    if ($_REQUEST['promote_name'] == '全部') {
                        unset($_REQUEST['promote_name']);
                    } else if ($_REQUEST['promote_name'] == '自然注册') {
                        $map['promote_id'] = array("elt", 0);
                        unset($_REQUEST['promote_name']);
                    } else {
                        $map['promote_id'] = get_promote_id($_REQUEST['promote_name']);
                        unset($_REQUEST['promote_name']);
                        unset($_REQUEST['promote_id']);
                    }
                }
                if (isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])) {
                    $map['create_time'] = array('BETWEEN', array(strtotime($_REQUEST['time-start']), strtotime($_REQUEST['time-end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['time-start']);
                    unset($_REQUEST['time_end']);
                }
                if (isset($_REQUEST['start']) && isset($_REQUEST['end'])) {
                    $map['create_time'] = array('BETWEEN', array(strtotime($_REQUEST['start']), strtotime($_REQUEST['end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['start']);
                    unset($_REQUEST['end']);
                }
                if (isset($_REQUEST['game_name'])) {
                    if ($_REQUEST['game_name'] == '全部') {
                        unset($_REQUEST['game_name']);
                    } else {
                        $map['game_name'] = $_REQUEST['game_name'];
                        unset($_REQUEST['game_name']);
                    }
                }
                $map1 = $map;
                $map1['pay_status'] = 1;
                $total = M('Agent', 'tab_')->where($map1)->sum('amount');
                $total = sprintf("%.2f", $total);
                $xlsName = "代充记录";
                $xlsCell = array(
                    array('user_account', '玩家账号'),
                    array('game_name', '游戏名称'),
                    array('amount', '代充金额'),
                    array('zhekou', '折扣比例'),
                    array('real_amount', '实付金额'),
                    array('pay_status', '充值状态', 'get_info_status', '*', '7'),
                    array('pay_way', '支付方式', 'pay_way', '*'),
                    array('promote_account', '所属渠道'),
                    array('create_time', '代充时间', 'time_format', '*'),
                    array("", "共计代充{$total}"),
                );

                $xlsData = D('Agent')
                    ->where($map)
                    ->order('id DESC')
                    ->select();
                break;
            case 6:
                if(isset($_REQUEST['account'])){
                    if ($_REQUEST['account']=='全部') {
                        unset($_REQUEST['account']);
                    }
                    $map['account']=array('like','%'.$_REQUEST['account'].'%');
                    unset($_REQUEST['account']);
                }
                $map['pay_limit']=array('gt','0');
                $model=D('Promote');
                $xlsData=$model
                    ->where($map)
                    ->select();
                $xlsName = "代充额度";
                $xlsCell = array(
                    array('id','ID'),
                    array('account', '渠道账号'),
                    array('pay_limit_game', '游戏名称','get_game_limit_name','*'),
                    array('pay_limit', '代充额度'),
                    array('set_pay_time', '更新时间', 'time_format', '*'),
                );
                break;
            case 7:
                $xlsName = "消费记录";
                if (isset($_REQUEST['user_account'])) {
                    $map['user_account'] = array('like', '%' . trim($_REQUEST['user_account']) . '%');
                    unset($_REQUEST['user_account']);
                }
                if (isset($_REQUEST['spend_ip'])) {
                    $map['spend_ip'] = array('like', '%' . trim($_REQUEST['spend_ip']) . '%');
                    unset($_REQUEST['spend_ip']);
                }
                if (isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])) {
                    $map['pay_time'] = array('BETWEEN', array(strtotime($_REQUEST['time-start']), strtotime($_REQUEST['time-end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['time-start']);
                    unset($_REQUEST['time-end']);
                }
                if (isset($_REQUEST['start']) && isset($_REQUEST['end'])) {
                    $map['pay_time'] = array('BETWEEN', array(strtotime($_REQUEST['start']), strtotime($_REQUEST['end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['start']);
                    unset($_REQUEST['end']);
                }
                if (isset($_REQUEST['game_name'])) {
                    if ($_REQUEST['game_name'] == '全部') {
                        unset($_REQUEST['game_name']);
                    } else {
                        $map['game_name'] = $_REQUEST['game_name'];
                        unset($_REQUEST['game_name']);
                    }
                }
                if (isset($_REQUEST['pay_order_number'])) {
                    $map['pay_order_number'] = array('like', '%' . trim($_REQUEST['pay_order_number']) . '%');
                    unset($_REQUEST['pay_order_number']);
                }
                if (isset($_REQUEST['pay_status'])) {
                    $map['pay_status'] = $_REQUEST['pay_status'];
                    unset($_REQUEST['pay_status']);
                }
                if (isset($_REQUEST['pay_way'])) {
                    $map['pay_way'] = $_REQUEST['pay_way'];
                    unset($_REQUEST['pay_status']);
                }
                if (isset($_REQUEST['pay_game_status'])) {
                    $map['pay_game_status'] = $_REQUEST['pay_game_status'];
                    unset($_REQUEST['pay_game_status']);
                }
                $map1 = $map;
                $map1['pay_status'] = 1;
                $total = D('Spend')->where($map1)->sum('pay_amount');
                if (isset($map['pay_status']) && $map['pay_status'] == 0) {
                    $total = sprintf("%.2f", 0);
                } else {
                    $total = sprintf("%.2f", $total);
                }
                $xlsData = D('Spend')
                    ->where($map)
                    ->order('pay_time DESC')
                    ->select();
                $xlsCell = array(
                    array('pay_order_number', '订单号'),
                    array('pay_time', '充值时间', 'time_format', '*'),
                    array('user_account', '用户帐号'),
                    array('game_name', '游戏名称'),
                    array('server_name', '游戏区服'),
                    array('game_player_name', '角色名'),
                    array('spend_ip', '充值IP'),
                    array('pay_amount', '充值金额'),
                    array('pay_way', '充值方式', 'get_pay_way', '*'),
                    array('pay_status', '订单状态', 'get_info_status', '*', '9'),
                    array('pay_status', '游戏通知状态', 'get_info_status', '*', '14'),
                    array('', "共计消费{$total}"),
                );
                break;
            case 8:
                $xlsName = "平台币充值";
                if (isset($_REQUEST['user_account'])) {
                    $map['user_account'] = array('like', '%' . trim($_REQUEST['user_account']) . '%');
                    unset($_REQUEST['user_account']);
                }
                if (isset($_REQUEST['pay_order_number'])) {
                    $map['pay_order_number'] = array('like', '%' . trim($_REQUEST['pay_order_number']) . '%');
                    unset($_REQUEST['pay_order_number']);
                }
                if (isset($_REQUEST['pay_ip'])) {
                    $map['pay_ip'] = array('like', '%' . trim($_REQUEST['pay_ip']) . '%');
                    unset($_REQUEST['pay_ip']);
                }
                if (!isset($_REQUEST['promote_id'])) {

                } else if (isset($_REQUEST['promote_id']) && $_REQUEST['promote_id'] == 0) {
                    $map['promote_id'] = array('elt', 0);
                    unset($_REQUEST['promote_id']);
                    unset($_REQUEST['promote_name']);
                } elseif (isset($_REQUEST['promote_name']) && $_REQUEST['promote_id'] == -1) {
                    $map['promote_id'] = get_promote_id($_REQUEST['promote_name']);
                    unset($_REQUEST['promote_id']);
                    unset($_REQUEST['promote_name']);
                } else {
                    $map['promote_id'] = $_REQUEST['promote_id'];
                    unset($_REQUEST['promote_id']);
                    unset($_REQUEST['promote_name']);
                }
                if (isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])) {
                    $map['create_time'] = array('BETWEEN', array(strtotime($_REQUEST['time-start']), strtotime($_REQUEST['time-end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['time-start']);
                    unset($_REQUEST['time-end']);
                }
                if (isset($_REQUEST['start']) && isset($_REQUEST['end'])) {
                    $map['create_time'] = array('BETWEEN', array(strtotime($_REQUEST['start']), strtotime($_REQUEST['end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['start']);
                    unset($_REQUEST['end']);
                }
                if (isset($_REQUEST['pay_way'])) {
                    $map['pay_way'] = $_REQUEST['pay_way'];
                    unset($_REQUEST['pay_way']);
                }
                if (isset($_REQUEST['pay_status'])) {
                    $map['pay_status'] = $_REQUEST['pay_status'];
                    unset($_REQUEST['pay_status']);
                }
                $map1 = $map;
                $map1['pay_status'] = 1;
                $total = D('Deposit')->where($map1)->sum('pay_amount');
                if (isset($map['pay_status']) && $map['pay_status'] == 0) {
                    $total = sprintf("%.2f", 0);
                } else {
                    $total = sprintf("%.2f", $total);
                }

                $xlsCell = array(
                    array('pay_order_number', '订单号'),
                    array('create_time', '充值时间', 'time_format'),
                    array('user_account', '玩家账号'),
                    array('pay_ip', '充值IP'),
                    array('promote_account', '所属渠道'),
                    array('pay_amount', '充值金额'),
                    array('pay_way', '充值方式', 'get_pay_way', '*'),
                    array('pay_status', '订单状态', 'get_info_status', '*', '9'),
                    array('', "共计充值{$total}"),
                );
                $xlsData = D('Deposit')
                    ->where($map)
                    ->order('id DESC')
                    ->select();
                break;
            case 9:
                $xlsName = "平台币发放";
                if (isset($_REQUEST['user_account'])) {
                    $map['user_account'] = array('like', '%' . trim($_REQUEST['user_account']) . '%');
                    unset($_REQUEST['user_account']);
                }
                if (isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])) {
                    $map['create_time'] = array('BETWEEN', array(strtotime($_REQUEST['time-start']), strtotime($_REQUEST['time-end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['time-start']);
                    unset($_REQUEST['time-end']);
                }
                if (isset($_REQUEST['start']) && isset($_REQUEST['end'])) {
                    $map['create_time'] = array('BETWEEN', array(strtotime($_REQUEST['start']), strtotime($_REQUEST['end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['start']);
                    unset($_REQUEST['end']);
                }
                if (isset($_REQUEST['game_name'])) {
                    if ($_REQUEST['game_name'] == '全部') {
                        unset($_REQUEST['game_name']);
                    } else {
                        $map['game_name'] = $_REQUEST['game_name'];
                        unset($_REQUEST['game_name']);
                    }
                }
                if (isset($_REQUEST['op_account'])) {
                    if ($_REQUEST['op_account'] == '全部') {
                        unset($_REQUEST['op_account']);
                    } else {
                        $map['op_account'] = $_REQUEST['op_account'];
                        unset($_REQUEST['op_account']);
                    }
                }
                $map1 = $map;
                $map1['status'] = 1;
                $total = M('provide', 'tab_')->where($map1)->sum('amount');
                $total = sprintf("%.2f", $total);
                $xlsData = M('provide', 'tab_')
                    ->where($map)
                    ->order('id DESC')
                    ->select();
                $xlsCell = array(
                    array('pay_order_number', '订单号'),
                    array('user_account', '玩家账号'),
                    array('game_name', '游戏名称'),
                    array('amount', '发放金额'),
                    array('status', '状态', 'get_info_status', '*', '15'),
                    array('op_account', '操作人'),
                    array('create_time', '发放时间', 'time_format', '*'),
                    array('', "共计发放{$total}"),
                );
                break;
            case 10:
                $xlsName = "绑定平台币消费";
                if (isset($_REQUEST['user_account'])) {
                    $map['user_account'] = array('like', '%' . trim($_REQUEST['user_account']) . '%');
                    unset($_REQUEST['user_account']);
                }
                if (isset($_REQUEST['pay_order_number'])) {
                    $map['pay_order_number'] = array('like', '%' . trim($_REQUEST['pay_order_number']) . '%');
                    unset($_REQUEST['pay_order_number']);
                }
                if (isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])) {
                    $map['pay_time'] = array('BETWEEN', array(strtotime($_REQUEST['time-start']), strtotime($_REQUEST['time-end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['time-start']);
                    unset($_REQUEST['time-end']);
                }
                if (isset($_REQUEST['start']) && isset($_REQUEST['end'])) {
                    $map['pay_time'] = array('BETWEEN', array(strtotime($_REQUEST['start']), strtotime($_REQUEST['end']) + 24 * 60 * 60 - 1));
                    unset($_REQUEST['start']);
                    unset($_REQUEST['end']);
                }
                if (isset($_REQUEST['game_name'])) {
                    if ($_REQUEST['game_name'] == '全部') {
                        unset($_REQUEST['game_name']);
                    } else {
                        $map['game_name'] = $_REQUEST['game_name'];
                    }
                    unset($_REQUEST['game_name']);
                }
                if (isset($_REQUEST['server_name'])) {
                    if ($_REQUEST['server_name'] == '全部') {
                        unset($_REQUEST['server_name']);
                    } else {
                        $map['server_name'] = $_REQUEST['server_name'];
                        unset($_REQUEST['server_name']);
                    }
                }
                $map1 = $map;
                $map1['pay_status'] = 1;
                $total = D('BindSpend')->where($map1)->sum('pay_amount');
                $total = sprintf("%.2f", $total);
                $xlsCell = array(
                    array('pay_order_number', '订单号'),
                    array('pay_time', '充值时间', 'time_format', '*'),
                    array('user_account', '玩家账号'),
                    array('game_name', '游戏名称'),
                    array('server_name', '游戏区服'),
                    array('game_player_name', '角色名'),
                    array('spend_ip', '充值IP'),
                    array('pay_amount', '充值金额'),
                    array('pay_status', '订单状态', 'get_info_status', '*', '9'),
                    array('pay_game_status', '游戏通知状态', 'get_info_status', '*', '14'),
                    array('', "共计使用{$total}")
                );
                $xlsData = D('BindSpend')
                    ->where($map)
                    ->order('pay_time DESC,id DESC')
                    ->select();
                break;
            case 11:
                $xlsName = "礼包领取";
                $xlsCell = array(
                    array('user_account', '玩家账号'),
                    array('game_name', '游戏名称'),
                    array('gift_name', '礼包名称'),
                    array('novice', '礼包卡号'),
                    array('gift_id', '运营平台', 'get_operation_platform', '*', 'giftbag'),
                    array('create_time', '领取时间', 'time_format', '*'),
                );
                if(isset($_REQUEST['game_name'])){
                    $extend['game_name']=trim($_REQUEST['game_name']);
                    unset($_REQUEST['game_name']);
                }
                if (isset($_REQUEST['user_account'])) {
                    $extend['user_account']=trim($_REQUEST['user_account']);
                    unset($_REQUEST['user_account']);
                }
                if(isset($_REQUEST['sdk_version'])){
                    if($_REQUEST['sdk_version'] ==''){
                        unset($_REQUEST['sdk_version']);
                    }else{
                        $map['sdk_version'] = $_REQUEST['sdk_version'];
                        $game_ids = M('game','tab_')->field('id')->where($map)->select();
                        $game_ids = array_column($game_ids,'id');
                        $extend['sdk_version'] = ['in',$game_ids];
                        unset($_REQUEST['sdk_version']);
                    }
                }
                $xlsData = M('gift_record', 'tab_')
                    ->where($extend)
                    ->order("id DESC")
                    ->select();
                break;
            case 12:
                  $xlsName  = "渠道提现";
                  $xlsCell  = array(
                      array('settlement_number','提现单号'),
                      array('sum_money','提现金额'),
                      array('promote_id','渠道账号'),
                      array('create_time','申请时间','time_format','*'),
                      array('status','提现状态','get_info_status','*',19),
                      array('end_time','审核时间','time_format','*'),
                  );
                if(isset($_REQUEST['settlement_number'])){
                    $map['settlement_number']=$_REQUEST['settlement_number'];
                }
                if(isset($_REQUEST['status'])){
                    $map['status']=$_REQUEST['status'];
                }
                if(isset($_REQUEST['promote_account'])){
                    if($_REQUEST['promote_account']=='全部'){
                        unset($_REQUEST['promote_account']);
                    }else{
                        $map['promote_account'] = $_REQUEST['promote_account'];
                    }
                }

                if($_REQUEST['create_time']==2){
                    $order='create_time desc';
                }elseif($_REQUEST['create_time']==1){
                    $order='create_time asc';
                }
                if($_REQUEST['sum_money']==2){
                    $order='sum_money desc';
                }elseif($_REQUEST['sum_money']==1){
                    $order='sum_money asc';
                }
                $model = array(
                    'm_name' => 'withdraw',
                    'order'  => $order,
                    'title'  => '渠道提现',
                    'template_list' =>'withdraw',
                );
                $name = $model['m_name'];
                $xlsData = D($name)
                    ->where($map)
                    ->order($model['order'])
                    ->select();
              break;
             /* case 13:
                  $xlsName  = "代充额度";
                  $xlsCell  = array(
                      array('id','编号'),
                      array('account','渠道账号'),
                      array('pay_limit','代充上限'),
                      array('set_pay_time','更新时间'),
                  );
              if(isset($_REQUEST['promote_name'])){
                  if($_REQUEST['promote_name']=='全部'){
                      unset($_REQUEST['promote_name']);
                  }else if($_REQUEST['promote_name']=='自然注册'){
                      $map['id']=array("elt",0);
                      unset($_REQUEST['promote_name']);
                  }else{
                      $map['id']=get_promote_id($_REQUEST['promote_name']);
                      unset($_REQUEST['promote_name']);
                  }
              }
              $map['pay_limit']=array('gt','0');
                  $xlsData=M('Promote','tab_')
                  ->field("id,account,pay_limit,FROM_UNIXTIME(set_pay_time,'%Y-%m-%d %H:%i:%s') as set_pay_time")
                  ->where($map)
                  ->order("set_pay_time")
                  ->select();
              break;*/
            case 14:
                $xlsName = "游戏返利";
                $xlsCell = array(
                    array('pay_order_number', '订单号'),
                    array('create_time', '返利时间', 'time_foramt', '*'),
                    array('user_id', '玩家账号', 'get_user_account', '*'),
                    array('game_name', '游戏名称'),
                    array('pay_amount', '充值金额'),
                    array('ratio', '返利比例', 'ratio_stytl', '*'),
                    array('ratio_amount', '返利绑币'),
                );
                if (isset($_REQUEST['user_account'])) {
                    $map['user_name'] = array('like', '%' . trim($_REQUEST['user_account']) . '%');
                    unset($_REQUEST['user_account']);
                }
                if (isset($_REQUEST['game_name'])) {
                    if ($_REQUEST['game_name'] == '全部') {
                        unset($_REQUEST['game_name']);
                    } else {
                        $map['game_name'] = $_REQUEST['game_name'];
                        unset($_REQUEST['game_name']);
                    }
                }

                $xlsData = M('RebateList', 'tab_')
                    ->where($map)
                    ->order('id DESC')
                    ->select();
                break;
            case 15:
                $xlsName = "渠道结算";
                $xlsCell = array(
                    array('settlement_number', '订单号'),
                    array('starttime','开始时间','time_format','*'),
                    array('endtime','结束时间','time_format','*'),
                    array('promote_account', '渠道账号'),
                    array('game_name', '游戏名称'),
                    array('total_money', '总充值'),
                    array('total_number', '总注册'),
                    array('pattern','合作模式 0 cps 1cpa'),
                    array('ratio','分成比例%'),
                    array('money','注册单价'),
                    array('sum_money', '结算金额'),
                    array('create_time', '结算时间','time_format','*'),
                    // array('create_time', '结算时间', 'time_foramt', '*'),
                );
                if (isset($_REQUEST['promote_account'])) {
                    $map['promote_account'] = array('like', '%' . trim($_REQUEST['promote_account']) . '%');
                    unset($_REQUEST['promote_account']);
                }
                if (isset($_REQUEST['game_name'])) {
                    if ($_REQUEST['game_name'] == '全部') {
                        unset($_REQUEST['game_name']);
                    } else {
                        $map['game_name'] = $_REQUEST['game_name'];
                        unset($_REQUEST['game_name']);
                    }
                }

                $xlsData = M('settlement', 'tab_')
                    ->where($map)
                    ->order('id DESC')
                    ->select();
                    break;
                var_dump($xlsData);
            default:$xlsName = $xlsCell = $xlsData = [];

        }
        //数据处理
        foreach ($xlsData as $key => $val) {
            foreach ($xlsCell as $k => $v) {
                if (isset($v[2])) {
                    $ar_k = array_search('*', $v);
                    if ($ar_k !== false) {
                        $v[$ar_k] = $val[$v[0]];
                    }
                    $fun = $v[2];
                    $param = $v;
                    unset($param[0], $param[1], $param[2]);
                    $xlsData[$key][$v[0]] = call_user_func_array($fun, $param);
                }
            }
        }
        $this->exportExcel($xlsName, $xlsCell, $xlsData);

    }

}