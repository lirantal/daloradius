<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
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
 * Description:    Collects server, runtime, storage, and network diagnostics and renders
 *                 the refreshed Bootstrap 5 Server Status dashboard.
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Carlos Cesario <carloscesario@gmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
$extension_file = '/library/extensions/server_info.php';
if (strpos($_SERVER['PHP_SELF'] ?? '', $extension_file) !== false) {
    header("Location: ../../index.php");
    exit;
}

require_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'common', 'includes', 'pdo_connection.php' ]);

// -------------------------------------------------------------------------
// Formatting & generic helpers
// -------------------------------------------------------------------------

function server_info_escape($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function server_info_na() {
    return t('serverStatus', 'notAvailable');
}

function server_info_read_file($path) {
    if (!is_string($path) || $path === '' || !is_readable($path)) {
        return null;
    }
    $content = @file_get_contents($path);
    return ($content === false) ? null : $content;
}

function server_info_can_exec() {
    if (!function_exists('exec')) {
        return false;
    }
    $disabled = (string) ini_get('disable_functions');
    if ($disabled === '') {
        return true;
    }
    $list = array_map('trim', explode(',', strtolower($disabled)));
    return !in_array('exec', $list, true);
}

function server_info_exec($command, &$output, &$result_code) {
    $output = array();
    $result_code = 1;
    if (!server_info_can_exec()) {
        return false;
    }
    @exec($command, $output, $result_code);
    return true;
}

function server_info_percent($part, $total) {
    if (!is_numeric($part) || !is_numeric($total) || (float)$total <= 0) {
        return null;
    }
    $pct = ((float)$part / (float)$total) * 100.0;
    return round(max(0.0, min(100.0, $pct)), 1);
}

function server_info_threshold_variant($percent, $warn = 75.0, $danger = 90.0) {
    if ($percent === null) {
        return 'secondary';
    }
    if ($percent >= $danger) {
        return 'danger';
    }
    if ($percent >= $warn) {
        return 'warning';
    }
    return 'success';
}

function server_info_format_duration($seconds) {
    if ($seconds === null || !is_numeric($seconds) || (float)$seconds < 0) {
        return server_info_na();
    }

    $totalSeconds = (int) floor((float)$seconds);
    $days = intdiv($totalSeconds, 86400);
    $hours = intdiv($totalSeconds % 86400, 3600);
    $minutes = intdiv($totalSeconds % 3600, 60);

    $parts = array();
    if ($days > 0) {
        $parts[] = $days . 'd';
    }
    if ($hours > 0 || $days > 0) {
        $parts[] = $hours . 'h';
    }
    $parts[] = $minutes . 'm';

    if ($totalSeconds < 60) {
        return $totalSeconds . 's';
    }

    return implode(' ', $parts);
}

// Convert byte count to human-readable string (kept backward-compatible)
function convert_ToMB($bytes) {
    if ($bytes === null || !is_numeric($bytes) || (float)$bytes < 0) {
        return server_info_na();
    }

    $bytes = (float)$bytes;
    if ($bytes == 0.0) {
        return '0 B';
    }

    $si_prefix = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $base = 1024;
    $i = max(0, min((int) floor(log($bytes, $base)), count($si_prefix) - 1));

    return sprintf('%1.2f %s', ($bytes / pow($base, $i)), $si_prefix[$i]);
}

// -------------------------------------------------------------------------
// System, CPU, Memory, Storage, Runtime & Network collectors
// -------------------------------------------------------------------------

// Returns system name and version
function get_system_name_and_version() {
    foreach (array('/etc/os-release', '/usr/lib/os-release') as $path) {
        $content = server_info_read_file($path);
        if ($content === null) {
            continue;
        }

        $parsed = array();
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            list($key, $val) = explode('=', $line, 2);
            $val = trim($val);
            if (strlen($val) >= 2 && (($val[0] === '"' && substr($val, -1) === '"') || ($val[0] === "'" && substr($val, -1) === "'"))) {
                $val = substr($val, 1, -1);
            }
            $parsed[$key] = $val;
        }

        if (!empty($parsed['PRETTY_NAME'])) {
            return $parsed['PRETTY_NAME'];
        }
        if (!empty($parsed['NAME'])) {
            return !empty($parsed['VERSION'])
                ? sprintf('%s %s', $parsed['NAME'], $parsed['VERSION'])
                : $parsed['NAME'];
        }
    }

    $uname = trim(php_uname('s') . ' ' . php_uname('r'));
    return ($uname !== '') ? $uname : server_info_na();
}

// Returns system uptime in seconds, or null if unavailable
function get_uptime_seconds() {
    $content = server_info_read_file('/proc/uptime');
    if ($content !== null) {
        $parts = preg_split('/\s+/', trim($content));
        if (!empty($parts) && is_numeric($parts[0])) {
            return (float)$parts[0];
        }
    }
    return null;
}

// Display uptime system
function uptime() {
    $seconds = get_uptime_seconds();
    return ($seconds !== null) ? server_info_format_duration($seconds) : server_info_na();
}

// Display hostname system
function get_hostname() {
    $content = server_info_read_file('/proc/sys/kernel/hostname');
    if ($content !== null && trim($content) !== '') {
        return trim($content);
    }

    $host = @gethostname();
    if (is_string($host) && trim($host) !== '') {
        return trim($host);
    }

    $uname = php_uname('n');
    return ($uname !== '') ? $uname : server_info_na();
}

// Display current date/time
function get_datetime() {
    $today = date('Y-m-d H:i:s');
    return ($today) ? $today : server_info_na();
}

// Get CPU model and core count
function get_cpu_info() {
    $model = null;
    $cores = 0;

    $cpuinfo = server_info_read_file('/proc/cpuinfo');
    if ($cpuinfo !== null) {
        foreach (explode("\n", $cpuinfo) as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            list($key, $val) = explode(':', $line, 2);
            $key = trim($key);
            $val = trim($val);

            if ($key === 'processor') {
                $cores++;
            } elseif ($model === null && in_array($key, array('model name', 'Model', 'Hardware', 'Processor'), true) && $val !== '') {
                $model = $val;
            }
        }
    }

    if ($cores === 0) {
        $output = array();
        $code = 1;
        if (server_info_exec('nproc 2>/dev/null', $output, $code) && $code === 0 && !empty($output) && ctype_digit(trim($output[0]))) {
            $cores = (int) trim($output[0]);
        }
    }

    return array(
        'model' => $model,
        'cores' => ($cores > 0) ? $cores : null,
    );
}

