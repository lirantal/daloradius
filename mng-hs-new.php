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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include_once('library/config_read.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $macaddress = (array_key_exists('macaddress', $_POST) && isset($_POST['macaddress']) &&
                       filter_var($_POST['macaddress'], FILTER_VALIDATE_MAC) &&
                       filter_var($_POST['macaddress'], FILTER_VALIDATE_IP))
                    ? $_POST['macaddress'] : "";
        $name = (array_key_exists('name', $_POST) && isset($_POST['name']) && trim(str_replace("%", "", $_POST['name'])))
              ? trim($_POST['name']) : "";
        $name_enc = (!empty($name)) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : "";
        
        $geocode = (array_key_exists('geocode', $_POST) && isset($_POST['geocode'])) ? trim($_POST['geocode']) : "";
        $hotspot_type = (array_key_exists('hotspot_type', $_POST) && isset($_POST['hotspot_type'])) ? trim($_POST['hotspot_type']) : "";
                    
        $owner = (array_key_exists('owner', $_POST) && isset($_POST['owner'])) ? trim($_POST['owner']) : "";
        $manager = (array_key_exists('manager', $_POST) && isset($_POST['manager'])) ? trim($_POST['manager']) : "";
        $email_manager = (array_key_exists('email_manager', $_POST) && isset($_POST['email_manager'])) ? trim($_POST['email_manager']) : "";
        $email_owner = (array_key_exists('email_owner', $_POST) && isset($_POST['email_owner'])) ? trim($_POST['email_owner']) : "";
        $address = (array_key_exists('address', $_POST) && isset($_POST['address'])) ? trim($_POST['address']) : "";
        $company = (array_key_exists('company', $_POST) && isset($_POST['company'])) ? trim($_POST['company']) : "";
        $phone1 = (array_key_exists('phone1', $_POST) && isset($_POST['phone1'])) ? trim($_POST['phone1']) : "";
        $phone2 = (array_key_exists('phone2', $_POST) && isset($_POST['phone2'])) ? trim($_POST['phone2']) : "";
        
        $companyphone = (array_key_exists('companyphone', $_POST) && isset($_POST['companyphone'])) ? trim($_POST['companyphone']) : "";
        $companywebsite = (array_key_exists('companywebsite', $_POST) && isset($_POST['companywebsite'])) ? trim($_POST['companywebsite']) : "";
        $companyemail = (array_key_exists('companyemail', $_POST) && isset($_POST['companyemail'])) ? trim($_POST['companyemail']) : "";
        $companycontact = (array_key_exists('companycontact', $_POST) && isset($_POST['companycontact'])) ? trim($_POST['companycontact']) : "";
        
        include('library/opendb.php');
        
        if (empty($macaddress) || empty($name)) {
            // if statement returns false which means that the user has left an empty field for
            // either macaddress, name, or both

            $failureMsg = "IP/MAC Address or name are empty";
            $logAction .= "Failed adding (possible empty IP/MAC Addr and/or name) new HS on page: ";
        } else {
            $sql = sprintf("SELECT COUNT(id) FROM %s WHERE name='%s' OR mac='%s'",
                           $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                           $dbSocket->escapeSimple($name), $dbSocket->escapeSimple($mac));
            $res = $dbSocket->query($sql);
            $logDebugSQL .= "$sql;\n";
            
            $exists = $res->fetchrow()[0] > 0;
            
            if ($exists) {
                // if statement returns false which means there is at least one HS
                // in the database with the same name or macaddress

                $failureMsg = sprintf("Name <strong>%s</strong> or IP/MAC address <strong>%s</strong> already exist in database", $name_enc, $macaddress);
                $logAction .= "Failed adding new HS. Name or IP/MAC address already existing in database [$name, $macaddress] on page: ";
            } else {
                $currDate = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];
                
                $sql = sprintf("INSERT INTO %s (id, name, mac, geocode, owner, email_owner, manager, email_manager, address,
                                                company, phone1, phone2, type, companywebsite, companyemail, companycontact,
                                                companyphone, creationdate, creationby, updatedate, updateby)
                                        VALUES (0, '%s', '%s', '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
                                                '%s', '%s', '%s', NULL, NULL)", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'],
                               $dbSocket->escapeSimple($name), $dbSocket->escapeSimple($macaddress), $dbSocket->escapeSimple($geocode),
                               $dbSocket->escapeSimple($owner), $dbSocket->escapeSimple($email_owner), $dbSocket->escapeSimple($manager),
                               $dbSocket->escapeSimple($email_manager), $dbSocket->escapeSimple($address), $dbSocket->escapeSimple($company),
                               $dbSocket->escapeSimple($phone1), $dbSocket->escapeSimple($phone2), $dbSocket->escapeSimple($hotspot_type),
                               $dbSocket->escapeSimple($companywebsite), $dbSocket->escapeSimple($companyemail),
                               $dbSocket->escapeSimple($companycontact), $dbSocket->escapeSimple($companyphone), $currDate, $currBy);
                $res = $dbSocket->query($sql);
                $logDebugSQL .= "$sql;\n";
                
                if (!DB::isError($res)) {
                    // it seems that operator could not be added
                    $f = "Failed to add this new hotspot [%s] to database";
                    $failureMsg = sprintf($f, $name_enc);
                    $logAction .= sprintf($f, $name);
                } else {
                    $successMsg = sprintf("Added to database new hotspot: <strong>%s</strong>", $name_enc);
                    $logAction .= sprintf("Successfully added new hotspot [%s] on page: ", $name);
                }
            }
            
        }
        
        include('library/closedb.php');

    }

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/pages_common.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );

    $title = t('Intro','mnghsnew.php');
    $help = t('helpPage','mnghsnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-mng-hs.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    if (!isset($successMsg)) {

        // set form component descriptors
        $input_descriptors = array();
        
        $input_descriptors[] = array(
                                        "name" => "name",
                                        "caption" => t('all','HotSpotName'),
                                        "type" => "text",
                                        "value" => ((isset($name)) ? $name : ""),
                                        "tooltipText" => t('Tooltip','hotspotNameTooltip')
                                     );
                                    
        $input_descriptors[] = array(
                                        "name" => "macaddress",
                                        "caption" => t('all','MACAddress'),
                                        "type" => "text",
                                        "value" => ((isset($macaddress)) ? $macaddress : ""),
                                        "tooltipText" => t('Tooltip','hotspotMacaddressTooltip')
                                     );
                                     
        $input_descriptors[] = array(
                                        "name" => "geocode",
                                        "caption" => t('all','Geocode'),
                                        "type" => "text",
                                        "value" => ((isset($geocode)) ? $geocode : ""),
                                        "tooltipText" => t('Tooltip','geocodeTooltip')
                                     );
        
        $submit_descriptor = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                  );

        // set navbar stuff
        $navbuttons = array(
                              'HotspotInfo-tab' => t('title','HotspotInfo'),
                              'ContactInfo-tab' => t('title','ContactInfo'),
                           );

        print_tab_navbuttons($navbuttons);

?>

<form method="POST">
    <div id="HotspotInfo-tab" class="tabcontent" style="display: block">
        <fieldset>

            <h302><?= t('title','HotspotInfo') ?></h302>

            <ul style="margin: 30px auto">

<?php
                foreach ($input_descriptors as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>

            </ul>
        </fieldset>

<?php
        print_form_component($submit_descriptor);
?>

    </div><!-- #HotspotInfo-tab -->


    <div id="ContactInfo-tab" class="tabcontent">

<?php
        include_once('include/management/contactinfo.php');
?>

    </div><!-- #ContactInfo-tab -->

</form>

<?php
    }

    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
