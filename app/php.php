<?php

# Copyright (c) 2011, Jiang Jilin. All rights reserved.
#
# This file is *NOT* Part of LTMP.
# 
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 3 of the License, or
#  (at your option) any later version.

#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.

#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.  */


error_reporting(0); 
@header("content-Type: text/html; charset=utf-8"); 
ob_start();

define('HTTP_HOST', preg_replace('~^www\.~i', '', $_SERVER['HTTP_HOST']));

function get_time() {
  $mtime = microtime();
  $mtime = explode(' ', $mtime);
  return $mtime[1] + $mtime[0];
}

$time_start = get_time();

function memory_cost() {
  $memory	 = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
  return $memory;
}


function valid_email($str) {
  return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
}

function show($varName) {
  switch($result = get_cfg_var($varName)) {
  case 0:
    return '<font color="red">X</font>';
    break;
  case 1:
    return '<font color="blue">O</font>'; 
    break;
  default:
    return $result;
    break;
  }
}

if ($_GET['act'] == "phpinfo") {
  phpinfo();
  exit();
}

if ($_POST['act'] == 'MySQL Test') {
  $host = isset($_POST['host']) ? trim($_POST['host']) : '';
  $port = isset($_POST['port']) ? (int) $_POST['port'] : '';
  $login = isset($_POST['login']) ? trim($_POST['login']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  $host = preg_match('~[^a-z0-9\-\.]+~i', $host) ? '' : $host;
  $port = intval($port) ? intval($port) : '';
  $login = preg_match('~[^a-z0-9\_\-]+~i', $login) ? '' : htmlspecialchars($login);
  $password = is_string($password) ? htmlspecialchars($password) : '';
} elseif ($_POST['act'] == 'Function Test') {
  $funRe = "函数".$_POST['funName']."Result:".is_fun($_POST['funName']);
} elseif ($_POST['act'] == 'Email Test') {
  $mailRe = "Send";
  $mailRe .= (false !== @mail($_POST["mailAdd"], "http://".$_SERVER['SERVER_NAME'].($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']), "This is a test mail!")) ? "OK":"FAIL";
}

function is_fun($funName = '') {
  if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return 'Error';
  return (false !== function_exists($funName)) ? '<font color="blue">O</font>' : '<font color="red">X</font>';
}

function int_test() {
  $timeStart = gettimeofday();
  for($i = 0; $i < 3000000; $i++) {
    $t = 1+1;
  }
  $timeEnd = gettimeofday();
  $time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
  $time = round($time, 3)."sec.";
  return $time;
}

function float_test() {
  $t = pi();
  $timeStart = gettimeofday();

  for($i = 0; $i < 3000000; $i++) {
    sqrt($t);
  }

  $timeEnd = gettimeofday();
  $time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
  $time = round($time, 3)." sec.";
  return $time;
}

function io_test() {
  $fp = @fopen(PHPSELF, "r");
  $timeStart = gettimeofday();
  for($i = 0; $i < 10000; $i++) {
    @fread($fp, 10240);
    @rewind($fp);
  }
  $timeEnd = gettimeofday();
  @fclose($fp);
  $time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
  $time = round($time, 3)." sec.";
  return($time);
}

switch(PHP_OS) {
case "Linux":
  $sysReShow = (false !== ($sys_info = sys_linux()))?"show":"none";
  break;
case "FreeBSD":
  $sysReShow = (false !== ($sys_info = sys_freebsd()))?"show":"none";
  break;
default:
  ;
}

function sys_linux()
{
  // CPU
  if (false === ($str = @file("/proc/cpuinfo"))) return false;
  $str = implode("", $str);
  @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
  @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);
  @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
  @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);
  if (false !== is_array($model[1]))
  {
    $res['cpu']['num'] = sizeof($model[1]);
    for($i = 0; $i < $res['cpu']['num']; $i++)
    {
      $res['cpu']['model'][] = $model[1][$i];
      $res['cpu']['mhz'][] = $mhz[1][$i];
      $res['cpu']['cache'][] = $cache[1][$i];
      $res['cpu']['bogomips'][] = $bogomips[1][$i];
    }
    if (false !== is_array($res['cpu']['model'])) $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);
    if (false !== is_array($res['cpu']['mhz'])) $res['cpu']['mhz'] = implode("<br />", $res['cpu']['mhz']);
    if (false !== is_array($res['cpu']['cache'])) $res['cpu']['cache'] = implode("<br />", $res['cpu']['cache']);
    if (false !== is_array($res['cpu']['bogomips'])) $res['cpu']['bogomips'] = implode("<br />", $res['cpu']['bogomips']);
  }

  // NETWORK

  // UPTIME
  if (false === ($str = @file("/proc/uptime"))) return false;
  $str = explode(" ", implode("", $str));
  $str = trim($str[0]);
  $min = $str / 60;
  $hours = $min / 60;
  $days = floor($hours / 24);
  $hours = floor($hours - ($days * 24));
  $min = floor($min - ($days * 60 * 24) - ($hours * 60));
  if ($days !== 0) $res['uptime'] = $days." days ";
  if ($hours !== 0) $res['uptime'] .= $hours." hours ";
  $res['uptime'] .= $min." minutes";

  // MEMORY
  if (false === ($str = @file("/proc/meminfo"))) return false;
  $str = implode("", $str);
  preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);

  $res['mem_total'] = round($buf[1][0]/1024, 2);
  $res['mem_free'] = round($buf[2][0]/1024, 2);
  $res['mem_cached'] = round($buf[3][0]/1024, 2);
  $res['mem_used'] = ($res['mem_total']-$res['mem_free']);
  $res['mem_percent'] = (floatval($res['mem_total'])!=0)?round($res['mem_used']/$res['mem_total']*100,2):0;
  $res['mem_realused'] = ($res['mem_total'] - $res['mem_free'] - $res['mem_cached']);
  $res['mem_realpercent'] = (floatval($res['mem_total'])!=0)?round($res['mem_realused']/$res['mem_total']*100,2):0;

  $res['swap_total'] = round($buf[4][0]/1024, 2);
  $res['swap_free'] = round($buf[5][0]/1024, 2);
  $res['swap_used'] = ($res['swap_total']-$res['swap_free']);
  $res['swap_percent'] = (floatval($res['swap_total'])!=0)?round($res['swap_used']/$res['swap_total']*100,2):0;

  // LOAD AVG
  if (false === ($str = @file("/proc/loadavg"))) return false;
  $str = explode(" ", implode("", $str));
  $str = array_chunk($str, 4);
  $res['load_avg'] = implode(" ", $str[0]);

  return $res;
}

