<?php

namespace Admin\Controller;
use Think\Model;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PromoteCoinController extends ThinkController {

	const model_name = 'PromoteCoin';

    public function send_lists($p=0){
        $extend['type'] = 1;
        $extend['source_id'] = 0;
        $map = $extend;
        $map['create_time'] = total(1,false);
        $sum['to_day'] = D(self::model_name)->where($map)->sum('num');
        $map['create_time'] = total(5,false);
        $sum['yst_day'] = D(self::model_name)->where($map)->sum('num');
        $sum['all_num'] = D(self::model_name)->where($extend)->sum('num');
        $this->assign('sum',$sum);
        parent::order_lists(self::model_name,$_GET["p"],$extend);
    }

    public function deduct_lists($p=0){
        $extend['type'] = 2;
        $extend['source_id'] = 0;
        $map = $extend;
        $map['create_time'] = total(1,false);
        $sum['to_day'] = D(self::model_name)->where($map)->sum('num');
        $map['create_time'] = total(5,false);
        $sum['yst_day'] = D(self::model_name)->where($map)->sum('num');
        $sum['all_num'] = D(self::model_name)->where($extend)->sum('num');
        $this->assign('sum',$sum);
        parent::order_lists(self::model_name,$_GET["p"],$extend);
    }

    /**
     * 发放平台币
     */
    public function send(){
        $this->add(1,'send_lists');
    }

    public function deduct(){
        $this->add(2,'deduct_lists');
    }

    public function add($type,$url)
    {
        $model = M('Model')->getByName(self::model_name);
        $model || $this->error('模型不存在！');
        if(IS_POST){
            //验证二级密码
            $pwd = I('second_pwd');
            $res = D('Member')->check_sc_pwd($pwd);
            if(!$res){
                $this->error('二级密码错误');
            }
            $res = D('PromoteCoin')->create();
            if (!$res){
                $this->error(D('PromoteCoin')->getError());
            }
            //平台币修改
            $promote = D('promote');
            $res = $promote->edit_promote_balance_coin(I('promote_id'),I('num'),$type);
            if($res){
                $this->success('操作成功！', U($url.'?model='.$model['name']));
            } else {
                $this->error($promote->getError());
            }
        } else {

            $fields = get_model_attribute($model['id']);

            $this->assign('model', $model);
            $this->assign('fields', $fields);
            $this->meta_title = '平台币操作';
            $this->display($model['template_add']?$model['template_add']:'');
        }
    }

    /**
     * 平台币转移记录
     */
    public function record($p=0){
        $extend['type'] = 2;
        $extend['source_id'] = ['neq',0];
        $map = $extend;
        $map['create_time'] = total(1,false);
        $sum['to_day'] = D(self::model_name)->where($map)->sum('num');
        $map['create_time'] = total(5,false);
        $sum['yst_day'] = D(self::model_name)->where($map)->sum('num');
        $sum['all_num'] = D(self::model_name)->where($extend)->sum('num');
        $this->assign('sum',$sum);
        parent::order_lists(self::model_name,$_GET["p"],$extend);
    }
}
