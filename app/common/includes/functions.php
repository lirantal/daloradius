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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/common/includes/functions.php') !== false) {
    http_response_code(404);
    exit;
}

// setup HTML purifier
include(__DIR__ . '/../library/htmlpurifier/HTMLPurifier.auto.php');

$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.AllowedElements', 'div,p,br,strong,ul,li,h1,h2,h3');
$config->set('HTML.AllowedAttributes', '*.class');
$purifier = new HTMLPurifier($config);

// The code above includes the HTMLPurifier library and configures it to allow only specific HTML elements and attributes.
// It creates an instance of the HTMLPurifier class for later use in filtering HTML content.

// returns true if the operator decided to update the message
function should_update_message($type) {
    global $valid_message_types, $_POST;

    // Checks if the given message type is valid.
    if (!in_array($type, $valid_message_types)) {
        return false;
    }

    // Check if the message has been changed by comparing its label in the $_POST array.
    $changed_check_label = $type . "_message_changed";
    if (!isset($_POST[$changed_check_label]) || $_POST[$changed_check_label] !== "yes") {
        return false;
    }

    return true;
}

// The above function checks if the message of the given type should be updated based on the operator's input in the form.

// updates the message
function update_message($dbSocket, $type) {
    global $_SESSION, $_POST, $purifier, $configValues, $logDebugSQL;

    // Define the message associative label in the $_POST array.
    $message_label = $type . "_message";

    // Filter the message using HTMLPurifier to remove any unsafe HTML content.
    $content = $purifier->purify($_POST[$message_label]);

    // SQL query to update the message in the database with the filtered content, modified time, and operator details.
    $sql = sprintf("UPDATE %s SET content='%s', modified_on=NOW(), modified_by='%s' WHERE `type`='%s'",
                   $configValues['CONFIG_DB_TBL_DALOMESSAGES'], $dbSocket->escapeSimple($content),
                   $dbSocket->escapeSimple($_SESSION['operator_user']), $dbSocket->escapeSimple($type));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    return $res;
}

// The above function updates the message of the given type in the database
// with the filtered content and sets the modified time and operator details.

function get_message($dbSocket, $type) {
    global $valid_message_types, $purifier, $configValues, $logDebugSQL;

    // Check if the given message type is valid.
    if (!in_array($type, $valid_message_types)) {
        return "";
    }

    // SQL query to retrieve the message content from the database for the given type.
    $sql = sprintf("SELECT content, modified_on, modified_by, created_on, created_by FROM %s WHERE `type`='%s' ORDER BY id ASC LIMIT 1",
                   $configValues['CONFIG_DB_TBL_DALOMESSAGES'], $dbSocket->escapeSimple($type));
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";

    // Fetch the message data from the query result in an associative array and filter the content using HTMLPurifier.
    $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
    $data["content"] = $purifier->purify($data["content"]);

    return $data;
}

// The above function retrieves the message data for the given type
// from the database and filters the content using HTMLPurifier before returning it.
