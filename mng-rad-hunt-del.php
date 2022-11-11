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

        $groupname = "";
        $nasipaddress = "";
        $nasportid = "";


        $showRemoveDiv = "block";

        if (isset($_POST['nashost'])) {
                $hgroup_array = $_POST['nashost'];
        } else {
                if (isset($_GET['nasportid']))
                $hgroup_array = array($_GET['nasipaddress']."||".$_GET['nasportid']);
        }

        if (isset($hgroup_array)) {

                $allNasipaddresses =  "";
                $allNasportid =  "";

                foreach ($hgroup_array as $hgroup) {

                        list($nasipaddress, $nasportid) = preg_split('/\|\|/', $hgroup);

                        if (trim($nasipaddress) != "") {

                                $allNasipaddresses .= $nasipaddress . ", ";
                                $allNasportid .= $nasportid . ", ";

                                if ( trim($nasportid) != "")  {

                                        include 'library/opendb.php';

                                        // delete only a specific groupname and it's attribute
                                        $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADHG'].
                                                        " WHERE nasipaddress='".$dbSocket->escapeSimple($nasipaddress).
                                                        "' AND nasportid='$nasportid' ";
                                        $res = $dbSocket->query($sql);
                                        $logDebugSQL .= $sql . "\n";

                                        $successMsg = "Deleted HuntGroup(s): <b> $allNasipaddresses </b> with Port ID(s): <b> $allNasportid </b> ";
                                        $logAction .= "Successfully deleted hunt group(s) [$allNasipaddresses] with port id [$allNasportid] on page: ";

                                        include 'library/closedb.php';

                                } else {

                                        include 'library/opendb.php';

                                        $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADHG']." WHERE nasipaddress='".$dbSocket->escapeSimple($nasipaddress)."'";
                                        $res = $dbSocket->query($sql);
                                        $logDebugSQL .= $sql . "\n";

                                        $successMsg = "Deleted all instances for HuntGroup(s): <b> $allNasipaddresses </b>";
                                        $logAction .= "Successfully deleted all instances for huntgroup(s) [$allNasipaddresses] on page: ";

                                        include 'library/closedb.php';

                                }

                        } else {

                                        $failureMsg = "No hunt groupname was entered, please specify a hunt groupname to remove from database";
                                        $logAction .= "Failed deleting empty hunt group on page: ";
                        }

                } // foreach

                $showRemoveDiv = "none";

        }

    include_once("lang/main.php");
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','mngradhuntdel.php');
    $help = t('helpPage','mngradhuntdel');
    
    print_html_prologue($title, $langCode);

    include ("menu-mng-rad-hunt.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
?>

<div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">

        <fieldset>

            <h302> <?php echo t('title','HGInfo') ?> </h302>
            <br/>

            <label for='nasipaddress' class='form'><?php echo t('all','HgIPHost') ?></label>
            <input name='nasipaddress' type='text' id='nasipaddress' value='' tabindex=100 />
            <br />

                        <label for='nasportid' class='form'><?php echo t('all','HgPortId') ?></label>
                        <input name='nasportid' type='text' id='nasportid' value='<?php echo $nasportid ?>' tabindex=101 />
                        <br/>

            <br/><br/>
            <hr><br/>

            <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>

        </form>
        </div>

<?php
    include('include/config/logging.php');
    
    include_once("include/management/autocomplete.php");
    if ($autoComplete) {
         $inline_extra_js = "
autoComEdit = new DHTMLSuite.autoComplete();
autoComEdit.add('nasipaddress','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteHGHost');";
    } else {
        $inline_extra_js = "";
    }
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
