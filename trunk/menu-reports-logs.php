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
	$m_active = "Reports";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/reports-subnav.php");
?>      

		<div id="sidebar">
		
				<h2>Logs</h2>
                                <ul class="subnav">
				
                                <h3>Log Files</h3>

                                                <li><a href="index-radius-log.php"><b>&raquo;</b>Radius log</a></li>
                                                <li><a href="index-system-log.php"><b>&raquo;</b>System log</a></li>
                                                <li><a href="index-boot-log.php"><b>&raquo;</b>Boot log</a></li>

				</ul>
		
				
				<br/><br/>
				<h2>Search</h2>
				<input name="" type="text" value="Search" />
				
		
		</div>
		
		
		