// Get structured system load averages
function get_load_average() {
    if (function_exists('sys_getloadavg')) {
        $load = @sys_getloadavg();
        if (is_array($load) && count($load) >= 3 && is_numeric($load[0])) {
            return array(
                '1m' => (float)$load[0],
                '5m' => (float)$load[1],
                '15m' => (float)$load[2],
            );
        }
    }

    $content = server_info_read_file('/proc/loadavg');
    if ($content !== null) {
        $parts = preg_split('/\s+/', trim($content));
        if (count($parts) >= 3 && is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
            return array(
                '1m' => (float)$parts[0],
                '5m' => (float)$parts[1],
                '15m' => (float)$parts[2],
            );
        }
    }

    return null;
}

// Detect whether FreeRADIUS daemon is running locally via /proc
function is_freeradius_running_locally() {
    $commFiles = @glob('/proc/[0-9]*/comm', GLOB_NOSORT);
    if (!is_array($commFiles)) {
        return false;
    }

    foreach ($commFiles as $commFile) {
        $comm = @file_get_contents($commFile);
        if ($comm === false) {
            continue;
        }
        $name = strtolower(trim($comm));
        if ($name === 'freeradius' || $name === 'radiusd') {
            return true;
        }
    }

    return false;
}

// Get Memory System info from /proc/meminfo
function get_memory() {
    $units = array('kB' => 10, 'MB' => 20, 'GB' => 30, 'TB' => 40);
    $result = array();

    $content = server_info_read_file('/proc/meminfo');
    if ($content === null) {
        return $result;
    }

    foreach (explode("\n", $content) as $line) {
        $matches = array();
        if (preg_match('/^([^:]+):\s+(\d+)(?:\s+([kMGT]B))?$/', trim($line), $matches) !== 1) {
            continue;
        }

        $key = $matches[1];
        $value = (float)$matches[2];
        $unit = $matches[3] ?? '';

        if ($unit !== '' && isset($units[$unit])) {
            $value *= pow(2, $units[$unit]);
        }

        $result[$key] = $value;
    }

    return $result;
}

// Build normalized memory & swap breakdown using MemAvailable (accurate Linux memory usage)
function get_memory_summary() {
    $raw = get_memory();
    if (empty($raw['MemTotal']) || $raw['MemTotal'] <= 0) {
        return null;
    }

    $total = (float)$raw['MemTotal'];
    $freeRaw = isset($raw['MemFree']) ? (float)$raw['MemFree'] : 0.0;

    if (isset($raw['MemAvailable'])) {
        $available = max(0.0, min($total, (float)$raw['MemAvailable']));
    } else {
        $buffers = isset($raw['Buffers']) ? (float)$raw['Buffers'] : 0.0;
        $cached = isset($raw['Cached']) ? (float)$raw['Cached'] : 0.0;
        $reclaimable = isset($raw['SReclaimable']) ? (float)$raw['SReclaimable'] : 0.0;
        $available = max(0.0, min($total, $freeRaw + $buffers + $cached + $reclaimable));
    }

    // Partition MemTotal into Used, Reclaimable (cache/buffers/reclaimable slab), and Free
    // so that Used + Reclaimable + Free == MemTotal and Reclaimable + Free == MemAvailable.
    $used = max(0.0, $total - $available);
    $free = max(0.0, min($freeRaw, $available));
    $reclaimable = max(0.0, $available - $free);

    $swapTotal = isset($raw['SwapTotal']) ? (float)$raw['SwapTotal'] : 0.0;
    $swapFree = isset($raw['SwapFree']) ? (float)$raw['SwapFree'] : 0.0;
    $swapUsed = max(0.0, $swapTotal - $swapFree);

    return array(
        'total' => $total,
        'used' => $used,
        'reclaimable' => $reclaimable,
        'free' => $free,
        'available' => $available,
        'used_pct' => server_info_percent($used, $total),
        'reclaimable_pct' => server_info_percent($reclaimable, $total),
        'swap_total' => $swapTotal,
        'swap_used' => $swapUsed,
        'swap_free' => $swapFree,
        'swap_pct' => ($swapTotal > 0) ? server_info_percent($swapUsed, $swapTotal) : null,
    );
}

// Get FreeDiskSpace for root mount (kept backward-compatible)
function get_hdd_freespace() {
    $bytes = @disk_free_space('/');
    return ($bytes !== false) ? convert_ToMB($bytes) : server_info_na();
}

