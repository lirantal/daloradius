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

    isset($_POST['paymentname']) ? $paymentname = $_POST['paymentname'] : $paymentname = "";
    isset($_POST['paymentnotes']) ? $paymentnotes = $_POST['paymentnotes'] : $paymentnotes = "";

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST["submit"])) {
        $paymentname = $_POST['paymentname'];
        $paymentnotes = $_POST['paymentnotes'];
        
        include 'library/opendb.php';

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES']." WHERE value='".$dbSocket->escapeSimple($paymentname)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 0) {
            if (trim($paymentname) != "") {

                $currDate = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];
                
                // insert apyment type info
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'].
                    " (id, value, notes, ".
                    "  creationdate, creationby, updatedate, updateby) ".
                    " VALUES (0, '".$dbSocket->escapeSimple($paymentname)."', '".
                    $dbSocket->escapeSimple($paymentnotes)."', ".
                    " '$currDate', '$currBy', NULL, NULL)";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                $successMsg = "Added to database new payment type: <b>$paymentname</b>";
                $logAction .= "Successfully added new payment type [$paymentname] on page: ";
            } else {
                $failureMsg = "you must provide a payment type name";    
                $logAction .= "Failed adding new payment type [$paymentname] on page: ";    
            }
        } else { 
            $failureMsg = "You have tried to add a payment type that already exist in the database: $paymentname";
            $logAction .= "Failed adding new payment type already in database [$paymentname] on page: ";        
        }
    
        include 'library/closedb.php';

    }

    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','paymenttypesnew.php');
    $help = t('helpPage','paymenttypesnew');
    
    print_html_prologue($title, $langCode);

    include("menu-bill-payments.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

?>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">



    <fieldset>

        <h302> <?php echo t('title','PayTypeInfo'); ?> </h302>
        <br/>

        <ul>

        <li class='fieldset'>
        <label for='name' class='form'><?php echo t('all','PayTypeName') ?></label>
        <input name='paymentname' type='text' id='paymentname' value='' tabindex=100 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('paymentTypeTooltip')" /> 
        
        <div id='paymentTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','paymentTypeTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='paymentnotes' class='form'><?php echo t('all','PayTypeNotes') ?></label>
        <input name='paymentnotes' type='text' id='paymentnotes' value='' tabindex=101 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('paymentTypeNotesTooltip')" /> 
        
        <div id='paymentTypeNotesTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?php echo t('Tooltip','paymentTypeNotesTooltip') ?>
        </div>
        </li>
    
        </li>

        </ul>
    </fieldset>

    <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />

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
