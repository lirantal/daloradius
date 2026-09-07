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
 *********************************************************************************************************
 *
 * Description:    Displays service status for local installations and
 *                 connectivity status for network deployments.
 *
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// Prevent this file from being directly accessed.
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header("Location: ../../index.php");
    exit;
}

function dalo_radius_status_result($state, $detail = '') {
    return array('state' => $state, 'detail' => $detail);
}

function dalo_radius_is_container() {
    return file_exists('/.dockerenv') ||
           file_exists('/run/.containerenv') ||
           getenv('DOCKER_CONTAINER') !== false;
}

function dalo_radius_status_mode($config_values, $is_container) {
    $mode = strtolower(trim((string) ($config_values['CONFIG_STATUS_MODE'] ?? 'auto')));
    if ($mode === 'auto') {
        return $is_container ? 'network' : 'local';
    }

    return in_array($mode, array('local', 'network', 'disabled'), true) ? $mode : 'invalid';
}

function dalo_radius_find_executable($name) {
    $directories = defined('DALO_RADIUS_TEST_BIN_DIR')
        ? array(DALO_RADIUS_TEST_BIN_DIR)
        : array('/usr/bin', '/usr/sbin', '/bin', '/sbin');

    foreach ($directories as $directory) {
        if ($directory === '') {
            continue;
        }

        $candidate = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
        if (is_file($candidate) && is_executable($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function dalo_radius_run_process($command, $input = '', $timeout_seconds = 3.0) {
    if (!function_exists('proc_open')) {
        return null;
    }

    $descriptors = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );
    $pipes = array();
    $process = @proc_open($command, $descriptors, $pipes, null, null, array('bypass_shell' => true));
    if (!is_resource($process)) {
        return null;
    }

    $stdout = '';
    $stderr = '';
    $started_at = microtime(true);
    $timed_out = false;
    $failed = false;
    $last_status = null;
    $close_code = -1;

    try {
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $pipes[0] = null;
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        do {
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);
            $last_status = proc_get_status($process);
            if (!$last_status['running']) {
                break;
            }
            if (microtime(true) - $started_at >= $timeout_seconds) {
                $timed_out = true;
                break;
            }
            usleep(20000);
        } while (true);

        $stdout .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);
    } catch (Throwable $exception) {
        $failed = true;
    } finally {
        if (($timed_out || $failed) && is_resource($process)) {
            @proc_terminate($process);
            usleep(100000);
            $status = @proc_get_status($process);
            if (is_array($status) && $status['running']) {
                @proc_terminate($process, 9);
            }
        }
        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                @fclose($pipe);
            }
        }
        if (is_resource($process)) {
            $close_code = @proc_close($process);
        }
    }

    if ($failed) {
        return null;
    }
    $exit_code = $timed_out ? 124 : $close_code;
    if (!$timed_out && $exit_code === -1 && is_array($last_status) && $last_status['exitcode'] >= 0) {
        $exit_code = $last_status['exitcode'];
    }

    return array(
        'exit_code' => $exit_code,
        'stdout' => $stdout,
        'stderr' => $stderr,
        'timed_out' => $timed_out,
    );
}

/**
 * Check a local process and its systemd service aliases without requesting
 * privileges or passing through a shell.
 */
function dalo_radius_check_local_service($daemon_names, $service_names = array()) {
    $pgrep = dalo_radius_find_executable('pgrep');
    $systemctl = dalo_radius_find_executable('systemctl');
    if ($pgrep === null && $systemctl === null) {
        return dalo_radius_status_result('unknown', 'No supported local service probe is installed');
    }

    if ($pgrep !== null) {
        foreach ($daemon_names as $daemon) {
            $result = dalo_radius_run_process(array($pgrep, '-x', $daemon));
            if ($result === null) {
                return dalo_radius_status_result('unknown', 'Process execution is unavailable');
            }
            if ($result['exit_code'] === 0) {
                return dalo_radius_status_result('up');
            }
            if ($result['exit_code'] !== 1 || preg_match('/permission denied|not permitted/i', $result['stderr'])) {
                return dalo_radius_status_result('unknown', 'Process inspection failed');
            }
        }
    }

    $service_names = !empty($service_names) ? $service_names : $daemon_names;
    if ($systemctl !== null) {
        $saw_inactive = false;
        foreach ($service_names as $service) {
            $result = dalo_radius_run_process(array($systemctl, 'is-active', '--quiet', $service));
            if ($result === null) {
                return dalo_radius_status_result('unknown', 'Service execution is unavailable');
            }
            if ($result['exit_code'] === 0) {
                return dalo_radius_status_result('up');
            }
            if ($result['exit_code'] === 3) {
                $saw_inactive = true;
                continue;
            }
            if ($result['exit_code'] !== 4 || preg_match('/permission denied|not permitted/i', $result['stderr'])) {
                return dalo_radius_status_result('unknown', 'Service inspection failed');
            }
        }
        if ($saw_inactive) {
            return dalo_radius_status_result('down');
        }
    }

    return $pgrep !== null
        ? dalo_radius_status_result('down')
        : dalo_radius_status_result('unknown', 'Service state is unavailable');
}

