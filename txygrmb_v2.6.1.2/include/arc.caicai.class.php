<?php   if(!defined('DEDEINC')) exit("Request Error!");

require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEINC."/channelunit.func.php");

class Caicai extends DataListCP
{
    var $maxPageSize = 100;
    var $arcCacheTime = 3600;

    function PreLoad()
    {
        global $totalresult,$pageno;
        if(empty($pageno) || preg_match("#[^0-9]#", $pageno)) $pageno = 1;
        if(empty($totalresult) || preg_match("#[^0-9]#", $totalresult)) $totalresult = 0;

        $this->pageNO = $pageno;
        $this->totalResult = $totalresult;

        if(isset($this->tpl->tpCfgs['pagesize'])){
            $this->pageSize = $this->tpl->tpCfgs['pagesize'];
        }
        $this->totalPage = ceil($this->totalResult/$this->pageSize);
        if($this->totalPage > $this->maxPageSize)
        {
            $this->totalPage = $this->maxPageSize;
        }

        if($this->pageNO > $this->totalPage)
        {
            $this->pageNO = $this->totalPage;
            $this->totalResult = $this->totalPage * $this->pageSize;
        }
        $this->sourceSql = preg_replace("#LIMIT [0-9,]{1,}#i", '', $this->sourceSql);
        if( $this->totalResult==0 )
        {
            $countQuery = preg_replace("#SELECT[ \r\n\t](.*)[ \r\n\t]FROM#is","SELECT COUNT(*) as dd FROM", $this->sourceSql);
            $row = $this->dsql->GetOne($countQuery);
            $this->totalResult = $row['dd'];
            $this->sourceSql .= " LIMIT 0,".$this->pageSize;
        }
        else
        {
            $this->sourceSql .= " LIMIT ".(($this->pageNO-1) * $this->pageSize).",".$this->pageSize;
        }
    }

