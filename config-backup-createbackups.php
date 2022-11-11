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

    $logAction = "";
    $logDebugSQL = "";

    
    isset($_POST['radacct']) ? $radacctTable = $_POST['radacct'] : $radacctTable = 'yes';
    isset($_POST['radcheck']) ? $radcheckTable = $_POST['radcheck'] : $radcheckTable = 'yes';
    isset($_POST['radreply']) ? $radreplyTable = $_POST['radreply'] : $radreplyTable = 'yes';
    isset($_POST['radgroupcheck']) ? $radgroupcheckTable = $_POST['radgroupcheck'] : $radgroupcheckTable = 'yes';
    isset($_POST['radgroupreply']) ? $radgroupreplyTable = $_POST['radgroupreply'] : $radgroupreplyTable = 'yes';
    isset($_POST['radusergroup']) ? $radusergroupTable = $_POST['radusergroup'] : $radusergroupTable = 'yes';
    isset($_POST['radpostauth']) ? $radpostauthTable = $_POST['radpostauth'] : $radpostauthTable = 'yes';
    isset($_POST['ippool']) ? $ippoolTable = $_POST['ippool'] : $ippoolTable = 'yes';
    isset($_POST['nas']) ? $nasTable = $_POST['nas'] : $nasTable = 'yes';
    isset($_POST['dictionary']) ? $dictionaryTable = $_POST['dictionary'] : $dictionaryTable = 'yes';
    isset($_POST['radhuntgroup']) ? $radhuntgroupTable = $_POST['radhuntgroup'] : $radhuntgroupTable = 'yes';    
    
    isset($_POST['hotspots']) ? $dalohotspotsTable = $_POST['hotspots'] : $dalohotspotsTable = 'yes';
    isset($_POST['operators']) ? $dalooperatorsTable = $_POST['operators'] : $dalooperatorsTable = 'yes';
    isset($_POST['proxys']) ? $daloproxysTable = $_POST['proxys'] : $daloproxysTable = 'yes';
    isset($_POST['realms']) ? $dalorealmsTable = $_POST['realms'] : $dalorealmsTable = 'yes';
    
    isset($_POST['billingpaypal']) ? $dalobillingpyapalTable = $_POST['billingpaypal'] : $dalobillingpaypalTable = 'yes';
    isset($_POST['userinfo']) ? $dalouserinfoTable = $_POST['userinfo'] : $dalouserinfoTable = 'yes';
    
    isset($_POST['billing_history']) ? $dalobillinghistoryTable = $_POST['billing_history'] : $dalobillinghistoryTable = 'yes';
    isset($_POST['billing_plans']) ? $dalobillingplansTable = $_POST['billing_plans'] : $dalobillingplansTable = 'yes';
    isset($_POST['billing_merchant']) ? $dalobillingmerchantTable = $_POST['billing_merchant'] : $dalobillingmerchantTable = 'yes';
    isset($_POST['billing_rates']) ? $dalobillingratesTable = $_POST['billing_rates'] : $dalobillingratesTable = 'yes';
    isset($_POST['userbillinfo']) ? $dalouserbillinfoTable = $_POST['userbillinfo'] : $dalouserbillinfoTable = 'yes';
    
    isset($_POST['operators_acl']) ? $dalooperatorsaclTable = $_POST['operators_acl'] : $dalooperatorsaclTable = 'yes';
    isset($_POST['operators_acl_files']) ? $dalooperatorsaclfilesTable = $_POST['operators_acl_files'] : $dalooperatorsaclfilesTable = 'yes';
    isset($_POST['batch_history']) ? $dalobatchhistoryTable = $_POST['batch_history'] : $dalobatchhistoryTable = 'yes';
    isset($_POST['billing_plans_profiles']) ? $dalobillingplansprofilesTable = $_POST['billing_plans_profiles'] : $dalobillingplansprofilesTable = 'yes';
    isset($_POST['invoice']) ? $daloinvoiceTable = $_POST['invoice'] : $daloinvoiceTable = 'yes';
    isset($_POST['invoice_items']) ? $daloinvoiceitemsTable = $_POST['invoice_items'] : $daloinvoiceitemsTable = 'yes';
    isset($_POST['invoice_status']) ? $daloinvoicestatusTable = $_POST['invoice_status'] : $daloinvoicestatusTable = 'yes';
    isset($_POST['invoice_type']) ? $daloinvoicetypeTable = $_POST['invoice_type'] : $daloinvoicetypeTable = 'yes';
    isset($_POST['payment']) ? $dalopaymentTable = $_POST['payment'] : $dalopaymentTable = 'yes';
    isset($_POST['payment_type']) ? $dalopaymenttypeTable = $_POST['payment_type'] : $dalopaymenttypeTable = 'yes';
    isset($_POST['node']) ? $dalonodeTable = $_POST['node'] : $dalonodeTable = 'yes';

    if (isset($_POST['submit'])) {
        
        $filePrefix = "backup";
        $fileDate = date("Ymd-His");
        $filePath = $configValues['CONFIG_PATH_DALO_VARIABLE_DATA']."/backup/";
        $fileName = $filePath.$filePrefix."-".$fileDate.".sql";
        
        $fileError = false;

        if ( (file_exists($filePath)) && (is_writable($filePath)) ) {
            $fh = fopen($fileName, "w");
            
            if($fh === false) {
                $fileError = true;
            }
        } else {        
            $fileError = true;
        }
        
        if($fileError) {
            $failureMsg = "Failed creating backup due to directory/file permissions, check that the webserver user has access ".
                            "to create the following file: <b>$fileName</b>";
            $logAction .= "Failed creating backup due to directory/file permissions on page: ";
        }
        else {

            include 'library/opendb.php';
            
            $dbError = false;

            foreach($_POST as $element=>$value) {

                if ($element == "submit")
                    continue;

                switch ($element) {
                    case "radacct":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADACCT'];
                        break;
                    case "radreply":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADREPLY'];
                        break;
                    case "radcheck":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADCHECK'];
                        break;
                    case "radusergroup":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADUSERGROUP'];
                        break;
                    case "radgroupreply":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADGROUPREPLY'];
                        break;
                    case "radgroupcheck":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADGROUPCHECK'];
                        break;
                    case "radpostauth":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADPOSTAUTH'];
                        break;
                    case "ippool":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADIPPOOL'];
                        break;
                    case "radhuntgroup":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADHG'];
                        break;
                    case "nas":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_RADNAS'];
                        break;
                    case "hotspots":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'];
                        break;
                    case "operators":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOOPERATORS'];
                        break;
                    case "billing_rates":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'];
                        break;
                    case "billingpaypal":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'];
                        break;
                    case "userinfo":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOUSERINFO'];
                        break;
                    case "dictionary":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALODICTIONARY'];
                        break;
                    case "realms":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOREALMS'];
                        break;
                    case "proxys":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOPROXYS'];
                        break;

                    case "billing_history":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'];
                        break;
                    case "billing_plans":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'];
                        break;
                    case "billing_merchant":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'];
                        break;
                    case "userbillinfo":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'];
                        break;
                    case "operators_acl":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'];
                        break;
                    case "operators_acl_files":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'];
                        break;
                    case "batch_history":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'];
                        break;
                    case "billing_plans_profiles":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'];
                        break;
                    case "invoice":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'];
                        break;
                    case "invoice_items":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'];
                        break;
                    case "invoice_status":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'];
                        break;
                    case "invoice_type":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'];
                        break;
                    case "payment":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOPAYMENTS'];
                        break;
                    case "payment_type":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'];
                        break;
                    case "node":
                        if ($value == "yes")
                            $table = $configValues['CONFIG_DB_TBL_DALONODE'];
                        break;
                }

                if (isset($table)) {

                    $sql = "SELECT * FROM $table LIMIT 1";
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";

                    if (DB::isError ($res)) {
                        $dbError = true;
                        break;
                    }

                    if ($res->numRows() == 0)
                        continue;
                    
                    $sqlTableQuery = "INSERT INTO $table (";
        
                    $colLength = 0;

                    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                    
                    foreach($row as $key=>$value) {                // $key is the table field and $value is the field's value
                        if($colLength > 0) {
                            $sqlTableQuery .= ",";
                        }
                        
                        $sqlTableQuery .= "$key";
                                            
                        $colLength++;
                    }

                    $sqlTableQuery .= ") VALUES ";
        
                    $sql = "SELECT * FROM $table";
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";

                    if (DB::isError ($res)) {
                        $dbError = true;
                        break;
                    }
                    
                    $numRow = 0;
        
                    while($row = $res->fetchRow()) {
        
                        $currRow = "(";
                        
                        for ($i = 0; $i < $colLength; $i++) {
                            if($i > 0) {
                                $currRow .= ",";
                            }
                            
                            $currRow .= "'$row[$i]'";
                        }
                        
                        $currRow .= ")";
                        
                        if($numRow > 0) {
                            $sqlTableQuery .= ",";
                        }
                        
                        $sqlTableQuery .= "$currRow";
                        
                        $numRow++;
                    }
        
                    $sqlTableQuery .= ";\n\n\n";

                    if(fwrite($fh, $sqlTableQuery) === false) {
                        $fileError = true;
                        break;
                    }
                    
                    unset($sqlTableQuery);
                }

                unset($table);
            }
            
            if(fclose($fh) === false) {
                $fileError = true;
            }

            if ($dbError) {
                $failureMsg = "Failed creating backup due to database error, check your database settings";
                $logAction .= "Failed creating backup due to database error on page: ";
            }
            else if ($fileError) {
                unlink($fileName);
                
                $failureMsg = "Failed creating backup due to file write error, check your disk space";
                $logAction .= "Failed creating backup due to file write error on page: ";
            }
            else {
                $successMsg = "Successfully created backup";
                $logAction .= "Successfully created backup file [$fileName] on page: ";
            }
        
            include 'library/closedb.php';
        }
    }

            
    

    include_once('library/config_read.php');
    $log = "visited page: ";
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','configbackupcreatebackups.php');
    $help = t('helpPage','configbackupcreatebackups');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include ("menu-config-backup.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
    
    
    // set navbar stuff
    $navbuttons = array(
                          "FreeRADIUSTables-tab" => t('title','FreeRADIUSTables'),
                          "daloRADIUSTables-tab" => t('title','daloRADIUSTables'),
                       );

    print_tab_navbuttons($navbuttons);
    
    
?>

<form name="createbackups" method="POST">
        <div id="FreeRADIUSTables-tab" class="tabcontent" style="display: block">
            <fieldset>
                <h302><?= t('title','Backups'); ?></h302>
                
                <br/>

                <h3 class="form">Select database tables to backup:</h3>

                <ul>

                    <li class="fieldset">
                    <label for="backup_radacct" class="form">radacct</label> 
            <select class="form" name="radacct">
                <option value="<?= $radacctTable ?>"><?= $radacctTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    <li class="fieldset">
                    <label for="backup_radcheck" class="form">radcheck</label> 
            <select class="form" name="radcheck">
                <option value="<?= $radcheckTable ?>"><?= $radcheckTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    <li class="fieldset">
                    <label for="backup_radreply" class="form">radreply</label> 
            <select class="form" name="radreply">
                <option value="<?= $radreplyTable ?>"><?= $radreplyTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    <li class="fieldset">
                    <label for="backup_radgroupcheck" class="form">radgroupcheck</label> 
            <select class="form" name="radgroupcheck">
                <option value="<?= $radgroupcheckTable ?>"><?= $radgroupcheckTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    <li class="fieldset">
                    <label for="backup_radgroupreply" class="form">radgroupreply</label> 
            <select class="form" name="radgroupreply">
                <option value="<?= $radgroupreplyTable ?>"><?= $radgroupreplyTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    <li class="fieldset">
                    <label for="backup_radusergroup" class="form">radusergroup</label> 
            <select class="form" name="radusergroup">
                <option value="<?= $radusergroupTable ?>"><?= $radusergroupTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    <li class="fieldset">
                    <label for="backup_radpostauth" class="form">radpostauth</label> 
            <select class="form" name="radpostauth">
                <option value="<?= $radpostauthTable ?>"><?= $radpostauthTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    <li class="fieldset">
                    <label for="backup_ippool" class="form">ippool</label> 
            <select class="form" name="ippool">
                <option value="<?= $ippoolTable ?>"><?= $ippoolTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    <li class="fieldset">
                    <label for="backup_nas" class="form">nas</label> 
            <select class="form" name="nas">
                <option value="<?= $nasTable ?>"><?= $nasTable ?></option>
                <option value="">  </option>
                <option value="no">no</option>
                <option value="yes">yes</option>
            </select>
            </li>

                    

                </ul>
            </fieldset>
        </div>

        <div id="daloRADIUSTables-tab" class="tabcontent" title="<?= t('title','daloRADIUSTables'); ?>">
            <fieldset>
                <h302> <?= t('title','Backups'); ?> </h302>
                
                <br/>

                <h3 class="form">Select databases tables to backup:</h3>

                <ul>


                <li class="fieldset">
                <label for="backup_dalooperators" class="form">operators</label> 
        <select class="form" name="operators">
            <option value="<?= $dalooperatorsTable ?>"><?= $dalooperatorsTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_dalohotspots" class="form">hotspots</label> 
        <select class="form" name="hotspots">
            <option value="<?= $dalohotspotsTable ?>"><?= $dalohotspotsTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_daloproxys" class="form">proxys</label> 
        <select class="form" name="proxys">
            <option value="<?= $daloproxysTable ?>"><?= $daloproxysTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_dalorealms" class="form">realms</label> 
        <select class="form" name="realms">
            <option value="<?= $dalorealmsTable ?>"><?= $dalorealmsTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_dalobilling_rates" class="form">billing rates</label> 
        <select class="form" name="billing_rates">
            <option value="<?= $dalobillingratesTable ?>"><?= $dalobillingratesTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_dalobillingpaypal" class="form">billing paypal</label> 
        <select class="form" name="billingpaypal">
            <option value="<?= $dalobillingpaypalTable ?>"><?= $dalobillingpaypalTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_dalouserinfo" class="form">userinfo</label> 
        <select class="form" name="userinfo">
            <option value="<?= $dalouserinfoTable ?>"><?= $dalouserinfoTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_dictionary" class="form">userbillinfo</label> 
        <select class="form" name="userbillinfo">
            <option value="<?= $dalouserbillinfoTable ?>"><?= $dalouserbillinfoTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_dictionary" class="form">dictionary</label> 
        <select class="form" name="dictionary">
            <option value="<?= $dictionaryTable ?>"><?= $dictionaryTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_billing_merchant" class="form">billing merchant</label> 
        <select class="form" name="billing_merchant">
            <option value="<?= $dalobillingmerchantTable ?>"><?= $dalobillingmerchantTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_billing_plans" class="form">billing plans</label> 
        <select class="form" name="billing_plans">
            <option value="<?= $dalobillingplansTable ?>"><?= $dalobillingplansTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_billing_history" class="form">billing history</label> 
        <select class="form" name="billing_history">
            <option value="<?= $dalobillinghistoryTable ?>"><?= $dalobillinghistoryTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>



















                <li class="fieldset">
                <label for="backup_operators_acl" class="form">operators_acl</label> 
        <select class="form" name="operators_acl">
            <option value="<?= $dalooperatorsaclTable ?>"><?= $dalooperatorsaclTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

                <li class="fieldset">
                <label for="backup_operators_acl_files" class="form">operators_acl_files</label> 
        <select class="form" name="operators_acl_files">
            <option value="<?= $dalooperatorsaclfilesTable ?>"><?= $dalooperatorsaclfilesTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


               <li class="fieldset">
                <label for="backup_batch_history" class="form">batch_history</label> 
        <select class="form" name="batch_history">
            <option value="<?= $dalobatchhistoryTable ?>"><?= $dalobatchhistoryTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


               <li class="fieldset">
                <label for="backup_billing_plans_profiles" class="form">billing_plans_profiles</label> 
        <select class="form" name="billing_plans_profiles">
            <option value="<?= $dalobillingplansprofilesTable ?>"><?= $dalobillingplansprofilesTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


               <li class="fieldset">
                <label for="backup_invoice" class="form">invoice</label> 
        <select class="form" name="invoice">
            <option value="<?= $daloinvoiceTable ?>"><?= $daloinvoiceTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


               <li class="fieldset">
                <label for="backup_invoice_items" class="form">invoice_items</label> 
        <select class="form" name="invoice_items">
            <option value="<?= $daloinvoiceitemsTable ?>"><?= $daloinvoiceitemsTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


               <li class="fieldset">
                <label for="backup_invoice_status" class="form">invoice_status</label> 
        <select class="form" name="invoice_status">
            <option value="<?= $daloinvoicestatusTable ?>"><?= $daloinvoicestatusTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


               <li class="fieldset">
                <label for="backup_invoice_type" class="form">invoice_type</label> 
        <select class="form" name="invoice_type">
            <option value="<?= $daloinvoicetypeTable ?>"><?= $daloinvoicetypeTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>

               <li class="fieldset">
                <label for="backup_payment" class="form">payment</label> 
        <select class="form" name="payment">
            <option value="<?= $dalopaymentTable ?>"><?= $dalopaymentTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


               <li class="fieldset">
                <label for="backup_payment_type" class="form">payment_type</label> 
        <select class="form" name="payment_type">
            <option value="<?= $dalopaymenttypeTable ?>"><?= $dalopaymenttypeTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


               <li class="fieldset">
                <label for="backup_node" class="form">node</label> 
        <select class="form" name="node">
            <option value="<?= $dalonodeTable ?>"><?= $dalonodeTable ?></option>
            <option value="">  </option>
            <option value="no">no</option>
            <option value="yes">yes</option>
        </select>
        </li>


                </ul>

        </fieldset>

    </div>

    <input type="submit" name="submit" value='<?= t('buttons','apply') ?>' class="button" />
</form>

        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

</body>
</html>
