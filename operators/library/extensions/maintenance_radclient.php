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
 * Description:    this code allows for running the 'radclient' binary tool provided with FreeRADIUS
 *                 for performing a dry-run check to see if a user is able to successfully login
 *                 or there may be problems connecting.
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Giso Kegal <kegel@barum.de>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */


// prevent this file to be directly accessed
$extension_file = '/library/extensions/maintenance_radclient.php';
if (strpos($_SERVER['PHP_SELF'], $extension_file) !== false) {
    header("Location: ../../index.php");
    exit;
}

$extension_file_enc = htmlspecialchars($extension_file, ENT_QUOTES, 'UTF-8');

// returns the absolute path of the radclient binary if found, otherwise false
function is_radclient_present() {
    exec("(which radclient || command -v radclient) 2> /dev/null", $output, $result_code);
    return ($result_code === 0) ? $output[0] : false;
}


// guarantees that optional radclient parmeters are correctly set up
function radclient_common_options($params) {

    $count = (array_key_exists('count', $params) && $params['count'] > 0) ? $params['count'] : 1;
    $requests = (array_key_exists('requests', $params) && $params['requests'] > 0) ? $params['requests'] : 1;
    $retries = (array_key_exists('retries', $params) && $params['retries'] > 0) ? $params['retries'] : 10;
    $timeout = (array_key_exists('timeout', $params) && $params['timeout'] > 0) ? $params['timeout'] : 3;
    $debug = (array_key_exists('debug', $params) && $params['debug'] == true) ? "-x" : "";
    $simulate = (array_key_exists('simulate', $params) && $params['simulate'] == true);

    $params["count"] = intval($count);
    $params["requests"] = intval($requests);
    $params["retries"] = intval($retries);
    $params["timeout"] = intval($timeout);
    $params["debug"] = boolval($debug);
    $params["simulate"] = boolval($simulate);

    return $params;
}


function user_disconnect($params) {

    $radclient_path = is_radclient_present();

    if ($radclient_path === false) {
        return array(
                        "error" => true,
                        "output" => "radclient binary not found on the system",
                    );
    }
    
    $required_params = array( "nas_id", "command", "username",  );
    
    $missing_params = array();
    foreach ($required_params as $required_param) {
        if (!array_key_exists($required_param, $params)) {
            $missing_params[] = $required_param;
        }
    }
    
    $valid_commands = array( "coa", "disconnect" );
    if (!in_array($params['command'], $valid_commands)) {
        return array(
                        "error" => true,
                        "output" => sprintf("'%s': invalid command", $params['command']),
                    );
    }
    
    // get nas details
    include('../common/includes/db_open.php');
        
    $sql = sprintf("SELECT DISTINCT(nasname), ports, secret FROM %s WHERE id=%d",
                   $configValues['CONFIG_DB_TBL_RADNAS'], $params['nas_id']);
    $res = $dbSocket->query($sql);

    list($addr, $port, $secret) = $res->fetchrow();
    
    include('../common/includes/db_close.php');
    
    $params = radclient_common_options($params);
    $count = (array_key_exists('count', $params) && $params['count'] > 0) ? $params['count'] : 1;
    $requests = (array_key_exists('requests', $params) && $params['requests'] > 0) ? $params['requests'] : 1;
    $retries = (array_key_exists('retries', $params) && $params['retries'] > 0) ? $params['retries'] : 10;
    $timeout = (array_key_exists('timeout', $params) && $params['timeout'] > 0) ? $params['timeout'] : 3;
    $debug = (array_key_exists('debug', $params) && $params['debug'] == true) ? "-x" : "";
    $command = (array_key_exists('command', $params) && in_array($params['command'], $valid_commands)) ? $params['command'] : "auth";
    
    // prepare radclient arguments
    $server_port = sprintf("%s:%d", $addr, $port);
    $positional_args = sprintf("%s %s %s", escapeshellarg($server_port), escapeshellarg($command), escapeshellarg($secret));
    
    $query = sprintf("User-Name=%s", escapeshellarg($params['username']));
    
    if (array_key_exists('customAttributes', $params) && !empty($params['customAttributes'])) {
        $query_params = array();
        
        $attr_values = explode(",", $params['customAttributes']);
        foreach ($attr_values as $attr_value) {
            list($attr, $value) = explode("=", $attr_value);
            $attr = trim($attr);
            $value = trim($value);
            
            if (!empty($attr) && !empty($value) && $attr !== 'User-Name') {
                $query_params[escapeshellarg($attr)] = escapeshellarg($value);
            }
        }
        
        $query .= ", " . implode(",", $query_params);
    }
    
    // other radclient options
    $radclient_options = sprintf(" -c %s -n %s -r %s -t %s %s", escapeshellarg($count), escapeshellarg($requests),
                                                                escapeshellarg($retries), escapeshellarg($timeout), $debug);
    
    if (isset($params['dictionary']) && !empty(trim($params['dictionary']))) {
        $radclient_options .= sprintf(" -d %s", escapeshellarg(trim($params['dictionary'])));
    }
    
    $cmd = sprintf('echo "%s" | %s %s %s 2>&1', escapeshellcmd($query), $radclient_path, $radclient_options, $positional_args);
    
    if ($params['simulate']) {
        return array(
                        "error" => false,
                        "output" => "$cmd (not executed)",
                    );
    }
    
    $result = shell_exec($cmd);

    if (empty($result)) {
        return array(
                        "error" => true,
                        "output" => "command did not return any output",
                    );
    }

    return array(
                    "error" => false,
                    "output" => "$cmd\n$result"
                );
    
}


