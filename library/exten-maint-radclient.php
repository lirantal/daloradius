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
$extension_file = '/library/exten-maint-radclient.php';
if (strpos($_SERVER['PHP_SELF'], $extension_file) !== false) {
    header("Location: ../index.php");
    exit;
}

$extension_file_enc = htmlspecialchars($extension_file, ENT_QUOTES, 'UTF-8');

// user_auth function
// sends to the radius server an authentication request packet (for the sake of testing a user)
// $radiusaddr  - the server address, this would most likely be the radius server IP Address/Hostname
// $radiusport  - the server's port number, radius server's port number (1812 or the old port for auth)
// $options     - the options passed to the radclient program
// $command     - the command string that radclient sends (auth, acct, status, coa, disconnect), by default this functions does 'auth'
function user_auth($options, $user, $pass, $radiusaddr, $radiusport, $secret, $command="auth", $additional="") {

    $user = escapeshellarg($user);
    $pass = escapeshellarg($pass);
    $args = escapeshellarg("$radiusaddr:$radiusport") . " " . escapeshellarg($command) . " " . escapeshellarg($secret);
    $query = "User-Name=$user,User-Password=$pass";

    $radclient = "radclient";       // or you can change this with the full path if the binary radcilent program can not be
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
