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
 * Description:    This script facilitates the execution of the 'radclient' binary tool from FreeRADIUS,
 *                 enabling a dry-run check to verify a user's ability to log in successfully or
 *                 to diagnose potential connectivity issues.
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

/**
 * Check if the radclient binary is present in the system.
 *
 * This function executes the shell command to check the availability of the radclient binary.
 * If found, it returns the absolute path of the radclient binary; otherwise, it returns false.
 *
 * @return string|false The absolute path of the radclient binary if found, otherwise false.
 */
function is_radclient_present() {
    exec("(which radclient || command -v radclient) 2> /dev/null", $output, $result_code);
    return ($result_code === 0) ? $output[0] : false;
}


/**
 * Ensure that optional radclient parameters are correctly set up.
 *
 * This function checks if optional parameters for radclient are provided and sets default values if not.
 * It also converts the parameters to the appropriate data types.
 *
 * @param array $params An associative array containing radclient parameters.
 *                     Possible parameters include 'count', 'requests', 'retries', 'timeout', 'debug', and 'simulate'.
 * @return array An associative array containing radclient parameters with correct values and data types.
 */
function radclient_common_options($params) {
    // Set default values for optional parameters if not provided
    $count = (array_key_exists('count', $params) && $params['count'] > 0) ? $params['count'] : 1;
    $requests = (array_key_exists('requests', $params) && $params['requests'] > 0) ? $params['requests'] : 1;
    $retries = (array_key_exists('retries', $params) && $params['retries'] > 0) ? $params['retries'] : 10;
    $timeout = (array_key_exists('timeout', $params) && $params['timeout'] > 0) ? $params['timeout'] : 3;
    $debug = (array_key_exists('debug', $params) && $params['debug'] == true) ? "-x" : "";
    $simulate = (array_key_exists('simulate', $params) && $params['simulate'] == true);

    // Convert parameters to appropriate data types
    $params["count"] = intval($count);
    $params["requests"] = intval($requests);
    $params["retries"] = intval($retries);
    $params["timeout"] = intval($timeout);
    $params["debug"] = boolval($debug);
    $params["simulate"] = boolval($simulate);

    return $params;
}


/**
 * Disconnects a user from the network.
 *
 * This function disconnects a user from the network by sending a COA (Change of Authorization)
 * or disconnect command to the RADIUS server using the radclient utility.
 * It requires certain parameters to be provided, including 'nas_id', 'command', and 'username'.
 * If any of the required parameters are missing or if the command is invalid, an error message is returned.
 * The function also retrieves necessary information from the database, such as NAS details.
 * Additionally, it prepares radclient arguments, custom attributes, and other radclient options
 * based on the provided parameters. It then executes the radclient command to disconnect the user.
 *
 * @param array $params An associative array containing the parameters required for disconnecting the user.
 *                      Required parameters: 'nas_id', 'command', 'username'.
 *                      Optional parameters: 'count', 'requests', 'retries', 'timeout', 'debug', 'customAttributes', 'dictionary', 'simulate'.
 * @return array An associative array containing the result of the operation.
 *               If successful, 'error' will be set to false and 'output' will contain the command executed and the result.
 *               If unsuccessful, 'error' will be set to true and 'output' will contain an error message.
 */
