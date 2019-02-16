
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<body>

<?php
        include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">
		
<?php
	$m_active = "Home";
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/home-subnav.php");
?>      

<div id="sidebar">

	<h2>Home</h2>

	<h3>Status</h3>

	<ul class="subnav">

		<li><a href="rep-stat-server.php"><b>&raquo;</b><?php echo t('button','ServerStatus') ?></a></li>
		<li><a href="rep-stat-services.php"><b>&raquo;</b><?php echo t('button','ServicesStatus') ?></a></li>
		<li><a href="rep-lastconnect.php"><b>&raquo;</b><?php echo t('button','LastConnectionAttempts') ?></a></li>

	<h3>Logs</h3>

	        <li><a href="rep-logs-radius.php"><b>&raquo;</b><?php echo t('button','RadiusLog') ?></a></li>
	        <li><a href="rep-logs-system.php"><b>&raquo;</b><?php echo t('button','SystemLog') ?></a></li>

	</ul>
	
	<h3>Support</h3>

	<p class="news">
		daloRADIUS <br/>
		RADIUS Management 
		<a href="https://github.com/lirantal/daloradius" class="more">Read More &raquo;</a>
	</p>


	

	

</div>

