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
    include_once('../common/includes/config_read.php');
    
    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    
    include_once("lang/main.php");
    include("../common/includes/validation.php");
    include("../common/includes/layout.php");

    $db_tbl_param_label = array(
                                    'CONFIG_DB_TBL_RADCHECK' => t('all','radcheck'), 
                                    'CONFIG_DB_TBL_RADREPLY' => t('all','radreply'),
                                    'CONFIG_DB_TBL_RADGROUPREPLY' => t('all','radgroupreply'), 
                                    'CONFIG_DB_TBL_RADGROUPCHECK' => t('all','radgroupcheck'), 
                                    'CONFIG_DB_TBL_RADUSERGROUP' => t('all','usergroup'), 
                                    'CONFIG_DB_TBL_RADACCT' => t('all','radacct'), 
                                    'CONFIG_DB_TBL_RADNAS' => t('all','nas'),
                                    'CONFIG_DB_TBL_RADHG' => t('all','hunt'), 
                                    'CONFIG_DB_TBL_RADPOSTAUTH' => t('all','radpostauth'), 
                                    'CONFIG_DB_TBL_RADIPPOOL' => t('all','radippool'), 
                                    'CONFIG_DB_TBL_DALOUSERINFO' => t('all','userinfo'), 
                                    'CONFIG_DB_TBL_DALODICTIONARY' => t('all','dictionary'), 
                                    'CONFIG_DB_TBL_DALOREALMS' => t('all','realms'), 
                                    'CONFIG_DB_TBL_DALOPROXYS' => t('all','proxys'), 
                                    'CONFIG_DB_TBL_DALOBILLINGMERCHANT' => t('all','billingmerchant'), 
                                    'CONFIG_DB_TBL_DALOBILLINGPAYPAL' => t('all','billingpaypal'), 
                                    'CONFIG_DB_TBL_DALOBILLINGPLANS' => t('all','billingplans'), 
                                    'CONFIG_DB_TBL_DALOBILLINGRATES' => t('all','billingrates'), 
                                    'CONFIG_DB_TBL_DALOBILLINGHISTORY' => t('all','billinghistory'), 
                                    'CONFIG_DB_TBL_DALOBATCHHISTORY' => t('button', 'BatchHistory'),
                                    'CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES' => 'Billing Plans Profiles',
                                    'CONFIG_DB_TBL_DALOUSERBILLINFO' => t('all','billinginfo'), 
                                    'CONFIG_DB_TBL_DALOBILLINGINVOICE' => t('all','Invoice'), 
                                    'CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS' => t('all','InvoiceItems'), 
                                    'CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS' => t('all','InvoiceStatus'), 
                                    'CONFIG_DB_TBL_DALOBILLINGINVOICETYPE' => t('all','InvoiceType'), 
                                    'CONFIG_DB_TBL_DALOPAYMENTTYPES' => t('all','payment_type'), 
                                    'CONFIG_DB_TBL_DALOPAYMENTS' => t('all','payments'), 
                                    'CONFIG_DB_TBL_DALOOPERATORS' => t('all','operators'), 
                                    'CONFIG_DB_TBL_DALOOPERATORS_ACL' => t('all','operators_acl'), 
                                    'CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES' => t('all','operators_acl_files'), 
                                    'CONFIG_DB_TBL_DALOHOTSPOTS' => t('all','hotspots'), 
                                    'CONFIG_DB_TBL_DALONODE' => t('all','node'), 
                                );
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (array_key_exists('csrf_token', $_POST) && isset($_POST['csrf_token']) && dalo_check_csrf_token($_POST['csrf_token'])) {
        
            $filePrefix = "backup";
            $fileDate = date("Ymd-His");
            $filePath = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA'] . "/backup";
            $fileName = sprintf("%s/%s-%s.sql", $filePath, $filePrefix, $fileDate);

            // check if backup file can be created
            $fileError = false;

            if ( is_dir($filePath) && is_writable($filePath) ) {
                $fh = fopen($fileName, "w");
                
                if($fh === false) {
                    $fileError = true;
                }
            } else {
                $fileError = true;
            }
            
            
            if($fileError) {
                $failureMsg = "Failed creating backup due to directory/file permissions. " 
                            . sprintf("Check that the webserver user has access to create the following file: %s", $fileName);
                $logAction .= "Failed creating backup due to directory/file permissions on page: ";
            } else {
                // backup file can be create
                
                include('../common/includes/db_open.php');
            
                $dbError = 0;
                $tables = array();
            
                
                foreach (array_keys($db_tbl_param_label) as $param) {
                    
                    if (array_key_exists($param, $_POST) && !empty(trim($_POST[$param])) && trim($_POST[$param]) === "yes") {
                        
                        // get table name from config file
                        $table = $configValues[$param];
                        
                        // first get column fields
                        $sql = sprintf("SELECT * FROM %s LIMIT 1", $table);
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";

                        if (DB::isError($res)) {
                            $dbError++;
                            break;
                        }
                        
                        $numrows = $res->numRows();
                        if ($numrows == 0) {
                            continue;
                        }
                    
                        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                        $columns = array_keys($row);
                        
                        $colLength = count($columns);
                        
                        if ($colLength == 0) {
                            continue;
                        }
                        
                        // start building insert query
                        $sqlTableQuery = "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES ";
                        
                        // get data
                        $sql = sprintf("SELECT * FROM %s", $table);
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= "$sql;\n";

                        if (DB::isError($res)) {
                            $dbError++;
                            break;
                        }
                    
                        $lastRow = $res->numRows() - 1;
                    
                        while ($row = $res->fetchRow()) {
                            $currRow = "(";
                            
                            $N = $colLength - 1;
                            for ($i = 0; $i < $N; $i++) {
                                $currRow .= sprintf("'%s', ", $dbSocket->escapeSimple($row[$i]));
                            }
                            
                            // add last item
                            $currRow .= sprintf("'%s')", $dbSocket->escapeSimple($row[$N]));
                        
                            if ($lastRow > 0) {
                                $currRow .= ", ";
                            }
                            
                            $lastRow--;
                            
                            $sqlTableQuery .= $currRow;
                        }
                        
                        $sqlTableQuery .= ";\n\n\n";
                        
                        // write query to backup file
                        if(fwrite($fh, $sqlTableQuery) === false) {
                            $fileError++;
                            break;
                        }
                        
                        $tables[] = $table;
                        
                    }
                    
                }

                include('../common/includes/db_close.php');

                // close file
                if(fclose($fh) === false) {
                    $fileError = true;
                }
                
                if ($dbError > 0) {
                    $failureMsg = "Failed creating backup due to database error, check your database settings";
                    $logAction .= "Failed creating backup due to database error on page: ";
                } else if ($fileError) {
                    unlink($fileName);
                    
                    $failureMsg = "Failed creating backup due to file write error, check your disk space";
                    $logAction .= "Failed creating backup due to file write error on page: ";
                } else {
                    
                    $fileSize = filesize($fileName);
                    if ($fileSize > 0) {
                        $successMsg = sprintf("Successfully created backup for %d table(s) [%s]", count($tables), implode(", ", $tables));
                        $logAction .= sprintf("Successfully created backup file [%s] on page: ", $fileName);
                    } else {
                        unlink($fileName);
                        
                        $failureMsg = "Failed creating backup due to file write error (empty file)";
                        $logAction .= "Failed creating backup due to file write error (empty file) on page: ";
                    }
                }
            }
        
        } else {
            // csrf
            $failureMsg = "CSRF token error";
            $logAction .= "$failureMsg on page: ";
        }
    }
    
    
    // print HTML prologue    
    $title = t('Intro','configbackupcreatebackups.php');
    $help = t('helpPage','configbackupcreatebackups');
    
    print_html_prologue($title, $langCode);

    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    // set navbar stuff
    $navkeys = array( 'FreeRADIUSTables', 'daloRADIUSTables' );

    $options = array( "yes", "no" );

    // section 0
    $toggler0_descriptor = array(
                                    "type" => "checkbox",
                                    "name" => "toggler0",
                                    "onclick" => "toggleFieldset('fieldset-0')",
                                    "checked" => true,
                                    "caption" => "Toggle"
                                );
    
    $input_descriptors0 = array();
    $input_descriptors0[] = $toggler0_descriptor;
    
    
    
    foreach ($db_tbl_param_label as $param => $label) {
        
        if (!preg_match('/^CONFIG_DB_TBL_RAD/', $param)) {
            continue;
        }
        
        $input_descriptors0[] = array(
                                        "name" => $param,
                                        "caption" => $label,
                                        "type" => "select",
                                        "options" => $options
                                     );
    }
    
    // section 1
    $toggler1_descriptor = array(
                                    "type" => "checkbox",
                                    "name" => "toggler1",
                                    "onclick" => "toggleFieldset('fieldset-1')",
                                    "checked" => true,
                                    "caption" => "Toggle"
                                );
    
    $input_descriptors1 = array();
    $input_descriptors1[] = $toggler1_descriptor;
    
    foreach ($db_tbl_param_label as $param => $label) {
        
        if (!preg_match('/^CONFIG_DB_TBL_DALO/', $param)) {
            continue;
        }
        
        $input_descriptors1[] = array(
                                        "name" => $param,
                                        "caption" => $label,
                                        "type" => "select",
                                        "options" => $options
                                     );
    }
    
    // section 2
    $input_descriptors2 = array();
    
    $input_descriptors2[] = array(
                                    "name" => "csrf_token",
                                    "type" => "hidden",
                                    "value" => dalo_csrf_token(),
                                 );

    $input_descriptors2[] = array(
                                    'type' => 'submit',
                                    'name' => 'submit',
                                    'value' => t('buttons','apply')
                                 );
    
    // fieldsets
    $fieldset0_descriptor = array(
                                    "title" => t('title','Backups'),
                                    "id" => "fieldset-0",
                                 );
                             
    $fieldset1_descriptor = array(
                                    "title" => t('title','Backups'),
                                    "id" => "fieldset-1",
                                 );
    
    // print navbar controls
    print_tab_header($navkeys);
    
    open_form();
    
    // open tab wrapper
    open_tab_wrapper();
    
    // open 0-th tab (shown)
    open_tab($navkeys, 0, true);
    
    // open 0-th fieldset
    open_fieldset($fieldset0_descriptor);
    
    foreach ($input_descriptors0 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_tab($navkeys, 0);
    
    // open 1-st tab
    open_tab($navkeys, 1);
    
    open_fieldset($fieldset1_descriptor);
    
    foreach ($input_descriptors1 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_fieldset();
    
    close_tab($navkeys, 1);
    
    // close tab wrapper
    close_tab_wrapper();
    
    foreach ($input_descriptors2 as $input_descriptor) {
        print_form_component($input_descriptor);
    }
    
    close_form();
    
    include('include/config/logging.php');
       
    $inline_extra_js = "
function toggleFieldset(fieldsetId) {
    var elements = document.getElementById(fieldsetId).getElementsByTagName('select');
    
    for (var i = 0; i < elements.length; i++) { 
        elements[i].options.selectedIndex = (elements[i].options.selectedIndex == 0) ? 1 : 0;
    }
    
}";
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