function user_disconnect($params) {
    global $configValues;

    // Check if radclient binary is present
    $radclient_path = is_radclient_present();

    // Return error if radclient binary is not found
    if ($radclient_path === false) {
        return array(
            "error" => true,
            "output" => "radclient binary not found on the system",
        );
    }

    // Required parameters for disconnecting the user
    $required_params = array("nas_id", "command", "username");

    // Check for missing required parameters
    $missing_params = array();
    foreach ($required_params as $required_param) {
        if (!array_key_exists($required_param, $params)) {
            $missing_params[] = $required_param;
        }
    }

    // Return error if any required parameter is missing
    if (!empty($missing_params)) {
        return array(
            "error" => true,
            "output" => "Missing required parameter(s): " . implode(', ', $missing_params),
        );
    }

    // Valid commands for radclient
    $valid_commands = array("coa", "disconnect");

    // Return error if command is invalid
    if (!in_array($params['command'], $valid_commands)) {
        return array(
            "error" => true,
            "output" => sprintf("'%s': invalid command", $params['command']),
        );
    }

    // Get NAS details from the database
    include implode(DIRECTORY_SEPARATOR, [$configValues['COMMON_INCLUDES'], 'db_open.php']);

    $sql = sprintf("SELECT DISTINCT(`nasname`), `ports`, `secret`
                      FROM `%s` WHERE `id`=?", $configValues['CONFIG_DB_TBL_RADNAS']);
    $prepared = $dbSocket->prepare($sql);
    $res = $dbSocket->execute($prepared, $params['nas_id']);

    list($addr, $port, $secret) = $res->fetchrow();

    include implode(DIRECTORY_SEPARATOR, [$configValues['COMMON_INCLUDES'], 'db_close.php']);

    // Set radclient options
    $params = radclient_common_options($params);

    // Prepare radclient arguments
    $count = $params['count'];
    $requests = $params['requests'];
    $retries = $params['retries'];
    $timeout = $params['timeout'];
    $debug = $params['debug'];
    $command = in_array($params['command'], $valid_commands) ? $params['command'] : "auth";

    // Prepare radclient query
    $server_port = sprintf("%s:%d", $addr, $port);
    $positional_args = sprintf("%s %s %s", escapeshellarg($server_port), escapeshellarg($command), escapeshellarg($secret));

    // Include custom attributes in the query
    $query = sprintf("User-Name=%s", escapeshellarg($params['username']));
    if (isset($params['customAttributes']) && !empty($params['customAttributes'])) {
        $attr_values = explode(",", $params['customAttributes']);
        foreach ($attr_values as $attr_value) {
            list($attr, $value) = explode("=", $attr_value);
            $attr = trim($attr);
            $value = trim($value);

            include_once implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);
            if (!empty($attr) && !empty($value) && $attr !== 'User-Name' && preg_match(ALLOWED_ATTRIBUTE_CHARS_REGEX, $attr) === 1) {
                $query .= sprintf(", %s=%s", $attr, escapeshellarg($value));
            }
        }
    }

    // Set radclient options
    $radclient_options = sprintf(" -c %s -n %s -r %s -t %s %s", escapeshellarg($count), escapeshellarg($requests),
        escapeshellarg($retries), escapeshellarg($timeout), $debug);

    // Add dictionary option if provided
    if (isset($params['dictionary']) && !empty(trim($params['dictionary']))) {
        $radclient_options .= sprintf(" -d %s", escapeshellarg(trim($params['dictionary'])));
    }

    // Construct radclient command
    $cmd = sprintf('echo "%s" | %s %s %s 2>&1', escapeshellcmd($query), $radclient_path, $radclient_options, $positional_args);

    // Simulate mode
    if ($params['simulate']) {
        return array(
            "error" => false,
            "output" => "$cmd (not executed)",
        );
    }

    // Execute radclient command
    $result = shell_exec($cmd);

    // Return error if command did not return any output
    if (empty($result)) {
        return array(
            "error" => true,
            "output" => "Command did not return any output",
        );
    }

    return array(
        "error" => false,
        "output" => "$cmd\n$result"
    );
}


/**
 * Authenticate a user against the RADIUS server.
 *
 * This function authenticates a user against the RADIUS server by sending an authentication
 * request using the radclient utility. It requires several parameters to be provided,
 * including 'command', 'username', 'password', 'password_type', 'server', 'port', and 'secret'.
 * If any of the required parameters are missing or if the command, password type, or server IP address is invalid,
 * an error message is returned. The function also validates other parameters such as port and secret.
 * Additionally, it prepares radclient arguments, custom attributes, and other radclient options
 * based on the provided parameters. It then executes the radclient command to authenticate the user.
 *
 * @param array $params An associative array containing the parameters required for authenticating the user.
 *                      Required parameters: 'command', 'username', 'password', 'password_type', 'server', 'port', 'secret'.
 *                      Optional parameters: 'count', 'requests', 'retries', 'timeout', 'debug', 'dictionary', 'simulate'.
 * @return array An associative array containing the result of the authentication operation.
 *               If successful, 'error' will be set to false and 'output' will contain the command executed and the result.
 *               If unsuccessful, 'error' will be set to true and 'output' will contain an error message.
 */
