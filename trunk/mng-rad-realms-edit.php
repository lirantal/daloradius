<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

        isset($_GET['realmname']) ? $realmname = $_GET['realmname'] : $realmname = "";

	$logDebugSQL = "";

	if (isset($_POST["submit"])) {

		isset($_POST['realmname']) ? $realmname = $_POST['realmname'] : $realmname = "";
		isset($_POST['type']) ? $type = $_POST['type'] : $type = "";
		isset($_POST['authhost']) ?$authhost = $_POST['authhost'] : $authhost = "";
		isset($_POST['accthost']) ? $accthost = $_POST['accthost'] : $accthost = "";
		isset($_POST['secret']) ? $secret = $_POST['secret'] : $secert = "";
		isset($_POST['ldflag']) ? $ldflag = $_POST['ldflag'] : $ldflag = "";
		isset($_POST['nostrip']) ? $nostrip = $_POST['nostrip'] : $nostrip = "";
		isset($_POST['hints']) ? $hints = $_POST['hinst'] : $hints = "";
		isset($_POST['notrealm']) ? $notrealm = $_POST['notrealm'] :  $notrealm = "";
		
		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS']." WHERE realmname='".$dbSocket->escapeSimple($realmname)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {
			if (trim($realmname) != "") {

				// update realm entry in database
                                $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOREALMS']." SET type='".
                                        $dbSocket->escapeSimple($type)."', authhost='".
                                        $dbSocket->escapeSimple($authhost)."', accthost='".$dbSocket->escapeSimple($accthost)."', secret='".
                                        $dbSocket->escapeSimple($secret)."', ldflag='".$dbSocket->escapeSimple($ldflag)."', nostrip='".
                                        $dbSocket->escapeSimple($nostrip)."', hints='".$dbSocket->escapeSimple($hints)."', notrealm='".
                                        $dbSocket->escapeSimple($notrealm)."' WHERE realmname=$realmname;";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Added to database new hotspot: <b>$name</b>";
				$logAction = "Successfully added new hotspot [$name] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "you must provide atleast a hotspot name and mac-address";	
				$logAction = "Failed adding new hotspot [$name] on page: ";	
			}
		} else { 
			$actionStatus = "failure";
			$actionMsg = "You have tried to add a hotspot that already exist in the database: $name";	
			$logAction = "Failed adding new hotspot already in database [$name] on page: ";		
		}
	
		include 'library/closedb.php';

	}


	include 'library/opendb.php';

        // fill-in realm information in html elements
        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS']." WHERE realmname='".$dbSocket->escapeSimple($realmname)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $type = $row[type];
        $authhost = $row[authhost];
        $accthost = $row[accthost];
        $secret = $row[secret];
        $ldflag = $row[ldflag];
        $nostrip = $row[nostrip];
        $hints = $row[hints];
        $notrealm = $row[notrealm];

	include 'library/closedb.php';


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
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-mng-rad-realms.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradrealmsedit.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradrealmsedit'] ?>
					<br/>
				</div>
				<br/>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['title']['RealmInfo']; ?>">

	<fieldset>

		<h302> <?php echo $l['title']['RealmInfo']; ?> </h302>
		<br/>

		<ul>

                <li class='fieldset'>
		<label for='realmname' class='form'><?php echo $l['all']['RealmName'] ?></label>
		<input name='realmname' type='text' id='realmname' value='<?php if (isset($realmname)) echo $realmname; ?>' tabindex=100
                        onfocus="javascript:toggleShowDiv('realmNameTooltip')"
                        onblur="javascript:toggleShowDiv('realmNameTooltip')" />
                <div id='realmNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmNameTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='type' class='form'><?php echo $l['all']['Type'] ?></label>
		<input name='type' type='text' id='type' value='<?php if (isset($type)) echo $type; ?>' tabindex=101
                        onfocus="javascript:toggleShowDiv('realmTypeTooltip')"
                        onblur="javascript:toggleShowDiv('realmTypeTooltip')" />
                <div id='realmTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmTypeTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='authhost' class='form'><?php echo $l['all']['AuthHost'] ?></label>
		<input name='authhost' type='text' id='authhost' value='<?php if (isset($authhost)) echo $authhost; ?>' tabindex=102
                        onfocus="javascript:toggleShowDiv('realmAuthhostTooltip')"
                        onblur="javascript:toggleShowDiv('realmAuthhostTooltip')" />
                <div id='realmAuthhostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmAuthhostTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='accthost' class='form'><?php echo $l['all']['AcctHost'] ?></label>
		<input name='accthost' type='text' id='accthost' value='<?php if (isset($accthost)) echo $accthost; ?>' tabindex=103
                        onfocus="javascript:toggleShowDiv('realmAccthostTooltip')"
                        onblur="javascript:toggleShowDiv('realmAccthostTooltip')" />
                <div id='realmAccthostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmAccthostTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='secret' class='form'><?php echo $l['all']['RealmSecret'] ?></label>
		<input name='secret' type='text' id='secret' value='<?php if (isset($secret)) echo $secret; ?>' tabindex=104
                        onfocus="javascript:toggleShowDiv('realmSecretTooltip')"
                        onblur="javascript:toggleShowDiv('realmSecretTooltip')" />
                <div id='realmSecretTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmSecretTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000
			class='button' />
		</li>

		</ul>
	</fieldset>

	</div>


     <div class="tabbertab" title="<?php echo $l['title']['Advanced']; ?>">

	<fieldset>

		<h302> <?php echo $l['title']['RealmInfo']; ?> </h302>
		<br/>
		<ul>

                <li class='fieldset'>
		<label for='ldflag' class='form'><?php echo $l['all']['Ldflag'] ?></label>
		<input name='ldflag' type='text' id='ldflag' value='<?php if (isset($ldflag)) echo $ldflag; ?>' tabindex=105
                        onfocus="javascript:toggleShowDiv('realmLdflagTooltip')"
                        onblur="javascript:toggleShowDiv('realmLdflagTooltip')" />
                <div id='realmLdflagTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmLdflagTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='nostrip' class='form'><?php echo $l['all']['Nostrip'] ?></label>
		<input name='nostrip' type='text' id='nostrip' value='<?php if (isset($nostrip)) echo $nostrip; ?>' tabindex=106
                        onfocus="javascript:toggleShowDiv('realmNostripTooltip')"
                        onblur="javascript:toggleShowDiv('realmNostripTooltip')" />
                <div id='realmNostripTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmNostripTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='hints' class='form'><?php echo $l['all']['Hints'] ?></label>
		<input name='hints' type='text' id='hints' value='<?php if (isset($hints)) echo $hints; ?>' tabindex=107
                        onfocus="javascript:toggleShowDiv('realmHintsTooltip')"
                        onblur="javascript:toggleShowDiv('realmHintsTooltip')" />
                <div id='realmHintsTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmHintsTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='notrealm' class='form'><?php echo $l['all']['Notrealm'] ?></label>
		<input name='notrealm' type='text' id='notrealm' value='<?php if (isset($notrealm)) echo $notrealm; ?>' tabindex=108
                        onfocus="javascript:toggleShowDiv('realmNotrealmTooltip')"
                        onblur="javascript:toggleShowDiv('realmNotrealmTooltip')" />
                <div id='realmNotrealmTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['realmNotrealmTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000
			class='button' />
		</li>

		</ul>
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





