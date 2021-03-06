<?php  if(!defined('DEDEINC')) exit('dedecms');

if ( ! function_exists('GetOneArchive'))
{
    function GetOneArchive($aid)
    {
        global $dsql;
        include_once(DEDEINC."/channelunit.func.php");
        $aid = trim(preg_replace('/[^0-9]/', '', $aid));
        $reArr = array();

        $chRow = $dsql->GetOne("SELECT arc.*,ch.maintable,ch.addtable,ch.issystem FROM `#@__arctiny` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$aid' ");

        if(!is_array($chRow)) {
            return $reArr;
        }
        else {
            if(empty($chRow['maintable'])) $chRow['maintable'] = '#@__archives';
        }

        if($chRow['issystem']!=-1)
        {
            $nquery = " SELECT arc.*,tp.typedir,tp.topid,tp.namerule,tp.moresite,tp.siteurl,tp.sitepath
                        FROM `{$chRow['maintable']}` arc LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
                        WHERE arc.id='$aid' ";
        }
        else
        {
            $nquery = " SELECT arc.*,1 AS ismake,0 AS money,'' AS filename,tp.typedir,tp.topid,tp.namerule,tp.moresite,tp.siteurl,tp.sitepath
                        FROM `{$chRow['addtable']}` arc LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
                        WHERE arc.aid='$aid' ";
        }

        $arcRow = $dsql->GetOne($nquery);

        if(!is_array($arcRow)) {
            return $reArr;
        }

        if(!isset($arcRow['description'])) {
            $arcRow['description'] = '';
        }

        if(empty($arcRow['description']) && isset($arcRow['body'])) {
            $arcRow['description'] = cn_substr(html2text($arcRow['body']), 250);
        }

        if(!isset($arcRow['pubdate'])) {
            $arcRow['pubdate'] = $arcRow['senddate'];
        }

        if(!isset($arcRow['notpost'])) {
            $arcRow['notpost'] = 0;
        }

        $reArr = $arcRow;
        $reArr['aid']    = $aid;
        $reArr['topid']  = $arcRow['topid'];
        $reArr['arctitle'] = $arcRow['title'];
        $reArr['arcurl'] = GetFileUrl($aid, $arcRow['typeid'], $arcRow['senddate'], $reArr['title'],
                          $arcRow['ismake'], $arcRow['arcrank'], $arcRow['namerule'], $arcRow['typedir'], 
                          $arcRow['money'], $arcRow['filename'], $arcRow['moresite'], $arcRow['siteurl'], 
                          $arcRow['sitepath']);
        return $reArr;

    }
}

if ( ! function_exists('GetChannelTable'))
{
    function GetChannelTable($id,$formtype='channel')
    {
        global $dsql;
        if($formtype == 'archive')
        {
            $query = "SELECT ch.maintable, ch.addtable FROM #@__arctiny tin LEFT JOIN #@__channeltype ch ON ch.id=tin.channel WHERE tin.id='$id'";
        }
        else if($formtype == 'typeid')
        {
            $query = "SELECT ch.maintable, ch.addtable FROM #@__arctype act LEFT JOIN #@__channeltype ch ON ch.id=act.channeltype WHERE act.id='$id'";
        }
        else
        {
            $query = "SELECT maintable, addtable FROM #@__channeltype WHERE id='$id'";
        }
        $row = $dsql->GetOne($query);
        return $row;
    }
}

if ( ! function_exists('GetTags'))
{
    function GetTags($aid)
    {
        global $dsql;
        $tags = '';
        $query = "SELECT tag FROM `#@__taglist` WHERE aid='$aid' ";
        $dsql->Execute('tag',$query);
        while($row = $dsql->GetArray('tag'))
        {
            $tags .= ($tags=='' ? $row['tag'] : ','.$row['tag']);
        }
        return $tags;
    }
}

