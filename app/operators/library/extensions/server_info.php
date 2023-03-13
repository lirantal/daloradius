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
 * Description:    this script process some important server information and displays it
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Carlos Cesario <carloscesario@gmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
$extension_file = '/library/extensions/server_info.php';
if (strpos($_SERVER['PHP_SELF'], $extension_file) !== false) {
    header("Location: ../../index.php");
    exit;
}


// returns system name and version
function get_system_name_and_version() {
    $command = "cat /etc/*release | grep ^NAME\= | cut -d'=' -f2- | tr -d '\"'";
    exec($command, $output, $result_code);
    if ($result_code !== 0) {
        return "(n/d)";
    }
    
    $result = $output[0];
    $output = null;
    $result_code = null;
    
    $command = "cat /etc/*release | grep ^VERSION\= | cut -d'=' -f2- | tr -d '\"'";
    exec($command, $output, $result_code);
    if ($result_code === 0) {
        $result .= sprintf(", version %s", $output[0]);
    }
    
    return $result;
}


// Display uptime system
// @return string Return uptime system
function uptime() {
    $file_name = "/proc/uptime";

    $fopen_file = fopen($file_name, 'r');
    $buffer = explode(' ', fgets($fopen_file, 4096));
    fclose($fopen_file);

    $sys_ticks = trim($buffer[0]);
    $min = $sys_ticks / 60;
    $hours = $min / 60;
    $days = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $min = floor($min - ($days * 60 * 24) - ($hours * 60));
    $result = "";

    if ($days != 0) {
        $result .= $days;
        $result .= ($days > 1) ? " days " : " day ";
    }

    if ($hours != 0) {
        $result .= $hours;
        $result .= ($hours > 1) ? " hours " : " hour ";
    }

    if ($min > 1 || $min == 0)
        $result .= "$min " . " minutes ";
    elseif ($min == 1)
        $result .= "$min " . " minute ";

    return $result;
}


// Display hostname system
// @return string System hostname or none
function get_hostname() {
    $file_name = "/proc/sys/kernel/hostname";

    if ($fopen_file = fopen($file_name, 'r')) {
        $result = trim(fgets($fopen_file, 4096));
        fclose($fopen_file);
    } else {
        $result = "(n/d)";
    }

    return $result;
}


// Display currenty date/time
// @return string Current system date/time or none
function get_datetime() {
    $today = date("F j, Y, g:i a");
    return ($today) ? $today : "(n/d)";
}



// Get System Load Average
// @return array System Load Average
function get_system_load() {
    $file_name = "/proc/loadavg";
    $result = "";
    $output = "";

    // get the /proc/loadavg information
    if ($fopen_file = fopen($file_name, 'r')) {
        $result = trim(fgets($fopen_file, 256));
        fclose($fopen_file);
    } else {
        $result = "(n/d)";
    }

    $loadavg = explode(" ", $result);
    $output .= $loadavg[0] . " " . $loadavg[1] . " " . $loadavg[2] . "<br/>";


    // get information the 'top' program
    $file_name = "top -b -n1 | grep \"Tasks:\" -A1";
    $result = "";

    if ($popen_file = popen($file_name, 'r')) {
        $result = trim(fread($popen_file, 2048));
        pclose($popen_file);
    } else {
        $result = "(n/d)";
    }

    $result = str_replace("\n", "<br/>", $result);
    $output .= $result;

    return $output;
}


// Get Memory System MemTotal|MemFree
// @return array Memory System MemTotal|MemFree
function get_memory() {
    $units = array( 'kB' => 10, 'MB' => 20, 'GB' => 30, 'TB' => 40 );
    $result = array();
    $proc_meminfo = explode("\n", file_get_contents("/proc/meminfo"));
    
    if (empty($proc_meminfo)) {
        return $result;
    }
    
    foreach ($proc_meminfo as $line) {
        $matches = array();
        if (
                preg_match('/^([^:]+)\:\s+(\d+)\s+([kMGT]B)$/', $line, $matches) === false ||
                count($matches) != 4
           ) {
            continue;
        }

        $key = $matches[1];
        $value = intval($matches[2]);
        $i = $matches[3];
        
        if (in_array($i, array_keys($units))) {
            $value *= pow(2, $units[$i]);
        }
        
        $result[$key] = $value;
    }
    
    return $result;
}


//Get FreeDiskSpace
function get_hdd_freespace() {
    $bytes = disk_free_space("/");
    return convert_ToMB($bytes);
}


// Convert value to MB
// @param decimal $value
// @return int Memory MB
function convert_ToMB($bytes) {
    $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
    $base = 1024;
    
    $i = min((int)log($bytes, $base), count($si_prefix) - 1);
    
    return sprintf('%1.2f %s' , ($bytes / pow($base, $i)), $si_prefix[$i]);
}


