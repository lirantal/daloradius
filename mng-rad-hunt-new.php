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


	// declaring variables
	$nasipaddress = "";
	$groupname = "";
	$nasportid = "";

	$logAction = "";
	$logDebugSQL = "";

	if (isset($_POST['submit'])) {
	
		$nasipaddress = $_POST['nasipaddress'];
		$groupname = $_POST['groupname'];
		$nasportid = $_POST['nasportid'];

		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADHG'].
				" WHERE nasipaddress='".$dbSocket->escapeSimple($nasipaddress)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {

			if (trim($nasipaddress) != "" and trim($groupname) != "") {

				if (!$nasportid) {
					$nasportid = 0;
				}
				
				// insert nas details
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADHG'].
					" (id,nasipaddress,groupname,nasportid) ".
					" values (0, '".$dbSocket->escapeSimple($nasipaddress)."', '".$dbSocket->escapeSimple($groupname).
					"', '".$dbSocket->escapeSimple($nasportid)."')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";
			
				$successMsg = "Added new HG to database: <b> $nasipaddress </b>  ";
				$logAction .= "Successfully added hg [$nasipaddress] on page: ";
			} else {
				$failureMsg = "no HG Host or HG GroupName was entered, it is required that you specify both HG Host and HG GroupName";
				$logAction .= "Failed adding (missing ip/groupname) hg [$nasipaddress] on page: ";
			}
		} else {
			$failureMsg = "The HG IP/Host $nasipaddress already exists in the database";	
			$logAction .= "Failed adding already existing hg [$nasipaddress] on page: ";
		}

		include 'library/closedb.php';
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
	include_once ("library/tabber/tab-layout.php");
?> 
 
<?php
	include ("menu-mng-rad-hunt.php");
?>

	<div id="contentnorightbar">
	
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradhuntnew.php') ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >				
			<?php echo t('helpPage','mngradhuntnew') ?>
			<br/>
		</div>
		<?php
			include_once('include/management/actionMessages.php');
		?>
				

                <form name="newhg" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','HGInfo'); ?>">

	<fieldset>

		<h302> <?php echo t('title','HGInfo') ?> </h302>
		<br/>

                <label for='nasipaddress' class='form'><?php echo t('all','HgIPHost') ?></label>
                <input name='nasipaddress' type='text' id='nasipaddress' value='' tabindex=100 />
                <br />


                <label for='groupname' class='form'><?php echo t('all','HgGroupName') ?></label>
                <input name='groupname' type='text' id='groupname' value='' tabindex=101 />
                <br />

                <label for='nasportid' class='form'><?php echo t('all','HgPortId') ?></label>
                <input name='nasportid' type='text' id='nasportid' value='0' tabindex=105 />
                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>


     </div>
</div>
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
