<?php
require_once(dirname(__FILE__)."/config.php");
require_once(sucms_ADMIN.'/inc_menu.php');
$defaultIcoFile = sucms_ROOT.'/data/admin/quickmenu.txt';
$myIcoFile = sucms_ROOT.'/data/admin/quickmenu-'.$cuserLogin->getUserID().'.txt';
if(!file_exists($myIcoFile)) {
	$myIcoFile = $defaultIcoFile;
}
include(sucms_ADMIN.'/templets/index.htm');
exit();
?>