if ( ! function_exists('GetIndexKey'))
{
    function GetIndexKey($arcrank, $typeid, $sortrank=0, $channelid=1, $senddate=0, $mid=1)
    {
        global $dsql,$senddate,$typeid2;
        if(empty($typeid2)) $typeid2 = 0;
        if(empty($senddate)) $senddate = time();
        if(empty($sortrank)) $sortrank = $senddate;
        $iquery = "
          INSERT INTO `#@__arctiny` (`arcrank`,`typeid`,`typeid2`,`channel`,`senddate`, `sortrank`, `mid`)
          VALUES ('$arcrank','$typeid','$typeid2' , '$channelid','$senddate', '$sortrank', '$mid') ";
        $dsql->ExecuteNoneQuery($iquery);
        $aid = $dsql->GetLastID();
        return $aid;
    }
}

if ( ! function_exists('UpIndexKey'))
{
    function UpIndexKey($id, $arcrank, $typeid, $sortrank=0, $tags='')
    {
        global $dsql,$typeid2;
        if(empty($typeid2)) $typeid2 = 0;
        $addtime = time();
        $query = " UPDATE `#@__arctiny` SET `arcrank`='$arcrank', `typeid`='$typeid', `typeid2`='$typeid2', `sortrank`='$sortrank' WHERE id = '$id' ";
        $dsql->ExecuteNoneQuery($query);

        if($tags!='')
        {
            $oldtag = GetTags($id);
            $oldtags = explode(',',$oldtag);
            $tagss = explode(',',$tags);
            foreach($tagss as $tag)
            {
                $tag = trim($tag);
                if(isset($tag[12]) || $tag!=stripslashes($tag))
                {
                    continue;
                }
                if(!in_array($tag,$oldtags))
                {
                    InsertOneTag($tag,$id);
                }
            }
            foreach($oldtags as $tag)
            {
                if(!in_array($tag,$tagss))
                {
                    $dsql->ExecuteNoneQuery("DELETE FROM `#@__taglist` WHERE aid='$id' AND tag LIKE '$tag' ");
                    $dsql->ExecuteNoneQuery("UPDATE `#@__tagindex` SET total=total-1 WHERE tag LIKE '$tag' ");
                }
                else
                {
                    $dsql->ExecuteNoneQuery("UPDATE `#@__taglist` SET `arcrank` = '$arcrank', `typeid` = '$typeid' WHERE tag LIKE '$tag' ");
                }
            }
        }
    }
}

if ( ! function_exists('InsertTags'))
{
    function InsertTags($tag, $aid)
    {
        $tags = explode(',',$tag);
        foreach($tags as $tag)
        {
            $tag = trim($tag);
            if(isset($tag[20]) || $tag!=stripslashes($tag))
            {
                continue;
            }
            InsertOneTag($tag,$aid);
        }
    }
}

if ( ! function_exists('InsertOneTag'))
{
    function InsertOneTag($tag, $aid)
    {
        global $typeid,$arcrank,$dsql;
        $tag = trim($tag);
        if($tag == '')
        {
            return '';
        }
        if(empty($typeid))
        {
            $typeid = 0;
        }
        if(empty($arcrank))
        {
            $arcrank = 0;
        }
        $rs = false;
        $addtime = time();
        $row = $dsql->GetOne("SELECT * FROM `#@__tagindex` WHERE tag LIKE '$tag' ");
        if(!is_array($row))
        {
            $rs = $dsql->ExecuteNoneQuery(" INSERT INTO `#@__tagindex`(`tag`,`typeid`,`count`,`total`,`weekcc`,`monthcc`,`weekup`,`monthup`,`addtime`) VALUES('$tag','$typeid','0','1','0','0','$addtime','$addtime','$addtime'); ");
            $tid = $dsql->GetLastID();
        }
        else
        {
            $rs = $dsql->ExecuteNoneQuery(" UPDATE `#@__tagindex` SET total=total+1,addtime=$addtime WHERE tag LIKE '$tag' ");
            $tid = $row['id'];
        }
        if($rs)
        {
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__taglist`(`tid`,`aid`,`arcrank`,`typeid` , `tag`) VALUES('$tid','$aid','$arcrank','$typeid' , '$tag'); ");
        }
    }
}