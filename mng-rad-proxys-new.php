<?php 
    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	$logDebugSQL = "";

	if (isset($_POST["submit"])) {

                isset($_POST['proxyname']) ? $proxyname = $_POST['proxyname'] : $proxyname = "";
                isset($_POST['retry_delay']) ? $retry_delay = $_POST['retry_delay'] : $retry_delay = "";
                isset($_POST['retry_count']) ? $retry_count = $_POST['retry_count'] : $retry_count = "";
                isset($_POST['dead_time']) ? $dead_time = $_POST['dead_time'] : $dead_time = "";
                isset($_POST['default_fallback']) ? $default_fallback = $_POST['default_fallback'] :  $default_fallback = "";
		
		include 'library/opendb.php';

		$sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOPROXYS']." WHERE proxyname='".$dbSocket->escapeSimple($proxyname)."'";
		$res = $dbSocket->query($sql);
		$logDebugSQL .= $sql . "\n";

		if ($res->numRows() == 0) {
			if (trim($proxyname) != "") {

				// insert proxy to database
				$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOPROXYS']." values (0, '".
					$dbSocket->escapeSimple($proxyname)."','".$dbSocket->escapeSimple($retry_delay)."','".
					$dbSocket->escapeSimple($retry_count)."','".$dbSocket->escapeSimple($dead_time)."','".
					$dbSocket->escapeSimple($default_fallback)."')";
				$res = $dbSocket->query($sql);
				$logDebugSQL .= $sql . "\n";

				$actionStatus = "success";
				$actionMsg = "Added to database new proxy: <b>$proxyname</b>";
				$logAction = "Successfully added new proxy [$proxyname] on page: ";
			} else {
				$actionStatus = "failure";
				$actionMsg = "you must provide at least a proxy name";
				$logAction = "Failed adding new proxy [$proxyname] on page: ";	
			}
		} else { 
			$actionStatus = "failure";
			$actionMsg = "You have tried to add a proxy that already exist in the database: $proxyname";
			$logAction = "Failed adding new proxy already in database [$proxyname] on page: ";
		}
	
		include 'library/closedb.php';

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
        include_once ("library/tabber/tab-layout.php");
?>
 
<?php

	include ("menu-mng-rad-realms.php");
	
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['mngradproxysnew.php'] ?>
				<h144>+</h144></a></h2>
				
				<div id="helpPage" style="display:none;visibility:visible" >
					<?php echo $l['helpPage']['mngradproxysnew'] ?>
					<br/>
				</div>
				<br/>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo $l['title']['ProxyInfo']; ?>">

	<fieldset>

		<h302> <?php echo $l['title']['ProxyInfo']; ?> </h302>
		<br/>

		<ul>

                <li class='fieldset'>
		<label for='proxyname' class='form'><?php echo $l['all']['ProxyName'] ?></label>
		<input name='proxyname' type='text' id='proxyname' value='' tabindex=100
                        onfocus="javascript:toggleShowDiv('proxyNameTooltip')"
                        onblur="javascript:toggleShowDiv('proxyNameTooltip')" />
                <div id='proxyNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['proxyNameTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='retry_delay' class='form'><?php echo $l['all']['RetryDelay'] ?></label>
		<input name='retry_delay' type='text' id='retry_delay' value='' tabindex=102
                        onfocus="javascript:toggleShowDiv('proxyRetryDelayTooltip')"
                        onblur="javascript:toggleShowDiv('proxyRetryDelayTooltip')" />
                <div id='proxyRetryDelayTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['proxyRetryDelayTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='retry_count' class='form'><?php echo $l['all']['RetryCount'] ?></label>
		<input name='retry_count' type='text' id='retry_count' value='' tabindex=103
                        onfocus="javascript:toggleShowDiv('proxyRetryCountTooltip')"
                        onblur="javascript:toggleShowDiv('proxyRetryCountTooltip')" />
                <div id='proxyRetryCountTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['proxyRetryCountTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='dead_time' class='form'><?php echo $l['all']['DeadTime'] ?></label>
		<input name='dead_time' type='text' id='dead_time' value='' tabindex=104
                        onfocus="javascript:toggleShowDiv('proxyDeadTimeTooltip')"
                        onblur="javascript:toggleShowDiv('proxyDeadTimeTooltip')" />
                <div id='proxyDeadTimeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['proxyDeadTimeTooltip'] ?>
                </div>
		</li>

                <li class='fieldset'>
		<label for='default_fallback' class='form'><?php echo $l['all']['DefaultFallback'] ?></label>
		<input name='default_fallback' type='text' id='default_fallback' value='' tabindex=104
                        onfocus="javascript:toggleShowDiv('proxyDefaultFallbackTooltip')"
                        onblur="javascript:toggleShowDiv('proxyDefaultFallbackTooltip')" />
                <div id='proxyDefaultFallbackTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/error.png' alt='Tip' border='0' />
                        <?php echo $l['Tooltip']['proxyDefaultFallbackTooltip'] ?>
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