function sys_freebsd() {
  //CPU
  if (false === ($res['cpu']['num'] = get_key("hw.ncpu"))) return false;
  $res['cpu']['model'] = get_key("hw.model");

  //LOAD AVG
  if (false === ($res['load_avg'] = get_key("vm.loadavg"))) return false;

  //UPTIME
  if (false === ($buf = get_key("kern.boottime"))) return false;
  $buf = explode(' ', $buf);
  $sys_ticks = time() - intval($buf[3]);
  $min = $sys_ticks / 60;
  $hours = $min / 60;
  $days = floor($hours / 24);
  $hours = floor($hours - ($days * 24));
  $min = floor($min - ($days * 60 * 24) - ($hours * 60));
  if ($days !== 0) $res['uptime'] = $days." days";
  if ($hours !== 0) $res['uptime'] .= $hours." hours";
  $res['uptime'] .= $min." minutes";

  //MEMORY
  if (false === ($buf = get_key("hw.physmem"))) return false;
  $res['mem_total'] = round($buf/1024/1024, 2);

  $str = get_key("vm.vmtotal");
  preg_match_all("/\nVirtual Memory[\:\s]*\(Total[\:\s]*([\d]+)K[\,\s]*Active[\:\s]*([\d]+)K\)\n/i", $str, $buff, PREG_SET_ORDER);
  preg_match_all("/\nReal Memory[\:\s]*\(Total[\:\s]*([\d]+)K[\,\s]*Active[\:\s]*([\d]+)K\)\n/i", $str, $buf, PREG_SET_ORDER);

  $res['mem_realused'] = round($buf[0][2]/1024, 2);
  $res['mem_cached'] = round($buff[0][2]/1024, 2);
  $res['mem_used'] = round($buf[0][1]/1024, 2) + $res['mem_cached'];
  $res['mem_free'] = $res['mem_total'] - $res['mem_used'];
  $res['mem_percent'] = (floatval($res['mem_total'])!=0)?round($res['mem_used']/$res['mem_total']*100,2):0;

  $res['mem_realpercent'] = (floatval($res['mem_total'])!=0)?round($res['mem_realused']/$res['mem_total']*100,2):0;

  return $res;
}