    function GetArcList($atts, $refObj='', $fields=array())
    {
        $rsArray = array();
        $t1 = Exectime();
        if(!$this->isQuery)
        {
            $this->dsql->Execute('dlist', $this->sourceSql);
        }
        $i = 0;
        while($arr=$this->dsql->GetArray('dlist'))
        {
            $i++;
            $arr['filename'] = $arr['arcurl'] = GetFileUrl($arr['id'],$arr['typeid'],$arr['senddate'],$arr['title'],$arr['ismake'],
            $arr['arcrank'],$arr['namerule'],$arr['typedir'],$arr['money'],$arr['filename'],$arr['moresite'],$arr['siteurl'],$arr['sitepath']);
            $arr['typeurl'] = GetTypeUrl($arr['typeid'],MfTypedir($arr['typedir']),$arr['isdefault'],$arr['defaultname'],
            $arr['ispart'],$arr['namerule2'],$arr['moresite'],$arr['siteurl'],$arr['sitepath']);
            if($arr['litpic'] == '-' || $arr['litpic'] == '')
            {
                $arr['litpic'] = 'templets/images/dfpic.gif';
            }
            if(!preg_match("#^http:\/\/#i", $arr['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y')
            {
                $arr['litpic'] = $GLOBALS['cfg_mainsite'].$arr['litpic'];
            }
            $arr['picname'] = $arr['litpic'];
            $arr['alttitle'] = $arr['userid']." �Ŀռ�";
            $arr['face'] = ($arr['face']!='' ? $arr['face'] : 'images/nopic.gif');
            if($arr['userid']!='')
            {
                $arr['spaceurl'] = $GLOBALS['cfg_basehost'].'/member/index.php?uid='.$arr['userid'];
            }
            else
            {
                $arr['alttitle'] = $arr['title'];
                $arr['spaceurl'] = $arr['arcurl'];
                $arr['face'] = $arr['litpic'];
                $arr['face'] = str_replace('defaultpic','dfcaicai',$arr['face']);
            }
            if(!empty($arr['lastpost']))
            {
                $arr['lastpost'] = MyDate('m-d h:i',$arr['lastpost']);
            }
            else
            {
                $arr['lastpost'] = "<a href='../plus/feedback.php?aid={$arr['id']}'>˵����&gt;&gt;</a>";
            }
            $rsArray[$i]  =  $arr;
            if($i >= $this->pageSize)
            {
                break;
            }
        }
        $this->dsql->FreeResult('dlist');
        $this->queryTime = (Exectime() - $t1);
        return $rsArray;
    }

    function GetSortArc($atts, $refObj='', $fields=array())
    {
        $arcrow = (empty($atts['row']) ?  12 : $atts['row']);
        $order = (empty($atts['order']) ? 'scores' : $atts['order'] );
        $orderway = (empty($atts['orderway']) ? 'desc' : $atts['orderway'] );
        if(empty($arcrow)) $arcrow = 12;

        $query = "SELECT arc.*,tp.typedir,tp.typename,
              tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
          FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON tp.id = arc.typeid
          WHERE arc.arcrank>-1 ORDER BY arc.{$order} $orderway LIMIT 0,$arcrow ";

        $rsArray = array();
        
        $cacheFile = DEDEDATA.'/cache/caicai_'.md5($query).'.inc';
        $needCache = false;
        if(file_exists($cacheFile) && filemtime($cacheFile)-time() < $this->arcCacheTime)
        {
            $fp = fopen($cacheFile, 'r');
            $ids = fread($fp, filesize($cacheFile));
            fclose($fp);
            $ids = trim($ids);
            if( !empty($ids) )
            {
                $query = "SELECT arc.*,tp.typedir,tp.typename,
              tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
          FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
          WHERE arc.id in($ids) ORDER BY arc.{$order} $orderway ";
            }
        }
        else
        {
            $needCache = true;
        }
        $ids = array();
        $i = 0;
        $this->dsql->Execute('cai',$query);
        while($arr=$this->dsql->GetArray('cai'))
        {
            $i++;
            $ids[] = $arr['id'];
            $arr['filename'] = $arr['arcurl'] = GetFileUrl($arr['id'],$arr['typeid'],$arr['senddate'],$arr['title'],$arr['ismake'],
            $arr['arcrank'],$arr['namerule'],$arr['typedir'],$arr['money'],$arr['filename'],$arr['moresite'],$arr['siteurl'],$arr['sitepath']);

            $arr['typeurl'] = GetTypeUrl($arr['typeid'], MfTypedir($arr['typedir']), $arr['isdefault'], $arr['defaultname'],
            $arr['ispart'], $arr['namerule2'], $arr['moresite'], $arr['siteurl'], $arr['sitepath']);

            if($arr['litpic']=='') $arr['litpic'] = '/../yunteng_cc_images/cloudcms_img.jpg';

            if(!preg_match("#^http:\/\/#", $arr['litpic']))
            {
                $arr['picname'] = $arr['litpic'] = $GLOBALS['cfg_cmsurl'].$arr['litpic'];
            }
            else
            {
                $arr['picname'] = $arr['litpic'] = $arr['litpic'];
            }

            $rsArray[$i]  =  $arr;
        }
        $this->dsql->FreeResult('cai');

        if($needCache && count($ids) > 0)
        {
            $idsstr = join(',', $ids);
            file_put_contents($cacheFile, $idsstr);

        }
        
        return $rsArray;

    }

    function GetCatalog($atts,$refObj='',$fields=array())
    {
        $maxrow = (empty($atts['row']) ?  12 : $atts['row']);
        $query = "SELECT id,typename FROM `#@__arctype` WHERE reid=0 AND ispart<2 AND channeltype>0 ORDER BY sortrank ASC LIMIT 0,$maxrow ";
        $rsArray = array();
        $this->dsql->Execute('co',$query);
        $i = 0;
        while($arr=$this->dsql->GetArray('co'))
        {
            $i++;
            $rsArray[$i]  =  $arr;
        }
        $this->dsql->FreeResult('co');
        return $rsArray;
    }
}