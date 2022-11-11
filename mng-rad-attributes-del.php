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

    isset($_GET['vendor']) ? $vendor = $$_GET['vendor'] : $vendor = "";
    isset($_GET['attribute']) ? $attribute = $$_GET['attribute'] : $attribute = "";

    $showRemoveDiv = "block";

    if (isset($_POST['vendor'])) {

        if (is_array($_POST['vendor'])) {
            $vendor_array = $_POST['vendor'];
        } else {
            $vendor_array = array($_POST['vendor']."||".$_POST['attribute']);
        }

        foreach ($vendor_array as $vendor_attribute) {

                    list($vendor, $attribute) = preg_split('\|\|', $vendor_attribute);

                    if ( (trim($vendor) != "") && (trim($attribute) != "") ) {

                            $allVendors =  "";
                            $allAttributes = "";
                            include 'library/opendb.php';

                include 'library/opendb.php';

                $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." WHERE vendor='".
                        $dbSocket->escapeSimple($vendor)."' AND attribute='".$dbSocket->escapeSimple($attribute)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                if ($res->numRows() == 1) {
                    if (trim($vendor) != "" and trim($attribute) != "") {
                        // remove vendor/attribute pairs from database
                        $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." WHERE vendor='".
                            $dbSocket->escapeSimple($vendor)."' AND attribute='".$dbSocket->escapeSimple($attribute)."'";
                        $res = $dbSocket->query($sql);
                        $logDebugSQL .= $sql . "\n";

                        $successMsg = "Removed from database vendor attribute: <b>$attribute</b> of vendor: <b>$vendor</b>";
                        $logAction .= "Successfully removed vendor [$vendor] and attribute [$attribute] from database on page: ";
                    } else {
                        $failureMsg = "you must provide atleast a vendor name and attribute";
                        $logAction .= "Failed removing vendor [$vendor] and attribute [$attribute] from database on page: ";
                    }
                } else {
                    $failureMsg = "You have tried to remove a vendor's attribute that either is not present in the database or there
                            may be more than 1 entry for this vendor attribute in database (attribute :$attribute)";
                    $logAction .= "Failed removing vendor attribute already in database [$attribute] on page: ";
                } //if ($res->numRows() == 1)

                include 'library/closedb.php';

            } // if (trim...

        } //foreach

        $showRemoveDiv = "none";

    } //if (isset)


    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradattributesdel.php');
    $help = t('helpPage','mngradattributesdel');
    
    print_html_prologue($title, $langCode);

    include ("menu-mng-rad-attributes.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');

?>

    <div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <fieldset>

        <h302> <?php echo t('title','VendorAttribute'); ?> </h302>
        <br/>

        <ul>

        <li class='fieldset'>
        <label for='vendor' class='form'><?php echo t('all','VendorName') ?></label>
        <input name='vendor' type='text' id='vendor' value='<?php if (isset($vendor)) echo $vendor ?>' tabindex=100 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('vendorNameTooltip')" />

        <div id='vendorNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','vendorNameTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='attribute' class='form'><?php echo t('all','Attribute') ?></label>
        <input name='attribute' type='text' id='attribute' value='<?php if (isset($attribute)) echo $attribute ?>' tabindex=101 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('attributeTooltip')" />

        <div id='attributeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','attributeTooltip') ?>
        </div>
        </li>


        <li class='fieldset'>
        <br/>
        <hr><br/>
        <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
        </li>

        </ul>
    </fieldset>

    </form>
    </div>

<?php
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