function get_key($keyName) {
  return do_command('sysctl', "-n $keyName");
}

function find_command($commandName) {
  $path = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');
  foreach($path as $p) {
    if (@is_executable("$p/$commandName")) return "$p/$commandName";
  }
  return false;
}

function do_command($commandName, $args) {
  $buffer = "";
  if (false === ($command = find_command($commandName))) return false;
  if ($fp = @popen("$command $args", 'r')) {
    while (!@feof($fp)){
      $buffer .= @fgets($fp, 4096);
    }
    return trim($buffer);
  }
  return false;
}


$uptime = $sys_info['uptime'];
$stime = date("Y-n-j H:i:s");
$df = round(@disk_free_space(".")/(1024*1024*1024),3);

$mt = $sys_info['mem_total'];
$mu = round($sys_info['mem_used']/1024,3);
$mf = round($sys_info['mem_free']/1024,3);
$mc = round($sys_info['mem_cached']/1024,3);
$st = $sys_info['swap_total'];
$su = round($sys_info['swap_used']/1024,3);
$sf = round($sys_info['swap_free']/1024,3);
$swap_percent = $sys_info['swap_percent'];
$load = $sys_info['load_avg'];
$mem_realpercent = $sys_info['mem_realpercent'];
$mem_percent = $sys_info['mem_percent'];

$strs = @file("/proc/net/dev"); 

for ($i = 2; $i < count($strs); $i++ )
{
  preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );
  $tmo = round($info[2][0]/1024/1024, 5); 
  $tmo2 = round($tmo / 1024, 5);
  $NetInput[$i] = $tmo2;

  $tmp = round($info[10][0]/1024/1024, 5); 
  $tmp2 = round($tmp / 1024, 5);
  $NetOut[$i] = $tmp2;
}

?> 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LTMP.NET INSPECTOR</title>
<style>
</style>
</head>
<body  onload="startTime()">
<center>Server Infomation</center>
<br>
IP:<font color="blue"><?php echo @get_current_user();?>  - <?php echo $_SERVER['SERVER_NAME'];?> (<?php echo $_SERVER['SERVER_ADDR'];?> )
<br></font>
Client IP:<font color="blue"><?php echo @$_SERVER['REMOTE_ADDR'];?></font> <br>
Server :<font color="blue"><?php echo @php_uname();?> <br></font>
Uptime: <font color="blue"><?php echo $sys_info['uptime'];?> <br> </font>
OS:<font color="blue"> <?php $os = explode(" ", php_uname()); echo $os[0];?> <br></font>
Kernel:<font color="blue"> <?php echo $os[2]?> <br></font>
Engine: <font color="blue"><?php echo $_SERVER['SERVER_SOFTWARE'];?> <br></font>
Free(DISK):<font color="blue"><?php echo round((@disk_free_space(".")/(1024*1024*1024)),2) ? round((@disk_free_space(".")/(1024*1024*1024)),2) : intval(diskfreespace("/") / (1024 * 1024*1024));?> G <br></font>
Language:<font color="blue"> <?php echo getenv("HTTP_ACCEPT_LANGUAGE");?> <br></font>
Port: <font color="blue"><?php echo $_SERVER['SERVER_PORT'];?> <br></font>
Email: <font color="blue"><?php echo $_SERVER['SERVER_ADMIN'];?> <br></font>
Path: <font color="blue"><?php echo $_SERVER['DOCUMENT_ROOT'].$_SERVER['$PATH_INFO'];?> <br> </font>
Zend Optimizer: <?php echo (get_cfg_var("zend_optimizer.optimization_level")||get_cfg_var("zend_extension_manager.optimizer_ts")||get_cfg_var("zend_extension_ts")) ? '<font color="blue">O</font>' : '<font color="red">X</font>'; ?> <br>
Load Avg.:<font color="blue"><?php echo $sys_info['load_avg'];?><br> </font>

