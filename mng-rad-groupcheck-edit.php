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

    include 'library/opendb.php';

    // declaring variables
    $groupname = "";
    $value = "";
    $op = "";
    $attribute = "";

    isset($_GET['groupname']) ? $groupname = $_GET['groupname'] : $groupname = "";
    isset($_GET['value']) ? $value = $_GET['value'] : $value = "";
    isset($_GET['value']) ? $valueOld = $_GET['value'] : $valueOld = "";
    isset($_GET['attribute']) ? $attribute = $_GET['attribute'] : $attribute = "";

    $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." WHERE GroupName='".$dbSocket->escapeSimple($groupname).
        "' AND Value='".$dbSocket->escapeSimple($value)."' AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
    $res = $dbSocket->query($sql);

    $logDebugSQL = "";
    $logDebugSQL .= $sql . "\n";

    $row = $res->fetchRow();        // array fetched with values from $sql query

    $op = $row[3];
    $attribute = $row[2];
    

    if (isset($_POST['submit'])) {
        $groupname = $_REQUEST['groupname'];
        $value = $_REQUEST['value'];;
        $valueOld = $_REQUEST['valueOld'];;            
        $op = $_REQUEST['op'];;
        $attribute = $_REQUEST['attribute'];;

        include 'library/opendb.php';

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." WHERE GroupName='".$dbSocket->escapeSimple($groupname).
            "'AND Value='".$dbSocket->escapeSimple($valueOld)."'  AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 1) {

            if (trim($groupname) != "" and trim($value) != "" and trim($op) != "" and trim($attribute) != "") {

                $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADGROUPCHECK']." SET Value='".$dbSocket->escapeSimple($value).
                    "', op='".$dbSocket->escapeSimple($op)."', Attribute='".$dbSocket->escapeSimple($attribute).
                    "' WHERE GroupName='".$dbSocket->escapeSimple($groupname)."' AND Value='".$dbSocket->escapeSimple($valueOld).
                    "' AND Attribute='".$dbSocket->escapeSimple($attribute)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
            
            $successMsg = "Updated group attributes for: <b> $groupname </b>";
            $logAction .= "Successfully updated attributes for group [$groupname] on page: ";

            } else { // if groupname  != ""
                $failureMsg = "you are missing possible values for Groupname, Attribute, Operator or Value";    
                $logAction .= "Failed updating (possible missing attributes) attributes for group [$groupname] on page: ";
            }

        } else {
            $failureMsg = "The group <b> $groupname </b> already exists in the database with value <b> $value </b>
            <br/> Please check that there are no duplicate entries in the database";
            $logAction .= "Failed updating already existing group [$groupname] with value [$value] on page: ";
        } 

        include 'library/closedb.php';
        
    }

    if (isset($_REQUEST['groupname']))
        $groupname = $_REQUEST['groupname'];
    else
        $groupname = "";

    if (trim($groupname) != "") {
        $groupname = $_REQUEST['groupname'];
    } else {
        $failureMsg = "no Groupname was entered, please specify a Groupname to edit </b>";
    }    

    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradgroupcheckedit.php');
    $help = t('helpPage','mngradgroupcheckedit');
    
    print_html_prologue($title, $langCode);

    include ("menu-mng-rad-hunt.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
    
?>
        
<form name="newuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <input type="hidden" value="<?php echo $groupname ?>" name="groupname" /><br/>
    <input type="hidden" value="<?php echo $valueOld ?>" name="valueOld" /><br/>
            
    <fieldset>

            <h302> <?php echo t('title','GroupInfo') ?> </h302>
            <br/>

            <label for='attribute' class='form'><?php echo t('all','Attribute') ?></label>
            <input name='attribute' type='text' id='attribute' value='<?php echo $attribute ?>' tabindex=100 />
            <br/>

            <label for='op' class='form'><?php echo t('all','Operator') ?></label>
            <select name='op' id='op' class='form' tabindex=101 />
                    <option value='<?php echo $op ?>'><?php echo $op ?></option>
                    <?php
                        include 'include/management/populate_selectbox.php';
                        drawOptions();
                    ?>
            </select>
            <br/>


            <label for='newvalue' class='form'><?php echo t('all','NewValue') ?></label>
            <input name='value' type='text' id='value' value='<?php echo $value ?>' tabindex=102 />
            <br/>

            <br/><br/>
            <hr><br/>

            <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>

        </form>

<?php
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
