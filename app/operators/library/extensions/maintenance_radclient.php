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
 * Description:    This script facilitates the execution of the 'radclient' binary tool from FreeRADIUS,
 *                 enabling a dry-run check to verify a user's ability to log in successfully or
 *                 to diagnose potential connectivity issues.
 *
 * Authors:        Liran Tal <liran@lirantal.com>
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
 * Thin wrapper around the `radclient` utility.
 *
 * Shared "advanced" options (count, requests, retries, timeout, dictionary,
 * simulate) are normalised once in the constructor; the two public methods only
 * receive action-specific parameters:
 *
 *   - disconnect()         -> sends a CoA / Packet-of-Disconnect to a NAS
 *   - check_connectivity() -> sends an auth / status request to a RADIUS server
 *
 * Both return: array("error" => bool, "output" => string)
 *
 * NOTE: the destination UDP port for CoA/PoD is a dedicated parameter
 * (default 3799), NOT the `ports` column of the `nas` table, which is a purely
 * informational field and must never be used as a network port.
 */
class RadClient {

    const DEFAULT_COA_PORT  = 3799;   // RFC 5176 (some legacy NAS use 1700)
    const DEFAULT_AUTH_PORT = 1812;

    private $path;
    private $options;
    private $simulate;

    /**
     * @param array $params Shared options: count, requests, retries, timeout,
     *                       dictionary, simulate.
     * @throws RuntimeException if the radclient binary is not available.
     */
    public function __construct(array $params = array()) {
        $this->path = self::is_radclient_present();
        if ($this->path === false) {
            throw new RuntimeException("radclient binary not found on the system");
        }

        // normalise the shared advanced options a single time
        $params = $this->radclient_common_options($params);

        $this->options = array(
            'count'      => $params['count'],
            'requests'   => $params['requests'],
            'retries'    => $params['retries'],
            'timeout'    => $params['timeout'],
            'dictionary' => isset($params['dictionary']) ? trim($params['dictionary']) : '',
        );

        $this->simulate = !empty($params['simulate']);
    }

    /**
     * Disconnect a user via CoA / Packet-of-Disconnect.
     *
     * Required: nas_id, command ("coa"|"disconnect"), username
     * Optional: port (1-65535, default 3799), customAttributes
     */
    public function disconnect(array $params) {
        $missing = $this->missing_params($params, array("nas_id", "command", "username"));
        if (!empty($missing)) {
            return $this->fail("Missing required parameter(s): " . implode(", ", $missing));
        }

        if (!in_array($params['command'], array("coa", "disconnect"), true)) {
            return $this->fail(sprintf("'%s': invalid command", $params['command']));
        }

        // dedicated CoA/PoD port, decoupled from the DB `ports` column
        $port = $this->valid_port($params, self::DEFAULT_COA_PORT);

        $nas = $this->get_nas($params['nas_id']);
        if ($nas === null) {
            return $this->fail("NAS not found");
        }
        list($addr, $secret) = $nas;

        $query  = sprintf("User-Name=%s", escapeshellarg($params['username']));
        $query .= $this->build_custom_attributes($params);

        return $this->run(sprintf("%s:%d", $addr, $port), $params['command'], $secret, $query);
    }

    /**
     * Test connectivity / credentials via auth or status request.
     *
     * Required: command ("auth"|"status"), username, password, password_type,
     *           server (IP), port, secret
     */
    public function check_connectivity(array $params) {
        include_once implode(DIRECTORY_SEPARATOR, [$GLOBALS['configValues']['COMMON_INCLUDES'], 'validation.php']);
        global $valid_passwordTypes;

        $required = array("command", "username", "password", "password_type", "server", "port", "secret");
        $missing  = $this->missing_params($params, $required);
        if (!empty($missing)) {
            return $this->fail("Missing required parameter(s): " . implode(", ", $missing));
        }

        if (!in_array($params['command'], array("auth", "status"), true)) {
            return $this->fail(sprintf("'%s': invalid command", $params['command']));
        }

        if (!in_array($params['password_type'], $valid_passwordTypes, true)) {
            return $this->fail(sprintf("'%s': invalid password type", $params['password_type']));
        }

        if (filter_var(trim($params['server']), FILTER_VALIDATE_IP) === false) {
            return $this->fail("Invalid RADIUS server IP address");
        }

        $port   = $this->valid_port($params, self::DEFAULT_AUTH_PORT);
        $secret = (trim($params['secret']) !== "") ? trim($params['secret']) : "testing123";

        // password_type is whitelist-validated above, so it is safe unescaped
        $query = sprintf("User-Name=%s,%s=%s",
            escapeshellarg($params['username']), $params['password_type'], escapeshellarg($params['password']));

        $server = sprintf("%s:%d", trim($params['server']), $port);
        return $this->run($server, $params['command'], $secret, $query);
    }

