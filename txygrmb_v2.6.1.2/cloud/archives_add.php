<?php

require_once(dirname(__FILE__).'/config.php');
CheckPurview('a_New,a_AccNew');
require_once(DEDEINC.'/customfields.func.php');
require_once(DEDEADMIN.'/inc/inc_archives_functions.php');

if(empty($dopost)) $dopost = '';

if($dopost != 'save')
{
    require_once(DEDEINC.'/dedetag.class.php');
    require_once(DEDEADMIN.'/inc/inc_catalog_options.php');
    ClearMyAddon();
    $channelid = empty($channelid) ? 0 : intval($channelid);
    $cid = empty($cid) ? 0 : intval($cid);

    if($cid > 0 && $channelid == 0)
    {
        $row = $dsql->GetOne("SELECT channeltype FROM `#@__arctype` WHERE id='$cid'; ");
        $channelid = $row['channeltype'];
    }
    else
    {
        if($channelid==0)
        {
            ShowMsg('�޷�ʶ��ģ����Ϣ������޷�������', '-1');
            exit();
        }
    }

    $cInfos = $dsql->GetOne(" SELECT * FROM  `#@__channeltype` WHERE id='$channelid' ");
    $channelid = $cInfos['id'];

    $maxWright = $dsql->GetOne("SELECT COUNT(*) AS cc FROM #@__archives");
    include DedeInclude('templets/archives_add.htm');
    exit();
}

else if($dopost=='save')
{
    require_once(DEDEINC.'/image.func.php');
    require_once(DEDEINC.'/oxwindow.class.php');

    $flag = isset($flags) ? join(',',$flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1: 0;
    if(empty($click)) $click = ($cfg_arc_click=='-1' ? mt_rand(50, 200) : $cfg_arc_click);

    if(empty($typeid2)) $typeid2 = 0;
    if(!isset($autokey)) $autokey = 0;
    if(!isset($remote)) $remote = 0;
    if(!isset($dellink)) $dellink = 0;
    if(!isset($autolitpic)) $autolitpic = 0;
    if(empty($click)) $click = ($cfg_arc_click=='-1' ? mt_rand(50, 200) : $cfg_arc_click);

    if($typeid==0)
    {
        ShowMsg('��ָ���ĵ�����Ŀ��', '-1');
        exit();
    }
    if(empty($channelid))
    {
        ShowMsg('�ĵ�Ϊ��ָ�������ͣ������㷢�����ݵı����Ƿ�Ϸ���', '-1');
        exit();
    }
    if(!CheckChannel($typeid,$channelid) )
    {
        ShowMsg('����ѡ�����Ŀ�뵱ǰģ�Ͳ��������ѡ���ɫ��ѡ�', '-1');
        exit();
    }
    if(!TestPurview('a_New'))
    {
        CheckCatalog($typeid, "�Բ�����û�в�����Ŀ {$typeid} ��Ȩ�ޣ�");
    }

    if(empty($writer)) $writer = $cuserLogin->getUserName();
    if(empty($source)) $source = '����˭����';
    $pubdate = GetMkTime($pubdate);
    $senddate = time();
    $sortrank = AddDay($pubdate,$sortup);
    $ismake = $ishtml == 0 ? -1 : 0;
    $title = preg_replace("#\"#", '��', $title);
    $title = cn_substrR($title,$cfg_title_maxlen);
    $shorttitle = cn_substrR($shorttitle,36);
    $color =  cn_substrR($color,7);
    $writer =  cn_substrR($writer,20);
    $source = cn_substrR($source,30);
    $description = cn_substrR($description,$cfg_auot_description);
    $keywords = cn_substrR($keywords,60);
    $filename = trim(cn_substrR($filename,40));
    $userip = GetIP();
    $isremote  = (empty($isremote)? 0  : $isremote);
    $voteid = (empty($voteid)? 0 : $voteid);
    $serviterm=empty($serviterm)? "" : $serviterm;
    if(!TestPurview('a_Check,a_AccCheck,a_MyCheck'))
    {
        $arcrank = -1;
    }
    $adminid = $cuserLogin->getUserID();

    if(empty($ddisremote))
    {
        $ddisremote = 0;
    }
    $litpic = GetDDImage('none',$picname,$ddisremote);

    $arcID = GetIndexKey($arcrank,$typeid,$sortrank,$channelid,$senddate,$adminid);

    if(empty($arcID))
    {
        ShowMsg("�޷��������������޷����к���������","-1");
        exit();
    }

    $inadd_f = $inadd_v = '';
    if(!empty($dede_addonfields))
    {
        $addonfields = explode(';', $dede_addonfields);
        if(is_array($addonfields))
        {
            foreach($addonfields as $v)
            {
                if($v=='') continue;
                $vs = explode(',', $v);
                if($vs[1]=='htmltext' || $vs[1]=='textdata')
                {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $litpic, $keywords, $vs[1]);
                }
                else
                {
                    if(!isset(${$vs[0]})) ${$vs[0]} = '';
                    ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $arcID);
                }
                $inadd_f .= ','.$vs[0];
                $inadd_v .= " ,'".${$vs[0]}."' ";
            }
        }
    }

    if($litpic!='' && !preg_match("#p#", $flag))
    {
        $flag = ($flag=='' ? 'p' : $flag.',p');
    }
    if($redirecturl!='' && !preg_match("#j#", $flag))
    {
        $flag = ($flag=='' ? 'j' : $flag.',j');
    }

    if(preg_match("#j#", $flag)) $ismake = -1;

    $query = "INSERT INTO `#@__archives`(id,typeid,typeid2,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,