// Get all network names devices
// @return array Get list network name interfaces
function get_interface_list() {
    $result = array();
    $proc_net_dev = explode("\n", file_get_contents("/proc/net/dev"));
    
    if (empty($proc_net_dev)) {
        return $result;
    }
    
    foreach ($proc_net_dev as $line) {
        $matches = array();
        
        if (
                preg_match('/^\s*([^:]+):/', $line, $matches) === false ||
                count($matches) != 2
           ) {
            continue;
        }
        
        $iface = $matches[1];
        if ($iface !== 'lo') {
            $result[] = $iface;
        }            
    }
    
    return $result;
}


// Get ip address
// @param string $ifname
// @return string Ip address or (none)
function get_ip_addr($ifname) {
    $command_formats = array(
        '(ip addr show %s || /sbin/ip addr show %s) | grep inet | grep -v inet6 | sed -E "s/^\s+//g" | cut -d" " -f2 | cut -d"/" -f1',
        '(ifconfig %s || /sbin/ifconfig %s) | grep -oE "inet ([0-9]{1,3}\.?){4}" | cut -d" " -f2'
    );

    foreach ($command_formats as $format) {
        $command = sprintf($format, escapeshellarg($ifname), escapeshellarg($ifname));
        exec($command, $output, $result_code);
        if ($result_code === 0) {
            return $output[0];
        }
    }

    return "(n/d)";
}

// Get mac address
// @param string $ifname
// @return string Mac address or (none)
function get_mac_addr($ifname) {
    $command_formats = array(
        '(ip addr show %s || /sbin/ip addr show %s) | grep "link/ether" | sed -E "s/^\s+//g" | cut -d" " -f2',
        '(ifconfig %s || /sbin/ifconfig %s) | grep "ether" | sed -E "s/^\s+//g" | cut -d" " -f2'
    );

    foreach ($command_formats as $format) {
        $command = sprintf($format, escapeshellarg($ifname), escapeshellarg($ifname));
        exec($command, $output, $result_code);

        if ($result_code === 0 && preg_match("/^([0-9A-F]{2}\:){5}[0-9A-F]{2}$/i", $output[0])) {
            return $output[0];
        }
    }

    return "(n/d)";
}


// Get netmask address
// @param string $ifname
// @return string Netmask address or (none)
function get_mask_addr($ifname) {
    $command_formats = array(
        'echo -n "/"; (ip addr show %s || /sbin/ip addr show %s) | grep inet | grep -v inet6 | sed -E "s/^\s+//g" | cut -d" " -f2 | cut -d"/" -f2',
        '(ifconfig %s || /sbin/ifconfig %s) | grep -oE "netmask ([0-9]{1,3}\.?){4}" | cut -d" " -f2'
    );
    
    foreach ($command_formats as $format) {
        $command = sprintf($format, escapeshellarg($ifname), escapeshellarg($ifname));
        exec($command, $output, $result_code);
        if ($result_code === 0) {
            return $output[0];
        }
    }
    
    return "(n/d)";
}

// memory info
$meminfo = get_memory();
$meminfo['MemUsed'] = $meminfo['MemTotal'] - $meminfo['MemFree'];

// hdd info
$hddfreespace = get_hdd_freespace();

// network interfaces info
$iflist = get_interface_list();


$table1 = array( 'title' => 'General Information', 'rows' => array() );
$table1['rows'][] = array( "System distro", get_system_name_and_version() );
$table1['rows'][] = array( "Uptime", uptime() );
$table1['rows'][] = array( "System Load", get_system_load() );
$table1['rows'][] = array( "Hostname", get_hostname() );
$table1['rows'][] = array( "Current Date", get_datetime() );
print_simple_table($table1);


$table2 = array( 'title' => 'Memory Information', 'rows' => array() );
$table2['rows'][] = array( "Mem. Total", convert_ToMB($meminfo['MemTotal']) );
$table2['rows'][] = array( "Mem. Free", convert_ToMB($meminfo['MemFree']) );
$table2['rows'][] = array( "Mem. Used", convert_ToMB($meminfo['MemUsed']) );
print_simple_table($table2);

$table3 = array( 'title' => 'Harddrive Information', 'rows' => array() );
$table3['rows'][] = array( "Free Drive Space", $hddfreespace );
print_simple_table($table3);


foreach ($iflist as $ifname) {
    $title = "Network Interface " . $ifname;
    $table = array( 'title' => $title, 'rows' => array() );
    
    $table['rows'][] = array( "Interface", $ifname );
    $table['rows'][] = array( "IP addr", get_ip_addr($ifname) );
    $table['rows'][] = array( "Subnet mask", get_mask_addr($ifname) );
    $table['rows'][] = array( "MAC addr", get_mac_addr($ifname) );
    
    print_simple_table($table);
}


?>