// user_auth function
// $params is an associative array.
// "command", "username", "password", "password_type", "server", "port", "secret" are required keywords
//
// other allowed keywords are: "count", "requests", "retries", "timeout" and "debug", "dictionary", "simulate"
function user_auth($params) {

    $radclient_path = is_radclient_present();

    if ($radclient_path === false) {
        return array(
                        "error" => true,
                        "output" => "radclient binary not found on the system",
                    );
    }

    include_once('library/validation.php');
    global $valid_passwordTypes;
    
    $required_params = array( "command", "username", "password", "password_type", "server", "port", "secret" );
    
    $missing_params = array();
    foreach ($required_params as $required_param) {
        if (!array_key_exists($required_param, $params)) {
            $missing_params[] = $required_param;
        }
    }
    
    if (count($missing_params) > 0) {
        return array(
                        "error" => true,
                        "output" => sprintf("Missing params: %s", implode(", ", $missing_params)),
                    );
    }
    
    $valid_commands = array( "auth", "status" );
    if (!in_array($params['command'], $valid_commands)) {
        return array(
                        "error" => true,
                        "output" => sprintf("'%s': invalid command", $params['command']),
                    );
    }
    
    if (!in_array($params['password_type'], $valid_passwordTypes)) {
        return array(
                        "error" => true,
                        "output" => sprintf("'%s': invalid password type", $params['password_type']),
                    );
    }
    
    if (filter_var(trim($params['server']), FILTER_VALIDATE_IP) === false) {
        return array(
                        "error" => true,
                        "output" => "Invalid RADIUS server IP address",
                    );
    }
    
    // validate other params
    $command = (array_key_exists('command', $params) && in_array($params['command'], $valid_commands)) ? $params['command'] : "auth";
    $port = (array_key_exists('port', $params) && $params['port'] >= 1 && $params['port'] <= 65535) ? $params['port'] : 1812;
    $secret = (array_key_exists('secret', $params) && !empty(trim($params['secret']))) ? escapeshellarg(trim($params['secret'])) : "testing123";

    $params = radclient_common_options($params);
    $count = (array_key_exists('count', $params) && $params['count'] > 0) ? $params['count'] : 1;
    $requests = (array_key_exists('requests', $params) && $params['requests'] > 0) ? $params['requests'] : 1;
    $retries = (array_key_exists('retries', $params) && $params['retries'] > 0) ? $params['retries'] : 10;
    $timeout = (array_key_exists('timeout', $params) && $params['timeout'] > 0) ? $params['timeout'] : 3;
    $debug = (array_key_exists('debug', $params) && $params['debug'] == true) ? "-x" : "";

    // prepare radclient arguments
    $server_port = sprintf("%s:%d", $params['server'], $params['port']);
    $positional_args = sprintf("%s %s %s", escapeshellarg($server_port), escapeshellarg($command), $secret);
    
    $query = sprintf("User-Name=%s,%s=%s", escapeshellarg($params['username']), $params['password_type'], escapeshellarg($params['password']));
    
    // other radclient options
    $radclient_options = sprintf(" -c %s -n %s -r %s -t %s %s", escapeshellarg($count), escapeshellarg($requests),
                                                                escapeshellarg($retries), escapeshellarg($timeout), $debug);
    
    if (isset($params['dictionary']) && !empty(trim($params['dictionary']))) {
        $radclient_options .= sprintf(" -d %s", escapeshellarg(trim($params['dictionary'])));
    }

    $cmd = sprintf('echo "%s" | %s %s %s 2>&1', escapeshellcmd($query), $radclient_path, $radclient_options, $positional_args);
    
    if ($params['simulate']) {
        return array(
                        "error" => false,
                        "output" => "$cmd (not executed)",
                    );
    }
    
    $result = shell_exec($cmd);

    if (empty($result)) {
        return array(
                        "error" => true,
                        "output" => "command did not return any output",
                    );
    }

    return array(
                    "error" => false,
                    "output" => "$cmd\n$result"
                );
}
