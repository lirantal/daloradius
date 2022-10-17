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



    include 'library/opendb.php';

	$nashost = "";
	$nassecret = "";
	$nasname = "";
	$nasports = "";
	$nastype = "";
	$nasdescription = "";
	$nascommunity = "";
	$nasvirtualserver = '';

	isset($_REQUEST['nashost']) ? $nashost = $_REQUEST['nashost'] : $nashost = "";

	$logAction = "";
	$logDebugSQL = "";

	// fill-in nashost details in html textboxes
	$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADNAS']." WHERE nasname='".$dbSocket->escapeSimple($nashost)."'";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";

	$row = $res->fetchRow();		// array fetched with values from $sql query

	// assignment of values from query to local variables
	// to be later used in html to display on textboxes (input)
	$nassecret = $row[5];
	$nasname = $row[2];
	$nasports = $row[4];
	$nastype = $row[3];
	$nasvirtualserver = $row[6];
	$nascommunity = $row[7];
	$nasdescription = $row[8];

	if (isset($_POST['submit'])) {
	
		$nashostold = $_REQUEST['nashostold'];
		$nashost = $_REQUEST['nashost'];
		$nassecret = $_REQUEST['nassecret'];
		$nasname = $_REQUEST['nasname'];
		$nasports = $_REQUEST['nasports'];
		$nastype = $_REQUEST['nastype'];
		$nasdescription = $_REQUEST['nasdescription'];
		$nascommunity = $_REQUEST['nascommunity'];
		$nasvirtualserver = $_REQUEST['nasvirtualserver'];
			
		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADNAS'].
				" WHERE nasname='".$dbSocket->escapeSimple($nashostold)."' ";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 1) {

			if (trim($nashost) != "" and trim($nassecret) != "") {

				if (!$nasports) {
					$nasports = 0;
				}

				if (!$nasvirtualserver) {
                      $nasvirtualserver = '';
               }
				
				// insert nas details
				$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADNAS'].
					" SET nasname='".$dbSocket->escapeSimple($nashost)."', ".
					" shortname='".$dbSocket->escapeSimple($nasname)."', ".
					" type='".$dbSocket->escapeSimple($nastype)."', ".
					" ports=".$dbSocket->escapeSimple($nasports).", ".
					" secret='".$dbSocket->escapeSimple($nassecret)."', ".
                   " server='".$dbSocket->escapeSimple($nasvirtualserver)."', ".
					" community='".$dbSocket->escapeSimple($nascommunity)."', ".
					" description='".$dbSocket->escapeSimple($nasdescription)."' ".
					" WHERE nasname='".$dbSocket->escapeSimple($nashostold)."'";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$successMsg = "Updated NAS settings in database: <b> $nashost </b>  ";
				$logAction .= "Successfully updated attributes for nas [$nashost] on page: ";
			} else {
				$failureMsg = "no NAS Host or NAS Secret was entered, it is required that you specify both NAS Host and NAS Secret";
				$logAction .= "Failed updating attributes for nas [$nashost] on page: ";
			}
			
		} elseif ($res->numRows() > 1) {
			$failureMsg = "The NAS IP/Host <b> $nashost </b> already exists in the database
			<br/> Please check that there are no duplicate entries in the database";
			$logAction .= "Failed updating attributes for already existing nas [$nashost] on page: ";
		} else {
			$failureMsg = "The NAS IP/Host <b> $nashost </b> doesn't exist at all in the database.
			<br/>Please re-check the nashost ou specified.";
			$logAction .= "Failed updating empty nas on page: ";
		}

		include 'library/closedb.php';
	}

	if (isset($_REQUEST['nashost']))
		$nashost = $_REQUEST['nashost'];
	else
		$nashost = "";

	if (trim($nashost) == "") {
		$failureMsg = "no NAS Host or NAS Secret was entered, it is required that you specify both NAS Host and NAS Secret";
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
	include ("menu-mng-rad-nas.php");
?>

	<div id="contentnorightbar">

			<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','mngradnasedit.php') ?>
			:: <?php if (isset($nashost)) { echo $nashost; } ?><h144>&#x2754;</h144></a></h2>

			<div id="helpPage" style="display:none;visibility:visible" >
				<?php echo t('helpPage','mngradnasedit') ?>
				<br/>
			</div>
			<?php
				include_once('include/management/actionMessages.php');
			?>

			<form name="newnas" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			
<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','NASInfo'); ?>">
		<input type="hidden" value="<?php echo $nashost ?>" name="nashostold" />


        <fieldset>

                <h302> <?php echo t('title','NASInfo') ?> </h302>
                <br/>

                <label for='nashost' class='form'><?php echo t('all','NasIPHost') ?></label>
                <input name='nashost' type='text' id='nashost' value='<?php echo $nashost ?>' tabindex=100 />
                <br />

                <label for='nassecret' class='form'><?php echo t('all','NasSecret') ?></label>
                <input name='nassecret' type='text' id='nassecret' value='<?php echo $nassecret ?>' tabindex=101 />
                <br />

                <label for='nastype' class='form'><?php echo t('all','NasType') ?></label>
                <input name='nastype' type='text' id='nastype' value='<?php echo $nastype ?>' tabindex=102 />
                <select onChange="javascript:setStringText(this.id,'nastype')" id="optionSele" tabindex=103 class='form'>
                        <option value="">Select Type...</option>
                        <option value="other">other</option>
                        <option value="cisco">cisco</option>
                        <option value="livingston">livingston</option>
                        <option value="computon">computon</option>
                        <option value="max40xx">max40xx</option>
                        <option value="multitech">multitech</option>
                        <option value="natserver">natserver</option>
                        <option value="pathras">pathras</option>
                        <option value="patton">patton</option>
                        <option value="portslave">portslave</option>
                        <option value="tc">tc</option>
                        <option value="usrhiper">usrhiper</option>
                </select>
                <br />


                <label for='nasname' class='form'><?php echo t('all','NasShortname') ?></label>
                <input name='nasname' type='text' id='nasname' value='<?php echo $nasname ?>' tabindex=104 />
                <br />



                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />

        </fieldset>

	<br/>

        <fieldset>

                <h302> <?php echo t('title','NASAdvanced') ?> </h302>
                <br/>

                <label for='nasports' class='form'><?php echo t('all','NasPorts') ?></label>
                <input name='nasports' type='text' id='nasports' value='<?php echo $nasports ?>' tabindex=105 />
                <br />

                <label for='nascommunity' class='form'><?php echo t('all','NasCommunity') ?></label>
                <input name='nascommunity' type='text' id='nascommunity' value='<?php echo $nascommunity ?>' tabindex=106 />
                <br />

                <label for='nasvirtualserver' class='form'><?php echo t('all','NasVirtualServer') ?></label>
                <input name='nasvirtualserver' type= 'text' id='nasvirtualserver' value='<?php echo $nasvirtualserver ?>' tabindex=107 >
                <br />

                <label for='nasdescription' class='form'><?php echo t('all','NasDescription') ?></label>
	        <textarea class='form' name='nasdescription' id='nasdescription' tabindex=108 ><?php echo $nasdescription ?></textarea>
                <br />

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
