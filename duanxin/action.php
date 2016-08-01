<?php
//接收输入的手机验证码
$checkcode = $_POST['checkcode'];
session_start();
$code = $_SESSION['code'];
if($code==$checkcode){
        echo 'ok';
}else{
        echo 'no';
}
?>