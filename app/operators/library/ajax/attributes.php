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

include('../checklogin.php');
include_once('../../lang/main.php');
include_once('../../../common/includes/validation.php');

//
// functions
//

function populateTables() {

    $tables = array( 'check', 'reply' );
    
    foreach ($tables as $table) {
        printf("if (objTable.type == 'select-one') { objTable.add(new Option('%s', '%s')); }\n", $table, $table);
    }

}


function populateOPs() {
    global $valid_ops;
    
    foreach ($valid_ops as $op) {
        printf("objOP.add(new Option('%s', '%s'));\n", $op, $op);
    }
}


function drawHelperDateTime($num) {
    echo "objValues.setAttribute('type', 'datetime-local');\n";
    printf("objValues.setAttribute('value', '%s');\n", date('Y-m-d H:i:s'));
}


function drawHelperDate($num) {
    echo "objValues.setAttribute('type', 'date');\n";
    printf("objValues.setAttribute('value', '%s');\n", date('Y-m-d'));
}


// general purpose datalist helper draw function
function drawDatalistHelper($num, $helperIdPrefix, $options) {
    // setup IDs
    $num = intval($num);
    $inputId = sprintf("dictValues%d", $num);
    $helperId = sprintf("%s%d", $helperIdPrefix, $num);
    
    echo 'objHelper.innerHTML = ' . "'";
    printf('<datalist id="%s">', $helperId);
    foreach ($options as $value) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        printf('<option value="%s">', $value);
    }
    echo '</datalist>' . "';\n";
    
    
    echo "objValues.setAttribute('placeholder', 'double click or start typing...');\n";
    printf("objValues.setAttribute('list', '%s');\n", $helperId);
    
}


// general purpose select helper draw function
function drawSelectHelper($num, $helperIdPrefix, $options) {
    // setup IDs
    $num = intval($num);
    $inputId = sprintf("dictValues%d", $num);
    $helperId = sprintf("%s%d", $helperIdPrefix, $num);
    $onclick = sprintf("setStringText(\'%s\', \'%s\')", $helperId, $inputId);
    
    // draw helper
    echo 'objHelper.innerHTML = ' . "'";
    printf('<select onclick="%s" id="%s" class="form">', $onclick, $helperId);
    foreach ($options as $value => $caption) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $caption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');
        
        printf('<option value="%s">%s</option>', $value, $caption);
    }
    echo '</select>' . "';\n";
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

include_once('../../include/management/pages_common.php');
include_once('../../../common/includes/db_open.php');


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

        $sql = sprintf("SELECT RecommendedOP, RecommendedTable, RecommendedTooltip, type, RecommendedHelper
                          FROM %s WHERE Attribute='%s'", $configValues['CONFIG_DB_TBL_DALODICTIONARY'],
                                                         $dbSocket->escapeSimple($attribute));
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


include_once('../../../common/includes/db_close.php');

?>
