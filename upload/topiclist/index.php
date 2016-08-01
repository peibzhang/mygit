<?php
require_once(dirname(__FILE__)."/../include/common.php");
require_once(sucms_INC."/main.class.php");

$paras=str_replace(getfileSuffix(),'',$_SERVER['QUERY_STRING']);
if(strpos($paras,"-")>0){
	$parasArray=explode("-",$paras);
	$id=$parasArray[0];
	$page=$parasArray[1];
}else{
	$id=$paras;
	$page=1;
}
$id = isset($id) && is_numeric($id) ? $id : 0;
$page = isset($page) && is_numeric($page) ? $page : 1;
if($id==0){
	showmsg('参数丢失，请返回！', -1);
	exit;
}
echoTopic();

function echoTopic()
{
	global $dsql,$cfg_iscache,$mainClassObj,$page,$t1,$id;
	$sql="select id,name,template,enname from sucms_topic where id =".$id;
	$row = $dsql->GetOne($sql);
	if(!is_array($row)) exit("不存在此专题");
	$cacheName="parse_topic_".$id;
	$topicTemplatePath="/templets/".$GLOBALS['cfg_df_style']."/".$GLOBALS['cfg_df_html']."/".$row['template'];
	$currentTopicId=$row['id'];
						//echo $topicId; 专题id
						$sql="select * from sucms_topic where id='$id'";
						$rows=array();
						$dsql->SetQuery($sql);
						$dsql->Execute('al');
						while($rowr=$dsql->GetObject('al'))
						{
						$rows[]=$rowr;
						}
						unset($rowr);
						$aa=explode("ttttt",$rows[0]->vod);
						$topicDes=$rows[0]->des;
						$topicKeyword=$rows[0]->keyword;
						$topicName=$rows[0]->name;
						$topicPic=$rows[0]->pic;
						$topicPic=$GLOBALS['cfg_cmspath']."uploads/zt/".$topicPic;
	$page_size = getPageSizeOnCache($topicTemplatePath,"topicpage",$row['template']);
	if (empty($page_size)) $page_size=12;
	if(is_array($aa))
	{
		$TotalResult = count($aa)-1;
	}
	else
	{
		$TotalResult = 0;
	}
	$pCount = ceil($TotalResult/$page_size);
	if($cfg_iscache){
		if(chkFileCache($cacheName)){
			$content = getFileCache($cacheName);
		}else{
			$content = parseTopicPart($topicName,$topicTemplatePath,$id,$topicDes,$topicKeyword,$topicPic);
			$content = str_replace("{sucms:currrent_topic_id}",$currentTopicId,$content);
			setFileCache($cacheName,$content);
		}
	}else{
			$content = parseTopicPart($topicName,$topicTemplatePath,$id,$topicDes,$topicKeyword,$topicPic);
			$content = str_replace("{sucms:currrent_topic_id}",$currentTopicId,$content);
	}
	$content=$mainClassObj->ParsePageList($content,$id,$page,$pCount,$TotalResult,"topicpage",$currentTopicId);
	$content=$mainClassObj->parseIf($content);
	$content=str_replace("{sucms:member}",front_member(),$content);
	echo str_replace("{sucms:runinfo}",getRunTime($t1),$content);

}

function parseTopicPart($ptopicName,$templatePath,$id,$ptopicDes,$ptopicKeyword,$ptopicPic)
{
	global $mainClassObj;
	$content=loadFile(sucms_ROOT.$templatePath);
	$content=$mainClassObj->parseTopAndFoot($content);
	$content = str_replace("{sucms:currenttypeid}",-444,$content);
	$content=$mainClassObj->parseHistory($content);
	$content=$mainClassObj->parseSelf($content);
	$content=$mainClassObj->parseGlobal($content);
	$content=$mainClassObj->parseMenuList($content,"",$currentTypeId);
	$content=$mainClassObj->parsucmsreaList($content);
	$content=$mainClassObj->parseVideoList($content,$currentTypeId,$id);
	$content=$mainClassObj->parseTopicList($content);
	$content=$mainClassObj->parseLinkList($content);
	$content = str_replace("{sucms:topicname}",$ptopicName,$content);
	$content = str_replace("{sucms:topicdes}",$ptopicDes,$content);
	$content = str_replace("{sucms:topickeyword}",$ptopicKeyword,$content);
	$content = str_replace("{sucms:topicpic}",$ptopicPic,$content);
	return $content;
}
?>