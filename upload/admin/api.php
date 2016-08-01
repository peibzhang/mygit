<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>资源站一键采集接口</title>
<script src="js/common.js" type="text/javascript"></script>
<script src="js/main.js" type="text/javascript"></script>
<script src="js/drag.js" type="text/javascript"></script>
<link type="text/css" href="img/alerts.css" rel="stylesheet" media="screen">

<link  href="img/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
tr a {
	font-size: 12px;
	color: #666;
}
tr a:link {
	text-decoration: none;
}
tr a:visited {
	text-decoration: none;
	color: #666;
}
tr a:hover {
	text-decoration: underline;
	color: #FF0000;
}
tr a:active {
	text-decoration: none;
	color: #f60;
}
tr{}
td{ background-color: #fff; height:30px; line-height:30px;font-size: 12px; color: #333;border:1px solid #E7E8EA;text-align:center;}
.bi{width:78%;text-align:left;padding-left:10px;}
.b2{text-align:left;padding-left:10px; color: #333;}
.der{border:1px solid #E7E8EA;}
</style>
</head>
<body oncontextmenu="return false" onselectstart="return false">
<!--当前导航-->
<script type="text/JavaScript">if(parent.$('admincpnav')) parent.$('admincpnav').innerHTML='后台首页&nbsp;&raquo;&nbsp;采集&nbsp;&raquo;&nbsp;第三方资源库 ';</script>
<?php
require_once(dirname(__FILE__)."/config.php");
require_once(sucms_DATA."/mark/inc_photowatermark_config.php");
CheckPurview();
if(RWCache('collect_xml'))
echo "<script>openCollectWin(400,'auto','上次采集未完成，继续采集？','".RWCache('collect_xml')."')</script>";
?>
<script language="JavaScript" type="text/javascript" charset="utf-8" src="js/sucmsapi.js"></script>
<?php
exit();	
?>
</body>
</html>
