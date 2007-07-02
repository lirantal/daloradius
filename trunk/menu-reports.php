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
	$m_active = "Reports";
	include_once ("include/menu/header.php");
?>      

		<div id="sidebar">
		
				<h2>Reports</h2>
				
				<h3>Users Reports</h3>
				<ul class="subnav">
				
						<li><a href="javascript:document.searchusername.submit();"><b>&raquo;</b>Search User<a>
							<form name="searchusername" action="rep-username.php" method="post" class="sidebar">
							<input name="username" type="text">
							</form></li>

						<li><a href="javascript:document.topusers.submit();"><b>&raquo;</b>Top User<a>
							<form name="topusers" action="rep-topusers.php" method="post" class="sidebar">
							<select name="limit" type="text">
								<option value="5"> 5
								<option value="10"> 10
								<option value="20"> 20
								<option value="50"> 50
								<option value="100"> 100
								<option value="500"> 500
								<option value="1000"> 1000
							</select>
							<select name="order" type="text">
								<option value="AcctInputOctets"> bandwidth
								<option value="AcctSessionTime"> time
							</select>
							</form></li>
						<li><a href="rep-all.php"><b>&raquo;</b>List All Users</a></li>
	
				</ul>
		

				<h3>Hotspots Reports</h3>
				<ul class="subnav">
				
						<li><a href="rep-hs-all.php"><b>&raquo;</b>List all Hotspots</a></li>
				</ul>
				
				<br/><br/>
				<h2>Search</h2>
				<input name="" type="text" value="Search" />
				
		
		</div>
		
		
		
