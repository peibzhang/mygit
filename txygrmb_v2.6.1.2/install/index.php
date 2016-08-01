<?php
@set_time_limit(0);
//error_reporting(E_ALL);
error_reporting(E_ALL || ~E_NOTICE);

$verMsg = ' BATE2016';
$s_lang = 'gb2312';
$dfDbname = 'yt_mysql';
$errmsg = '';
$install_demo_name = 'demo.txt';
$insLockfile = dirname(__FILE__).'/yt_install.txt';
$moduleCacheFile = dirname(__FILE__).'/modules.tmp.inc';

define('DEDEINC',dirname(__FILE__).'/../include');
define('DEDEDATA',dirname(__FILE__).'/../yunteng_cc_data');
define('DEDEROOT',preg_replace("#[\\\\\/]install#", '', dirname(__FILE__)));
header("Content-Type: text/html; charset={$s_lang}");

require_once(DEDEROOT.'/install/install.inc.php');
require_once(DEDEINC.'/zip.class.php');

foreach(Array('_GET','_POST','_COOKIE') as $_request)
{
    foreach($$_request as $_k => $_v) ${$_k} = RunMagicQuotes($_v);
}

require_once(DEDEINC.'/common.func.php');

if(file_exists($insLockfile))
{
    exit(" 程序已运行安装，如果你确定要重新安装，请先从FTP中删除 install/yt_install.txt！");
}

