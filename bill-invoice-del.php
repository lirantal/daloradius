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

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
    
    include('library/check_operator_perm.php');
    include_once('library/config_read.php');
    
    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    $invoice_id = array();
    
    if (array_key_exists('invoice_id', $_REQUEST) && !empty($_REQUEST['invoice_id'])) {
        $tmparr = (!is_array($_REQUEST['invoice_id'])) ? array( $_REQUEST['invoice_id'] ) : $_POST['invoice_id'];
        
        foreach ($tmparr as $tmp_id) {
            $tmp_id = intval(trim($tmp_id));
            if (!in_array($tmp_id, $invoice_id)) {
                $invoice_id[] = $tmp_id;
            }
        }
    }

    $showRemoveDiv = "block";

    if (count($invoice_id) > 0) {
        $allInvoices = "";

        include 'library/opendb.php';
    
        foreach ($invoice_id as $variable=>$value) {
            if (trim($value) != "") {

                $invoice_id_single = $value;
                $allInvoices .= $invoice_id_single . ", ";

                // remove invoice id 
                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'].
                        " WHERE id='".$dbSocket->escapeSimple($invoice_id_single)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                // remove invoice items associated with this invoice id
                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'].
                        " WHERE invoice_id='".$dbSocket->escapeSimple($invoice_id_single)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
                
                $successMsg = "Deleted invoice id(s): <b> $allInvoices </b>";
                $logAction .= "Successfully deleted invoice id(s) [$allInvoices] on page: ";
                
            } else { 
                $failureMsg = "no invoice id was entered, please specify an invoice id to remove from database";
                $logAction .= "Failed deleting invoice id(s) [$allInvoices] on page: ";
            }

        } //foreach

        $plans = "";
        include 'library/closedb.php';

        $showRemoveDiv = "none";
    } 


    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','billinvoicedel.php');
    $help = t('helpPage','billinvoicedel');
    
    print_html_prologue($title, $langCode);

    include ("menu-mng-batch.php");
    
    if (!empty($invoice_id) && !is_array($invoice_id)) {
        $title .= " :: #" . htmlspecialchars($invoice_id, ENT_QUOTES, 'UTF-8');
    }
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

?>

<div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <fieldset>

        <h302> <?php echo t('title','InvoiceRemoval') ?> </h302>
        <br/>

        <label for='invoice_id' class='form'><?php echo t('all','InvoiceID') ?></label>
        <input name='invoice_id[]' type='text' id='invoice_id' value='<?php echo $invoice_id ?>' tabindex=100 autocomplete="off" />
        <br/>

        <br/><br/>
        <hr><br/>

        <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=1000 
            class='button' />

    </fieldset>

    </form>
    </div>

<?php
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
