<?php

$configFile = "library/daloradius.conf";
$commentChar = "#";

$fp = fopen($configFile, "r");

while (!feof($fp)) {
  $line = trim(fgets($fp));
  if ($line && !ereg("^$commentChar", $line)) {
    $pieces = explode("=", $line);
    $option = trim($pieces[0]);
    $value = trim($pieces[1]);
    $configValues[$option] = $value;
  }
}
fclose($fp);

?>
