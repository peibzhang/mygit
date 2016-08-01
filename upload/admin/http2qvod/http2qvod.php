<?php
require_once("../config.php");
function removeSiteName($qvodurl)
{
	preg_replace("/\[[^\]]*?\]/i", "", $qvodurl);
	return $qvodurl;
}
function http2qvod($httpurl)
{
	$httpurltemp="http://www.qvodsou.cc/http2qvod/http2qvod.do?url=".$httpurl;
	$content=@file_get_contents($httpurltemp);
	preg_match_all("/转换:<\/td><td>(.*?)<\/td>/i", $content, $matches);
	$qvodurl=$matches[1][0];
	return $qvodurl;
}
if($action=="http2qvod")
{
	$pagesize = 30;
	$sql="select count(*) as dd from sucms_playdata where body like '%qvod%' and body like '%http://%'";
	$row = $dsql->GetOne($sql);
	$num = $row['dd'];
	if ($num%$pagesize) {
		$zongye=ceil($num/$pagesize);
	}elseif($num%$pagesize==0){
		$zongye=$num/$pagesize;
	}
	if($pageval<=1)$pageval=1;
	if($_GET['page']){
		$pageval=$_GET['page'];
		$page=($pageval-1)*$pagesize; 
		$page.=',';
	}
	if($pageval>$zongye)
	{
		echo "<script>alert('转换成功！');location.href='http2qvod.php'</script>";
	}
	$sql = "select v_id,body from `sucms_playdata` where body like '%qvod%' and body like '%http://%' order by v_id ASC limit $page $pagesize";
	$dsql->SetQuery($sql);
	$dsql->Execute('http2qvod');
	echo "<div style='font-size:13px'>正在更新。。。<br>";
	while($row = $dsql->GetArray('http2qvod'))
	{
		$tempdata = $row['body'];
		$isChange = False; 
		preg_match_all('|\$(http:\/\/[^\$]+?)\$qvod|i', $tempdata, $matches);
 		foreach ($matches[0] as $k=>$match)
		{
			$httpurl=$matches[1][$k];
			echo "http: &nbsp;&nbsp;&nbsp;&nbsp;".$httpurl."<br/>";
//			$httpurla = explode(httpurl,"/");
			$qvodurl=http2qvod($httpurl);
			$qvodurl=removeSiteName($qvodurl);
			$qvodurla=explode("|",$qvodurl);
			if(count($qvodurla)>1){
				if($sitename!='') $qvodurl=str_replace($qvodurla[2],"[".$sitename."]".$qvodurla[2],$qvodurl);
			}
			if(substr($qvodurl,0,4)=='qvod'){
				$isChange=true;
				$tempdata=str_replace($httpurl, $qvodurl, $tempdata);
				echo "<font color='green'>转换成功</font><br/>";
			}else{
				echo "<font color='red'>转换失败</font><br/>";
			}
		}
		if($isChange){
			$dsql->ExecNoneQuery("update sucms_playdata set body='".$tempdata."' where v_id=".$row['v_id']);
			echo "<font color='green'>更新数据成功</font><br/>";
		}else{
			if($delmode==1){
				$dsql->ExecNoneQuery("delete from sucms_playdata where v_id=".$row['v_id']);
				Echo "<font color='red'>不能转换数据，数据已删除</font><br/>";
			}else{
				Echo "<font color='red'>不能转换数据，未更新数据</font><br/>";
			}
		}
		$upSql = "update `sucms_content` set body='".$row['v_content']."' where v_id =".$row[v_id];
		$dsql->ExecNoneQuery($upSql);
		echo '成功更新&nbsp;ID:'.$row[v_id];
		echo '&nbsp;<font color=red>'.$row[v_name].'</font><br>';
	}
	echo "请等待3秒更新下一页<div>";
	echo "<script>function urlto(){location.href='?action=http2qvod&page=".($pageval+1)."&sitename=".$sitename."&delmode=".$delmode."';}setInterval('urlto()',3000);</script>";
	exit();	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link media="all" type="text/css" href="../img/admin.css" rel="stylesheet">
</head>
<body bgcolor="#F7FBFF">
<div class="container" id="cpcontainer">

<input class="btn" name="go1" type="button" id="go1" value="http2qvod地址转换"
onclick="window.location='?action=http2qvod&sitename='+document.getElementById('sitename').value+'&delmode='+(document.getElementById('delmode').checked?1:0);" />
<br/>
广告域名：<input class="input" name="sitename" type="text" id="sitename" value="" />
<br/>
(如：qvod://305043178|BD5D8CEF9A8C39D4FFC656F75B1F4C57AF6DFDB7|[xxx.com]xxxx.rm| )
<br/>
<input  class="checkbox" name="delmode" type="checkbox" id="delmode" value="1" />&nbsp;是否删除不能转换数据
<br/>
<div align=center>Copyright 2011-2012 All rights reserved. <a href="http://www.sucms.org/" target="_blank">sucms</a>
</div>
</div>
</body>
</html>