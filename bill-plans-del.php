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

    isset($_REQUEST['planName']) ? $plans = $_REQUEST['planName'] : $plans = "";

    $showRemoveDiv = "block";

    if (isset($_REQUEST['planName'])) {

        if (!is_array($plans))
            $plans = array($plans);

        $allPlans = "";

        include 'library/opendb.php';
    
        foreach ($plans as $variable=>$value) {
            if (trim($value) != "") {

                $planName = $value;
                $allPlans .= $planName . ", ";

                // remove the plan entry from the plans table
                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
                        " WHERE planName='".$dbSocket->escapeSimple($planName)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
                
                // remove plan's association with profiles from the plans_profiles table
                $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'].
                        " WHERE plan_name='".$dbSocket->escapeSimple($planName)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
                
                $successMsg = "Deleted billing plan(s): <b> $allPlans </b>";
                $logAction .= "Successfully deleted billing plan(s) [$allPlans] on page: ";
                
            } else { 
                $failureMsg = "no billing plan name was entered, please specify a billing plan name to remove from database";
                $logAction .= "Failed deleting billing plan(s) [$allPlans] on page: ";
            }

        } //foreach

        $plans = "";
        include 'library/closedb.php';

        $showRemoveDiv = "none";
    } 


    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','billplansdel.php');
    $help = t('helpPage','billplansdel');
    
    print_html_prologue($title, $langCode);

    include("menu-bill-plans.php");
    
    if (!empty($plans) && !is_array($plans)) {
        $title .= " :: " . htmlspecialchars($plans, ENT_QUOTES, 'UTF-8');
    }
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

?>

    <div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <fieldset>

        <h302> <?php echo t('title','PlanRemoval') ?> </h302>
        <br/>

        <label for='planNname' class='form'><?php echo t('all','PlanName') ?></label>
        <input name='planName[]' type='text' id='planName' value='<?php echo $plans ?>' tabindex=100 autocomplete="off" />
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
    
    include_once("include/management/autocomplete.php");

    if ($autoComplete) {
        $inline_extra_js = "
autoComEdit = new DHTMLSuite.autoComplete();
autoComEdit.add('planName','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteBillingPlans');";
    } else {
        $inline_extra_js = "";
    }
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
