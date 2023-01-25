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
 *        This code allows for running the 'radtest' binary tool provided with freeradius
 *        for performing a dry-run check to see if a user is able to successfully login
 *        or that there may be problems connecting.
 *
 * Authors:    Liran Tal <liran@enginx.com>
 *             Giso Kegal <kegel@barum.de>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
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

// user_auth function
// sends to the radius server an authentication request packet (for the sake of testing a user)
// $radiusaddr  - the server address, this would most likely be the radius server IP Address/Hostname
// $radiusport  - the server's port number, radius server's port number (1812 or the old port for auth)
// $options     - the options passed to the radclient program
// $command     - the command string that radclient sends (auth, acct, status, coa, disconnect), by default this functions does 'auth'
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
    
    $required_params = array( "username", "password", "password_type", "server", "port", "secret" );
    
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
    
    if (!in_array($params['password_type'], $valid_passwordTypes)) {
        return array(
                        "error" => true,
                        "output" => sprintf("'%s': invalid password type", $password_type),
                    );
    }
    
    if (filter_var(trim($params['server']), FILTER_VALIDATE_IP) === false) {
        return array(
                        "error" => true,
                        "output" => "Invalid RADIUS server IP address",
                    );
    }
    
    // validate other params
    $port = (array_key_exists('port', $params) && $params['port'] >= 0 && $params['port'] <= 65535) ? $params['port'] : 1812;
    $valid_commands = array( "auth", "acct", "status", "coa", "disconnect" );
    $command = (array_key_exists('command', $params) && in_array($params['command'], $valid_commands)) ? $params['command'] : "auth";
    
    $count = (array_key_exists('count', $params) && $params['count'] > 0) ? $params['count'] : 1;
    $requests = (array_key_exists('requests', $params) && $params['requests'] > 0) ? $params['requests'] : 1;
    $retries = (array_key_exists('retries', $params) && $params['retries'] > 0) ? $params['retries'] : 10;
    $timeout = (array_key_exists('timeout', $params) && $params['timeout'] > 0) ? $params['timeout'] : 3;
    $debug = (array_key_exists('debug', $params) && $params['debug'] == true) ? "-x" : "";
    
    $secret = (array_key_exists('secret', $params) && !empty(trim($params['secret']))) ? escapeshellarg(trim($params['secret'])) : "testing123";
    
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
    $res = shell_exec($cmd);

    //~ $print_cmd = "<strong>Executed:</strong><br>$cmd<br>";
    if ($res == "") {
        return array(
                        "error" => true,
                        "output" => "command did not return any output",
                    );
        
        //~ echo "<b>Error:</b> Command did not return any results.<br>"
           //~ . "Please check that you have the <em>radclient binary program</em> installed and that it is found in your \$PATH variable<br>"
           //~ . "You may also consult the file <strong>$extension_file_enc</strong> for other problems<br>";
           //~ return $print_cmd;
    }

    return array(
                    "error" => false,
                    "output" => "$cmd\n$res"
                );

    //~ $output_html = '<br><strong>Results:</strong><br><div style="font-family: monospace">' . nl2br($res) . '</div>';
    //~ return $print_cmd . $output_html;
}


// user_disconnect function
// sends to the NAS a CoA (Change of Authorization) or a CoD (Disconnect) packet
// $nasaddr    - NAS address to receive the coa or disconnect request packet
// $nasport    - NAS Port address (depends on the configuration on the NAS, this may be a different port for either CoA or Disconnect packets).
function user_disconnect($options, $user, $nasaddr, $nasport="3779", $nassecret, $command="disconnect", $additional="") {

    $user = escapeshellarg($user);

    $args = escapeshellarg("$nasaddr:$nasport") . " " . escapeshellarg($command). " " . escapeshellarg($nassecret);
    $query = "User-Name=$user";

    if (!empty($additional)) {
        $query .= ',' . $additional;
    }

    $radclient = "radclient";    // or you can change this with the full path if the binary radcilent program can not be
                                 // found within your $PATH variable

    $radclient_options = " -c " . escapeshellarg($options['count'])
                       . " -n " . escapeshellarg($options['requests'])
                       . " -r " . escapeshellarg($options['retries'])
                       . " -t " . escapeshellarg($options['timeout'])
                       . " " . $options['debug'];

    if ($options['dictionary']) {
        $radclient_options .= " -d " . escapeshellarg($options['dictionary']);
    }

    $cmd = sprintf('echo "%s" | %s %s %s 2>&1', escapeshellcmd($query), $radclient, $radclient_options, $args);
    $res = shell_exec($cmd);
    
    $print_cmd = "<strong>Executed:</strong><br>$cmd<br>";
    if ($res == "") {
        echo "<b>Error:</b> Command did not return any results.<br>"
           . "Please check that you have the <em>radclient binary program</em> installed and that it is found in your \$PATH variable<br>"
           . "You may also consult the file <strong>$extension_file_enc</strong> for other problems<br>";
           return $print_cmd;
    }
    
    $output_html = '<br><strong>Results:</strong><br><div style="font-family: monospace">' . nl2br($res) . '</div>';
    return $print_cmd . $output_html;
}

?>
