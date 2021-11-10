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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

        $groupname = "";
        $nasipaddress = "";
        $nasportid = "";
        $logAction = "";
        $logDebugSQL = "";

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

        include_once('library/config_read.php');
    $log = "visited page: ";





?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>


<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>


<?php
	include ("menu-mng-rad-hunt.php");
?>

	<div id="contentnorightbar">

		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradhuntdel.php') ?>
		:: <?php if (isset($nasipaddress)) { echo $nasipaddress; } ?><h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradhuntdel') ?>
			<br/>
		</div>
		<?php
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
?>

		</div>

		<div id="footer">

<?php
	include 'page-footer.php';
?>


		</div>

</div>
</div>

<?php
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('nasipaddress','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteHGHost');
                      </script>";
        }

?>

</body>
</html>