function dalo_radius_status_secret_is_valid($secret) {
    return strlen($secret) >= 16 &&
           strncmp($secret, 'CHANGE_ME_', 10) !== 0 &&
           preg_match('/\A[A-Za-z0-9_+=.\/-]+\z/D', $secret) === 1;
}

function dalo_radius_status_secret_file($config_values, &$temporary_file) {
    $temporary_file = null;
    $configured_file = trim((string) ($config_values['CONFIG_STATUS_SECRET_FILE'] ?? ''));
    if ($configured_file !== '') {
        if (!is_file($configured_file) || !is_readable($configured_file)) {
            return null;
        }
        $permissions = fileperms($configured_file);
        $secret = file_get_contents($configured_file);
        if ($permissions === false || ($permissions & 0037) !== 0 || $secret === false ||
            !dalo_radius_status_secret_is_valid(rtrim($secret, "\r\n"))) {
            return null;
        }
        return $configured_file;
    }

    $secret = (string) ($config_values['CONFIG_STATUS_SECRET'] ?? '');
    if (!dalo_radius_status_secret_is_valid($secret)) {
        return null;
    }

    $temporary_file = tempnam(sys_get_temp_dir(), 'dalo-status-');
    if ($temporary_file === false) {
        $temporary_file = null;
        return null;
    }
    chmod($temporary_file, 0600);
    if (file_put_contents($temporary_file, $secret, LOCK_EX) === false) {
        unlink($temporary_file);
        $temporary_file = null;
        return null;
    }

    return $temporary_file;
}

/**
 * Probe the configured RADIUS Status-Server without exposing its secret in
 * process arguments. The pinned FreeRADIUS client supports -S and -b.
 */
function dalo_radius_check_network_radius($config_values) {
    $radclient = dalo_radius_find_executable('radclient');
    if ($radclient === null) {
        return dalo_radius_status_result('unknown', 'radclient is not installed');
    }

    $server = trim((string) ($config_values['CONFIG_STATUS_SERVER'] ?? ''));
    $port = intval($config_values['CONFIG_STATUS_PORT'] ?? 18122);
    if ($server === '' || $port < 1 || $port > 65535) {
        return dalo_radius_status_result('unknown', 'Status-Server is not configured');
    }

    $temporary_file = null;
    $secret_file = dalo_radius_status_secret_file($config_values, $temporary_file);
    if ($secret_file === null) {
        return dalo_radius_status_result('unknown', 'Status secret is not configured');
    }

    $target = filter_var($server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false
        ? sprintf('[%s]:%d', $server, $port)
        : sprintf('%s:%d', $server, $port);
    $probe = "Message-Authenticator = 0x00\nFreeRADIUS-Statistics-Type = 1\n";

    try {
        $result = dalo_radius_run_process(
            array($radclient, '-b', '-r', '1', '-t', '1', '-S', $secret_file, $target, 'status'),
            $probe
        );
    } finally {
        if ($temporary_file !== null && is_file($temporary_file)) {
            unlink($temporary_file);
        }
    }

    if ($result === null) {
        return dalo_radius_status_result('unknown', 'RADIUS probe execution failed');
    }

    if ($result['timed_out']) {
        return dalo_radius_status_result('unknown', 'RADIUS probe timed out');
    }

    $output = $result['stdout'] . "\n" . $result['stderr'];
    $received_response = preg_match(
        '/(?:Received\s+)?(?:Status-Server-Response|Access-(?:Accept|Reject|Challenge))/i',
        $output
    ) === 1;

    return $result['exit_code'] === 0 && $received_response
        ? dalo_radius_status_result('up')
        : dalo_radius_status_result('down', 'No authenticated Status-Server response');
}

function dalo_radius_effective_database_config($config_values) {
    $location_name = $_SESSION['location_name'] ?? 'default';
    if ($location_name !== 'default' &&
        isset($config_values['CONFIG_LOCATIONS'][$location_name]) &&
        is_array($config_values['CONFIG_LOCATIONS'][$location_name])) {
        $location = $config_values['CONFIG_LOCATIONS'][$location_name];
        return array(
            'engine' => $location['Engine'] ?? '',
            'user' => $location['Username'] ?? '',
            'password' => $location['Password'] ?? '',
            'host' => $location['Hostname'] ?? '',
            'port' => $location['Port'] ?? '',
            'database' => $location['Database'] ?? '',
        );
    }

    return array(
        'engine' => $config_values['CONFIG_DB_ENGINE'] ?? '',
        'user' => $config_values['CONFIG_DB_USER'] ?? '',
        'password' => $config_values['CONFIG_DB_PASS'] ?? '',
        'host' => $config_values['CONFIG_DB_HOST'] ?? '',
        'port' => $config_values['CONFIG_DB_PORT'] ?? '',
        'database' => $config_values['CONFIG_DB_NAME'] ?? '',
    );
}

function dalo_radius_check_database($config_values) {
    $database = dalo_radius_effective_database_config($config_values);
    foreach (array('engine', 'user', 'host', 'port', 'database') as $required) {
        if ((string) $database[$required] === '') {
            return dalo_radius_status_result('unknown', 'Database is not configured');
        }
    }

    $engine = strtolower((string) $database['engine']);
    $port = intval($database['port']);
    if ($port < 1 || $port > 65535) {
        return dalo_radius_status_result('unknown', 'Database port is invalid');
    }

    if (in_array($engine, array('mysqli', 'mysql', 'mariadb'), true)) {
        if (!function_exists('mysqli_init')) {
            return dalo_radius_status_result('unknown', 'mysqli is not installed');
        }

        $connection = null;
        try {
            $connection = mysqli_init();
            if ($connection === false) {
                return dalo_radius_status_result('unknown', 'Unable to initialize mysqli');
            }
            mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 2);
            if (defined('MYSQLI_OPT_READ_TIMEOUT')) {
                mysqli_options($connection, MYSQLI_OPT_READ_TIMEOUT, 2);
            }
            if (!@mysqli_real_connect(
                $connection,
                $database['host'],
                $database['user'],
                $database['password'],
                $database['database'],
                $port
            )) {
                @mysqli_close($connection);
                return dalo_radius_status_result('down', 'Database connection failed');
            }

            $result = @mysqli_query($connection, 'SELECT 1');
            @mysqli_close($connection);
            return $result !== false
                ? dalo_radius_status_result('up')
                : dalo_radius_status_result('down', 'Database query failed');
        } catch (Throwable $exception) {
            if ($connection instanceof mysqli) {
                try {
                    mysqli_close($connection);
                } catch (Throwable $ignored) {
                    // The failed connection may already be closed.
                }
            }
            return dalo_radius_status_result('down', 'Database connection failed');
        }
    }

    if (in_array($engine, array('pgsql', 'postgres', 'postgresql'), true)) {
        if (!function_exists('pg_connect')) {
            return dalo_radius_status_result('unknown', 'pgsql is not installed');
        }
        $quote = static function ($value) {
            return "'" . str_replace(array('\\', "'"), array('\\\\', "\\'"), (string) $value) . "'";
        };
        $connection_string = sprintf(
            'host=%s port=%d dbname=%s user=%s password=%s connect_timeout=2',
            $quote($database['host']),
            $port,
            $quote($database['database']),
            $quote($database['user']),
            $quote($database['password'])
        );
        try {
            $connection = @pg_connect($connection_string, PGSQL_CONNECT_FORCE_NEW);
            if ($connection === false) {
                return dalo_radius_status_result('down', 'Database connection failed');
            }
            $result = @pg_query($connection, 'SELECT 1');
            @pg_close($connection);
            return $result !== false
                ? dalo_radius_status_result('up')
                : dalo_radius_status_result('down', 'Database query failed');
        } catch (Throwable $exception) {
            return dalo_radius_status_result('down', 'Database connection failed');
        }
    }

    return dalo_radius_status_result('unknown', 'Database driver has no bounded status probe');
}

