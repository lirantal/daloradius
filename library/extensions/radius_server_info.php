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
 * Description:    this script uses pgrep to check if services stored
 *                 in $services_to_check are running
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/extensions/radius_server_info.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

// given the $service_name, this function returns true if that service is running
function check_service($service_name) {
    if (empty($service_name)) {
        return false;
    }

    $command = sprintf("pgrep %s", escapeshellarg($service_name));
    exec($command, $output, $result_code);
    return $result_code === 0;
}

$services_to_check = array("FreeRADIUS", "MySQL", "MariaDB", "SSHd");

$table = array( 'title' => 'Service Status', 'rows' => array() );

foreach ($services_to_check as $service_name) {
    $running = check_service(strtolower($service_name));
    $class = ($running) ? "text-success" : "text-danger";
    $label = ($running) ? "running" : "not running";
    
    $value = sprintf('<span class="%s">%s</span>', $class, $label);
    
    $table['rows'][] = array( $service_name, $value);
}

print_simple_table($table);
