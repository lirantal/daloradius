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

	isset($_REQUEST['name']) ? $name = $_REQUEST['name'] : $name = "";
	$logAction = "";
	$logDebugSQL = "";

        $showRemoveDiv = "block";

	if (isset($_REQUEST['name'])) {

		if (!is_array($name))
			$name = array($name, NULL);

		$allHotspots = "";

		include 'library/opendb.php';
	
		foreach ($name as $variable=>$value) {
			if (trim($value) != "") {

				$name = $value;
				$allHotspots .= $name . ", ";

				// delete all attributes associated with a username
				$sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." WHERE name='".
						$dbSocket->escapeSimple($name)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
				
				$successMsg = "Deleted hotspot(s): <b> $allHotspots </b>";
				$logAction .= "Successfully deleted hotspot(s) [$allHotspots] on page: ";
				
			} else { 
				$failureMsg = "no hotspot was entered, please specify a hotspot name to remove from database";
				$logAction .= "Failed deleting hotspot(s) [$allHotspots] on page: ";
			}

		} //foreach

		include 'library/closedb.php';

	        $showRemoveDiv = "none";
	} 


	include_once('library/config_read.php');
    $log = "visited page: ";

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<?php

	include ("menu-mng-hs.php");
	
?>		

<div id="contentnorightbar">

	<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mnghsdel.php') ?>
	:: <?php if (isset($name)) { echo $name; } ?><h144>&#x2754;</h144></a></h2>

	<div id="helpPage" style="display:none;visibility:visible" >
		<?php echo t('helpPage','mnghsdel') ?>
		<br/>
	</div>
	<?php
		include_once('include/management/actionMessages.php');
	?>

	<div id="removeDiv" style="display:<?php echo $showRemoveDiv ?>;visibility:visible" >
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<fieldset>

		<h302> <?php echo t('title','HotspotRemoval') ?> </h302>
		<br/>

		<label for='name' class='form'><?php echo t('all','HotSpotName') ?></label>
		<input name='name[]' type='text' id='name' value='<?php echo $name ?>' tabindex=100 autocomplete="off" />
		<br/>

		<br/><br/>
		<hr><br/>

		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=1000 
			class='button' />

	</fieldset>

	</form>
	</div>


<?php
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('name','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteHotspots');
                      </script>";
        }

?>


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


</body>
</html>





