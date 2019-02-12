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

	$logAction = "";
	$logDebugSQL = "";

	isset($_POST['vendor']) ? $vendor = $_POST['vendor'] : $vendor = "";
	isset($_POST['dictionary']) ? $dictionary = $_POST['dictionary'] : $dictionary = "";

	if (isset($_POST['submit'])) {

		include 'library/opendb.php';

        $myDictionary = preg_split('/\n/', $dictionary);            // we break the POST variable (continous string) into an array

		$myVendor = $vendor;							// by default we set the vendor name to be the file name
		$myAttribute = '';								// variables are initialized
		$myType = '';

		$vendorUnique = 1;							// we set $vendorUnique boolean to be unique

		foreach($myDictionary as $line) {

			if (preg_match('/^#/', $line))
				continue;						// if a line starts with # then it's a comment, skip it

			if (preg_match('/^\n/', $line))
				continue;						// if a line is empty, we skip it as well

			if (preg_match('/^VALUE/', $line))
				continue;						// if a line starts with VALUE we have no use for it, we skip it

			if (preg_match('/^BEGIN-VENDOR/', $line))
				continue;						// if a line starts with BEGIN-VENDOR we have no use for it,
											// we skip it

			if (preg_match('/^END-VENDOR/', $line))
				continue;						// if a line starts with END-VENDOR we have no use for it,
											// we skip it


			if (preg_match('/^VENDOR/', $line)) {				// extract vendor name

				if (preg_match('/\t/', $line))
					list($junk, $vendorTmp) = preg_split('/\t+/', $line);		// check if line is splitted by a sequence of tabs
				else if (preg_match('/ /', $line))
					list($junk, $vendorTmp) = preg_split('/[ ]+/', $line);		// check if line is splitted by a sequence of
													// whitespaces

				if ($vendorTmp != "")
					$myVendor = "'".trim($vendorTmp)."'";

				continue;
			}


			if (preg_match('/^ATTRIBUTE/', $line)) {				// extract attribute name

				if (preg_match('/\t/', $line))
					list($junk, $attribute, $junk2, $type) = preg_split('/\t+/', $line);		// check if line is splitted by
															// a sequence of tabs
				else if (preg_match('/ /', $line))
					list($junk, $attribute, $junk2, $type) = preg_split('/[ ]+/', $line);		// check if line is splitted by
															//a sequence of whitespaces
				if ($attribute != "")
					$myAttribute = "'".trim($attribute)."'";
				else
					$myAttribute = "NULL";

				if ($type != "")
					$myType = "'".trim($type)."'";
				else
					$myType = "NULL";

				/*
				// before we start inserting vendor dictionary attributes to the database we need to check that the vendor
				// doesn't already exist - for now we don't check it...

                                $sql = "SELECT Vendor FROM ".$configValues['CONFIG_DB_TBL_DALODICTIONARY'].
                                                " WHERE Vendor = $myVendor";
                                $res = $dbSocket->query($sql);
                                $logDebugSQL .= $sql . "\n";

				$row = $res->fetchRow();

				$vendorName = $row[0];
				if ($vendorName == $myVendor) {
					$vendorUnique = 0;
					break;
				}
				*/

				$myVendor = "'".$vendor."'";

		                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALODICTIONARY']." (Id, Type, Attribute, Vendor)".
		                                " VALUES (0, $myType, $myAttribute, $myVendor)";
		                $res = $dbSocket->query($sql);
		                $logDebugSQL .= $sql . "\n";

			}

		} //foreach

		if ($vendorUnique == 0) {
 	               $failureMsg = "The vendor name specified already exist in the database";
                       $logAction .= "Failed adding duplicate vendor dictionary for vendor [$myVendor] on page: ";
		} else {
        	       $successMsg = "Successfully added vendor dictionary <b>$myVendor</b> to database";
	               $logAction .= "Successfully added vendor dictionary [$myVendor] to database on page: ";
		}


		include 'library/closedb.php';

	} //if (isset)


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

	include ("menu-mng-rad-attributes.php");

?>

	<div id="contentnorightbar">

			<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradattributesimport.php') ?>
			<h144>&#x2754;</h144></a></h2>

			<div id="helpPage" style="display:none;visibility:visible" >
				<?php echo t('helpPage','mngradattributesimport') ?>
				<br/>
			</div>
			<?php
				include_once('include/management/actionMessages.php');
			?>

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
		<label for='dictionary' class='form'><?php echo t('all','Dictionary') ?></label>
		<textarea class='form_fileimport' name='dictionary' tabindex=102></textarea>
		</li>


		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000 class='button' />
		</li>

		</ul>
	</fieldset>

	</form>

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
