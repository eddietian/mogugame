<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/27
 * Time: 17:05
 */

namespace Admin\Controller;

use Think\Controller;

Class AlidayuController extends Controller{

    public function send($phone,$code,$delay=10){
	    $config = api('Config/lists');
	    C($config); //添加配置

        $temp_code = C("alidayu.template_id");    //模板ID
        $param = "{'code':'$code'}";   //参数
//        $param = "{'code':'$code','delay','$delay'}";   //参数
        $sing_name = C("alidayu.sign");              //审核过的签名

        vendor('Alidayu.top.TopClient');
        vendor('Alidayu.top.ResultSet');
        vendor('Alidayu.top.RequestCheckUtil');
        vendor('Alidayu.top.TopLogger');
        vendor('Alidayu.top.request.AlibabaAliqinFcSmsNumSendRequest');
        
        $dayu = new \TopClient();
        $dayu->appkey = C("alidayu.appkey");
        $dayu->secretKey = C("alidayu.secretKey");
        $req = new \AlibabaAliqinFcSmsNumSendRequest();
        $req->setExtend("");
        $req->setSmsType("normal");//设置短信类型
        $req->setSmsFreeSignName($sing_name);//短信签名
        //短信模板变量，传参规则{"key":"value"}，key的名字须和申请模板中的变量名一致，多个变量之间以逗号隔开。
        //示例：针对模板“验证码${code}，您正在进行${product}身份验证，打死不要告诉别人哦！”，传参时需传入{"code":"1234","product":"alidayu"}
        $req->setSmsParam($param);
        $req->setRecNum($phone);//发送电话号
        $req->setSmsTemplateCode($temp_code);//模板ID
        $resp = $dayu->execute($req);
        var_dump($resp,$resp->result,$resp->result->success);exit;
        return $resp;
    }
}