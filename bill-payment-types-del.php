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
 *             Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');
    
    // init logging variables
    $logAction = "";
    $logDebugSQL = "";
    $log = "visited page: ";

    isset($_REQUEST['paymentname']) ? $paymentname = $_REQUEST['paymentname'] : $paymentname = "";

    $showRemoveDiv = "block";

    if (isset($_REQUEST['paymentname'])) {

        if (!is_array($paymentname))
            $paymentname = array($paymentname);

        $allPayments = "";

        include 'library/opendb.php';
    
        foreach ($paymentname as $variable=>$value) {
            if (trim($value) != "") {

                $name = $value;
                $allPayments .= $name . ", ";

                // delete all payment types 
                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']." WHERE value='".
                        $dbSocket->escapeSimple($name)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
                
                $successMsg = "Deleted payment type(s): <b> $allPayments </b>";
                $logAction .= "Successfully deleted payment type(s) [$allPayments] on page: ";
                
            } else { 
                $failureMsg = "no payment type was entered, please specify a rapayment type name to remove from database";
                $logAction .= "Failed deleting payment type(s) [$allPayments] on page: ";
            }

        } //foreach

        include 'library/closedb.php';

        $showRemoveDiv = "none";
    } 


    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','paymenttypesdel.php');
    $help = t('helpPage','paymenttypesdel');
    
    print_html_prologue($title, $langCode);

    include("menu-bill-payments.php");
    
    if (!empty($paymentname) && !is_array($paymentname)) {
        $title .= " :: " . htmlspecialchars($paymentname, ENT_QUOTES, 'UTF-8');
    }
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

?>

    <div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <fieldset>

        <h302> <?php echo t('title','PayTypeInfo') ?> </h302>
        <br/>

        <label for='paymentname' class='form'><?php echo t('all','PayTypeName') ?></label>
        <input name='paymentname[]' type='text' id='paymentname' value='<?php echo $paymentname ?>' tabindex=100 />
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
