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
 * Description:
 *        this script uses pgrep to check if services stored
 *              in $services_to_check are running
 *
 * Authors:    Liran Tal <liran@enginx.com>
 *              Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/exten-radius_server_info.php') !== false) {
    header("Location: ../index.php");
    exit;
}

// given the $service_name, this function returns "Running" if that service is running
function check_service($service_name) {
    if (empty($service_name)) {
        return "no service name";
    }

    $command = sprintf("pgrep %s", escapeshellarg($service_name));
    exec($command, $output, $result_code);
    return ($result_code === 0) ? "Running" : "Not running";
}

$services_to_check = array("FreeRADIUS", "MySQL", "MariaDB");

?>

<h3>Service Status</h3>
<table class="summarySection">
<?php
    $format = '<tr><td class="summaryKey">%s</td><td class="summaryValue"><span class="sleft">%s'
            . "</span></td></tr>\n";
    foreach ($services_to_check as $service_name) {
        printf($format, $service_name, check_service(strtolower($service_name)));
    }
?>
</table>
