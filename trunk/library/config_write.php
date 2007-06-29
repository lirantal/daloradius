<?php

$configFile = "library/daloradius.conf";
$commentChar = "#";

$fp = fopen($configFile, "w");

foreach ($configValues as $option => $elem) {

        fwrite($fp, $option . " = " . $configValues[$option] . "\n");
}

fclose($fp);



?>
