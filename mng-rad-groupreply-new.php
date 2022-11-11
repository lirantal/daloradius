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

    // declaring variables
    $groupname = "";

    $allValues = "";
    $allAttributes = "";

    if (isset($_POST['submit'])) {
    
        include 'library/opendb.php';    

        $groupname = $_REQUEST['groupname'];

        if ($groupname) {
    
            $counter = 0;
            foreach ($_POST as $element=>$field) {

                // switch case to rise the flag for several $attribute which we do not
                // wish to process (ie: do any sql related stuff in the db)
                switch ($element) {
                    case "groupname":
                    case "submit":
                            $skipLoopFlag = 1;      // if any of the cases above has been met we set a flag
                                                    // to skip the loop (continue) without entering it as
                                                    // we do not want to process this $attribute in the following
                                                    // code block
                            break;
                }

                if ($skipLoopFlag == 1) {
                    $skipLoopFlag = 0;              // resetting the loop flag
                    continue;
                }

                if (isset($field[0]))
                    $attribute = $field[0];
                if (isset($field[1]))
                    $value = $field[1];
                if (isset($field[2]))
                    $op = $field[2];

                // we explicitly set the table target to be radgroupreply
                $table = $configValues['CONFIG_DB_TBL_RADGROUPREPLY'];

                if (!($value))
                    continue;    // we don't process empty values attributes

                $allValues .= $value . "\n";
                $allAttributes .= $attribute . "\n";

                $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADGROUPREPLY'].
                        " WHERE GroupName='".$dbSocket->escapeSimple($groupname)."' AND Attribute='".
                        $dbSocket->escapeSimple($attribute)."'";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";
                
                if ($res->numRows() == 0) {
                    // insert radgroupreply details
                    // assuming there's no groupname with that attribute in the table
                    $sql = "INSERT INTO $table (id, GroupName, Attribute, Op, Value) ".
                        " VALUES (0,'".$dbSocket->escapeSimple($groupname)."','".
                        $dbSocket->escapeSimple($attribute)."', '".
                        $dbSocket->escapeSimple($op)."', '".
                        $dbSocket->escapeSimple($value)."')";
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
                    $counter++;
                    
                    $successMsg = "Added to database new group: <b> $groupname </b> with attribute(s): <b> $allAttributes </b> and value(s): <b> $allValues </b>";
                    $logAction .= "Successfully added group [$groupname] with attribute(s): <b> $allAttributes </b> and value(s): <b> $allValues </b> on page: ";
                } else { 
                    $failureMsg = "The group <b> $groupname </b> already exist in the database with attribute(s) <b> $allAttributes </b>";
                    $logAction .= "Failed adding already existing group [$groupname] with attribute(s) [$allAttributes] on page: ";
                } // end else if mysql
            }

           } else { // if groupname isset
            $failureMsg = "No groupname was defined";
            $logAction .= "Failed adding missing values for groupname on page: ";
           }
        include 'library/closedb.php';    
    }


    isset($groupname) ? $groupname = $groupname : $groupname = "";

     
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/dynamic_attributes.js",
        "library/javascript/ajaxGeneric.js",
    );
    
    $title = t('Intro','mngradgroupreplynew.php');
    $help = t('helpPage','mngradgroupreplynew');
    
    print_html_prologue($title, $langCode, array(), $extra_js);

    include("menu-mng-rad-groups.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

?>


        <form name="newgroupreply" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">


        <fieldset>

            <h302> <?php echo t('title','GroupInfo') ?> </h302>
            <br/>

            <label for='groupname' class='form'><?php echo t('all','Groupname') ?></label>
            <input name='groupname' type='text' id='groupname' value='<?php echo $groupname ?>' tabindex=100 />
            <br />

            <br/><br/>
            <hr><br/>

            <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>

    <br/>


<?php
    include_once('include/management/attributes.php');
?>


        </form>

<?php
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>

