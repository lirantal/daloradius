<?php
/*********************************************************************
* Name: calcs.php
* Author: Liran tal <liran.tal@gmail.com>
*
* Provides useful calculations for accounting pages and such
*
*********************************************************************/


function seconds2time ($seconds) {

$hours = 3600;
$minutes = 60;

$h = intval($seconds / $hours);
$s_remaining = ($seconds - ($h * $hours));
$m = intval($s_remaining / $minutes);
$s = ($s_remaining - ($m * $minutes));

$myTime = $h.":".$m.":".$s;
return $myTime;

}





function bytes2megabytes ($bytes) {

return intval(($bytes/1024/1024));

?>

