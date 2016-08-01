<?php
//要生成手机验证码，并且存储到session里面
session_start();
//随机验证码
$code = rand(1000,9999);
$_SESSION['code']=$code;
include_once("./CCPRestSmsSDK.php");
//主帐号,对应开官网发者主账号下的 ACCOUNT SID
$accountSid= '8aaf07085635aae501564039e0180437';
//主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
$accountToken= '2f82b65a1605448ebd3df6918025f4d5';
//应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
//在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
$appId='8aaf07085635aae501564039e078043d';
//请求地址
//沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
//生产环境（用户应用上线使用）：app.cloopen.com
$serverIP='sandboxapp.cloopen.com';
//请求端口，生产环境和沙盒环境一致
$serverPort='8883';
//REST版本号，在官网文档REST介绍中获得。
$softVersion='2013-12-26';


/**
  * 发送模板短信
  * @param to 手机号码集合,用英文逗号分开
  * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
  * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
  */
function sendTemplateSMS($to,$datas,$tempId)
{
     // 初始化REST SDK
     global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
     $rest = new REST($serverIP,$serverPort,$softVersion);
     $rest->setAccount($accountSid,$accountToken);
     $rest->setAppId($appId);

     // 发送模板短信
    // echo "Sending TemplateSMS to $to <br/>";
     $result = $rest->sendTemplateSMS($to,$datas,$tempId);
     if($result == NULL ) {
        return false;
     }
     if($result->statusCode!=0) {
         return false;
         //TODO 添加错误处理逻辑
     }else{
         return true;
         //TODO 添加成功处理逻辑
     }
}

//Demo调用
		//**************************************举例说明***********************************************************************
		//*假设您用测试Demo的APP ID，则需使用默认模板ID 1，发送手机号是13800000000，传入参数为6532和5，则调用方式为           *
		//*result = sendTemplateSMS("13800000000" ,array('6532','5'),"1");																		  *
		//*则13800000000手机号收到的短信内容是：【云通讯】您使用的是云通讯短信模板，您的验证码是6532，请于5分钟内正确输入     *
		//*********************************************************************************************************************
//获取传递手机号码
$telphone = $_GET['telphone'];
$res = sendTemplateSMS($telphone,array($code,1),"1");//手机号码，替换内容数组，模板ID
// var_dump($res);
if($res){
    echo 1;
}else{
    echo 0;
}


?>
