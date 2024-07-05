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
 * Description:    handles vendor/attribute dictionary via ajax
 * 
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include_once implode(DIRECTORY_SEPARATOR, [ __DIR__, '..', '..', '..', 'common', 'includes', 'config_read.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LIBRARY'], 'checklogin.php' ]);
include_once implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_LANG'], 'main.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'validation.php' ]);

/**
 * @brief Populates an HTML select element with options from table names.
 *
 * This function iterates through an array of table names and adds each name
 * as an option to an HTML select element, if the element has the type 'select-one'.
 */
function populateTables() {
    // Array of table names to add to the select element
    $tables = ['check', 'reply'];
    
    // Iterate through each table name and add it as an option to the select element
    foreach ($tables as $table) {
        // Print the JavaScript code to add the option
        printf(<<<EOF
if (objTable.type == 'select-one') {
    objTable.add(new Option('%s', '%s'));
}
EOF, $table, $table);
    }
}

/**
 * @brief Populates an HTML select element with options from valid operations.
 *
 * This function iterates through a global array of valid operations and adds each
 * as an option to an HTML select element.
 */
function populateOPs() {
    global $valid_ops;

    // Iterate through each valid operation and add it as an option to the select element
    foreach ($valid_ops as $op) {
        // Print the JavaScript code to add the option
        printf(<<<EOF
objOP.add(new Option('%s', '%s'));
EOF, $op, $op);
    }
}

/**
 * @brief Sets an HTML input element to be of type datetime-local with the current date and time.
 *
 * This function prints JavaScript code to set an HTML input element's type to 'datetime-local'
 * and its value to the current date and time.
 *
 * @param int $num An integer parameter (not used in this function).
 */
function drawHelperDateTime($num) {
    printf(<<<EOF
objValues.setAttribute('type', 'datetime-local');
objValues.value = "%s";

EOF, date('Y-m-d\TH:i'));
}

/**
 * @brief Sets an HTML input element to be of type text with the current date and time.
 *
 * This function prints JavaScript code to set an HTML input element's type to 'text'
 * and its value to the current date and time in a human-readable format.
 * 
 * @param int $num An integer parameter (not used in this function).
 * 
 * @see https://networkradius.com/doc/3.0.10/raddb/mods-available/expiration.html
 */
function drawHelperDate($num) {
    printf(<<<EOF
objValues.setAttribute('type', 'text');
objValues.value = "%s";

EOF, date("D j M Y G:i:s T"));
}

/**
 * @brief Generates a datalist HTML string with specified options.
 *
 * This function generates and returns a datalist HTML string with the given ID
 * and options provided as an array.
 *
 * @param string $id The ID attribute for the datalist element.
 * @param array $options An array of options to include in the datalist.
 * @return string The generated datalist HTML string.
 */
function get_datalist($id, $options) {
    $result = sprintf('<datalist id="%s">', $id);
    
    foreach ($options as $value) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $result .= sprintf('<option value="%s">', $value);
    }
    
    $result .= '</datalist>';
    return $result;
}

/**
 * @brief Sets up a datalist helper for an HTML input element.
 *
 * This function generates a datalist HTML string with the specified options,
 * sets up IDs for the input and datalist elements, and associates the datalist with the input element.
 *
 * @param int $num An integer used to generate unique IDs.
 * @param string $helperIdPrefix A prefix for the datalist ID.
 * @param array $options An array of options to include in the datalist.
 */
function drawDatalistHelper($num, $helperIdPrefix, $options) {
    // Setup IDs
    $num = intval($num);
    $inputId = sprintf("dictValues%d", $num);
    $helperId = sprintf("%s%d", $helperIdPrefix, $num);
    
    // Generate the datalist HTML string
    $datalist = get_datalist($helperId, $options);

    // Begin JavaScript output
    echo <<<EOF
objHelper.innerHTML = '{$datalist}';
objValues.setAttribute('placeholder', 'double click or start typing...');
objValues.setAttribute('list', '{$helperId}');

EOF;
}