// Get mounted real filesystems, with safe fallback to root via PHP disk_*_space
function get_filesystems() {
    $filesystems = array();
    $seenMounts = array();
    $ignoredTypes = array(
        'tmpfs', 'devtmpfs', 'squashfs', 'proc', 'sysfs', 'devpts', 'mqueue',
        'hugetlbfs', 'pstore', 'securityfs', 'debugfs', 'tracefs', 'fusectl',
        'configfs', 'efivarfs', 'ramfs', 'autofs', 'nsfs', 'rpc_pipefs',
        'cgroup', 'cgroup2', 'binfmt_misc', 'bpf', 'overlay',
    );

    $output = array();
    $code = 1;
    if (server_info_exec('df -P -B1 -T 2>/dev/null', $output, $code) && $code === 0 && count($output) > 1) {
        // Skip header line
        for ($i = 1; $i < count($output); $i++) {
            $line = trim($output[$i]);
            if ($line === '') {
                continue;
            }
            $parts = preg_split('/\s+/', $line, 7);
            if (count($parts) < 7) {
                continue;
            }

            list($device, $fstype, $total, $used, $avail, $pctStr, $mount) = $parts;
            if (!is_numeric($total) || (float)$total <= 0) {
                continue;
            }

            $fsTypeLower = strtolower($fstype);
            // Always keep '/' even on overlayfs (e.g. Docker containers)
            if ($mount !== '/' && in_array($fsTypeLower, $ignoredTypes, true)) {
                continue;
            }

            // Skip synthetic docker/container bind mounts under /etc or /proc or /sys
            if ($mount !== '/' && preg_match('#^/(proc|sys|dev|run|etc)(/|$)#', $mount)) {
                continue;
            }

            if (isset($seenMounts[$mount])) {
                continue;
            }
            $seenMounts[$mount] = true;

            $totalBytes = (float)$total;
            $usedBytes = is_numeric($used) ? (float)$used : max(0.0, $totalBytes - (float)$avail);
            $freeBytes = is_numeric($avail) ? (float)$avail : max(0.0, $totalBytes - $usedBytes);

            $filesystems[] = array(
                'device' => $device,
                'fstype' => $fstype,
                'mount' => $mount,
                'total' => $totalBytes,
                'used' => $usedBytes,
                'free' => $freeBytes,
                'percent' => server_info_percent($usedBytes, $totalBytes),
            );
        }
    }

    // Guarantee '/' is present via native PHP disk_*_space fallback
    if (!isset($seenMounts['/'])) {
        $total = @disk_total_space('/');
        $free = @disk_free_space('/');
        if ($total !== false && $free !== false && $total > 0) {
            $used = max(0.0, (float)$total - (float)$free);
            array_unshift($filesystems, array(
                'device' => '',
                'fstype' => '',
                'mount' => '/',
                'total' => (float)$total,
                'used' => $used,
                'free' => (float)$free,
                'percent' => server_info_percent($used, (float)$total),
            ));
        }
    }

    // Place '/' first, then sort alphabetically by mount point
    usort($filesystems, function ($a, $b) {
        if ($a['mount'] === '/') {
            return -1;
        }
        if ($b['mount'] === '/') {
            return 1;
        }
        return strcmp($a['mount'], $b['mount']);
    });

    return $filesystems;
}

// Get network traffic counters (rx/tx bytes) from /proc/net/dev
function get_interface_traffic() {
    $traffic = array();
    $content = server_info_read_file('/proc/net/dev');
    if ($content === null) {
        return $traffic;
    }

    foreach (explode("\n", $content) as $line) {
        if (strpos($line, ':') === false) {
            continue;
        }
        list($iface, $rest) = explode(':', $line, 2);
        $iface = trim($iface);
        if ($iface === '' || $iface === 'lo') {
            continue;
        }
        $cols = preg_split('/\s+/', trim($rest));
        if (count($cols) >= 9 && is_numeric($cols[0]) && is_numeric($cols[8])) {
            $traffic[$iface] = array(
                'rx' => (float)$cols[0],
                'tx' => (float)$cols[8],
            );
        }
    }

    return $traffic;
}

// Get all network device names (kept backward-compatible)
function get_interface_list() {
    return array_keys(get_interface_traffic());
}

// Fallback IPv4 helper (kept backward-compatible)
function get_ip_addr($ifname) {
    $command_formats = array(
        '(ip addr show %s 2>/dev/null || /sbin/ip addr show %s 2>/dev/null) | grep inet | grep -v inet6 | sed -E "s/^\s+//g" | cut -d" " -f2 | cut -d"/" -f1',
        '(ifconfig %s 2>/dev/null || /sbin/ifconfig %s 2>/dev/null) | grep -oE "inet ([0-9]{1,3}\.?){4}" | cut -d" " -f2',
    );

    foreach ($command_formats as $format) {
        $command = sprintf($format, escapeshellarg($ifname), escapeshellarg($ifname));
        $output = array();
        $result_code = 1;
        server_info_exec($command, $output, $result_code);
        if ($result_code === 0 && !empty($output) && trim($output[0]) !== '') {
            return trim($output[0]);
        }
    }

    return server_info_na();
}

// Fallback MAC helper (kept backward-compatible)
function get_mac_addr($ifname) {
    $sysMac = server_info_read_file('/sys/class/net/' . basename($ifname) . '/address');
    if ($sysMac !== null) {
        $sysMac = trim($sysMac);
        if (preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i', $sysMac)) {
            return $sysMac;
        }
    }

    $command_formats = array(
        '(ip addr show %s 2>/dev/null || /sbin/ip addr show %s 2>/dev/null) | grep "link/ether" | sed -E "s/^\s+//g" | cut -d" " -f2',
        '(ifconfig %s 2>/dev/null || /sbin/ifconfig %s 2>/dev/null) | grep "ether" | sed -E "s/^\s+//g" | cut -d" " -f2',
    );

    foreach ($command_formats as $format) {
        $command = sprintf($format, escapeshellarg($ifname), escapeshellarg($ifname));
        $output = array();
        $result_code = 1;
        server_info_exec($command, $output, $result_code);
        if ($result_code === 0 && !empty($output) && preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i', trim($output[0]))) {
            return trim($output[0]);
        }
    }

    return server_info_na();
}

// Fallback netmask helper (kept backward-compatible)
function get_mask_addr($ifname) {
    $command_formats = array(
        'echo -n "/"; (ip addr show %s 2>/dev/null || /sbin/ip addr show %s 2>/dev/null) | grep inet | grep -v inet6 | sed -E "s/^\s+//g" | cut -d" " -f2 | cut -d"/" -f2',
        '(ifconfig %s 2>/dev/null || /sbin/ifconfig %s 2>/dev/null) | grep -oE "netmask ([0-9]{1,3}\.?){4}" | cut -d" " -f2',
    );

    foreach ($command_formats as $format) {
        $command = sprintf($format, escapeshellarg($ifname), escapeshellarg($ifname));
        $output = array();
        $result_code = 1;
        server_info_exec($command, $output, $result_code);
        if ($result_code === 0 && !empty($output) && $output[0] !== '' && $output[0] !== '/') {
            return trim($output[0]);
        }
    }

    return server_info_na();
}

// Detect virtual network devices via sysfs (/sys/class/net/<name>), with name-pattern fallback
function server_info_is_virtual_interface($ifname) {
    $safeBase = basename($ifname);
    if ($safeBase !== '' && $safeBase !== '.' && $safeBase !== '..') {
        $sysPath = '/sys/class/net/' . $safeBase;
        if (@file_exists($sysPath)) {
            $realPath = @realpath($sysPath);
            if (is_string($realPath) && $realPath !== '') {
                return strpos($realPath, '/sys/devices/virtual/') === 0;
            }
            return !@file_exists($sysPath . '/device');
        }
    }

    return (bool) preg_match(
        '/^(docker\d*|veth[a-z0-9@._-]+|br-[a-z0-9]+|virbr\d*|cni\d*|flannel[a-z0-9._-]*|cali[a-z0-9._-]*|kube-[a-z0-9._-]+|tun\d*|tap\d*|wg\d*|bond\d*|dummy\d*)$/i',
        $ifname
    );
}