function dalo_radius_render_status($status, $down_label = 'not running') {
    $labels = array(
        'up' => array('text-success', 'running'),
        'down' => array('text-danger', $down_label),
        'unknown' => array('text-warning', 'status unavailable'),
        'disabled' => array('text-muted fst-italic', 'disabled'),
        'na' => array('text-muted fst-italic', 'N/A (container)'),
    );
    $state = isset($labels[$status['state']]) ? $status['state'] : 'unknown';
    return sprintf(
        '<span class="%s fw-bold">%s</span>',
        $labels[$state][0],
        htmlspecialchars($labels[$state][1], ENT_QUOTES, 'UTF-8')
    );
}

if (!defined('DALO_RADIUS_STATUS_FUNCTIONS_ONLY')) {
    $is_container = dalo_radius_is_container();
    $status_mode = dalo_radius_status_mode($configValues ?? array(), $is_container);

    if ($status_mode === 'network') {
        $status_freeradius = dalo_radius_check_network_radius($configValues ?? array());
    } elseif ($status_mode === 'local') {
        $status_freeradius = dalo_radius_check_local_service(
            array('freeradius', 'radiusd'),
            array('freeradius', 'radiusd')
        );
    } elseif ($status_mode === 'disabled') {
        $status_freeradius = dalo_radius_status_result('disabled');
    } else {
        $status_freeradius = dalo_radius_status_result('unknown', 'Invalid status mode');
    }

    $status_database = dalo_radius_check_database($configValues ?? array());
    $status_sshd = $is_container
        ? dalo_radius_status_result('na')
        : dalo_radius_check_local_service(array('sshd'), array('ssh', 'sshd'));

    $table = array(
        'title' => (isset($title) && $title !== '') ? $title : 'Service Status',
        'rows' => array(
            array('FreeRADIUS', dalo_radius_render_status($status_freeradius, 'unreachable')),
            array('MySQL / MariaDB', dalo_radius_render_status($status_database, 'connection failed')),
            array('SSHd', dalo_radius_render_status($status_sshd)),
        )
    );

    print_simple_table($table);
}