color,writer,source,litpic,pubdate,senddate,mid,voteid,notpost,description,keywords,filename,dutyadmin,weight)
    VALUES ('$arcID','$typeid','$typeid2','$sortrank','$flag','$ismake','$channelid','$arcrank','$click','$money','$title','$shorttitle',
    '$color','$writer','$source','$litpic','$pubdate','$senddate','$adminid','$voteid','$notpost','$description','$keywords','$filename','$adminid','$weight');";

    if(!$dsql->ExecuteNoneQuery($query))
    {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("�����ݱ��浽���ݿ����� `#@__archives` ʱ��������������Ϣ�ύ������˭���� - www.haiyoushei.net��".str_replace('"','',$gerr),"javascript:;");
        exit();
    }

    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if(!empty($addtable))
    {
        $useip = GetIP();
        $query = "INSERT INTO `{$addtable}`(aid,typeid,redirecturl,userip{$inadd_f}) Values('$arcID','$typeid','$redirecturl','$useip'{$inadd_v})";
        if(!$dsql->ExecuteNoneQuery($query))
        {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
            ShowMsg("�����ݱ��浽���ݿ⸽�ӱ� `{$addtable}` ʱ��������������Ϣ�ύ������˭���� - www.haiyoushei.net��".str_replace('"','',$gerr),"javascript:;");
            exit();
        }
    }

    InsertTags($tags, $arcID);
    if($cfg_remote_site=='Y' && $isremote=="1")
    {    
        if($serviterm!="")
        {
            list($servurl,$servuser,$servpwd) = explode(',',$serviterm);
            $config=array( 'hostname' => $servurl, 'username' => $servuser, 
                                                 'password' => $servpwd,'debug' => 'TRUE');
        } else {
            $config=array();
        }
        if(!$ftp->connect($config)) exit('Error:None FTP Connection!');
    }
    $artUrl = MakeArt($arcID, true, true,$isremote);
    if($artUrl=='')
    {
        $artUrl = $cfg_phpurl."/view.php?aid=$arcID";
    }
    ClearMyAddon($arcID, $title);

    $msg = "    ������ѡ����ĺ���������
    <a href='archives_add.php?cid=$typeid'><u>���������ĵ�</u></a>
    &nbsp;&nbsp;
    <a href='$artUrl' target='_blank'><u>�鿴�ĵ�</u></a>
    &nbsp;&nbsp;
    <a href='archives_do.php?aid=".$arcID."&dopost=editArchives'><u>�����ĵ�</u></a>
    &nbsp;&nbsp;
    <a href='catalog_do.php?cid=$typeid&dopost=listArchives'><u>�ѷ����ĵ�����</u></a>
    &nbsp;&nbsp;
    $backurl
  ";
    $msg = "<div style=\"line-height:36px;height:36px\">{$msg}</div>".GetUpdateTest();

    $wintitle = '�ɹ������ĵ���';
    $wecome_info = '�ĵ�����::�����ĵ�';
    $win = new OxWindow();
    $win->AddTitle('�ɹ������ĵ���');
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow('hand', '&nbsp;', false);
    $win->Display();
}