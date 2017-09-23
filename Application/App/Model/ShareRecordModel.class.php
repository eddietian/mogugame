<?php
/**
 * Created by PhpStorm.
 * User: xmy 280564871@qq.com
 * Date: 2017/4/7
 * Time: 13:59
 */

namespace App\Model;

class ShareRecordModel extends BaseModel{


	/**
	 * 添加邀请好友注册记录
	 * @param $invite_account   邀请人账号
	 * @param $user_account     被邀请人账号
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function addShareRecord($invite_account,$user_account){
		$data['invite_id'] = get_user_id($invite_account);
		$data['invite_account'] = $invite_account;
		$data['user_id'] = get_user_id($user_account);
		$data['user_account'] = $user_account;
		$data['create_time'] = time();
		$data['award_coin'] = 0;
		return $this->add($data);
	}


	/**
	 * 获取我的邀请记录
	 * @param $invite_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getMyInviteRecord($invite_id){
		$map['invite_id'] = $invite_id;
		$data = $this->field("user_account,create_time,sum(award_coin) as award_coin")
			->where($map)
			->group("user_id")
			->order("create_time desc")
			->select();
		return $data;
	}


	/**
	 * 获取用户邀请统计
	 * @param $invite_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getUserInviteInfo($invite_id){
		$map['invite_id'] = $invite_id;
		$data = $this->field("count(distinct user_id) as invite_num,sum(award_coin) as award_coin")
			->where($map)
			->group("invite_id")
			->find();
		return $data;
	}


	/**
	 * 获取奖励平台币 总额
	 * @param $invite_id
	 * @param $user_id
	 * @return mixed
	 * author: xmy 280564871@qq.com
	 */
	public function getUserInviteCoin($invite_id,$user_id){
		$map['invite_id'] = $invite_id;
		$map['user_id'] = $user_id;
		$data = $this->field("sum(award_coin) as award_coin")
			->where($map)
			->group("user_id")
			->find();
		return $data['award_coin'];
	}


	/**
	 * 邀请好友消费奖励平台币
	 * @param $user_id
	 * @param $pay_amount
	 * @param $order_number
	 * @return bool
	 * author: xmy 280564871@qq.com
	 */
	public function inviteFriendAward($user_id,$pay_amount,$order_number){
		$map['user_id'] = $user_id;
		$share_record = $this->where($map)->find();
		if(empty($share_record)){
			return true;
		}
		$invite_id = $share_record['invite_id'];

		//计算奖励
		$award_coin = round($pay_amount * 0.05,2);

		//增加邀请用户 平台币
		$invite = M("user","tab_")->find($invite_id);//邀请人

		//获取该邀请人共获得多少平台币
		$total = $this->getUserInviteCoin($invite_id,$user_id);

		//是否到达上限
		if($total >= 100){
			return true;
		}

		if($total+$award_coin > 100){
			$award_coin = 100 - $total;
		}

		$invite['balance'] += $award_coin;

		//奖励平台币记录
		$share_record['create_time'] = time();
		$share_record['award_coin'] = $award_coin;
		$share_record['order_number'] = $order_number;
		unset($share_record['id']);

		//开启事务
		$this->startTrans();
		$record_result = $this->add($share_record);//平台币奖励记录
		$invite_result = M("user","tab_")->save($invite);//邀请人平台币存储
		if($record_result === false || $invite_result === false){
			$this->rollback();
			return false;
		}else{
			$this->commit();
			return true;
		}
	}
}