/**
 * @brief Generates a <select> HTML element with specified attributes and options.
 *
 * This function generates and returns a <select> HTML element with the given ID, onclick event,
 * and options provided as an associative array of value-caption pairs.
 *
 * @param string $id The ID attribute for the <select> element.
 * @param string $onclick The onclick event handler for the <select> element.
 * @param array $options An associative array of options in the format value => caption.
 * @return string The generated <select> HTML string.
 */
function get_select($id, $onclick, $options) {
    $result = sprintf('<select id="%s" onclick="%s">', $id, $onclick);
    
    foreach ($options as $value => $caption) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $caption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');
        
        // Append each option to the result string
        $result .= sprintf('<option value="%s">%s</option>', $value, $caption);
    }
    
    $result .= '</select>';
    return $result;
}


// general purpose select helper draw function
function drawSelectHelper($num, $helperIdPrefix, $options) {
    // setup IDs
    $num = intval($num);
    $inputId = sprintf("dictValues%d", $num);
    $helperId = sprintf("%s%d", $helperIdPrefix, $num);
    $onclick = sprintf("setStringText(\'%s\', \'%s\')", $helperId, $inputId);
    $select = get_select($helperId, $onclick, $options);
    
    echo <<<EOF
objHelper.innerHTML = '{$select}';

EOF;
}


function drawBytes($num) {
    // setup options
    $options = array(
                        '' => 'Select...',
                        '10485760' => '10 MB',
                        '52428800' => '50 MB',
                        '104857600' => '100 MB',
                        '524288000' => '500 MB',
                        '1073741824' => '1 GB',
                        '2147483648' => '2 GB',
                        '4294967296' => '4 GB',
                        '8589934592' => '8 GB',
                        '12884901888' => '12 GB',
                        '17179869184' => '16 GB',
                    );

    $helperIdPrefix = "drawBytes";
    drawSelectHelper($num, $helperIdPrefix, $options);
}


function drawFramedProtocol($num) {
    // setup options
    $options = array(
                        //~ '' => 'Select...',
                        'PPP' => 'PPP',
                        'SLIP' => 'SLIP',
                        'ARAP' => 'ARAP',
                        'Gandalf-SLML' => 'Gandalf-SLML',
                        'Xylogics-IPX-SLIP' => 'Xylogics-IPX-SLIP',
                        'X.75-Synchronous' => 'X.75-Synchronous',
                        'PPTP' => 'PPTP',
                        'GPRS-PDP-Context' => 'GPRS-PDP-Context',
                    );
    
    $helperIdPrefix = "drawFramedProtocol";
    drawDatalistHelper($num, $helperIdPrefix, $options);

}


function drawBitPerSecond($num) {
    // setup options
    $options = array(
                        '' => 'Select...',
                        '32000' => '32 Kbps',
                        '64000' => '64 Kbps',
                        '128000' => '128 Kbps',
                        '256000' => '256 Kbps',
                        '512000' => '512 Kbps',
                        '750000' => '750 Kbps',
                        '1048576' => '1 Mbps',
                        '1572864' => '1.5 Mbps',
                        '2097152' => '2 Mbps',
                        '3145728' => '3 Mbps',
                        '5242880' => '5 Mbps',
                        '8388608' => '8 Mbps',
                        '10485760' => '10 Mbps',
                    );
                    
    $helperIdPrefix = "drawBitPerSecond";
    drawSelectHelper($num, $helperIdPrefix, $options);
}


function mikrotikRateLimit($num) {
    // setup options
    $options = array(
                        //~ '' => 'Select...',
                        '128k/128k' => '128k/128k',
                        '128k/256k' => '128k/256k',
                        '128k/512k' => '128k/512k',
                        '128k/1M' => '128k/1M',
                        '256k/256k' => '256k/256k',
                        '256k/1M' => '256k/1M',
                        '512k/512k' => '512k/512k',
                        '512k/1M' => '512k/1M',
                        '512k/2M' => '512k/2M',
                        '1M/1M' => '1M/1M',
                        '1M/2M' => '1M/2M',
                        '2M/2M' => '2M/2M',
                        '1M/5M' => '1M/5M',
                    );

    $helperIdPrefix = "drawBitPerSecond";
    drawDatalistHelper($num, $helperIdPrefix, $options);
}


