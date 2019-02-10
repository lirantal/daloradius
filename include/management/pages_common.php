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
function createPassword($length, $chars) {
	
	if (!$chars)
		$chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";
		
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= ($length - 1)) {
        $num = rand() % (strlen($chars));
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
	$str = preg_replace("/, $/",'',$str);
	return $str;
}



// return next billing date (Y-m-d format) based on
// the billing recurring period and billing schedule type 
function getNextBillingDate($planRecurringBillingSchedule = "Fixed", $planRecurringPeriod, $billDates = null) {
	
	// initialize next bill date string (Y-m-d style)
	
	if ($billDates == null) {
		$nextBillDate = "0000-00-00";
		$prevBillDate = "0000-00-00";
	} else {
		$nextBillDate = $billDates['nextBillDate'];
		$prevBillDate = $billDates['prevBillDate'];
	}
	
	switch ($planRecurringBillingSchedule) {
	
		case "Anniversary":
			switch ($planRecurringPeriod) {
				case "Daily":
					// current day is the start of the period and it's also the end of it
					// confused? so are we!
					$nextBillDate = date('Y-m-d', strtotime("+1 day"));
					break;
				case "Weekly":
					// add 1 week
					$nextBillDate = date('Y-m-d', strtotime("+1 week"));
					break;
				case "Monthly":
					// add 1 month of time
					$nextBillDate = date('Y-m-d', strtotime("+1 month"));
					break;
				case "Quarterly":
					// add 3 months worth of time
					$nextBillDate = date('Y-m-d', strtotime("+3 month"));
					break;
				case "Semi-Yearly":
					// add 6 months worth of time 
					$nextBillDate = date('Y-m-d', strtotime("+6 month"));
					break;
				case "Yearly":
					// add 1 year (same month/day, next year)
					$nextBillDate = date('Y-m-d', strtotime("+1 year"));
					break;
			}
			break;						
		
		case "Fixed":
		default:
			switch ($planRecurringPeriod) {
				case "Daily":
					// current day is the start of the period and it's also the end of it
					// confused? so are we!
					$nextBillDate = date("Y-m-d");
					break;
				case "Weekly":
					// set to the end of this week
					// +6 used to get the last day of this week, +7 will be the start of next week (i.e: sunday)
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - date('w') + 6, date('Y')));
					break;
				case "Monthly":
					// set to the end of the current month
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('t'), date('Y')));
					break;
				case "Quarterly":
					// set to the end of this quarter
					$currMonth = (int)date('n');
					$quarterMonth = 1;
					if ( ($currMonth >= 1) && ($currMonth <= 3) )
						$quarterMonth = 3;
					if ( ($currMonth >= 4) && ($currMonth <= 6) )
						$quarterMonth = 6;
					if ( ($currMonth >= 7) && ($currMonth <= 9) )
						$quarterMonth = 9;
					if ( ($currMonth >= 10) && ($currMonth <= 12) )
						$quarterMonth = 12;
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, $quarterMonth, date('t', mktime(0,0,0, $quarterMonth, 1, date('Y'))), date('Y')));
					break;
				case "Semi-Yearly":
					$currMonth = (int)date('n');
					$quarterMonth = 1;
					if ( ($currMonth >= 1) && ($currMonth <= 6) )
						$quarterMonth = 6;
					else if ( ($currMonth >= 7) && ($currMonth <= 12) )
						$quarterMonth = 12;
					
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, $quarterMonth, (date('t', mktime(0,0,0, $quarterMonth, 1, date('Y')))), date('Y')));
					break;
				case "Yearly":
					// set to the end of the year
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, 12, (date('t', mktime(0,0,0, 12, 1, date('Y')))), date('Y')));
					break;
			}							
			
			break;
			
	}
	
	return $nextBillDate;
	
}











// return prev/start billing date (Y-m-d format) based on
// the billing recurring period and billing schedule type 
function getPrevBillingDate($planRecurringBillingSchedule = "Fixed", $planRecurringPeriod, $billDates = null) {
	
	// initialize next bill date string (Y-m-d style)
	
	if ($billDates == null) {
		$nextBillDate = "0000-00-00";
		$prevBillDate = "0000-00-00";
	} else {
		$nextBillDate = $billDates['nextBillDate'];
		$prevBillDate = $billDates['prevBillDate'];
	}
	
	switch ($planRecurringBillingSchedule) {
	
		case "Anniversary":
			switch ($planRecurringPeriod) {
				case "Daily":
					// current day is the start of the period and it's also the end of it
					// confused? so are we!
					$nextBillDate = date('Y-m-d', strtotime("+1 day"));
					break;
				case "Weekly":
					// add 1 week
					$nextBillDate = date('Y-m-d', strtotime("+1 week"));
					break;
				case "Monthly":
					// add 1 month of time
					$nextBillDate = date('Y-m-d', strtotime("+1 month"));
					break;
				case "Quarterly":
					// add 3 months worth of time
					$nextBillDate = date('Y-m-d', strtotime("+3 month"));
					break;
				case "Semi-Yearly":
					// add 6 months worth of time 
					$nextBillDate = date('Y-m-d', strtotime("+6 month"));
					break;
				case "Yearly":
					// add 1 year (same month/day, next year)
					$nextBillDate = date('Y-m-d', strtotime("+1 year"));
					break;
			}
			break;						
		
		case "Fixed":
		default:
			switch ($planRecurringPeriod) {
				case "Daily":
					// current day is the start of the period and it's also the end of it
					// confused? so are we!
					$nextBillDate = date("Y-m-d");
					break;
				case "Weekly":
					// set to the start of this week
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - date('w'), date('Y')));
					break;
				case "Monthly":
					// set to the start of the current month
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, date('m'), 01, date('Y')));
					break;
				case "Quarterly":
					// set to the start of this quarter
					$currMonth = (int)date('n');
					$quarterMonth = 1;
					if ( ($currMonth >= 1) && ($currMonth <= 3) )
						$quarterMonth = 1;
					if ( ($currMonth >= 4) && ($currMonth <= 6) )
						$quarterMonth = 4;
					if ( ($currMonth >= 7) && ($currMonth <= 9) )
						$quarterMonth = 7;
					if ( ($currMonth >= 10) && ($currMonth <= 12) )
						$quarterMonth = 10;
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, $quarterMonth, date(01, mktime(0,0,0, $quarterMonth, 1, date('Y'))), date('Y')));
					break;
				case "Semi-Yearly":
					// set to the start of the occurrence of either half of the year (start of june or start of january)
					$currMonth = (int)date('n');
					$quarterMonth = 1;
					if ( ($currMonth >= 1) && ($currMonth <= 6) )
						$quarterMonth = 1;
					else if ( ($currMonth >= 7) && ($currMonth <= 12) )
						$quarterMonth = 6;
					
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, $quarterMonth, 01, date('Y')));
					break;
				case "Yearly":
					// set to the start of the year
					$nextBillDate = date('Y-m-d', mktime(0, 0, 0, 01, 01, date('Y')));
					break;
			}							
			
			break;
			
	}
	
	return $nextBillDate;
	
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
