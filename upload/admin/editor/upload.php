<?php
session_start();
if(empty($_SESSION[sucms_admin_id])){die('ERR');}

require 'upload.inc.php';
$save_dir='../../uploads/editor/'; //保存文件目录
$save_url='../../uploads/editor/'; //目录的web访问网址
$file_field_name='file';
$max_size=1024*1024; 
$exts='jpg;gif;png;jpeg'; 
echo json_encode(uploadFile($save_dir, $save_url, $file_field_name, $max_size,$exts));