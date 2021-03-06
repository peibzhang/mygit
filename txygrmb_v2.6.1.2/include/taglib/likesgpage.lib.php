<?php   if(!defined('DEDEINC')) exit('Request Error!');

function lib_likesgpage(&$ctag,&$refObj)
{
    global $dsql;

    $attlist="row|8";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $innertext = trim($ctag->GetInnerText());

    $aid = (isset($refObj->Fields['aid']) ? $refObj->Fields['aid'] : 0);

    $revalue = '';
    if($innertext=='') $innertext = GetSysTemplets("part_likesgpage.htm");

    $likeid = (empty($refObj->Fields['likeid']) ?  'all' : $refObj->Fields['likeid']);

    $dsql->SetQuery("SELECT aid,title,filename FROM `#@__sgpage` WHERE likeid LIKE '$likeid' LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field','[',']');
    $ctp->LoadSource($innertext);
    while($row = $dsql->GetArray())
    {
        if($aid != $row['aid'])
        {
            $row['url'] = $GLOBALS['cfg_cmsurl'].'/'.$row['filename'];
            foreach($ctp->CTags as $tagid=>$ctag) {
                if(!empty($row[$ctag->GetName()])) $ctp->Assign($tagid,$row[$ctag->GetName()]);
            }
            $revalue .= $ctp->GetResult();
        }
        else
        {
            $revalue .= '<dd class="cur"><span>'.$row['title'].'</span></dd>';
        }
    }
    return $revalue;
}