function user_auth($params) {
    global $configValues;

    // Check if radclient binary is present
    $radclient_path = is_radclient_present();

    // Return error if radclient binary is not found
    if ($radclient_path === false) {
        return array(
            "error" => true,
            "output" => "radclient binary not found on the system",
        );
    }

    // Include validation functions
    include_once implode(DIRECTORY_SEPARATOR, [$configValues['COMMON_INCLUDES'], 'validation.php']);
    global $valid_passwordTypes;

    // Required parameters for user authentication
    $required_params = array("command", "username", "password", "password_type", "server", "port", "secret");

    // Check for missing required parameters
    $missing_params = array();
    foreach ($required_params as $required_param) {
        if (!array_key_exists($required_param, $params)) {
            $missing_params[] = $required_param;
        }
    }

    // Return error if any required parameter is missing
    if (!empty($missing_params)) {
        return array(
            "error" => true,
            "output" => "Missing required parameter(s): " . implode(", ", $missing_params),
        );
    }

    // Valid commands for radclient
    $valid_commands = array("auth", "status");

    // Return error if command is invalid
    if (!in_array($params['command'], $valid_commands)) {
        return array(
            "error" => true,
            "output" => sprintf("'%s': invalid command", $params['command']),
        );
    }

    // Return error if password type is invalid
    if (!in_array($params['password_type'], $valid_passwordTypes)) {
        return array(
            "error" => true,
            "output" => sprintf("'%s': invalid password type", $params['password_type']),
        );
    }

    // Return error if server IP address is invalid
    if (filter_var(trim($params['server']), FILTER_VALIDATE_IP) === false) {
        return array(
            "error" => true,
            "output" => "Invalid RADIUS server IP address",
        );
    }

    // Validate other parameters
    $command = in_array($params['command'], $valid_commands) ? $params['command'] : "auth";
    $port = ($params['port'] >= 1 && $params['port'] <= 65535) ? $params['port'] : 1812;
    $secret = !empty(trim($params['secret'])) ? escapeshellarg(trim($params['secret'])) : "testing123";

    // Set radclient options
    $params = radclient_common_options($params);
    $count = $params['count'];
    $requests = $params['requests'];
    $retries = $params['retries'];
    $timeout = $params['timeout'];
    $debug = $params['debug'];

    // Prepare radclient arguments
    $server_port = sprintf("%s:%d", $params['server'], $params['port']);
    $positional_args = sprintf("%s %s %s", escapeshellarg($server_port), escapeshellarg($command), $secret);

    // Prepare radclient query
    $query = sprintf("User-Name=%s,%s=%s", escapeshellarg($params['username']), $params['password_type'], escapeshellarg($params['password']));

    // Other radclient options
    $radclient_options = sprintf(" -c %s -n %s -r %s -t %s -x", escapeshellarg($count), escapeshellarg($requests),
        escapeshellarg($retries), escapeshellarg($timeout));

    // Add dictionary option if provided
    if (isset($params['dictionary']) && !empty(trim($params['dictionary']))) {
        $radclient_options .= sprintf(" -d %s", escapeshellarg(trim($params['dictionary'])));
    }

    // Construct radclient command
    $cmd = sprintf('echo "%s" | %s %s %s 2>&1', escapeshellcmd($query), $radclient_path, $radclient_options, $positional_args);

    // Simulate mode
    if ($params['simulate']) {
        return array(
            "error" => false,
            "output" => "$cmd (not executed)",
        );
    }

    // Execute radclient command
    $result = shell_exec($cmd);

    // Return error if command did not return any output
    if (empty($result)) {
        return array(
            "error" => true,
            "output" => "Command did not return any output",
        );
    }

    return array(
        "error" => false,
        "output" => "$cmd\n$result"
    );
}
