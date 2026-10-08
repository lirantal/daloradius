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
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
/* Render the complete proxy configuration from the caller's PDO transaction. */
if (strpos($_SERVER['PHP_SELF'] ?? '', '/include/management/saveRealmsProxys.php') !== false) {
    http_response_code(404);
    exit;
}

function realm_proxy_config_value($value) {
    if (!is_scalar($value) && $value !== null) {
        throw new RuntimeException('Invalid proxy configuration value');
    }
    $value = (string)$value;
    if (preg_match('/[\r\n{}\x00]/', $value)) {
        throw new RuntimeException('Invalid proxy configuration syntax');
    }
    return $value;
}

function realm_proxy_render(PDO $pdo, $proxyTable, $realmTable) {
    $output = '# daloradius - ' . date('Y-m-d_H:i:s') . "\n\n";
    $proxies = $pdo->query("SELECT proxyname,retry_delay,retry_count,dead_time,default_fallback "
                           . "FROM $proxyTable ORDER BY id");
    foreach ($proxies as $row) {
        if (!$row['proxyname']) { continue; }
        $output .= 'proxy ' . realm_proxy_config_value($row['proxyname']) . " { \n";
        foreach (array('retry_delay','retry_count','dead_time','default_fallback') as $param) {
            if ($row[$param]) { $output .= "\t$param = " . realm_proxy_config_value($row[$param]) . "\n"; }
        }
        $output .= "}\n\n";
    }
    $output .= "\n\n";
    $realms = $pdo->query("SELECT realmname,type,authhost,accthost,secret,ldflag,nostrip,hints,notrealm "
                          . "FROM $realmTable ORDER BY id");
    foreach ($realms as $row) {
        if (!$row['realmname']) { continue; }
        $output .= 'realm ' . realm_proxy_config_value($row['realmname']) . " { \n";
        foreach (array('type','authhost','accthost','secret','ldflag','hints','notrealm') as $param) {
            if ($row[$param] !== null && $row[$param] !== '' &&
                (!in_array($param, array('hints','notrealm'), true) || (int)$row[$param] !== 0)) {
                $output .= "\t$param = " . realm_proxy_config_value($row[$param]) . "\n";
            }
        }
        if ($row['nostrip']) { $output .= "\tnostrip\n"; }
        $output .= "}\n\n";
    }
    return $output;
}

function realm_proxy_file_target($config) {
    $path = $config['CONFIG_FILE_RADIUS_PROXY'] ?? null;
    if (!is_string($path) || $path === '' || !is_file($path) || is_link($path) ||
        !is_readable($path) || !is_writable($path) || !is_writable(dirname($path))) {
        throw new RuntimeException('Proxy configuration file unavailable');
    }
    $old = file_get_contents($path);
    if (!is_string($old) || strncmp($old, '# daloradius', 12) !== 0) {
        throw new RuntimeException('Proxy configuration is not managed by daloRADIUS');
    }
    return array($path, $old);
}

function realm_proxy_stage_file($path, $content) {
    $tmp = tempnam(dirname($path), '.dalo-proxy-');
    if ($tmp === false) { throw new RuntimeException('Cannot stage proxy configuration'); }
    try {
        $source = stat($path);
        if ($source === false) { throw new RuntimeException('Cannot stat proxy configuration'); }
        // Keep the staging file mode 0600 while it contains unpublished secrets.
        $fd = fopen($tmp, 'wb');
        if ($fd === false) { throw new RuntimeException('Cannot open proxy staging file'); }
        try {
            $length = strlen($content);
            for ($offset = 0; $offset < $length; $offset += $written) {
                $written = fwrite($fd, substr($content, $offset));
                if ($written === false || $written === 0) {
                    throw new RuntimeException('Cannot write proxy staging file');
                }
            }
            if (!fflush($fd) || (function_exists('fsync') && !fsync($fd))) {
                throw new RuntimeException('Cannot sync proxy staging file');
            }
        } finally { fclose($fd); }
        if (!@chown($tmp, $source['uid']) || !@chgrp($tmp, $source['gid']) ||
            !@chmod($tmp, $source['mode'] & 0777)) {
            throw new RuntimeException('Cannot preserve proxy file permissions');
        }
        clearstatcache(true, $tmp);
        $staged = stat($tmp);
        if ($staged === false || $staged['uid'] !== $source['uid'] ||
            $staged['gid'] !== $source['gid'] ||
            ($staged['mode'] & 0777) !== ($source['mode'] & 0777)) {
            throw new RuntimeException('Proxy file permissions changed');
        }
        return $tmp;
    } catch (Throwable $exception) {
        @unlink($tmp);
        throw $exception;
    }
}