if(empty($step))
{
    $step = 1;
}
if($step==1)
{
    include('./templates/step-1.html');
    exit();
}
else if($step==2)
{
    $phpv = phpversion();
    $sp_os = PHP_OS;
    $sp_gd = gdversion();
    $sp_server = $_SERVER['SERVER_SOFTWARE'];
    $sp_host = (empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR']);
    $sp_name = $_SERVER['SERVER_NAME'];
    $sp_max_execution_time = ini_get('max_execution_time');
    $sp_allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
    $sp_allow_url_fopen = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
    $sp_safe_mode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');
    $sp_gd = ($sp_gd>0 ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
    $sp_mysql = (function_exists('mysql_connect') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');

    if($sp_mysql=='<font color=red>[×]Off</font>')
    $sp_mysql_err = TRUE;
    else
    $sp_mysql_err = FALSE;

    $sp_testdirs = array(
        '/',
        '/cloud/*',
		'/install',
		'/yunteng_cc_plus/*',
        '/yunteng_cc_data/*',
        '/yunteng_cc_images/*',
		'/yt/*'
    );
    include('./templates/step-2.html');
    exit();
}
else if($step==3)
{
    if(!empty($_SERVER['REQUEST_URI']))
    $scriptName = $_SERVER['REQUEST_URI'];
    else
    $scriptName = $_SERVER['PHP_SELF'];

    $basepath = preg_replace("#\/install(.*)$#i", '', $scriptName);

    if(!empty($_SERVER['HTTP_HOST']))
        $baseurl = 'http://'.$_SERVER['HTTP_HOST'];
    else
        $baseurl = "http://".$_SERVER['SERVER_NAME'];
    

    $rnd_cookieEncode = chr(mt_rand(ord('A'),ord('Z'))).chr(mt_rand(ord('a'),ord('z'))).chr(mt_rand(ord('A'),ord('Z'))).chr(mt_rand(ord('A'),ord('Z'))).chr(mt_rand(ord('a'),ord('z'))).mt_rand(1000,9999).chr(mt_rand(ord('A'),ord('Z')));

    if(file_get_contents($install_demo_name)) $isdemosign = 1;
    $module_local = DEDEDATA.'/module/';
    include('./templates/step-3.html');
    exit();
}
else if($step==4)
{
    $conn = mysql_connect($dbhost,$dbuser,$dbpwd) or die("<script>alert('数据库服务器或登录密码无效，\\n\\n无法连接数据库，请重新设定！');history.go(-1);</script>");

    mysql_query("CREATE DATABASE IF NOT EXISTS `".$dbname."`;",$conn);
    
    mysql_select_db($dbname) or die("<script>alert('选择数据库失败，可能是你没权限，请预先创建一个数据库！');history.go(-1);</script>");

    $rs = mysql_query("SELECT VERSION();",$conn);
    $row = mysql_fetch_array($rs);
    $mysqlVersions = explode('.',trim($row[0]));
    $mysqlVersion = $mysqlVersions[0].".".$mysqlVersions[1];

    mysql_query("SET NAMES '$dblang',character_set_client=binary,sql_mode='';",$conn);

    $fp = fopen(dirname(__FILE__)."/common.inc.php","r");
    $configStr1 = fread($fp,filesize(dirname(__FILE__)."/common.inc.php"));
    fclose($fp);

    $fp = fopen(dirname(__FILE__)."/config.cache.inc.php","r");
    $configStr2 = fread($fp,filesize(dirname(__FILE__)."/config.cache.inc.php"));
    fclose($fp);

    $configStr1 = str_replace("~dbhost~",$dbhost,$configStr1);
    $configStr1 = str_replace("~dbname~",$dbname,$configStr1);
    $configStr1 = str_replace("~dbuser~",$dbuser,$configStr1);
    $configStr1 = str_replace("~dbpwd~",$dbpwd,$configStr1);
    $configStr1 = str_replace("~dbprefix~",$dbprefix,$configStr1);
    $configStr1 = str_replace("~dblang~",$dblang,$configStr1);

    @chmod(DEDEDATA,0777);
    $fp = fopen(DEDEDATA."/common.inc.php","w") or die("<script>alert('写入配置失败，请检查../yunteng_cc_data目录是否可写入！');history.go(-1);</script>");
    fwrite($fp,$configStr1);
    fclose($fp);

    $cmspath = trim(preg_replace("#\/{1,}#", '/', $cmspath));
    if($cmspath!='' && !preg_match("#^\/#", $cmspath)) $cmspath = '/'.$cmspath;

    if($cmspath=='') $indexUrl = '/';
    else $indexUrl = $cmspath;

    $configStr2 = str_replace("~baseurl~",$baseurl,$configStr2);
    $configStr2 = str_replace("~basepath~",$cmspath,$configStr2);
    $configStr2 = str_replace("~indexurl~",$indexUrl,$configStr2);
    $configStr2 = str_replace("~cookieEncode~",$cookieencode,$configStr2);
    $configStr2 = str_replace("~webname~",$webname,$configStr2);
    $configStr2 = str_replace("~adminmail~",$adminmail,$configStr2);

    $fp = fopen(DEDEDATA.'/config.cache.inc.php','w');
    fwrite($fp,$configStr2);
    fclose($fp);

    $fp = fopen(DEDEDATA.'/config.cache.bak.php','w');
    fwrite($fp,$configStr2);
    fclose($fp);

    if($mysqlVersion >= 4.1)
    {
        $sql4tmp = "ENGINE=MyISAM DEFAULT CHARSET=".$dblang;
    }

    $query = '';
    $fp = fopen(dirname(__FILE__).'/sql-dftables.txt','r');
    while(!feof($fp))
    {
        $line = rtrim(fgets($fp,1024));
        if(preg_match("#;$#", $line))
        {
            $query .= $line."\n";
            $query = str_replace('#@__',$dbprefix,$query);
            if($mysqlVersion < 4.1)
            {
                $rs = mysql_query($query,$conn);
            } else {
                if(preg_match('#CREATE#i', $query))
                {
                    $rs = mysql_query(preg_replace("#TYPE=MyISAM#i",$sql4tmp,$query),$conn);
                }
                else
                {
                    $rs = mysql_query($query,$conn);
                }
            }
            $query='';
        } else if(!preg_match("#^(\/\/|--)#", $line))
        {
            $query .= $line;
        }
    }
    fclose($fp);

    $query = '';
    $fp = fopen(dirname(__FILE__).'/sql-dfdata.txt','r');
    while(!feof($fp))
    {
        $line = rtrim(fgets($fp, 1024));
        if(preg_match("#;$#", $line))
        {
            $query .= $line;
            $query = str_replace('#@__',$dbprefix,$query);
            if($mysqlVersion < 4.1) $rs = mysql_query($query,$conn);
            else $rs = mysql_query(str_replace('#~lang~#',$dblang,$query),$conn);
            $query='';
        } else if(!preg_match("#^(\/\/|--)#", $line))
        {
            $query .= $line;
        }
    }
    fclose($fp);

    $cquery = "Update `{$dbprefix}sysconfig` set value='{$baseurl}' where varname='cfg_basehost';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$cmspath}' where varname='cfg_cmspath';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$indexUrl}' where varname='cfg_indexurl';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$cookieencode}' where varname='cfg_cookie_encode';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$webname}' where varname='cfg_webname';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$adminmail}' where varname='cfg_adminemail';";
    mysql_query($cquery,$conn);

    $adminquery = "INSERT INTO `{$dbprefix}admin` VALUES (1, 10, '$adminuser', '".substr(md5($adminpwd),5,20)."', 'admin', '', '', 0, '".time()."', '127.0.0.1');";
    mysql_query($adminquery,$conn);

    if($installdemo == 1)
    {
        if($setupsql = file_get_contents($install_demo_name))
		{
			$setupsql = preg_replace("#ENGINE=MyISAM#i", 'TYPE=MyISAM', $setupsql);
			$sql41tmp = 'ENGINE=MyISAM DEFAULT CHARSET='.$cfg_db_language;
			if($mysql_version >= 4.1) {
				$setupsql = preg_replace("#TYPE=MyISAM#i", $sql41tmp, $setupsql);
			}
			$setupsql = preg_replace("#_ROOTURL_#i", $rooturl, $setupsql);
			$setupsql = preg_replace("#[\r\n]{1,}#", "\n", $setupsql);
			$setupsql = preg_replace('/#@__/i',$dbprefix,$setupsql);
			$sqls = preg_split("#;[ \t]{0,}\n#", $setupsql);
			foreach($sqls as $sql) {
				if(trim($sql)!='') mysql_query($sql,$conn);
			}

			UpDateCatCache();
		} else {
			die("没有体验数据包文件,请检查是否下载.");
		}
    }

    if(!isset($modules) || !is_array($modules))
    {
        $fp = fopen($insLockfile,'w');
        fwrite($fp,'ok');
        fclose($fp);
        include('./templates/step-5.html');
        exit();
    }
    else
    {
        $module = join(',',$modules);
        $fp = fopen($moduleCacheFile,'w');
        fwrite($fp,'<'.'?php'."\r\n");
        fwrite($fp,'$selModule = "'.$module.'"; '."\r\n");
        fwrite($fp,'?'.'>');

        if(!$fp)
        {
            $fp = fopen($insLockfile,'w');
            fwrite($fp,'ok');
            fclose($fp);
            $errmsg = "<font color='red'>由于无法写入模块缓存，安装可选模块失败，请登录后在模块管理处安装。</font>";
            include('./templates/step-5.html');
            exit();
        }
        fclose($fp);
        include('./templates/step-4.html');
        exit();
    }
    exit();
}
else if($step==5)
{
    header("location:module-install.php");
    exit();
}
else if($step==10)
{
    header("Pragma:no-cache\r\n");
    header("Cache-Control:no-cache\r\n");
    header("Expires:0\r\n");
    $conn = @mysql_connect($dbhost,$dbuser,$dbpwd);
    if($conn)
    {
		if(empty($dbname)){
			echo "<font color='green'>信息正确</font>";
		}else{
			$info = mysql_select_db($dbname,$conn)?"<font color='red'>数据库已经存在，系统将覆盖数据库</font>":"<font color='green'>数据库不存在,系统将自动创建</font>";
			echo $info;
		}
    }
    else
    {
        echo "<font color='red'>数据库连接失败！</font>";
    }
    @mysql_close($conn);
    exit();
}
else if($step==11)
{
	require_once('../yunteng_cc_data/admin/config_update.php');
	$rmurl = $updateHost."dedecms/demodata.{$s_lang}.txt";
	
	$sql_content = file_get_contents($rmurl);
	$fp = fopen($install_demo_name,'w');
	if(fwrite($fp,$sql_content))
		echo '&nbsp; <font color="green">[√]</font> 存在(您可以选择安装进行体验)';
	else
		echo '&nbsp; <font color="red">[×]</font> 远程获取失败';
	unset($sql_content);
	fclose($fp);
	exit();
}