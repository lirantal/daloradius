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
        if ($size <= 1024 )
        {
                $ret = $size." B";
                return $ret;
        }

}




?>
