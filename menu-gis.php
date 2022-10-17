
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php
	include "library/leaflet.php";
?>
</head>

<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<body onload="load()">

<?php
	include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">

<?php
	$m_active = "Gis";
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/gis-subnav.php");
?>      

<div id="sidebar">

	<h2>GIS</h2>
	
	<h3>GIS Mapping</h3>
	<ul class="subnav">
		<li><a href="gis-viewmap.php"><b>&raquo;</b><?php echo t('button','ViewMAP') ?></a></li>
		<li><a href="gis-editmap.php"><b>&raquo;</b><?php echo t('button','EditMAP') ?></a></li>		
	</ul>

	<h3>Settings</h3>
	<ul class="subnav">
	
		<li><a href="javascript:document.gisregister.submit();"/><b>&raquo;</b><?php echo t('button','RegisterGoogleMapsAPI')?>
			</a>
			<form name="gisregister" action="gis-main.php" method="get" class="sidebar">
			<input name="code" type="text">
			<input class="sidebutton" name="submit" type="submit" value="Register code">
			</form>
		</li>
	</ul>
	
	<br/><br/>
	

	

</div>