function drawKBitPerSecond($num) {
    // setup options
    $options = array(
                        '' => 'Select...',
                        '32' => '32 Kbps',
                        '64' => '64 Kbps',
                        '128' => '128 Kbps',
                        '256' => '256 Kbps',
                        '512' => '512 Kbps',
                        '750' => '750 Kbps',
                        '1000' => '1 Mbps',
                        '1500' => '1.5 Mbps',
                        '2500' => '2 Mbps',
                        '3000' => '3 Mbps',
                        '5000' => '5 Mbps',
                        '8000' => '8 Mbps',
                        '10000' => '10 Mbps',
                    );
    
    $helperIdPrefix = "drawKBitPerSecond";
    drawSelectHelper($num, $helperIdPrefix, $options);

}


function drawAuthType($num) {
    // setup options
    $options = array(
                        'Local' => 'Local',
                        'System' => 'System',
                        'Accept' => 'Accept',
                        'Reject' => 'Reject',
                        'SecurID' => 'SecurID',
                        'Crypt-Local' => 'Crypt-Local',
                        'ActivCard' => 'ActivCard',
                        'EAP' => 'EAP',
                        'PAP' => 'PAP',
                        'CHAP' => 'CHAP',
                        'MS-CHAP' => 'MS-CHAP',
                        'PAM' => 'PAM',
                        'Kerberos' => 'Kerberos',
                        'CRAM' => 'CRAM',
                        'NS-MTA-MD5' => 'NS-MTA-MD5',
                        'SMB' => 'SMB',
                        'Unix' => 'Unix',
                        'None' => 'None',
                        'ARAP' => 'ARAP',
                    );

    $helperIdPrefix = "drawAuthType";
    drawDatalistHelper($num, $helperIdPrefix, $options);

}


function drawServiceType($num) {
    // setup options
    $options = array(
                        'Login-User' => 'Login-User',
                        'Framed-User' => 'Framed-User',
                        'Callback-Login-User' => 'Callback-Login-User',
                        'Callback-Framed-User' => 'Callback-Framed-User',
                        'Outbound-User' => 'Outbound-User',
                        'Administrative-User' => 'Administrative-User',
                        'NAS-Prompt-User' => 'NAS-Prompt-User',
                        'Authenticate-Only' => 'Authenticate-Only',
                        'Callback-NAS-Prompt' => 'Callback-NAS-Prompt',
                        'Call-Check' => 'Call-Check',
                        'Callback-Administrative' => 'Callback-Administrative',
                        'Sip-session' => 'Sip-session',
                        'Annex-Authorize-Only' => 'Annex-Authorize-Only',
                        'Annex-Framed-Tunnel' => 'Annex-Framed-Tunnel',
                        'Authorize-Only' => 'Authorize-Only',
                        'Shell-User' => 'Shell-User',
                        'Dialback-Login-User' => 'Dialback-Login-User',
                        'Dialback-Framed-User' => 'Dialback-Framed-User',
                        'Login' => 'Login',
                        'Framed' => 'Framed',
                        'Callback-Login' => 'Callback-Login',
                        'Callback-Framed' => 'Callback-Framed',
                        'Exec-User' => 'Exec-User',
                        'Sip-Session' => 'Sip-Session',
                        'Dialout-Framed-User' => 'Dialout-Framed-User',
                    );

	$helperIdPrefix = "drawServiceType";
    drawDatalistHelper($num, $helperIdPrefix, $options);

}


