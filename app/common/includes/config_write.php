<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Description:    writes configuration information from the $configValues array
 *                 to the daloradius.conf.php configuration file
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/common/includes/config_write.php') !== false) {
    http_response_code(404);
    exit;
}

// useful variables
$configFile = __DIR__ . '/daloradius.conf.php';
clearstatcache(true, $_configFile);
$date = date("D M j G:i:s T Y");

//
// generating file contents
//

// 1. open
$fileContents = <<<EOL
<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Description:          daloRADIUS Configuration File
 *
 * Modification Date:    {$date}
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos(\$_SERVER['PHP_SELF'], '/common/includes/daloradius.conf.php') !== false) {
    http_response_code(404);
    exit;
}


EOL;

// 2. body
foreach ($configValues as $_configOption => $_configElem) {
    $fileContents .= sprintf("\$configValues['%s'] =", $_configOption);

    if (is_array($configValues[$_configOption])) {
        $fileContents .= str_repeat(" ", 8) . sprintf("%s;\n", var_export($configValues[$_configOption], true));
    } else {
        $fileContents .= sprintf(" '%s';\n", addslashes($configValues[$_configOption]));
    }
}

// 3. close
$fileContents .= <<<EOL

?>

EOL;

//
// putting contents into file
//
$writtenBytes = intval(file_put_contents($configFile, $fileContents));

if ($writtenBytes > 0) {
    $successMsg = "Configuration file has been successfully updated";
} else {
    $failureMsg = sprintf("Could not open the file for writing: <strong>%s</strong>", $configFile)
                . "<br>Check file permissions. The file should be writable by the webserver's user/group";
}

?>
