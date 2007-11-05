
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->

</head>
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<body>

<?php
    include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">

<?php
	$m_active = "Accounting";
        include_once ("include/menu/menu-items.php");
	include_once ("include/menu/accounting-subnav.php");
?>	
		<div id="sidebar">
		
				<h2>Accounting</h2>
				
				<h3>Users Accounting</h3>
				<ul class="subnav">
				
						<li><a href="javascript:document.acctusername.submit();"><b>&raquo;</b>User Accounting<a>
							<form name="acctusername" action="acct-username.php" method="post" class="sidebar">
							<input name="username" type="text" value="username">
							</form></li>

						<li><a href="javascript:document.acctipaddress.submit();"><b>&raquo;</b>IP Accounting<a>
							<form name="acctipaddress" action="acct-ipaddress.php" method="post" class="sidebar">
							<input name="ipaddress" type="text" value="0.0.0.0">
							</form></li>

						<li><a href="javascript:document.acctnasipaddress.submit();"><b>&raquo;</b>NAS IP Accounting<a>
							<form name="acctnasipaddress" action="acct-nasipaddress.php" method="post" class="sidebar">
							<input name="nasipaddress" type="text" value="0.0.0.0">
							</form></li>

						<li><a href="javascript:document.acctdate.submit();"><b>&raquo;</b>Date Accounting<a>
							<form name="acctdate" action="acct-date.php" method="post" class="sidebar">
							<input name="username" type="text" value="username">
							<input name="startdate" type="text" id="startdate" value="2006-01-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

							<input name="enddate" type="text" id="enddate" value="2006-12-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

							</form></li>






						<li><a href="acct-all.php"><b>&raquo;</b>Display All Accounting Records</a></li>
						<li><a href="acct-active.php"><b>&raquo;</b>Display Active Records</a></li>


				</ul>
		

				<br/><br/>
				<h2>Search</h2>
				<input name="" type="text" value="Search" />
				
		
		</div>

		
