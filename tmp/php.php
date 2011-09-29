<?php

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

$version = "ltmp-1.0";

define('HTTP_HOST', preg_replace('~^www\.~i', '', $_SERVER['HTTP_HOST']));

$time_start = microtime_float();

function memory_usage() {
$memory	 = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
return $memory;
}

function microtime_float() {
$mtime = microtime();
$mtime = explode(' ', $mtime);
return $mtime[1] + $mtime[0];
}

function valid_email($str) {
return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
}

function show($varName) {
switch($result = get_cfg_var($varName)) {
case 0:
return '<font color="red">Not Supported</font>';
break;
case 1:
return 'Supported';
break;
default:
return $result;
break;
}
}

$valInt = isset($_POST['pInt']) ? $_POST['pInt'] : "未测试";
$valFloat = isset($_POST['pFloat']) ? $_POST['pFloat'] : "未测试";
$valIo = isset($_POST['pIo']) ? $_POST['pIo'] : "未测试";

if ($_GET['act'] == "phpinfo") {
phpinfo();
exit();
} elseif($_POST['act'] == "INT Test") {
$valInt = test_int();
} elseif($_POST['act'] == "FLOAT Test") {
$valFloat = test_float();
} elseif($_POST['act'] == "IO Test") {
$valIo = test_io();
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
$funRe = "函数".$_POST['funName']."Result:".isfun($_POST['funName']);
} elseif ($_POST['act'] == 'Email Test') {
$mailRe = "Send";
$mailRe .= (false !== @mail($_POST["mailAdd"], "http://".$_SERVER['SERVER_NAME'].($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']), "This is a test mail!")) ? "OK":"FAIL";
}

function isfun($funName = '') {
if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return 'Error';
return (false !== function_exists($funName)) ? 'Supported' : '<font color="red">Not Supported</font>';
}

function test_int() {
$timeStart = gettimeofday();
for($i = 0; $i < 3000000; $i++) {
$t = 1+1;
}
$timeEnd = gettimeofday();
$time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
$time = round($time, 3)."seconds";
return $time;
}

function test_float() {
$t = pi();
$timeStart = gettimeofday();

for($i = 0; $i < 3000000; $i++) {
sqrt($t);
}

$timeEnd = gettimeofday();
$time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
$time = round($time, 3)." seconds";
return $time;
}

function test_io() {
$fp = @fopen(PHPSELF, "r");
$timeStart = gettimeofday();
for($i = 0; $i < 10000; $i++) {
@fread($fp, 10240);
@rewind($fp);
}
$timeEnd = gettimeofday();
@fclose($fp);
$time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
$time = round($time, 3)." seconds";
return($time);
}

switch(PHP_OS) {
case "Linux":
$sysReShow = (false !== ($sysInfo = sys_linux()))?"show":"none";
break;
case "FreeBSD":
$sysReShow = (false !== ($sysInfo = sys_freebsd()))?"show":"none";
break;
case "WINNT":
$sysReShow = (false !== ($sysInfo = sys_windows()))?"show":"none";
break;
default:
break;
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
if ($days !== 0) $res['uptime'] = $days." days";
if ($hours !== 0) $res['uptime'] .= $hours." hours";
$res['uptime'] .= $min." minutes";

// MEMORY
if (false === ($str = @file("/proc/meminfo"))) return false;
$str = implode("", $str);
preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);

$res['memTotal'] = round($buf[1][0]/1024, 2);
$res['memFree'] = round($buf[2][0]/1024, 2);
$res['memCached'] = round($buf[3][0]/1024, 2);
$res['memUsed'] = ($res['memTotal']-$res['memFree']);
$res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;
$res['memRealUsed'] = ($res['memTotal'] - $res['memFree'] - $res['memCached']);
$res['memRealPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0;

$res['swapTotal'] = round($buf[4][0]/1024, 2);
$res['swapFree'] = round($buf[5][0]/1024, 2);
$res['swapUsed'] = ($res['swapTotal']-$res['swapFree']);
$res['swapPercent'] = (floatval($res['swapTotal'])!=0)?round($res['swapUsed']/$res['swapTotal']*100,2):0;

// LOAD AVG
if (false === ($str = @file("/proc/loadavg"))) return false;
$str = explode(" ", implode("", $str));
$str = array_chunk($str, 4);
$res['loadAvg'] = implode(" ", $str[0]);

return $res;
}

function sys_freebsd() {
//CPU
if (false === ($res['cpu']['num'] = get_key("hw.ncpu"))) return false;
$res['cpu']['model'] = get_key("hw.model");

//LOAD AVG
if (false === ($res['loadAvg'] = get_key("vm.loadavg"))) return false;

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
$res['memTotal'] = round($buf/1024/1024, 2);

$str = get_key("vm.vmtotal");
preg_match_all("/\nVirtual Memory[\:\s]*\(Total[\:\s]*([\d]+)K[\,\s]*Active[\:\s]*([\d]+)K\)\n/i", $str, $buff, PREG_SET_ORDER);
preg_match_all("/\nReal Memory[\:\s]*\(Total[\:\s]*([\d]+)K[\,\s]*Active[\:\s]*([\d]+)K\)\n/i", $str, $buf, PREG_SET_ORDER);

$res['memRealUsed'] = round($buf[0][2]/1024, 2);
$res['memCached'] = round($buff[0][2]/1024, 2);
$res['memUsed'] = round($buf[0][1]/1024, 2) + $res['memCached'];
$res['memFree'] = $res['memTotal'] - $res['memUsed'];
$res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;

$res['memRealPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0;

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

function sys_windows() {
if (PHP_VERSION >= 5) {
$objLocator = new COM("WbemScripting.SWbemLocator");
$wmi = $objLocator->ConnectServer();
$prop = $wmi->get("Win32_PnPEntity");
} else {
return false;
}

//CPU
$cpuinfo = GetWMI($wmi,"Win32_Processor", array("Name","L2CacheSize","NumberOfCores"));
$res['cpu']['num'] = $cpuinfo[0]['NumberOfCores'];
if (null == $res['cpu']['num']) {
$res['cpu']['num'] = 1;
}
for ($i=0;$i<$res['cpu']['num'];$i++){
$res['cpu']['model'] .= $cpuinfo[0]['Name']."<br />";
$res['cpu']['cache'] .= $cpuinfo[0]['L2CacheSize']."<br />";
}
// SYSINFO
$sysinfo = GetWMI($wmi,"Win32_OperatingSystem", array('LastBootUpTime','TotalVisibleMemorySize','FreePhysicalMemory','Caption','CSDVersion','SerialNumber','InstallDate'));
$sysinfo[0]['Caption']=iconv('GBK', 'UTF-8',$sysinfo[0]['Caption']);
$sysinfo[0]['CSDVersion']=iconv('GBK', 'UTF-8',$sysinfo[0]['CSDVersion']);
$res['win_n'] = $sysinfo[0]['Caption']." ".$sysinfo[0]['CSDVersion']." Serial NO.:{$sysinfo[0]['SerialNumber']} from".date('Y-m-d H:i:s',strtotime(substr($sysinfo[0]['InstallDate'],0,14)))."Install";
//UPTIME
$res['uptime'] = $sysinfo[0]['LastBootUpTime'];

$sys_ticks = 3600*8 + time() - strtotime(substr($res['uptime'],0,14));
$min = $sys_ticks / 60;
$hours = $min / 60;
$days = floor($hours / 24);
$hours = floor($hours - ($days * 24));
$min = floor($min - ($days * 60 * 24) - ($hours * 60));
if ($days !== 0) $res['uptime'] = $days." days";
if ($hours !== 0) $res['uptime'] .= $hours." hours";
$res['uptime'] .= $min." minutes";

//MEMORY
$res['memTotal'] = $sysinfo[0]['TotalVisibleMemorySize'];
$res['memFree'] = $sysinfo[0]['FreePhysicalMemory'];
$res['memUsed'] = $res['memTotal'] - $res['memFree'];
$res['memPercent'] = round($res['memUsed'] / $res['memTotal']*100,2);

$swapinfo = GetWMI($wmi,"Win32_PageFileUsage", array('AllocatedBaseSize','CurrentUsage'));

// LoadPercentage
$loadinfo = GetWMI($wmi,"Win32_Processor", array("LoadPercentage"));
$res['loadAvg'] = $loadinfo[0]['LoadPercentage'];

return $res;
}

function GetWMI($wmi,$strClass, $strValue = array()) {
$arrData = array();

$objWEBM = $wmi->Get($strClass);
$arrProp = $objWEBM->Properties_;
$arrWEBMCol = $objWEBM->Instances_();
foreach($arrWEBMCol as $objItem) {
@reset($arrProp);
$arrInstance = array();
foreach($arrProp as $propItem) {
eval("\$value = \$objItem->" . $propItem->Name . ";");
if (empty($strValue)) {
$arrInstance[$propItem->Name] = trim($value);
} else {
if (in_array($propItem->Name, $strValue)) {
$arrInstance[$propItem->Name] = trim($value);
}
}
}
$arrData[] = $arrInstance;
}
return $arrData;
}

function bar($percent) {
?>
<div class="bar"><div class="barli" style="width:<?=$percent?>%">&nbsp;</div></div>
<?php
}

$uptime = $sysInfo['uptime'];
$stime = date("Y-n-j H:i:s");
$df = round(@disk_free_space(".")/(1024*1024*1024),3);

$mt = $sysInfo['memTotal'];
$mu = round($sysInfo['memUsed']/1024,3);
$mf = round($sysInfo['memFree']/1024,3);
$mc = round($sysInfo['memCached']/1024,3);
$st = $sysInfo['swapTotal'];
$su = round($sysInfo['swapUsed']/1024,3);
$sf = round($sysInfo['swapFree']/1024,3);
$swapPercent = $sysInfo['swapPercent'];
$load = $sysInfo['loadAvg'];
$memRealPercent = $sysInfo['memRealPercent'];
$memPercent = $sysInfo['memPercent'];

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

if ($_GET['act'] == "rt")
{
$arr=array('title'=>"$title",'freeSpace'=>"$df",'TotalMemory'=>"$mt",'UsedMemory'=>"$mu",'FreeMemory'=>"$mf",'CachedMemory'=>"$mc",'TotalSwap'=>"$st",'swapUsed'=>"$su",'swapFree'=>"$sf",'loadAvg'=>"$load",'uptime'=>"$uptime",'freetime'=>"$freetime",'bjtime'=>"$bjtime",'stime'=>"$stime",'memRealPercent'=>"$memRealPercent",'memPercent'=>"$memPercent%",'swapPercent'=>"$swapPercent",'barmemRealPercent'=>"$memRealPercent%",'barswapPercent'=>"$swapPercent%",'NetOut2'=>"$NetOut[2]",'NetOut3'=>"$NetOut[3]",'NetOut4'=>"$NetOut[4]",'NetOut5'=>"$NetOut[5]",'NetOut6'=>"$NetOut[6]",'NetOut7'=>"$NetOut[7]",'NetOut8'=>"$NetOut[8]",'NetOut9'=>"$NetOut[9]",'NetOut10'=>"$NetOut[10]",'NetInput2'=>"$NetInput[2]",'NetInput3'=>"$NetInput[3]",'NetInput4'=>"$NetInput[4]",'NetInput5'=>"$NetInput[5]",'NetInput6'=>"$NetInput[6]",'NetInput7'=>"$NetInput[7]",'NetInput8'=>"$NetInput[8]",'NetInput9'=>"$NetInput[9]",'NetInput10'=>"$NetInput[10]");
$jarr=json_encode($arr); 
echo $_GET['callback'],'(',$jarr,')';
exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LTMP.NET PHP INSPECTOR</title>
<!-- Power by: LTMP.NET -->
<style type="text/css">
<!--
* {font-family: Tahoma, "Microsoft Yahei", Arial; }
body{text-align: center; margin: 0 auto; padding: 0; background-color:#FFFFFF;font-size:12px;font-family:Tahoma, Arial}
h1 {font-size: 26px; font-weight: normal; padding: 0; margin: 0; color: #444444;}
h1 small {font-size: 11px; font-family: Tahoma; font-weight: bold; }
a{color: #333333; text-decoration:none;}
a.black{color: #000000; text-decoration:none;}
b{color: #999999;}
table{clear:both;padding: 0; margin: 0 0 10px;border-collapse:collapse; border-spacing: 0;}
th{padding: 3px 6px; font-weight:bold;background:#3066a6;color:#FFFFFF;border:1px solid #3066a6; text-align:left;}
tr{padding: 0; background:#F7F7F7;}
td{padding: 3px 6px; border:1px solid #CCCCCC;}
input{padding: 2px; background: #FFFFFF; border-top:1px solid #666666; border-left:1px solid #666666; border-right:1px solid #CCCCCC; border-bottom:1px solid #CCCCCC; font-size:12px}
input.btn{font-weight: bold; height: 20px; line-height: 20px; padding: 0 6px; color:#666666; background: #f2f2f2; border:1px solid #999;font-size:12px}
.bar {border:1px solid #999999; background:#FFFFFF; height:5px; font-size:2px; width:60%; margin:2px 0 5px 0;padding:1px;}
.barli{background:#36b52a; height:5px; margin:0px; padding:0;}
#page {width: 920px; padding: 0 20px; margin: 0 auto; text-align: left;}
#header{position: relative; padding: 10px;}
#footer {padding: 15px 0; text-align: center; font-size: 11px; font-family: Tahoma, Verdana;}
#download {position: absolute; top: 20px; right: 10px; text-align: right; font-weight: bold; color: #06C;}
#download a {color: #0000FF; text-decoration: underline;}
.w_small{font-family: Courier New;}
.w_number{color: #f800fe;}
-->
</style>
<script language="JavaScript" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.min.js"></script>
<script type="text/javascript"> 
<!--
$(document).ready(function(){getJSONData();});
function getJSONData()
{
setTimeout("getJSONData()", 1000);
$.getJSON('?act=rt&callback=?', displayData);
}
function displayData(dataJSON)
{
$("#title").html(dataJSON.title);
$("#freeSpace").html(dataJSON.freeSpace);
$("#TotalMemory").html(dataJSON.TotalMemory);
$("#UsedMemory").html(dataJSON.UsedMemory);
$("#FreeMemory").html(dataJSON.FreeMemory);
$("#CachedMemory").html(dataJSON.CachedMemory);
$("#TotalSwap").html(dataJSON.TotalSwap);
$("#swapUsed").html(dataJSON.swapUsed);
$("#swapFree").html(dataJSON.swapFree);
$("#swapPercent").html(dataJSON.swapPercent);
$("#loadAvg").html(dataJSON.loadAvg);
$("#uptime").html(dataJSON.uptime);
$("#freetime").html(dataJSON.freetime);
$("#stime").html(dataJSON.stime);
$("#bjtime").html(dataJSON.bjtime);
$("#memRealPercent").html(dataJSON.memRealPercent);
$("#memPercent").html(dataJSON.memPercent);
$("#barmemPercent").width(dataJSON.memPercent);
$("#barmemRealPercent").width(dataJSON.barmemRealPercent);
$("#barswapPercent").width(dataJSON.barswapPercent);
$("#NetOut2").html(dataJSON.NetOut2);
$("#NetOut3").html(dataJSON.NetOut3);
$("#NetOut4").html(dataJSON.NetOut4);
$("#NetOut5").html(dataJSON.NetOut5);
$("#NetOut6").html(dataJSON.NetOut6);
$("#NetOut7").html(dataJSON.NetOut7);
$("#NetOut8").html(dataJSON.NetOut8);
$("#NetOut9").html(dataJSON.NetOut9);
$("#NetOut10").html(dataJSON.NetOut10);
$("#NetInput2").html(dataJSON.NetInput2);
$("#NetInput3").html(dataJSON.NetInput3);
$("#NetInput4").html(dataJSON.NetInput4);
$("#NetInput5").html(dataJSON.NetInput5);
$("#NetInput6").html(dataJSON.NetInput6);
$("#NetInput7").html(dataJSON.NetInput7);
$("#NetInput8").html(dataJSON.NetInput8);
$("#NetInput9").html(dataJSON.NetInput9);
$("#NetInput10").html(dataJSON.NetInput10);	
}
-->
</script>
</head>
<body  onload="startTime()">
<div id="page">
<div id="header">
<h1>PHP INSPECTOR</h1>
<div id="download"><A HREF="http://ltmp.net/ltmp.tar.gz">DOWNLOAD</A></div>
</div>

<table width="100%" cellpadding="3" cellspacing="0">
<tr><th colspan="4">Server Infomation</th></tr>
<tr>
<td>IP</td>
<td colspan="3"><?php echo @get_current_user();?> - <?php echo $_SERVER['SERVER_NAME'];?>(<?=$_SERVER['SERVER_ADDR'];?>)&nbsp;&nbsp;Client IP:<?php echo @$_SERVER['REMOTE_ADDR'];?></td>
</tr>
<tr>
<td>Server ID</td>
<td colspan="3"><?php if($sysInfo['win_n'] != ''){echo $sysInfo['win_n'];}else{echo @php_uname();};?></td>
</tr>
<tr>
<td>OS</td>
<td><?$os = explode(" ", php_uname());?><?=$os[0];?> &nbsp;Kernel: <?=$os[2]?></td>
<td>Engine</td>
<td><?php echo $_SERVER['SERVER_SOFTWARE'];?></td>
</tr>
<tr>
<td width="13%">Time</td>
<td width="37%"><span id="stime">0</span></td>
<td width="13%">Free(DISK)</td>
<td width="37%"><font color='#CC0000'><span id="freeSpace">0</span></font>&nbsp;G</td>
</tr>
<tr>
<td>Language</td>
<td><?php echo getenv("HTTP_ACCEPT_LANGUAGE");?></td>
<td>Port</td>
<td><?php echo $_SERVER['SERVER_PORT'];?></td>
</tr>
<tr>
<td>Email</td>
<td><?php echo $_SERVER['SERVER_ADMIN'];?></td>
<td>Path</td>
<td><?php echo $_SERVER['DOCUMENT_ROOT']. "<br />".$_SERVER['$PATH_INFO'];?></td>
</tr>
<tr>
<td>Zend Optimizer</td>
<td><?php echo (get_cfg_var("zend_optimizer.optimization_level")||get_cfg_var("zend_extension_manager.optimizer_ts")||get_cfg_var("zend_extension_ts")) ? 'Supported' : '<font color="red">Not Supported</font>'; ?></td>
<td>Load Avg.</td>
<td class="w_number"><span id="loadAvg"></span></td>
</tr>
</table>

<?if("show"==$sysReShow){?>
<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="6">CPU/MEMORY</th></tr>
<tr>
<td>CPU</td>
<td><?php echo $sysInfo['cpu']['num'];?>&nbsp;</td>
<td>Uptime</td>
<td><span id="uptime">0</span></td>
<td></td>
<td></td>
</tr>
<tr>
<td>CPU Model</td>
<td><?php echo $sysInfo['cpu']['model'];?></td>
<td>CPU Cache-2</td>
<td><?php echo $sysInfo['cpu']['cache'];?></td>
<td>Bogomips</td>
<td><?=$sysInfo['cpu']['bogomips']?></td>
</tr>
<tr>
<td>Memory</td>
<td colspan="5">
<?php
$tmp = array(
'memTotal', 'memUsed', 'memFree', 'memPercent',
'memCached', 'memRealPercent',
'swapTotal', 'swapUsed', 'swapFree', 'swapPercent'
);
foreach ($tmp AS $v) {
$sysInfo[$v] = $sysInfo[$v] ? $sysInfo[$v] : 0;
}
?>
Physical Memory: Total
<font color='#CC0000'><?=round($sysInfo['memTotal']/1024,2)?></font>
G , Used
<font color='#CC0000'><span id="UsedMemory">0</span></font>
G , Free
<font color='#CC0000'><span id="FreeMemory">0</span></font>
G, Used%
<span id="memPercent">0</span>
<div class="bar"><div id="barmemPercent" class="barli" >&nbsp;</div> </div>
Cache Memory
<span id="CachedMemory">0</span>
G, Real Used%
<span id="memRealPercent">0</span>
%
<div class="bar"><div id="barmemRealPercent" class="barli" >&nbsp;</div></div>
SWAP: Total
<?=round($sysInfo['swapTotal']/1024,2)?>
G, Used
<span id="swapUsed">0</span>
G, Free
<span id="swapFree">0</span>
G, Used%
<span id="swapPercent">0</span>
%
<div class="bar"><div id="barswapPercent" class="barli" >&nbsp;</div> </div>
</td>
</tr>
</table>
<?}?>

<?php if (false !== ($strs = @file("/proc/net/dev"))) : ?>
<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="3">Network</th></tr>
<?php for ($i = 2; $i < count($strs); $i++ ) : ?>
<?php preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );?>
<tr>
<td width="15%"><?=$info[1][0]?> : </td>
<td width="47%">Inbound : <font color='#CC0000'><span id="NetInput<?=$i?>">0</span></font> G</td>
<td width="47%">OutBound : <font color='#CC0000'><span id="NetOut<?=$i?>">0</span></font> G</td>
</tr>
<?php endfor; ?>
</table>
<?php endif; ?>

<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr>
<th colspan="4">Moduals Compiled</th>
</tr>
<tr>
<td colspan="4"><span class="w_small">
<?php
$able=get_loaded_extensions();
foreach ($able as $key=>$value) {
if ($key!=0 && $key%13==0) {
echo '<br />';
}
echo "$value&nbsp;&nbsp;";
}
?></span>
</td>
</tr>
</table>
<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="4">PHP INFORMATION</th></tr>
<tr>
<td width="35%">PHPINFO:</td>
<td width="15%">
<?php
$phpSelf = $_SERVER[PHP_SELF] ? $_SERVER[PHP_SELF] : $_SERVER[SCRIPT_NAME];
$disFuns=get_cfg_var("disable_functions");
?>
<?php echo (false!==eregi("phpinfo",$disFuns))? 'NO' :"<a href='$phpSelf?act=phpinfo' target='_blank'>PHPINFO</a>";?>
</td>
<td width="35%">PHP Version:</td>
<td width="15%"><?php echo PHP_VERSION;?></td>
</tr>
<tr>
<td>PHP Run:</td>
<td><?php echo strtoupper(php_sapi_name());?></td>
<td>Memory Limit:</td>
<td><?php echo show("memory_limit");?></td>
</tr>
<tr>
<td>Safe Mode:</td>
<td><?php echo show("safe_mode");?></td>
<td>Post Max Size:</td>
<td><?php echo show("post_max_size");?></td>
</tr>
<tr>
<td>Upload Max Filesize:</td>
<td><?php echo show("upload_max_filesize");?></td>
<td>Precision:</td>
<td><?php echo show("precision");?></td>
</tr>
<tr>
<td>Max Execution Time:</td>
<td><?php echo show("max_execution_time");?> seconds</td>
<td>Default Socket Timeout:</td>
<td><?php echo show("default_socket_timeout");?> seconds</td>
</tr>
<tr>
<td>Doc Root:</td>
<td><?php echo show("doc_root");?></td>
<td>User Dir:</td>
<td><?php echo show("user_dir");?></td>
</tr>
<tr>
<td>dl() Func:</td>
<td><?php echo show("enable_dl");?></td>
<td>Include Path:</td>
<td><?php echo show("include_path");?></td>
</tr>
<tr>
<td>Display Errors:</td>
<td><?php echo show("display_errors");?></td>
<td>Register Globals:</td>
<td><?php echo show("register_globals");?></td>
</tr>
<tr>
<td>Magic Quotes Gpc）:</td>
<td><?php echo show("magic_quotes_gpc");?></td>
<td>"&lt;?...?&gt;"Short open Tag:</td>
<td><?php echo show("short_open_tag");?></td>
</tr>
<tr>
<td>"&lt;% %&gt;"ASP Tags:</td>
<td><?php echo show("asp_tags");?></td>
<td>Ignore Repeated Errors:</td>
<td><?php echo show("ignore_repeated_errors");?></td>
</tr>
<tr>
<td>Ignore Repeated Source:</td>
<td><?php echo show("ignore_repeated_source");?></td>
<td>Report Memleaks:</td>
<td><?php echo show("report_memleaks");?></td>
</tr>
<tr>
<td>Magic Quotes Gpc）:</td>
<td><?php echo show("magic_quotes_gpc");?></td>
<td>Magic Quotes Runtime:</td>
<td><?php echo show("magic_quotes_runtime");?></td>
</tr>
<tr>
<td>Allow Url Fopen:</td>
<td><?php echo show("allow_url_fopen");?></td>
<td>Register Argc Argv:</td>
<td><?php echo show("register_argc_argv");?></td>
</tr>
<tr>
<td colspan="4">Disable Functions: <?=(""==($disFuns=get_cfg_var("disable_functions")))?"No":str_replace(",",", ",$disFuns)?></td>
</tr>
</table>

<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="4">Component Supported</th></tr>
<tr>
<td width="30%">FTP Supported:</td>
<td width="20%"><?php echo isfun("ftp_login");?></td>
<td width="30%">XML Supported:</td>
<td width="20%"><?php echo isfun("xml_set_object");?></td>
</tr>
<tr>
<td>Session Supported:</td>
<td><?php echo isfun("session_start");?></td>
<td>Socket Supported:</td>
<td><?php echo isfun("socket_accept");?></td>
</tr>
<tr>
<td>ZEND Optimizer Supported:</td>
<td><?php echo (get_cfg_var("zend_optimizer.optimization_level")||get_cfg_var("zend_extension_manager.optimizer_ts")||get_cfg_var("zend.ze1_compatibility_mode")||get_cfg_var("zend_extension_ts"))?'Supported':'<font color="red">Not Supported</font>';?></td>
<td>Allow URL Fopen:</td>
<td><?php echo show("allow_url_fopen");?></td>
</tr>
<tr>
<td>GD Supported:</td>
<td><?php echo isfun("gd_info");?>
<?php
if(function_exists(gd_info)) {
$gd_info = @gd_info();
$gd_info = $gd_info["GD Version"];
echo $gd_info ? '&nbsp; Version:'.$gd_info : '';
}
?></td>
<td>Supported(Zlib):</td>
<td><?php echo isfun("gzclose");?></td>
</tr>
<tr>
<td>IMAP:</td>
<td><?php echo isfun("imap_close");?></td>
<td>Canlendar:</td>
<td><?php echo isfun("JDToGregorian");?></td>
</tr>
<tr>
<td>Regex:</td>
<td><?php echo isfun("preg_match");?></td>
<td>WDDX Supported:</td>
<td><?php echo isfun("wddx_add_vars");?></td>
</tr>
<tr>
<td>Iconv:</td>
<td><?php echo isfun("iconv");?></td>
<td>mbstring:</td>
<td><?php echo isfun("mb_eregi");?></td>
</tr>
<tr>
<td>BC :</td>
<td><?php echo isfun("bcadd");?></td>
<td>LDAP:</td>
<td><?php echo isfun("ldap_close");?></td>
</tr>
<tr>
<td>MCrypt:</td>
<td><?php echo isfun("mcrypt_cbc");?></td>
<td>Hash:</td>
<td><?php echo isfun("mhash_count");?></td>
</tr>
</table>

<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="4">DB Supported</th></tr>
<tr>
<td width="30%">MySQL :</td>
<td width="20%"><?php echo isfun("mysql_close");?>
<?php
if(function_exists("mysql_get_server_info")) {
$s = @mysql_get_server_info();
$s = $s ? '&nbsp; mysql_server Version:'.$s : '';
$c = '&nbsp; mysql_client Version:'.@mysql_get_client_info();
echo $s;
}
?>
</td>
<td width="30%">ODBC :</td>
<td width="20%"><?php echo isfun("odbc_close");?></td>
</tr>
<tr>
<td>Oracle :</td>
<td><?php echo isfun("ora_close");?></td>
<td>SQL Server :</td>
<td><?php echo isfun("mssql_close");?></td>
</tr>
<tr>
<td>dBASE :</td>
<td><?php echo isfun("dbase_close");?></td>
<td>mSQL :</td>
<td><?php echo isfun("msql_close");?></td>
</tr>
<tr>
<td>SQLite :</td>
<td><?php echo isfun("sqlite_close"); if(isfun("sqlite_close") == 'Supported'){echo "&nbsp; Version: ".@sqlite_libversion();}?></td>
<td>Hyperwave :</td>
<td><?php echo isfun("hw_close");?></td>
</tr>
<tr>
<td>Postgre SQL :</td>
<td><?php echo isfun("pg_close"); ?></td>
<td>Informix :</td>
<td><?php echo isfun("ifx_close");?></td>
</tr>
</table>
<form action="<?php echo $_SERVER[PHP_SELF]."#bottom";?>" method="post">

<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="5">Server Performance</th></tr>
<tr align="center">
<td width="19%">Objects</td>
<td width="17%">Int Test<br />(1+1/3Million times)</td>
<td width="17%">FLOAT Test<br />(PI/3Million times)</td>
<td width="17%">I/O Test<br />(read 10K-file/10000 times)</td>
<td width="30%">CPU</td>
</tr>
<tr align="center">
<td align="left"><a href="http://www.ltmp.net/" class="black">PhotonVPS.com</a></td>
<td>0.431 seconds</td>
<td>1.024 seconds</td>
<td>0.034 seconds</td>
<td align="left">8 x Xeon E5520 @ 2.27GHz</td>
</tr>
<tr align="center">
<td align="left"><a href="http://www.ltmp.net/" class="black">SpaceRich.com</a></td>
<td>0.421 seconds</td>
<td>1.003 seconds</td>
<td>0.038 seconds</td>
<td align="left">4 x Core i7 920 @ 2.67GHz</td>
</tr>
<tr align="center">
<td align="left"><a href="http://www.ltmp.net/" class="black">RiZie.com</a></td>
<td>0.521 seconds</td>
<td>1.559 seconds</td>
<td>0.054 seconds</td>
<td align="left">2 x Pentium4 3.00GHz</td>
</tr>
<tr align="center">
<td align="left"><a href="http://www.ltmp.net/" class="black">CitynetHost.com</a></td>
<td>0.343 seconds</td>
<td>0.761 seconds</td>
<td>0.023 seconds</td>
<td align="left">2 x Core2Duo E4600 @ 2.40GHz</td>
</tr>
<tr align="center">
<td align="left"><a href="http://www.ltmp.net/" class="black">IXwebhosting.com</a></td>
<td>0.535 seconds</td>
<td>1.607 seconds</td>
<td>0.058 seconds</td>
<td align="left">4 x Xeon E5530 @ 2.40GHz</td>
</tr>
<tr align="center">
<td>Server</td>
<td><?php echo $valInt;?><br /><input class="btn" name="act" type="submit" value="IntTest" /></td>
<td><?php echo $valFloat;?><br /><input class="btn" name="act" type="submit" value="FloatTest" /></td>
<td><?php echo $valIo;?><br /><input class="btn" name="act" type="submit" value="IOTest" /></td>
<td></td>
</tr>
</table>
<input type="hidden" name="pInt" value="<?php echo $valInt;?>" />
<input type="hidden" name="pFloat" value="<?php echo $valFloat;?>" />
<input type="hidden" name="pIo" value="<?php echo $valIo;?>" />

<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="3">MySQL Connect Test</th></tr>
<tr>
<td width="15%"></td>
<td width="60%">
IP:<input type="text" name="host" value="localhost" size="10" />
PORT:<input type="text" name="port" value="3306" size="10" />
USER:<input type="text" name="login" size="10" />
PASSWORD:<input type="password" name="password" size="10" />
</td>
<td width="25%">
<input class="btn" type="submit" name="act" value="MySQLTest" />
</td>
</tr>
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
echo "<script>alert('Not Supported')</script>";
}
}
?>
<!--FuncTest-->
<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="3">FuncTest</th></tr>
<tr>
<td width="15%"></td>
<td width="60%">
Function:
<input type="text" name="funName" size="50" />
</td>
<td width="25%">
<input class="btn" type="submit" name="act" align="right" value="FuncTest" />
</td>
</tr>
<?php
if ($_POST['act'] == 'FuncTest') {
echo "<script>alert('$funRe')</script>";
}
?>
</table>

<table width="100%" cellpadding="3" cellspacing="0" align="center">
<tr><th colspan="3">Email Test</th></tr>
<tr>
<td width="15%"></td>
<td width="60%">
Email:
<input type="text" name="mailAdd" size="50" />
</td>
<td width="25%">
<input class="btn" type="submit" name="act" value="EmailTest" />
</td>
</tr>
<?php
if ($_POST['act'] == 'EmailTest') {
echo "<script>alert('$mailRe')</script>";
}
?>
</table>
</form>
<a id="bottom"></a>

<div id="footer">
&copy; <?php echo date("Y",time());?> Powered by <A HREF="http://www.ltmp.net" target="_blank">ltmp.net</A> <?php echo $version; ?> .<br />
<?php $run_time = sprintf('%0.4f', microtime_float() - $time_start);?>
Processed in <?=$run_time?> seconds. <?=memory_usage();?> memory usage.
</div>
</div>
</body>
</html>
