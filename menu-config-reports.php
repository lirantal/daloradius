<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<body>
<?php
	include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">
		
<?php
	$m_active = "Config";
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/config-subnav.php");
?>      

<div id="sidebar">

	<h2>Configuration</h2>
	
	<h3>Reporting Settings</h3>
	

	<ul class="subnav">

		<li><a href="config-reports-dashboard.php"><b>&raquo;</b>
			<?php echo t('button','DashboardSettings') ?></a>
		</li>

		
	</ul>
	
</div>
