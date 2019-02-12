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
	$m_active = "Reports";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/reports-subnav.php");
?>      

<div id="sidebar">

	<h2>Status</h2>
		<ul class="subnav">

		<h3>Status</h3>

			<li><a href="rep-stat-server.php"><b>&raquo;</b>
				<img src='images/icons/reportsStatus.png' border='0'>&nbsp;<?php echo t('button','ServerStatus') ?></a></li>
			<li><a href="rep-stat-services.php"><b>&raquo;</b>
				<img src='images/icons/reportsStatus.png' border='0'>&nbsp;<?php echo t('button','ServicesStatus') ?></a></li>

		</ul>

		<ul class="subnav">
		<h3>Extended Peripherals</h3>

			<li><a href="rep-stat-cron.php"><b>&raquo;</b>
				<img src='images/icons/reportsStatus.png' border='0'>&nbsp;CRON Status</a></li>
			<li><a href="rep-stat-ups.php"><b>&raquo;</b>
				<img src='images/icons/reportsStatus.png' border='0'>&nbsp;UPS Status</a></li>
			<li><a href="rep-stat-raid.php"><b>&raquo;</b>
				<img src='images/icons/reportsStatus.png' border='0'>&nbsp;RAID Status</a></li>
				

		</ul>
		
		
	<br/><br/>
	
	
	

</div>