<center>CPU</center>

CPU:<font color="blue"> <?php echo $sys_info['cpu']['num'];?> <br></font>
CPU Model: <font color="blue"><?php echo $sys_info['cpu']['model'];?> <br></font>
CPU Cache-2:<font color="blue"> <?php echo $sys_info['cpu']['cache'];?> <br></font>
Bogomips:<font color="blue"> <?php echo $sys_info['cpu']['bogomips'];?> <br> </font>

<center>MEMORY</center>
Physical Memory: <font color="blue">Total <?php echo round($sys_info['mem_total'],2)?> </font>M, 
Used <font color="blue"><?php echo round($sys_info['mem_used'],2)?>  </font>M, 
Free <font color="blue"><?php echo round($sys_info['mem_free'],2)?>  </font>M,
Used% <font color="blue"><?php echo round($sys_info['mem_percent'],2)?> <br></font>
Cache Memory <font color="blue"><?php echo round($sys_info['mem_cached'],2)?> </font> M,
Real<font color="blue"> <?php echo round($sys_info['mem_realused'],2)?> </font> 
Used%<font color="blue"> <?php echo round($sys_info['mem_realpercent'],2)?> </font><br>
SWAP: <font color="blue">Total <?php echo round($sys_info['swap_total'],2)?></font>  M
Used <font color="blue"><?php echo round($sys_info['swap_used'],2)?>  </font>M, 
Free <font color="blue"><?php echo round($sys_info['swap_free'],2)?>  </font>M,
Used% <font color="blue"><?php echo round($sys_info['swap_percent'],2)?> <br></font>

<?php if (false !== ($strs = @file("/proc/net/dev"))) : ?> <br>
<center>Network</center>
<?php for ($i = 2; $i < count($strs); $i++ ) : ?> 
<?php preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );?> 

<font color="blue"><?php echo $info[1][0] ?></font>  : Inbound: <font color="blue"><?php echo round($info[2][0]/1024/1024/1024,2) ?> </font> G, OutBound: <font color="blue"><?php echo round($info[10][0]/1024/1024/1024,2) ?> </font>G<br>

<?php endfor; endif; ?> <br>

<center>Moduals Compiled</center>

<?php
$able=get_loaded_extensions();
foreach ($able as $key=>$value) {
  if ($key!=0 && $key%13==0) {
    echo '<br />';
  }
  echo "$value&nbsp;&nbsp;";
}
?> <br>


<center>PHP INFORMATION</center>

PHPINFO: <?php
$phpSelf = $_SERVER[PHP_SELF] ? $_SERVER[PHP_SELF] : $_SERVER[SCRIPT_NAME];
$disFuns=get_cfg_var("disable_functions");
?> 
<?php echo (false!==eregi("phpinfo",$disFuns))? 'NO' :"<a href='$phpSelf?act=phpinfo' target='_blank'>PHPINFO</a>";?> <br>

