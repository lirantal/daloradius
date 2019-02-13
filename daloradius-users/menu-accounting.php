
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->

</head>
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<script type="text/javascript" src="library/javascript/ajax.js"></script>
<script type="text/javascript" src="library/javascript/ajaxGeneric.js"></script>
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
	
		<li><a href="javascript:document.acctdate.submit();"><b>&raquo;</b><?php echo t('button','DateAccounting') ?><a>
			<form name="acctdate" action="acct-date.php" method="get" class="sidebar">
			<input name="startdate" type="text" id="startdate" 
				value="<?php if (isset($accounting_date_startdate)) echo $accounting_date_startdate;
			else echo date("Y-m-01"); ?>">
			
			<img src="library/js_date/calendar.gif" 
				onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
			<div id="chooserSpan" class="dateChooser select-free" 
				style="display: none; visibility: hidden; 	width: 160px;"></div>

			<input name="enddate" type="text" id="enddate" 
				value="<?php if (isset($accounting_date_enddate)) echo $accounting_date_enddate;
				else echo date("Y-m-t"); ?>">
				
			<img src="library/js_date/calendar.gif" 
				onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, 2010, 'Y-m-d', false);">
			<div id="chooserSpan" class="dateChooser select-free" 
				style="display: none; visibility: hidden; width: 160px;"></div>

			</form></li>

	</ul>

	<br/><br/>
	
	

</div>