// Collect full structured network interface diagnostics
function get_network_interfaces() {
    $traffic = get_interface_traffic();
    $interfaces = array();

    // Fast path: single `ip -j addr show` call gives state, MAC, IPv4/CIDR, and IPv6/CIDR for all interfaces
    $ipJsonParsed = false;
    $output = array();
    $code = 1;
    if (server_info_exec('(ip -j addr show 2>/dev/null || /sbin/ip -j addr show 2>/dev/null)', $output, $code) && $code === 0 && !empty($output)) {
        $decoded = json_decode(implode("\n", $output), true);
        if (is_array($decoded)) {
            $ipJsonParsed = true;
            foreach ($decoded as $entry) {
                $name = isset($entry['ifname']) ? (string)$entry['ifname'] : '';
                if ($name === '' || $name === 'lo') {
                    continue;
                }

                $state = isset($entry['operstate']) ? strtoupper((string)$entry['operstate']) : 'UNKNOWN';
                if ($state === 'UNKNOWN' && isset($entry['flags']) && is_array($entry['flags']) && in_array('UP', $entry['flags'], true)) {
                    $state = 'UP';
                }

                $mac = (isset($entry['address']) && preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i', (string)$entry['address']))
                    ? (string)$entry['address']
                    : null;

                $ipv4 = array();
                $ipv6 = array();
                if (isset($entry['addr_info']) && is_array($entry['addr_info'])) {
                    foreach ($entry['addr_info'] as $addr) {
                        $local = isset($addr['local']) ? (string)$addr['local'] : '';
                        if ($local === '') {
                            continue;
                        }
                        $prefix = isset($addr['prefixlen']) ? '/' . $addr['prefixlen'] : '';
                        $family = isset($addr['family']) ? (string)$addr['family'] : '';
                        if ($family === 'inet') {
                            $ipv4[] = $local . $prefix;
                        } elseif ($family === 'inet6') {
                            $ipv6[] = $local . $prefix;
                        }
                    }
                }

                $interfaces[$name] = array(
                    'name' => $name,
                    'state' => $state,
                    'ipv4' => $ipv4,
                    'ipv6' => $ipv6,
                    'mac' => $mac,
                    'rx' => $traffic[$name]['rx'] ?? null,
                    'tx' => $traffic[$name]['tx'] ?? null,
                    'virtual' => server_info_is_virtual_interface($name),
                );
            }
        }
    }

    // Fallback when `ip -j` is unavailable
    if (!$ipJsonParsed) {
        foreach (array_keys($traffic) as $name) {
            $safeBase = basename($name);
            $stateRaw = server_info_read_file('/sys/class/net/' . $safeBase . '/operstate');
            $state = ($stateRaw !== null && trim($stateRaw) !== '') ? strtoupper(trim($stateRaw)) : 'UNKNOWN';

            $ip = get_ip_addr($name);
            $mask = get_mask_addr($name);
            $ipv4 = array();
            if ($ip !== server_info_na()) {
                $ipv4[] = ($mask !== server_info_na()) ? ($ip . (strpos($mask, '/') === 0 ? $mask : ' (' . $mask . ')')) : $ip;
            }

            $mac = get_mac_addr($name);
            $interfaces[$name] = array(
                'name' => $name,
                'state' => $state,
                'ipv4' => $ipv4,
                'ipv6' => array(),
                'mac' => ($mac !== server_info_na()) ? $mac : null,
                'rx' => $traffic[$name]['rx'] ?? null,
                'tx' => $traffic[$name]['tx'] ?? null,
                'virtual' => server_info_is_virtual_interface($name),
            );
        }
    }

    // Sort: non-virtual UP interfaces first, then non-virtual others, then virtual
    uasort($interfaces, function ($a, $b) {
        if ($a['virtual'] !== $b['virtual']) {
            return $a['virtual'] ? 1 : -1;
        }
        $aUp = ($a['state'] === 'UP') ? 0 : 1;
        $bUp = ($b['state'] === 'UP') ? 0 : 1;
        if ($aUp !== $bUp) {
            return $aUp <=> $bUp;
        }
        return strcmp($a['name'], $b['name']);
    });

    return array_values($interfaces);
}

// Check database connectivity, version, latency, and uptime via PDO
function get_database_status($configValues) {
    $status = array(
        'connected' => false,
        'version' => null,
        'latency_ms' => null,
        'uptime' => null,
        'engine' => isset($configValues['CONFIG_DB_ENGINE']) ? (string)$configValues['CONFIG_DB_ENGINE'] : null,
    );

    $pdo = null;
    try {
        $start = microtime(true);
        $pdo = dalo_pdo_connect($configValues, $_SESSION['location_name'] ?? 'default');
        $stmt = $pdo->prepare('SELECT VERSION()');
        $stmt->execute();
        $version = $stmt->fetchColumn();
        $elapsed = (microtime(true) - $start) * 1000.0;

        $status['connected'] = true;
        $status['version'] = is_string($version) ? $version : null;
        $status['latency_ms'] = round($elapsed, 1);

        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            // Use natively preparable SELECT queries (SHOW STATUS is not preparable when ATTR_EMULATE_PREPARES is false)
            $uptimeQueries = array(
                'SELECT VARIABLE_VALUE FROM information_schema.GLOBAL_STATUS WHERE VARIABLE_NAME = :metric',
                'SELECT VARIABLE_VALUE FROM performance_schema.global_status WHERE VARIABLE_NAME = :metric',
            );
            foreach ($uptimeQueries as $uptimeSql) {
                try {
                    $uptimeStmt = $pdo->prepare($uptimeSql);
                    $uptimeStmt->execute(array(':metric' => 'Uptime'));
                    $uptimeVal = $uptimeStmt->fetchColumn();
                    if (is_numeric($uptimeVal)) {
                        $status['uptime'] = (int)$uptimeVal;
                        break;
                    }
                } catch (Throwable $uptimeException) {
                    // Fall back to the next status view (e.g. performance_schema on MySQL 8+)
                }
            }
        }
    } catch (Throwable $exception) {
        $status['connected'] = false;
    } finally {
        $pdo = null;
    }

    return $status;
}

