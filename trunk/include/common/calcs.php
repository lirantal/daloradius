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
$s = ($s_remaining - ($m * minutes));

$myTime = $h.":".$m.":".$s;
return $myTime;

}

/*
no permission was given to use this code yet...
function sec2hms ($sec, $padHours = false) {
// this code was adopted from Jon: http://www.laughing-buddha.net/jon/php/sec2hms/

	// holds formatted string
	$hms = "";

	// there are 3600 seconds in an hour, so if we
	// divide total seconds by 3600 and throw away
	// the remainder, we've got the number of hours
	$hours = intval(intval($sec) / 3600); 

	// add to $hms, with a leading 0 if asked for
	$hms .= ($padHours) 
		  ? str_pad($hours, 2, "0", STR_PAD_LEFT). ':'
		  : $hours. ':';
	 
	// dividing the total seconds by 60 will give us
	// the number of minutes, but we're interested in 
	// minutes past the hour: to get that, we need to 
	// divide by 60 again and keep the remainder
	$minutes = intval(($sec / 60) % 60); 

	// then add to $hms (with a leading 0 if needed)
	$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ':';

	// seconds are simple - just divide the total
	// seconds by 60 and keep the remainder
	$seconds = intval($sec % 60); 

	// add to $hms, again with a leading 0 if needed
	$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

	// done!
	return $hms;
    
}
*/

?>

