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


	isset($_REQUEST['nashost']) ? $nashost = $_REQUEST['nashost'] : $nashost = "";

	$logAction = "";
	$logDebugSQL = "";

	$showRemoveDiv = "block";

        if (isset($_REQUEST['nashost'])) {

		$allNASs = "";

		/* since the foreach loop will report an error/notice of undefined variable $value because
                   it is possible that the $nashost is not an array, but rather a simple GET request
                   with just some value, in this case we check if it's not an array and convert it to one with
                   a NULL 2nd element
		*/

		if (!is_array($nashost))
			$nashost = array($nashost, NULL);

		foreach ($nashost as $variable=>$value) {

			if (trim($variable) != "") {

			include 'library/opendb.php';

			$nashost = $value;
			$allNASs .= $nashost . ", ";
			//echo "nas: $nashost <br/>";


			// delete all attributes associated with a username
			$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADNAS'].
					" WHERE nasname='".$dbSocket->escapeSimple($nashost)."'";
			$res = $dbSocket->query($sql);
			$logDebugSQL .= $sql . "\n";

			$successMsg = "Deleted all NASs from database: <b> $allNASs </b>";
			$logAction .= "Successfully deleted nas(s) [$allNASs] on page: ";
				
			include 'library/closedb.php';

			}  else {
				$failureMsg = "No nas ip/host was entered, please specify a nas ip/host to remove from database";
				$logAction .= "Failed deleting empty nas on page: ";
			} //if trim

		} //foreach

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
	include ("menu-mng-rad-nas.php");
?>

	<div id="contentnorightbar">
	
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradnasdel.php') ?>
		:: <?php if (isset($nashost)) { echo $nashost; } ?><h144>&#x2754;</h144></a></h2>
		
		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','mngradnasdel') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>

		<div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <fieldset>

			<h302> <?php echo t('title','NASInfo') ?> </h302>
			<br/>

			<label for='nashost' class='form'><?php echo t('all','NasIPHost') ?></label>
			<input name='nashost' type='text' id='nashost' value='' tabindex=100 />
			<br />

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
                      autoComEdit.add('nashost','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteNASHost');
                      </script>";
        }

?>

</body>
</html>