// -------------------------------------------------------------------------
// Collect all metrics
// -------------------------------------------------------------------------

$distro = get_system_name_and_version();
$hostname = get_hostname();
$kernel = trim(php_uname('r'));
$arch = trim(php_uname('m'));
$datetime = get_datetime();
$timezone = date_default_timezone_get();
$uptimeSec = get_uptime_seconds();
$uptimeStr = ($uptimeSec !== null) ? server_info_format_duration($uptimeSec) : server_info_na();

$cpuInfo = get_cpu_info();
$loadAvg = get_load_average();
$freeradiusLocal = is_freeradius_running_locally();
$memSummary = get_memory_summary();
$filesystems = get_filesystems();
$rootFs = (!empty($filesystems) && $filesystems[0]['mount'] === '/') ? $filesystems[0] : null;
$interfaces = get_network_interfaces();
$dbStatus = get_database_status($configValues);

$daloVersion = isset($configValues['DALORADIUS_VERSION']) ? (string)$configValues['DALORADIUS_VERSION'] : server_info_na();
$daloDate = isset($configValues['DALORADIUS_DATE']) ? (string)$configValues['DALORADIUS_DATE'] : '';
$phpRuntime = PHP_VERSION . ' (' . PHP_SAPI . ')';
$webServer = !empty($_SERVER['SERVER_SOFTWARE']) ? (string)$_SERVER['SERVER_SOFTWARE'] : server_info_na();

// Derived KPI states
$cpuLoadPct = null;
if ($loadAvg !== null && $cpuInfo['cores'] !== null && $cpuInfo['cores'] > 0) {
    $cpuLoadPct = round(min(100.0, ($loadAvg['1m'] / $cpuInfo['cores']) * 100.0), 1);
}
$cpuVariant = server_info_threshold_variant($cpuLoadPct, 70.0, 100.0);
$cpuStatusLabel = ($cpuVariant === 'danger')
    ? t('serverStatus', 'statusHigh')
    : (($cpuVariant === 'warning') ? t('serverStatus', 'statusElevated') : t('serverStatus', 'statusNormal'));

$memPct = ($memSummary !== null) ? $memSummary['used_pct'] : null;
$memVariant = server_info_threshold_variant($memPct, 75.0, 90.0);

$diskPct = ($rootFs !== null) ? $rootFs['percent'] : null;
$diskVariant = server_info_threshold_variant($diskPct, 80.0, 90.0);

$naEsc = server_info_escape(server_info_na());

// -------------------------------------------------------------------------
// Render: 1. KPI Summary Cards (4 columns on xl, 2 on sm, 1 on xs)
// -------------------------------------------------------------------------
?>
<div class="row g-3 mb-4">
    <!-- KPI 1: CPU Load -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold"><?= server_info_escape(t('serverStatus', 'cpuLoad')) ?></div>
                        <div class="fs-4 fw-bold mt-1">
                            <?= ($loadAvg !== null) ? server_info_escape(number_format($loadAvg['1m'], 2)) : $naEsc ?>
                            <?php if ($cpuLoadPct !== null): ?>
                                <span class="badge text-bg-<?= server_info_escape($cpuVariant) ?> fs-6 fw-normal align-middle ms-1"><?= server_info_escape($cpuStatusLabel) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="server-kpi-icon rounded-3 d-flex align-items-center justify-content-center bg-<?= server_info_escape($cpuVariant) ?>-subtle text-<?= server_info_escape($cpuVariant) ?>">
                        <i class="bi bi-cpu fs-4" aria-hidden="true"></i>
                    </div>
                </div>
                <?php if ($cpuLoadPct !== null): ?>
                    <div class="progress mb-2" style="height: 6px;" role="progressbar" aria-label="<?= server_info_escape(t('serverStatus', 'cpuLoad')) ?>" aria-valuenow="<?= server_info_escape($cpuLoadPct) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-<?= server_info_escape($cpuVariant) ?>" style="width: <?= server_info_escape($cpuLoadPct) ?>%"></div>
                    </div>
                <?php endif; ?>
                <div class="text-muted small">
                    <?php if ($loadAvg !== null): ?>
                        <?= server_info_escape(sprintf('%.2f / %.2f / %.2f', $loadAvg['1m'], $loadAvg['5m'], $loadAvg['15m'])) ?>
                        <?php if ($cpuInfo['cores'] !== null): ?>
                            &middot; <?= (int)$cpuInfo['cores'] ?> <?= server_info_escape($cpuInfo['cores'] === 1 ? t('serverStatus', 'core') : t('serverStatus', 'cores')) ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= $naEsc ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 2: Memory (RAM) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold"><?= server_info_escape(t('serverStatus', 'memoryRam')) ?></div>
                        <div class="fs-4 fw-bold mt-1">
                            <?= ($memPct !== null) ? server_info_escape(number_format($memPct, 1) . '%') : $naEsc ?>
                        </div>
                    </div>
                    <div class="server-kpi-icon rounded-3 d-flex align-items-center justify-content-center bg-<?= server_info_escape($memVariant) ?>-subtle text-<?= server_info_escape($memVariant) ?>">
                        <i class="bi bi-memory fs-4" aria-hidden="true"></i>
                    </div>
                </div>
                <?php if ($memPct !== null): ?>
                    <div class="progress mb-2" style="height: 6px;" role="progressbar" aria-label="<?= server_info_escape(t('serverStatus', 'memoryRam')) ?>" aria-valuenow="<?= server_info_escape($memPct) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-<?= server_info_escape($memVariant) ?>" style="width: <?= server_info_escape($memPct) ?>%"></div>
                    </div>
                <?php endif; ?>
                <div class="text-muted small">
                    <?php if ($memSummary !== null): ?>
                        <?= server_info_escape(convert_ToMB($memSummary['used'])) ?> / <?= server_info_escape(convert_ToMB($memSummary['total'])) ?>
                        &middot; <?= server_info_escape(t('serverStatus', 'available')) ?>: <?= server_info_escape(convert_ToMB($memSummary['available'])) ?>
                    <?php else: ?>
                        <?= $naEsc ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 3: Root Disk -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold"><?= server_info_escape(t('serverStatus', 'rootDisk')) ?></div>
                        <div class="fs-4 fw-bold mt-1">
                            <?= ($diskPct !== null) ? server_info_escape(number_format($diskPct, 1) . '%') : $naEsc ?>
                        </div>
                    </div>
                    <div class="server-kpi-icon rounded-3 d-flex align-items-center justify-content-center bg-<?= server_info_escape($diskVariant) ?>-subtle text-<?= server_info_escape($diskVariant) ?>">
                        <i class="bi bi-hdd fs-4" aria-hidden="true"></i>
                    </div>
                </div>
                <?php if ($diskPct !== null): ?>
                    <div class="progress mb-2" style="height: 6px;" role="progressbar" aria-label="<?= server_info_escape(t('serverStatus', 'rootDisk')) ?>" aria-valuenow="<?= server_info_escape($diskPct) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-<?= server_info_escape($diskVariant) ?>" style="width: <?= server_info_escape($diskPct) ?>%"></div>
                    </div>
                <?php endif; ?>
                <div class="text-muted small">
                    <?php if ($rootFs !== null): ?>
                        <?= server_info_escape(sprintf(t('serverStatus', 'freeOf'), convert_ToMB($rootFs['free']), convert_ToMB($rootFs['total']))) ?>
                    <?php else: ?>
                        <?= $naEsc ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 4: Uptime & Host -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div class="min-w-0">
                        <div class="text-muted small text-uppercase fw-semibold"><?= server_info_escape(t('serverStatus', 'systemUptime')) ?></div>
                        <div class="fs-4 fw-bold mt-1"><?= server_info_escape($uptimeStr) ?></div>
                    </div>
                    <div class="server-kpi-icon rounded-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary">
                        <i class="bi bi-clock-history fs-4" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="text-muted small text-truncate" title="<?= server_info_escape($hostname . ' — ' . $distro) ?>">
                    <i class="bi bi-hdd-network me-1" aria-hidden="true"></i><strong><?= server_info_escape($hostname) ?></strong>
                    &middot; <?= server_info_escape($distro) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// -------------------------------------------------------------------------
