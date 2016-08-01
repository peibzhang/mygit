<?php

/**
 * ECSHOP ��������Ʒ�ƹ���
 * ============================================================================
 * ��Ȩ���� (C) 2005-2007 ��ʢ���루�������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.ecshop.com
 * ----------------------------------------------------------------------------
 * ����һ����ѿ�Դ�����������ζ���������ڲ�������ҵĿ�ĵ�ǰ���¶Գ������
 * �����޸ġ�ʹ�ú��ٷ�����
 * ============================================================================
 * $Author: arlicle $
 * $Date: 2007-10-26 16:04:13 +0800 (������, 26 ʮ�� 2007) $
 * $Id: brand.php 13225 2007-10-26 08:04:13Z arlicle $
*/

define('IN_ECS', true);

require('includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');

/*------------------------------------------------------ */
//-- Ʒ���б�
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',      $_LANG['600_goods_diy_list']);
	$diy='';
	$diy_list=array();
	$diy=$db->getAll("select diy_name,diy_code,diy_cat from " . $ecs->table('diy_cat'));
    foreach($diy as $value) {
		if($value['diy_cat']) {
			$cat_names='';
			$cats=explode(',',$value['diy_cat']);
			foreach($cats as $v) {
				$cat_name= $db->getOne("select cat_name from " . $ecs->table('category') . " where cat_id=$v");
				if($cat_name) {
					$cat_names .= $cat_name . ',';
				}
			}
			$cat_names=substr($cat_names,0,strlen($cat_names)-1);
			
			$value['diy_cat']=$cat_names;
		}
		else {
			$value['diy_cat']='';
		}
		$diy_list[]=$value;
	}
   
    $smarty->assign('full_page',    1);
    $smarty->assign('diy_list',   $diy_list);
    assign_query_info();
    $smarty->display('diy_list.htm');
}

/*------------------------------------------------------ */
//-- ���Ʒ��
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
	$diy_code=$_REQUEST['code'];
    $smarty->assign('ur_here',     $_LANG['601_diy_add']);
    $smarty->assign('action_link', array('text' => $_LANG['600_goods_diy_list'], 'href' => 'diy.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
	$diys=$db->getRow("select * from " .$ecs->table('diy_cat') . " where diy_code='$diy_code'");
	
	if($diys['diy_cat']) {
			$cat_names='';
			$cats=explode(',',$diys['diy_cat']);
			foreach($cats as $v) {
				$cat_name= $db->getOne("select cat_name from " . $ecs->table('category') . " where cat_id=$v");
				if($cat_name) {
					$cat_names .= $cat_name . ',';
				}
			}
			$cat_names=substr($cat_names,0,strlen($cat_name)-1);
			$diys['diy_cat']=$cat_names;
		}
		else {
			$diys['diy_cat']="<font color='#ff0000'>��û��Ϊ����������Ʒ���࣬�뾡�����ã�</font>";
		}

    $smarty->assign('diys', $diys);
	$smarty->assign('cat_list',     cat_list(0));
    $smarty->display('diy_info.htm');
}
elseif ($_REQUEST['act'] == 'insert')
{
    if(empty($_REQUEST['cat_id']) || $_REQUEST['cat_id']==0) {
		sys_msg('��ѡ����������');
	}
	$diy_code=$_REQUEST['code'];
	$cat_ids=$_REQUEST['cat_id'];
	$cat_ids=implode(',',$cat_ids);
	$db->query("update " . $ecs->table('diy_cat') . " set diy_cat='$cat_ids' where diy_code='$diy_code'");
    /* ������� */
    clear_cache_files();
	$link[0]['text'] = '���ص�DIY�������';
    $link[0]['href'] = 'diy.php?act=list';

    sys_msg('�༭������Ʒ����ɹ�', 0, $link);
}
?>