    // ---------------------------------------------------------------- helpers

    /**
     * Check if the radclient binary is present in the system.
     *
     * @return string|false The absolute path of the radclient binary if found, otherwise false.
     */
    public static function is_radclient_present() {
        exec("(which radclient || command -v radclient) 2> /dev/null", $output, $result_code);
        return ($result_code === 0) ? $output[0] : false;
    }

    /**
     * Ensure that optional radclient parameters are correctly set up.
     *
     * @param array $params Shared radclient parameters.
     * @return array Normalized parameters.
     */
    private function radclient_common_options($params) {
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

    /** Build and (optionally) execute the radclient command. */
    private function run($server_port, $command, $secret, $query) {
        $positional = sprintf("%s %s %s",
            escapeshellarg($server_port), escapeshellarg($command), escapeshellarg($secret));

        $cmd = sprintf('echo "%s" | %s %s %s 2>&1',
            escapeshellcmd($query), $this->path, $this->build_options(), $positional);

        if ($this->simulate) {
            return array("error" => false, "output" => "$cmd (not executed)");
        }

        $result = shell_exec($cmd);
        if (empty($result)) {
            return array("error" => true, "output" => "Command did not return any output");
        }

        return array("error" => false, "output" => "$cmd\n$result");
    }

    /** Common radclient flags (-c -n -r -t -x [-d]). */
    private function build_options() {
        $opts = sprintf(" -c %s -n %s -r %s -t %s -x",
            escapeshellarg($this->options['count']),
            escapeshellarg($this->options['requests']),
            escapeshellarg($this->options['retries']),
            escapeshellarg($this->options['timeout']));

        if ($this->options['dictionary'] !== '') {
            $opts .= sprintf(" -d %s", escapeshellarg($this->options['dictionary']));
        }

        return $opts;
    }

    /** Fetch nasname + secret for a given NAS id (ports column intentionally ignored). */
    private function get_nas($nas_id) {
        global $configValues;

        include implode(DIRECTORY_SEPARATOR, [$configValues['COMMON_INCLUDES'], 'db_open.php']);

        $sql      = sprintf("SELECT DISTINCT(`nasname`), `secret` FROM `%s` WHERE `id`=?",
                            $configValues['CONFIG_DB_TBL_RADNAS']);
        $prepared = $dbSocket->prepare($sql);
        $res      = $dbSocket->execute($prepared, $nas_id);
        $row      = $res->fetchRow();

        include implode(DIRECTORY_SEPARATOR, [$configValues['COMMON_INCLUDES'], 'db_close.php']);

        return $row ? array($row[0], $row[1]) : null;
    }

    /** Parse, validate and shell-escape optional custom attributes. */
    private function build_custom_attributes($params) {
        global $configValues;

        if (empty($params['customAttributes'])) {
            return "";
        }

        include_once implode(DIRECTORY_SEPARATOR, [$configValues['COMMON_INCLUDES'], 'validation.php']);

        $out = "";
        foreach (explode(",", $params['customAttributes']) as $pair) {
            if (strpos($pair, "=") === false) {
                continue; // skip malformed entries instead of triggering a notice
            }

            list($attr, $value) = explode("=", $pair, 2);
            $attr  = trim($attr);
            $value = trim($value);

            if ($attr !== "" && $value !== "" && $attr !== 'User-Name'
                && preg_match(ALLOWED_ATTRIBUTE_CHARS_REGEX, $attr) === 1) {
                $out .= sprintf(", %s=%s", $attr, escapeshellarg($value));
            }
        }

        return $out;
    }

    private function valid_port($params, $default) {
        return (isset($params['port']) && intval($params['port']) >= 1 && intval($params['port']) <= 65535)
             ? intval($params['port']) : $default;
    }

    private function missing_params($params, $required) {
        $missing = array();
        foreach ($required as $name) {
            if (!array_key_exists($name, $params)) {
                $missing[] = $name;
            }
        }
        return $missing;
    }

    private function fail($message) {
        return array("error" => true, "output" => $message);
    }
}
