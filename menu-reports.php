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
		
				<h2>Reports</h2>
				
				<h3>Users Reports</h3>
				<ul class="subnav">
				
						<li><a href="javascript:document.reponline.submit();"><b>&raquo;</b>
							<img src='images/icons/reportsOnlineUsers.gif' border='0'>
							<?php echo $l['button']['OnlineUsers'] ?></a>
							
							<form name="reponline" action="rep-online.php" method="get" class="sidebar">
								<input name="usernameOnline" type="text" id="usernameOnline" autocomplete="off"
								value="<?php if (isset($usernameOnline)) echo $usernameOnline; ?>" tabindex=1>
							</form>
							</li>							

                                                <li><a href="javascript:document.replastconnect.submit();"><b>&raquo;</b>
							<img src='images/icons/reportsLastConnection.png' border='0'>
							<?php echo $l['button']['LastConnectionAttempts'] ?></a>

							<form name="replastconnect" action="rep-lastconnect.php" method="get" class="sidebar">
								<input name="usernameLastConnect" type="text" id="usernameLastConnect" autocomplete="off"
								value="<?php if (isset($usernameLastConnect)) echo $usernameLastConnect; ?>" tabindex=1>
							</form>
							</li>


						<li><a href="javascript:document.topusers.submit();"><b>&raquo;</b>
							<img src='images/icons/reportsTopUsers.png' border='0'>
							<?php echo $l['button']['TopUser'] ?></a>
							<form name="topusers" action="rep-topusers.php" method="get" class="sidebar">
							<select class="generic" name="limit" type="text">
								<option value="5"> 5 </option>
								<option value="10"> 10 </option>
								<option value="20"> 20 </option>
								<option value="50"> 50 </option>
								<option value="100"> 100 </option>
								<option value="500"> 500 </option>
								<option value="1000"> 1000 </option>
							</select>
							<select class="generic" name="orderBy" type="text">
								<option value="Bandwidth"> bandwidth </option>
								<option value="Time"> time </option>
							</select>
							</form></li>
                                                <li><a href="rep-history.php"><b>&raquo;</b>
							<img src='images/icons/reportsHistory.png' border='0'>
							<?php echo $l['button']['History'] ?></a></li>
				</ul>
		
				
				<br/><br/>
				<h2>Search</h2>
				<input name="" type="text" value="Search" />
				
		
		</div>
		
		
		