PHP Version:<font color="blue"> <?php echo PHP_VERSION; ?> <br> </font>
PHP Run: <font color="blue"><?php echo strtoupper(php_sapi_name());?> <br></font>
Memory Limit: <font color="blue"><?php echo show("memory_limit");?> <br> </font>
Safe Mode: <?php echo show("safe_mode");?> <br>
Post Max Size: <font color="blue"><?php echo show("post_max_size");?> <br> </font>
Upload Max Filesize: <font color="blue"><?php echo show("upload_max_filesize");?> <br></font>
Precision: <font color="blue"><?php echo show("precision");?> <br> </font>
Max Execution Time: <font color="blue"><?php echo show("max_execution_time");?></font>  seconds<br>
Default Socket Timeout: <font color="blue"><?php echo show("default_socket_timeout");?> </font>  seconds <br>
Doc Root: <?php echo show("doc_root");?> <br>
User Dir: <?php echo show("user_dir");?> <br> 
dl() Func: <?php echo show("enable_dl");?> <br>
Include Path: <?php echo show("include_path");?> <br> 
Display Errors: <?php echo show("display_errors");?> <br>
Register Globals: <?php echo show("register_globals");?> <br> 
Magic Quotes Gpc）: <?php echo show("magic_quotes_gpc");?> <br>
"&lt;?...?&gt;"
Short open Tag: <?php echo show("short_open_tag");?> <br> 
"&lt;% %&gt;"
ASP Tags: <?php echo show("asp_tags");?> <br>
Ignore Repeated Errors: <?php echo show("ignore_repeated_errors");?> <br> 
Ignore Repeated Source: <?php echo show("ignore_repeated_source");?> <br>
Report Memleaks: <?php echo show("report_memleaks");?> <br> 
Magic Quotes Gpc）: <?php echo show("magic_quotes_gpc");?> <br>
Magic Quotes Runtime: <?php echo show("magic_quotes_runtime");?> <br> 
Allow Url Fopen: <?php echo show("allow_url_fopen");?> <br>
Register Argc Argv: <?php echo show("register_argc_argv");?> <br> 

<center>Component </center>

FTP : <?php echo is_fun("ftp_login");?> <br>
XML :<?php echo is_fun("xml_set_object");?> <br> 
Session : <?php echo is_fun("session_start");?> <br>
Socket : <?php echo is_fun("socket_accept");?> <br> 
ZEND Optimizer : <?php echo (get_cfg_var("zend_optimizer.optimization_level")||get_cfg_var("zend_extension_manager.optimizer_ts")||get_cfg_var("zend.ze1_compatibility_mode")||get_cfg_var("zend_extension_ts"))?'<font color="blue>O</font>':'<font color="red">X</font>';?> <br>
Allow URL Fopen: <?php echo show("allow_url_fopen");?> <br> 
GD : <?php echo is_fun("gd_info");?> <br>
<?php
if(function_exists(gd_info)) {
  $gd_info = @gd_info();
  $gd_info = $gd_info["GD Version"];
  echo $gd_info ? 'Version:'.$gd_info : '';
}
?> <br>
Supported(Zlib): <?php echo is_fun("gzclose");?> <br> 
IMAP: <?php echo is_fun("imap_close");?> <br>
Canlendar: <?php echo is_fun("JDToGregorian");?> <br> 
Regex: <?php echo is_fun("preg_match");?> <br>
WDDX : <?php echo is_fun("wddx_add_vars");?> <br> 
Iconv: <?php echo is_fun("iconv");?> <br>
mbstring: <?php echo is_fun("mb_eregi");?> <br> 
BC : <?php echo is_fun("bcadd");?> <br>
LDAP: <?php echo is_fun("ldap_close");?> <br> 
MCrypt: <?php echo is_fun("mcrypt_cbc");?> <br>
Hash: <?php echo is_fun("mhash_count");?> <br>
<center>DB Supported </center>
MySQL :<?php echo is_fun("mysql_get_server_info");?> <?php
if(function_exists("mysql_get_server_info")) {
  $s = @mysql_get_server_info();
  $s = $s ? '&nbsp; mysql_server Version:'.$s : '';
  $c = '&nbsp; mysql_client Version:'.@mysql_get_client_info();
  echo $s;
}
?> <br>

ODBC : <?php echo is_fun("odbc_close");?> <br>
Oracle : <?php echo is_fun("ora_close");?> <br>
SQL Server : <?php echo is_fun("mssql_close");?> <br> 
dBASE : <?php echo is_fun("dbase_close");?> <br>
mSQL : <?php echo is_fun("msql_close");?> <br> 
SQLite : <?php echo is_fun("sqlite_close"); if(is_fun("sqlite_close") == 'Supported'){echo "&nbsp; Version: ".@sqlite_libversion();}?> <br>
Hyperwave : <?php echo is_fun("hw_close");?> <br> 
Postgre SQL : <?php echo is_fun("pg_close"); ?> <br>
Informix : <?php echo is_fun("ifx_close");?> <br>

