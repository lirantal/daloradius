<?php

$configFile = "library/daloradius.confe";
$commentChar = "#";

$fp = fopen($configFile, "w");

foreach ($configValues as $option => $elem) {

        fwrite($fp, $option . " = " . $configValues[$option] . "\n");
}

fclose($fp);



?>
