<?php
/*********************************************************************
* Name: pages_common.php
* Author: Liran tal <liran.tal@gmail.com>
*
* Provides common operations on different management pages and other
* categories
*
*********************************************************************/


/* returns a random alpha-numeric string of length $length */
function createPassword($length) {

    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= ($length - 1)) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;

}





/* convert byte to to size */
/* function contributed by ugenk (Evgeniy Kozhuhovskiy <ugenk@xdsl.by>) */

function toxbyte($size)
{
        // Gigabytes
        if ( $size > 1073741824 )
        {
                $ret = $size / 1073741824;
                $ret = round($ret,2)." Gb";
                return $ret;
        }

        // Megabytes
        if ( $size > 1048576 )
        {
                $ret = $size / 1048576;
                $ret = round($ret,2)." Mb";
                return $ret;
        }

        // Kilobytes
        if ($size > 1024 )
        {
                $ret = $size / 1024;
                $ret = round($ret,2)." Kb";
                return $ret;
        }

        // Bytes
        if ( ($size != "") && ($size <= 1024 ) )
        {
                $ret = $size." B";
                return $ret;
        }

}


// set of functions to ease the usage of escaping " chars in echo or print functions
// thanks to php.net
function qq($text) {return str_replace('`','"',$text); }
function printq($text) { print qq($text); }
function printqn($text) { print qq($text)."\n"; }



// function taken from dialup_admin
function time2str($time) {

	$str = "";				// initialize variable
	$time = floor($time);
	if (!$time)
		return "0 seconds";
	$d = $time/86400;
	$d = floor($d);
	if ($d){
		$str .= "$d days, ";
		$time = $time % 86400;
	}
	$h = $time/3600;
	$h = floor($h);
	if ($h){
		$str .= "$h hours, ";
		$time = $time % 3600;
	}
	$m = $time/60;
	$m = floor($m);
	if ($m){
		$str .= "$m minutes, ";
		$time = $time % 60;
	}
	if ($time)
		$str .= "$time seconds, ";
	$str = preg_replace(', $','',$str);
	return $str;
}




/*
 * wrapper function to add a tooltip balloon
 *
 * @param		$view			array of view parameters
 * @return		$string			returns string
 */
function addToolTipBalloon($view) {

	if ($view['divId'])
		$viewId = '<div id="'.$view['divId'].'">Loading...</div>';
	else
		$viewId = '';
	
	$sep = ($view['onClick'] != '' && substr($view['onClick'], -1) != ';' ? ';' : '');
	
	$str = "<a class='tablenovisit' href='#'
				onClick=\"".$view['onClick'].$sep."return false;\"
                tooltipText='".$view['content']."
							<br/><br/>
							$viewId
							<br/>'
			>".$view['value']."</a>";

	return $str;
}



?>
