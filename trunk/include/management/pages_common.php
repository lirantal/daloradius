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


?>
