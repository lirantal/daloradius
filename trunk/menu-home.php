<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

?>
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
    include_once ("include/menu/header.php");
?>      

		<div id="sidebar">
		
				<h2>Home</h2>
				
				<h3>Status</h3>
				

                                <ul class="subnav">
                                
                                                <li><a href="index-server-stat.php"><b>&raquo;</b>Server Status</a></li>
                                                <li><a href="index-radius-stat.php"><b>&raquo;</b>RADIUS Status </a></li>
                                                <li><a href="index-last-connect.php"><b>&raquo;</b>Last Connection Attempts</a></li>

				<h3>Logs</h3>
				
                                                <li><a href="index-radius-log.php"><b>&raquo;</b>radius log</a></li>
                                                <li><a href="index-system-log.php"><b>&raquo;</b>system log</a></li>
				
				<h3>Support</h3>
				
				<p class="news">
					daloRADIUS <br/>
					RADIUS Management 
					<a href="http://www.enginx.com" class="more">Read More &raquo;</a>
				</p>
				
			
				<h2>Search</h2>
				
				<input name="" type="text" value="Search" />
		
		</div>
		
		
