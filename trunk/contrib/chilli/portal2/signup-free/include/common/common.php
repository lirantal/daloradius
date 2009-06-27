<?php
/*********************************************************************
* Name: common.php
* Author: Liran tal <liran.tal@gmail.com>
*
* Provides common functions
* adopted from daloRADIUS project
*
*********************************************************************/


/* returns a random alpha-numeric string of length $length */
function createPassword($length, $chars) {
	
	if (!$chars)
		$chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";
		
	if (!$length)
		$length = 8;
	
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



// set of functions to ease the usage of escaping " chars in echo or print functions
// thanks to php.net
function qq($text) {return str_replace('`','"',$text); }
function printq($text) { print qq($text); }
function printqn($text) { print qq($text)."\n"; }

?>
