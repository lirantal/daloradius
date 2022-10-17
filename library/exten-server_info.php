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
 *         this script process some important server information and displays it
 *
 * Authors:    Liran Tal <liran@enginx.com>
 *             Carlos Cesario <carloscesario@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
$extension_file = '/library/exten-server_info.php';
if (strpos($_SERVER['PHP_SELF'], $extension_file) !== false) {
    header("Location: ../index.php");
    exit;
}

include_once('include/management/pages_common.php');

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
    $file_name = "/proc/meminfo";
    $mem_array = array();

    $buffer = file($file_name);

    while (list($key, $value) = each($buffer)) {
        if (strpos($value, ':') !== false) {
            $match_line = explode(':', $value);
            $match_value = explode(' ', trim($match_line[1]));
            if (is_numeric($match_value[0])) {
                $mem_array[trim($match_line[0])] = trim($match_value[0]);
            }
        }
    }

    return $mem_array;
}


//Get FreeDiskSpace
function get_hdd_freespace() {
$df = disk_free_space("/");
return $df;
}


// Convert value to MB
// @param decimal $value
// @return int Memory MB
function convert_ToMB($value) {
    return round($value / 1024) . " MB\n";
}



// Get all network names devices (eth[0-9])
// @return array Get list network name interfaces
function get_interface_list() {
    $devices = array();
    $file_name = "/proc/net/dev";

    if ($fopen_file = fopen($file_name, 'r')) {
        while ($buffer = fgets($fopen_file, 4096)) {
            if (preg_match("/eth[0-9][0-9]*/i", trim($buffer), $match)) {
                $devices[] = $match[0];
            }
        }
        $devices = array_unique($devices);
        sort($devices);
        fclose ($fopen_file);
    }
    return $devices;
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
$memused = ($meminfo['MemTotal'] - $meminfo['MemFree']);

// hdd info
$hddfreespace = get_hdd_freespace();

// network interfaces info
$iflist = get_interface_list();

?>

<h3>General Information</h3>
<table class="summarySection">
  <tr>
    <td class="summaryKey">System distro</td>
    <td class="summaryValue"><span class="sleft"><?= get_system_name_and_version() ?></span></td>
  </tr>
    
  <tr>
    <td class="summaryKey">Uptime</td>
    <td class="summaryValue"><span class="sleft"><?= uptime() ?></span></td>
  </tr>
  <tr>
    <td class="summaryKey">System Load</td>
    <td class="summaryValue"><span class="sleft"><?= get_system_load() ?></span></td>
  </tr>
  <tr>
    <td class="summaryKey">Hostname</td>
    <td class="summaryValue"><span class="sleft"><?= get_hostname() ?></span></td>
  </tr>
  <tr>
    <td class="summaryKey">Current Date</td>
    <td class="summaryValue"><span class="sleft"><?= get_datetime() ?></span></td>
  </tr>
</table>


<h3>Memory Information</h3>
<table class="summarySection">
  <tr>
    <td class="summaryKey">Mem. Total</td>
    <td class="summaryValue"><span class="sleft"><?= convert_ToMB($meminfo['MemTotal']) ?></span></td>
  </tr>
  <tr>
    <td class="summaryKey">Mem. Free</td>
    <td class="summaryValue"><span class="sleft"><?= convert_ToMB($meminfo['MemFree']) ?></span></td>
  </tr>
  <tr>
    <td class="summaryKey">Mem. Used</td>
    <td class="summaryValue"><span class="sleft"><?= convert_ToMB($memused) ?></span></td>
  </tr>
</table>


<h3>Harddrive Information</h3>
<table class="summarySection">
  <tr>
    <td class="summaryKey">Free Drive Space</td>
    <td class="summaryValue"><span class="sleft"><?= toxbyte($hddfreespace) ?></span></td>
  </tr>
</table>

<h3>Network Interfaces</h3>

<?php
    foreach ($iflist as $ifname) {
?>
<table class="summarySection">    
  <tr>
    <td class="summaryKey">Interface</td>
    <td class="stitle"><?= $ifname ?></td>
  </tr>
  <tr>
    <td class="summaryKey">Ip</td>
    <td class="summaryValue"><span class="sleft"><?= get_ip_addr($ifname) ?></span></td>
  </tr>
  <tr>
    <td class="summaryKey">Mask</td>
    <td class="summaryValue"><span class="sleft"><?= get_mask_addr($ifname) ?></span></td>
  </tr>
  <tr>
    <td class="summaryKey">MAC address</td>
    <td class="summaryValue"><span class="sleft"><?= get_mac_addr($ifname) ?></span></td>
  </tr>
</table>

<?php
    }
?>