// we can handle these actions
$action = "";
if (isset($_GET['getVendorsList'])) {
    $action = 'getVendorsList';
} else if (isset($_GET['vendorAttributes'])) {
    $action = 'vendorAttributes';
} else if (isset($_GET['getValuesForAttribute'])) {
    $action = 'getValuesForAttribute';
} else {
    // this represents the default action
    $action = 'getVendorsList';
}

include implode(DIRECTORY_SEPARATOR, [ $configValues['OPERATORS_INCLUDE_MANAGEMENT'], 'pages_common.php' ]);
include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_open.php' ]);


switch ($action) {

    default:
    case 'getVendorsList':
/*
 * getVendorsList is set to yes when the user clicks on the Vendor select box
 * upon which the javascript code executes a call with this value which we catch
 * here and populate the Vendors select box with the available Vendors found from
 * the database
 */

        $sql = sprintf("SELECT DISTINCT(Vendor) AS Vendor
                          FROM %s
                         WHERE Vendor <> '' AND Vendor IS NOT NULL
                         ORDER BY Vendor ASC", $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
        $res = $dbSocket->query($sql);
        
        $numrows = $res->numRows();
        
        if ($numrows > 0) {
            
            while ($row = $res->fetchRow()) {
                $vendor = htmlspecialchars(trim($row[0]), ENT_QUOTES, 'UTF-8');
                printf("objVendors.add(new Option('%s', '%s'));\n", $vendor, $vendor);
            }
            
            echo "objVendors.disabled = false;\n";
        } else {
            echo "objVendors.disabled = true;\n";
            printf("alert('No vendors found. Is %s empty?');", $configValues['CONFIG_DB_TBL_DALODICTIONARY']);
        }
        
        break;
    
    case 'vendorAttributes':
/*
 * vendorAttributes is set to the vendor name which the user has chosen and passed
 * to us so that we populate the Attributes select box with the available attributes
 * found from the database for a specific vendor.
 */

        $vendor = (array_key_exists('vendorAttributes', $_GET) && !empty(trim($_GET['vendorAttributes'])))
                   ? trim($_GET['vendorAttributes']) : "";
        
        $sql = sprintf("SELECT attribute FROM %s WHERE Vendor='%s' AND (Value = '' OR Value IS NULL) ORDER BY attribute ASC",
                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $dbSocket->escapeSimple($vendor));
        $res = $dbSocket->query($sql);
        
        $numrows = $res->numRows();
        
        if ($numrows > 0) {
            echo "objAttributes.add(new Option('Select Attribute...', ''));\n";
            
            while ($row = $res->fetchRow()) {
                $attribute = htmlspecialchars(trim($row[0]), ENT_QUOTES, 'UTF-8');
                printf("objAttributes.add(new Option('%s', '%s'));\n", $attribute, $attribute);
            }
            
            echo "objAttributes.disabled = false;\n";
        } else {
            echo "objAttributes.disabled = true;\n";
            printf("alert('No attributes found for %s.');", htmlspecialchars($vendor, ENT_QUOTES, 'UTF-8'));
        }
        
        break;
        
    case 'getValuesForAttribute':
/*
 * getValuesForAttribute is set to the attribute's name upon which we expect to
 * run a sql query and fetch all the available pre-defined values availabe for 
 * this specific attribute from the database. If none, we simply reset the 
 * input box to null.
 * 
 * at this point we also populate the other fields such as OP with the default OP
 * found from the database (optional) and all the other possible options for OP.
 * The same goes for the Table field.
 * 
 * The tooltip and type are fields (text fields in html) which are also grabbed from
 * the database, the tooltip is some helpful information about the attribute
 * and the type is the attribute's type (string, integer, ipaddr, etc)
 */

        $attribute = (array_key_exists('getValuesForAttribute', $_GET) && !empty($_GET['getValuesForAttribute']))
                   ? $_GET['getValuesForAttribute'] : "";
        $num = (array_key_exists('instanceNum', $_GET) && !empty($_GET['instanceNum']))
             ? intval($_GET['instanceNum']) : rand();

        $sql = sprintf("SELECT `recommendedOP`, `recommendedTable`, `recommendedTooltip`, `type`, `recommendedHelper`
                          FROM %s WHERE `attribute`='%s' AND `recommendedHelper` IS NOT NULL ORDER BY `id` ASC LIMIT 1",
                       $configValues['CONFIG_DB_TBL_DALODICTIONARY'], $dbSocket->escapeSimple($attribute));
        $res = $dbSocket->query($sql);
        $numrows = $res->numRows();
        
        if ($numrows == 1) {

            $row = $res->fetchRow();

            $rowlen = count($row);
            for ($i = 0; $i < $rowlen; $i++) {
                $row[$i] = htmlspecialchars(trim($row[$i]), ENT_QUOTES, 'UTF-8');
            }

            list($RecommendedOP, $RecommendedTable, $RecommendedTooltip, $type, $RecommendedHelper) = $row;

            /*******************************************************************************************************/
            /* RecommendedOP
            /* set the first option of the dictOP select box to be the default recommended OP
             * from the dictionary table
            /*******************************************************************************************************/
            if (!empty($RecommendedOP)) {
                printf("objOP.add(new Option('%s', '%s'));\n", $RecommendedOP, $RecommendedOP);
            }
            
            /*******************************************************************************************************/
            /* RecommendedTable
            /* next up we set as the first option of the select box the default target table for this attribute
            /*******************************************************************************************************/
            if (!empty($RecommendedTable)) {
                printf("objTable.add(new Option('%s', '%s'));\n", $RecommendedTable, $RecommendedTable);
            }
            
            /*******************************************************************************************************/
            /* setting the dictValue to be empty
            /*******************************************************************************************************/
            echo "objValues.value = '';\n";
            /*******************************************************************************************************/
            
            
            /*******************************************************************************************************/
            /* RecommendedHelper
            /* this draws the appropriate helper function/for the attribute using the innerHTML method to the 
            /* html <span> element within the dynamic attribute boxes
            /*******************************************************************************************************/
            switch($RecommendedHelper) {

                case "datetime":
                    drawHelperDateTime($num);
                    break;

                case "date":
                    drawHelperDate($num);
                    break;

                case "authtype":
                    drawAuthType($num);
                    break;

                case "servicetype":
                    drawServiceType($num);
                    break;

                case "framedprotocol":
                    drawFramedProtocol($num);
                    break;

                case "volumebytes":
                    drawBytes($num);
                    break;

                case "bitspersecond":
                    drawBitPerSecond($num);
                    break;
                    
                case "mikrotikRateLimit":
                    mikrotikRateLimit($num);
                    break;

                case "kbitspersecond":
                    drawKBitPerSecond($num);
                    break;

            }
            /*******************************************************************************************************/
            
            
            /*******************************************************************************************************/
            /* RecommendedTooltip
            /* setting the tooltip 
            /*******************************************************************************************************/
            
            
            if (!empty(trim($RecommendedTooltip))) {
                printf("objTooltip.innerHTML = '<strong>Description</strong>: %s';\n",
                       addslashes(trim($RecommendedTooltip)));
            }
            /*******************************************************************************************************/


            /*******************************************************************************************************/
            /* Format type
            /* setting the format
            /*******************************************************************************************************/
            if (!empty($type)) {
                printf("objType.innerHTML = '<strong>Type</strong>: %s';\n", $type);
            }
            /*******************************************************************************************************/
            
        } else {
            echo "alert('Warning. Attribute non-existent or inconsistency in your vendor/attribute dictionary.');";
        }
        
        // of course we always populate possible tables
        populateTables();
        /*******************************************************************************************************/
        
        // then we populate the dictOP select box with the normal possible values for it:
        populateOPs();
        /*******************************************************************************************************/
        
        break;
}

include implode(DIRECTORY_SEPARATOR, [ $configValues['COMMON_INCLUDES'], 'db_close.php' ]);
