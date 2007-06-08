<?php
/*******************************************************************
* Extension name: exten server information                         *
*                                                                  *
* Description:                                                     *
* this script process some important server information and        *
* displays it                                                      *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

echo "<b> System Name: </b>";
echo "<br/>";
system("uname -a");
echo "<br/>";
echo "<br/>";

echo "<b> System Uptime: </b>";
echo "<br/>";
$ret = shell_exec("uptime");
echo $ret;
echo "<br/>";
echo "<br/>";

echo "<b> CPU: </b>";
echo "<br/>";
$ret = shell_exec("cat /proc/cpuinfo | egrep \"vendor|model name|cpu MHz\"");
$ret = eregi_replace("\n", "<br>", $ret);
echo $ret;
echo "<br/>";
echo "<br/>";

echo "<b> Memory:</b>";
echo "<br/>";
$ret = shell_exec("free -m");
$ret = eregi_replace("\n", "<br>", $ret);
echo $ret;
echo "<br/>";
echo "<br/>";


echo "<b> Disk Usage: </b>";
echo "<br/>";
$ret = shell_exec("df -h");
$ret = eregi_replace("\n", "<br>", $ret);
echo $ret;
echo "<br/>";
echo "<br/>";

?>

