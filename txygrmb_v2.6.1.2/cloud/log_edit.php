<?php
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Log');
if(empty($dopost))
{
    ShowMsg("你没指定任何参数！","javascript:;");
    exit();
}

if($dopost=="clear")
{
    $dsql->ExecuteNoneQuery("DELETE FROM #@__log");
    ShowMsg("成功清空所有日志！","log_list.php");
    exit();
}
else if($dopost=="del")
{
    $bkurl = isset($_COOKIE['ENV_GOBACK_URL']) ? $_COOKIE['ENV_GOBACK_URL'] : "log_list.php";
    $ids = explode('`',$ids);
    $dquery = "";
    foreach($ids as $id)
    {
        if($dquery=="")
        {
            $dquery .= " lid='$id' ";
        }
        else
        {
            $dquery .= " Or lid='$id' ";
        }
    }
    if($dquery!="") $dquery = " where ".$dquery;
    $dsql->ExecuteNoneQuery("DELETE FROM #@__log $dquery");
    ShowMsg("成功删除指定的日志！",$bkurl);
    exit();
}
else
{
    ShowMsg("无法识别你的请求！","javascript:;");
    exit();
}