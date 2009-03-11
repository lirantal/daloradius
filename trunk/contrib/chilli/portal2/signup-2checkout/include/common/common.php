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


// set of functions to ease the usage of escaping " chars in echo or print functions
// thanks to php.net
function qq($text) {return str_replace('`','"',$text); }
function printq($text) { print qq($text); }
function printqn($text) { print qq($text)."\n"; }



/*****************************************************************************************
 * logToFile()
 * logs all post variables to file
 *
 * $customMsg       custom text to be written to the log file
 * $logFile			destination log file
 *****************************************************************************************/
function logToFile($customMsg, $logFile) {

    include('library/config_read.php');

    $myTime = date("F j, Y, g:i a");

    $fh = fopen($logFile, 'a');

    if ($fh) {
        fwrite($fh, $myTime ." - ". $customMsg);

        $str = $myTime . " *** MERCHANT TRANSACTION BEGIN \n";
        fwrite($fh, $str);

        //loop through the $_POST array and print all vars to the screen.
        foreach($_POST as $key => $value){
            $postdata = $myTime ." - ". $key." = ". $value."\n";
                fwrite($fh, $postdata);
        }

        $str = $myTime . " *** MERCHANT TRANSACTION END \n\n";
        fwrite($fh, $str);

        fclose($fh);
    }
}



?>