// Render: 2. Detail Grid (System & Environment | Resources)
// -------------------------------------------------------------------------
$processorText = server_info_na();
if (!empty($cpuInfo['model'])) {
    $processorText = $cpuInfo['model'];
    if ($cpuInfo['cores'] !== null) {
        $processorText .= sprintf(' (%d %s)', $cpuInfo['cores'], $cpuInfo['cores'] === 1 ? t('serverStatus', 'core') : t('serverStatus', 'cores'));
    }
} elseif ($cpuInfo['cores'] !== null) {
    $processorText = sprintf('%d %s', $cpuInfo['cores'], $cpuInfo['cores'] === 1 ? t('serverStatus', 'core') : t('serverStatus', 'cores'));
}

$kernelText = ($kernel !== '') ? ($arch !== '' ? sprintf('%s (%s)', $kernel, $arch) : $kernel) : server_info_na();
$daloVersionText = ($daloDate !== '') ? sprintf('%s (%s)', $daloVersion, $daloDate) : $daloVersion;
?>
<div class="row g-3 mb-4">
    <!-- Left Column: System & Environment -->
    <div class="col-12 col-xl-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-light d-flex align-items-center gap-2">
                <i class="bi bi-pc-display text-primary" aria-hidden="true"></i>
                <h5 class="card-title mb-0 fs-6 fw-semibold"><?= server_info_escape(t('serverStatus', 'systemAndEnvironment')) ?></h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 server-kv-table">
                    <tbody>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'hostname')) ?></th>
                            <td class="pe-3"><code><?= server_info_escape($hostname) ?></code></td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'operatingSystem')) ?></th>
                            <td class="pe-3"><?= server_info_escape($distro) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'kernelAndArch')) ?></th>
                            <td class="pe-3"><?= server_info_escape($kernelText) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'processor')) ?></th>
                            <td class="pe-3"><?= server_info_escape($processorText) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'serverDateTime')) ?></th>
                            <td class="pe-3">
                                <?= server_info_escape($datetime) ?>
                                <span class="badge text-bg-light border text-muted ms-1"><?= server_info_escape($timezone) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'daloVersion')) ?></th>
                            <td class="pe-3"><?= server_info_escape($daloVersionText) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'phpRuntime')) ?></th>
                            <td class="pe-3"><?= server_info_escape($phpRuntime) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'webServer')) ?></th>
                            <td class="pe-3"><?= server_info_escape($webServer) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'database')) ?></th>
                            <td class="pe-3">
                                <?php if ($dbStatus['connected']): ?>
                                    <span class="badge text-bg-success me-1">
                                        <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i><?= server_info_escape(t('serverStatus', 'dbConnected')) ?>
                                    </span>
                                    <?php if (!empty($dbStatus['version'])): ?>
                                        <span><?= server_info_escape($dbStatus['version']) ?></span>
                                    <?php endif; ?>
                                    <div class="text-muted small mt-1">
                                        <?php if ($dbStatus['latency_ms'] !== null): ?>
                                            <?= server_info_escape(number_format($dbStatus['latency_ms'], 1) . ' ms') ?>
                                        <?php endif; ?>
                                        <?php if ($dbStatus['uptime'] !== null): ?>
                                            &middot; <?= server_info_escape(t('serverStatus', 'dbUptime')) ?>: <?= server_info_escape(server_info_format_duration($dbStatus['uptime'])) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge text-bg-danger">
                                        <i class="bi bi-x-circle-fill me-1" aria-hidden="true"></i><?= server_info_escape(t('serverStatus', 'dbUnavailable')) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="ps-3"><?= server_info_escape(t('serverStatus', 'freeradiusDaemon')) ?></th>
                            <td class="pe-3">
                                <?php if ($freeradiusLocal): ?>
                                    <span class="badge text-bg-success">
                                        <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i><?= server_info_escape(t('serverStatus', 'daemonRunningLocal')) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary" data-bs-toggle="tooltip" data-bs-title="<?= server_info_escape(t('serverStatus', 'daemonRemoteHint')) ?>">
                                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i><?= server_info_escape(t('serverStatus', 'daemonNotDetectedLocal')) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Load Average + Memory Breakdown + Storage -->
    <div class="col-12 col-xl-6 d-flex flex-column gap-3">
        <!-- Load Average Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-activity text-primary" aria-hidden="true"></i>
                    <h5 class="card-title mb-0 fs-6 fw-semibold"><?= server_info_escape(t('serverStatus', 'loadAverage')) ?></h5>
                </div>
                <?php if ($cpuInfo['cores'] !== null): ?>
                    <span class="text-muted small"><?= (int)$cpuInfo['cores'] ?> <?= server_info_escape($cpuInfo['cores'] === 1 ? t('serverStatus', 'core') : t('serverStatus', 'cores')) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($loadAvg !== null): ?>
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="border rounded px-2 py-2 text-center">
                                <div class="fs-5 fw-bold"><?= server_info_escape(number_format($loadAvg['1m'], 2)) ?></div>
                                <div class="text-muted small"><?= server_info_escape(t('serverStatus', 'load1m')) ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded px-2 py-2 text-center">
                                <div class="fs-5 fw-bold"><?= server_info_escape(number_format($loadAvg['5m'], 2)) ?></div>
                                <div class="text-muted small"><?= server_info_escape(t('serverStatus', 'load5m')) ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded px-2 py-2 text-center">
                                <div class="fs-5 fw-bold"><?= server_info_escape(number_format($loadAvg['15m'], 2)) ?></div>
                                <div class="text-muted small"><?= server_info_escape(t('serverStatus', 'load15m')) ?></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <span class="text-muted small"><?= $naEsc ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Memory Breakdown Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-memory text-primary" aria-hidden="true"></i>
                    <h5 class="card-title mb-0 fs-6 fw-semibold"><?= server_info_escape(t('serverStatus', 'memoryBreakdown')) ?></h5>
                </div>
                <?php if ($memSummary !== null): ?>
                    <span class="text-muted small"><?= server_info_escape(t('serverStatus', 'memTotal')) ?>: <strong><?= server_info_escape(convert_ToMB($memSummary['total'])) ?></strong></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($memSummary !== null): ?>
                    <?php
                        $usedBarClass = ($memVariant === 'success') ? 'bg-primary' : 'bg-' . $memVariant;
                        $usedPctVal = (float)($memSummary['used_pct'] ?? 0);
                        $reclaimablePctVal = min(max(0.0, 100.0 - $usedPctVal), (float)($memSummary['reclaimable_pct'] ?? 0));
                    ?>
                    <div class="progress-stacked mb-3" style="--bs-progress-height: 14px;">
                        <div class="progress" role="progressbar" aria-label="<?= server_info_escape(t('serverStatus', 'memUsed')) ?>" aria-valuenow="<?= server_info_escape($usedPctVal) ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= server_info_escape($usedPctVal) ?>%">
                            <div class="progress-bar <?= server_info_escape($usedBarClass) ?>"></div>
                        </div>
                        <div class="progress" role="progressbar" aria-label="<?= server_info_escape(t('serverStatus', 'memReclaimable')) ?>" aria-valuenow="<?= server_info_escape($reclaimablePctVal) ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= server_info_escape($reclaimablePctVal) ?>%">
                            <div class="progress-bar bg-info bg-opacity-75"></div>
                        </div>
                    </div>

                    <div class="row g-2 small mb-3">
                        <div class="col-6 col-sm-3">
                            <div class="d-flex align-items-center gap-1 text-muted">
                                <span class="server-legend-dot <?= server_info_escape($usedBarClass) ?>"></span>
                                <?= server_info_escape(t('serverStatus', 'memUsed')) ?>
                            </div>
                            <div class="fw-semibold"><?= server_info_escape(convert_ToMB($memSummary['used'])) ?></div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="d-flex align-items-center gap-1 text-muted">
                                <span class="server-legend-dot bg-info bg-opacity-75"></span>
                                <?= server_info_escape(t('serverStatus', 'memReclaimable')) ?>
                            </div>
                            <div class="fw-semibold"><?= server_info_escape(convert_ToMB($memSummary['reclaimable'])) ?></div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="d-flex align-items-center gap-1 text-muted">
                                <span class="server-legend-dot bg-secondary bg-opacity-25"></span>
                                <?= server_info_escape(t('serverStatus', 'memFree')) ?>
                            </div>
                            <div class="fw-semibold"><?= server_info_escape(convert_ToMB($memSummary['free'])) ?></div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="text-muted"><?= server_info_escape(t('serverStatus', 'memAvailable')) ?></div>
                            <div class="fw-semibold text-success"><?= server_info_escape(convert_ToMB($memSummary['available'])) ?></div>
                        </div>
                    </div>

                    <div class="border-top pt-2">
                        <div class="d-flex align-items-center justify-content-between small mb-1">
                            <span class="text-muted fw-semibold"><?= server_info_escape(t('serverStatus', 'swap')) ?></span>
                            <?php if ($memSummary['swap_total'] > 0): ?>
                                <span>
                                    <?= server_info_escape(convert_ToMB($memSummary['swap_used'])) ?> / <?= server_info_escape(convert_ToMB($memSummary['swap_total'])) ?>
                                    (<?= server_info_escape(number_format((float)$memSummary['swap_pct'], 1) . '%') ?>)
                                </span>
                            <?php else: ?>
                                <span class="text-muted"><?= server_info_escape(t('serverStatus', 'swapNotConfigured')) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($memSummary['swap_total'] > 0): ?>
                            <?php $swapVariant = server_info_threshold_variant($memSummary['swap_pct'], 50.0, 80.0); ?>
                            <div class="progress" style="height: 6px;" role="progressbar" aria-label="<?= server_info_escape(t('serverStatus', 'swap')) ?>" aria-valuenow="<?= server_info_escape($memSummary['swap_pct']) ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-<?= server_info_escape($swapVariant) ?>" style="width: <?= server_info_escape($memSummary['swap_pct']) ?>%"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted small"><?= $naEsc ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Storage & Filesystems Card -->
        <div class="card shadow-sm flex-grow-1">
            <div class="card-header bg-light d-flex align-items-center gap-2">
                <i class="bi bi-hdd-stack text-primary" aria-hidden="true"></i>
                <h5 class="card-title mb-0 fs-6 fw-semibold"><?= server_info_escape(t('serverStatus', 'storageFilesystems')) ?></h5>
            </div>
            <?php if (!empty($filesystems)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?= server_info_escape(t('serverStatus', 'mountPoint')) ?></th>
                                <th><?= server_info_escape(t('serverStatus', 'fsType')) ?></th>
                                <th><?= server_info_escape(t('serverStatus', 'usedTotal')) ?></th>
                                <th><?= server_info_escape(t('serverStatus', 'freeSpace')) ?></th>
                                <th class="pe-3"><?= server_info_escape(t('serverStatus', 'usage')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filesystems as $fs): ?>
                                <?php $fsVariant = server_info_threshold_variant($fs['percent'], 80.0, 90.0); ?>
                                <tr>
                                    <td class="ps-3">
                                        <code><?= server_info_escape($fs['mount']) ?></code>
                                    </td>
                                    <td class="text-muted small"><?= ($fs['fstype'] !== '') ? server_info_escape($fs['fstype']) : '&mdash;' ?></td>
                                    <td class="small"><?= server_info_escape(convert_ToMB($fs['used'])) ?> / <?= server_info_escape(convert_ToMB($fs['total'])) ?></td>
                                    <td class="small"><?= server_info_escape(convert_ToMB($fs['free'])) ?></td>
                                    <td class="pe-3 server-usage-cell">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-<?= server_info_escape($fsVariant) ?>" style="width: <?= server_info_escape($fs['percent'] ?? 0) ?>%"></div>
                                            </div>
                                            <span class="small fw-semibold"><?= ($fs['percent'] !== null) ? server_info_escape(number_format($fs['percent'], 1) . '%') : $naEsc ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card-body">
                    <div class="alert alert-info mb-0 py-2 small">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i><?= server_info_escape(t('serverStatus', 'noStorageData')) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// -------------------------------------------------------------------------
// Render: 3. Unified Network Interfaces Card
// -------------------------------------------------------------------------
$virtualCount = 0;
$nonVirtualCount = 0;
foreach ($interfaces as $iface) {
    if ($iface['virtual']) {
        $virtualCount++;
    } else {
        $nonVirtualCount++;
    }
}
$collapseVirtualByDefault = ($virtualCount > 0 && $nonVirtualCount > 0);
?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-ethernet text-primary" aria-hidden="true"></i>
            <h5 class="card-title mb-0 fs-6 fw-semibold"><?= server_info_escape(t('serverStatus', 'networkInterfaces')) ?></h5>
            <span class="badge text-bg-secondary"><?= count($interfaces) ?></span>
        </div>
        <?php if ($collapseVirtualByDefault): ?>
            <div class="form-check form-switch mb-0 small">
                <input class="form-check-input" type="checkbox" role="switch" id="toggleVirtualInterfaces">
                <label class="form-check-label text-muted" for="toggleVirtualInterfaces">
                    <?= server_info_escape(sprintf(t('serverStatus', 'showVirtualInterfaces'), $virtualCount)) ?>
                </label>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($interfaces)): ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0 align-middle server-net-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><?= server_info_escape(t('serverStatus', 'ifaceName')) ?></th>
                        <th><?= server_info_escape(t('serverStatus', 'ifaceStatus')) ?></th>
                        <th><?= server_info_escape(t('serverStatus', 'ifaceIpv4')) ?></th>
                        <th><?= server_info_escape(t('serverStatus', 'ifaceIpv6')) ?></th>
                        <th><?= server_info_escape(t('serverStatus', 'ifaceMac')) ?></th>
                        <th class="pe-3"><?= server_info_escape(t('serverStatus', 'ifaceTraffic')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interfaces as $iface): ?>
                        <?php
                            $rowHidden = ($collapseVirtualByDefault && $iface['virtual']);
                            if ($iface['state'] === 'UP') {
                                $stateBadgeClass = 'text-bg-success';
                                $stateLabel = t('serverStatus', 'stateUp');
                            } elseif ($iface['state'] === 'DOWN') {
                                $stateBadgeClass = 'text-bg-secondary';
                                $stateLabel = t('serverStatus', 'stateDown');
                            } else {
                                $stateBadgeClass = 'text-bg-light border text-dark';
                                $stateLabel = t('serverStatus', 'stateUnknown');
                            }
                        ?>
                        <tr<?= $iface['virtual'] ? ' data-virtual-iface="1"' : '' ?><?= $rowHidden ? ' class="d-none"' : '' ?>>
                            <td class="ps-3">
                                <code><?= server_info_escape($iface['name']) ?></code>
                                <?php if ($iface['virtual']): ?>
                                    <span class="badge text-bg-light border text-muted ms-1 fw-normal"><?= server_info_escape(t('serverStatus', 'ifaceVirtualBadge')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= server_info_escape($stateBadgeClass) ?>">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;" aria-hidden="true"></i><?= server_info_escape($stateLabel) ?>
                                </span>
                            </td>
                            <td class="server-net-wrap">
                                <?php if (!empty($iface['ipv4'])): ?>
                                    <?= implode('<br>', array_map('server_info_escape', $iface['ipv4'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="server-net-wrap small">
                                <?php if (!empty($iface['ipv6'])): ?>
                                    <?= implode('<br>', array_map('server_info_escape', $iface['ipv6'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($iface['mac'])): ?>
                                    <code><?= server_info_escape($iface['mac']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-3 small">
                                <?php if ($iface['rx'] !== null || $iface['tx'] !== null): ?>
                                    <span class="me-2" title="RX">
                                        <i class="bi bi-arrow-down text-success" aria-hidden="true"></i>
                                        <?= server_info_escape(convert_ToMB($iface['rx'])) ?>
                                    </span>
                                    <span title="TX">
                                        <i class="bi bi-arrow-up text-primary" aria-hidden="true"></i>
                                        <?= server_info_escape(convert_ToMB($iface['tx'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card-body">
            <div class="alert alert-info mb-0 py-2 small">
                <i class="bi bi-info-circle me-1" aria-hidden="true"></i><?= server_info_escape(t('serverStatus', 'noNetworkInterfaces')) ?>
            </div>
        </div>
    <?php endif; ?>
</div>