<form action="<?php echo $_SERVER[PHP_SELF]."#bottom";?> <br>" method="post">

<center>Server Performance</center>

(Objects)&nbsp;&nbsp;&nbsp;&nbsp; 
(1+1/3Million times)&nbsp;&nbsp;&nbsp;&nbsp;
(PI/3Million times)&nbsp;&nbsp;&nbsp;&nbsp;
(read 10K-file/10000 times)&nbsp;&nbsp;&nbsp;&nbsp;
<br>
Godaddy&nbsp;&nbsp;&nbsp;&nbsp; 
0.217sec.&nbsp;&nbsp;&nbsp;&nbsp;
0.211sec.&nbsp;&nbsp;&nbsp;&nbsp;
0.093sec.&nbsp;&nbsp;&nbsp;&nbsp;
<br>
110MB&nbsp;&nbsp;&nbsp;&nbsp; 
0.242sec.&nbsp;&nbsp;&nbsp;&nbsp;
0.240sec.&nbsp;&nbsp;&nbsp;&nbsp;
0.095sec.&nbsp;&nbsp;&nbsp;&nbsp;
<br>
000webhost&nbsp;&nbsp;&nbsp;&nbsp; 
0.209sec.&nbsp;&nbsp;&nbsp;&nbsp;
0.203sec.&nbsp;&nbsp;&nbsp;&nbsp;
0.099sec.&nbsp;&nbsp;&nbsp;&nbsp;
<br>
<font color="blue">Server&nbsp;&nbsp;&nbsp;&nbsp; </font>
<font color="blue"><?php echo int_test(); ?>&nbsp;&nbsp;&nbsp;&nbsp;</font>
<font color="blue"><?php echo float_test(); ?>&nbsp;&nbsp;&nbsp;&nbsp;</font>
<font color="blue"><?php echo io_test(); ?>&nbsp;&nbsp;&nbsp;&nbsp;</font>
<br>
<br>

<center>MySQL Connect Test</center>
<table width="100%" cellpadding="3" cellspacing="0" align="center"> 
IP:<input type="text" name="host" value="localhost" size="10" />
PORT:<input type="text" name="port" value="3306" size="10" />
USER:<input type="text" name="login" size="10" />
PASSWORD:<input type="password" name="password" size="10" /> 
<input class="btn" type="submit" name="act" value="MySQLTest" /> 
</table>
<?php
if ($_POST['act'] == 'MySQLTest') {
  if(function_exists("mysql_close")==1) {
    $link = @mysql_connect($host.":".$port,$login,$password);
    if ($link){
      echo "<script>alert('OK')</script>";
    } else {
      echo "<script>alert('FAILED')</script>";
    }
  } else {
    echo "<script>alert('Not supported')</script>";
  }
}
?> <br>
<center>FuncTest</center>
<table width="100%" cellpadding="3" cellspacing="0" align="center"> 
Function:
<input type="text" name="funName" size="50" /> 
<input class="btn" type="submit" name="act" align="right" value="FuncTest" /> 
<?php
if ($_POST['act'] == 'FuncTest') {
  echo "<script>alert('$funRe')</script>";
}
?> <br>
</table>
<br>

<center>Email Test</center>
<table width="100%" cellpadding="3" cellspacing="0" align="center"> 
Email:
<input type="text" name="mailAdd" size="50" /> 
<input class="btn" type="submit" name="act" value="EmailTest" /> 
<?php
if ($_POST['act'] == 'EmailTest') {
  echo "<script>alert('$mailRe')</script>";
}
?> <br>
</table>
</form>

<br>
<br>
<div id="footer">
&copy; <?php echo date("Y",time());?>  Powered by <a HREF="http://ltmp.net" target="_blank">ltmp.net</a>  
<?php $run_time = sprintf('%0.4f', get_time() - $time_start);?> <br>
Processed in <?php echo $run_time?>  seconds. <?php echo memory_cost();?>  memory cost.
</div>
<br>
</body>
</html>
