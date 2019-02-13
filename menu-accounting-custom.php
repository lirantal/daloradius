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
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />

</head>
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>

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
				
				<h3>Custom Query</h3>
				<ul class="subnav">

	<form name="acctcustomquery" action="acct-custom-query.php" method="get" class="sidebar">

	<input class="sidebutton" type="submit" name="submit" value="<?php echo t('button','ProcessQuery') ?>" tabindex=3 />
	<br/><br/>	

	<h109><?php echo t('button','BetweenDates'); ?></h109> <br/>
	

                                                        <input name="startdate" type="text" id="startdate" 
                                tooltipText='<?php echo t('Tooltip','Date'); ?> <br/>'
value="<?php if (isset($accounting_custom_startdate)) echo $accounting_custom_startdate;
						else echo date("Y-m-01"); ?>">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

                                                        <input name="enddate" type="text" id="enddate" 
                                tooltipText='<?php echo t('Tooltip','Date'); ?> <br/>'
value="<?php if (isset($accounting_custom_enddate)) echo $accounting_custom_enddate;
						else echo date("Y-m-t"); ?>">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>


		<br/><br/>
		<h109><?php echo t('button','Where'); ?></h109> <br/>
			<center>
			<select name="fields" size="1" class="generic" >
				<option value="RadAcctId"> RadAcctId </option>
				<option value="AcctSessionId"> AcctSessionId </option>
				<option value="AcctUniqueId"> AcctUniqueId </option>
				<option value="UserName"> UserName </option>
				<option value="Realm"> Realm </option>
				<option value="NASIPAddress"> NASIPAddress </option>
				<option value="NASPortId"> NASPortId </option>
				<option value="NASPortType"> NASPortType</option>
				<option value="AcctStartTime"> AcctStartTime </option>
				<option value="AcctStopTime"> AcctStopTime </option>
				<option value="AcctSessionTime"> AcctSessionTime </option>
				<option value="AcctAuthentic"> AcctAuthentic </option>
				<option value="ConnectInfo_start"> ConnectInfo_start </option>
				<option value="ConnectInfo_stop"> ConnectInfo_stop </option>
				<option value="AcctInputOctets"> AcctInputOctets </option>
				<option value="AcctOutputOctets"> AcctOutputOctets </option>
				<option value="CalledStationId"> CalledStationId </option>
				<option value="CallingStationId"> CallingStationId </option>
				<option value="AcctTerminateCause"> AcctTerminateCause </option>
				<option value="ServiceType"> ServiceType </option>
				<option value="FramedProtocol"> FramedProtocol </option>
				<option value="FramedIPAddress"> FramedIPAddress </option>
				<option value="AcctStartDelay"> AcctStartDelay </option>
				<option value="AcctStopDelay"> AcctStopDelay </option>
			</select>

			<select name="operator" size="1" class="generic" >
				<option value="="> Equals </option>
				<option value="LIKE"> Contains </option>
			</select>
			</center>
		<input type="text" name="where_field" 
                                tooltipText='<?php echo t('Tooltip','Filter'); ?> <br/>'
			value="<?php if (isset($accounting_custom_value)) echo $accounting_custom_value; ?>" />

		<br/><br/>
		<h109><?php echo t('button','AccountingFieldsinQuery'); ?></h109><br/>
		<input type="checkbox" name="sqlfields[]" value="RadAcctId" /> <h109> RadAcctId </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctSessionId" /> <h109> AcctSessionId </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctUniqueId" /> <h109> AcctUniqueId</h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="UserName" checked /> <h109> UserName </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="Realm" checked /> <h109> Realm</h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="NASIPAddress" checked /> <h109> NASIPAddress </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="NASPortId" /> <h109> NASPortId </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="NASPortType" /> <h109> NASPortType </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctStartTime" checked /> <h109> AcctStartTime </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctStopTime" checked /> <h109> AcctStopTime </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctSessionTime" checked /> <h109> AcctSessionTime </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctAuthentic" /> <h109> AcctAuthentic </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="ConnectInfo_start" /> <h109> ConnectInfo_start </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="ConnectInfo_stop" /> <h109> ConnectInfo_stop </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctInputOctets" checked /> <h109> AcctInputOctets </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctOutputOctets" checked /> <h109> AcctOutputOctets </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="CalledStationId" checked /> <h109> CalledStationId </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="CallingStationId" checked /> <h109> CallingStationId </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctTerminateCause" checked /> <h109> AcctTerminateCause </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="ServiceType" /> <h109> ServiceType </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="FramedProtocol" /> <h109> FramedProtocol </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="FramedIPAddress" checked /> <h109> FramedIPAddress </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctStartDelay" /> <h109> AcctStartDelay </h109> <br/>
		<input type="checkbox" name="sqlfields[]" value="AcctStopDelay" /> <h109> AcctStopDelay </h109> <br/>
		Select:
		<a class="table" href="javascript:SetChecked(1,'sqlfields[]','acctcustomquery')">All</a>
		<a class="table" href="javascript:SetChecked(0,'sqlfields[]','acctcustomquery')">None</a>
		<br/><br/>
		<h109><?php echo t('button','OrderBy') ?><h109> <br/>
			<center>
			<select name="orderBy" size="1">
				<option value="RadAcctId"> RadAcctId </option>
				<option value="AcctSessionId"> AcctSessionId </option>
				<option value="AcctUniqueId"> AcctUniqueId </option>
				<option value="UserName"> UserName </option>
				<option value="Realm"> Realm </option>
				<option value="NASIPAddress"> NASIPAddress </option>
				<option value="NASPortId"> NASPortId </option>
				<option value="NASPortType"> NASPortType</option>
				<option value="AcctStartTime"> AcctStartTime </option>
				<option value="AcctStopTime"> AcctStopTime </option>
				<option value="AcctSessionTime"> AcctSessionTime </option>
				<option value="AcctAuthentic"> AcctAuthentic </option>
				<option value="ConnectInfo_start"> ConnectInfo_start </option>
				<option value="ConnectInfo_stop"> ConnectInfo_stop </option>
				<option value="AcctInputOctets"> AcctInputOctets </option>
				<option value="AcctOutputOctets"> AcctOutputOctets </option>
				<option value="CalledStationId"> CalledStationId </option>
				<option value="CallingStationId"> CallingStationId </option>
				<option value="AcctTerminateCause"> AcctTerminateCause </option>
				<option value="ServiceType"> ServiceType </option>
				<option value="FramedProtocol"> FramedProtocol </option>
				<option value="FramedIPAddress"> FramedIPAddress </option>
				<option value="AcctStartDelay"> AcctStartDelay </option>
				<option value="AcctStopDelay"> AcctStopDelay </option>
			</select>

			<select name="orderType" size="1">
				<option value="ASC"> Ascending </option>
				<option value="DESC"> Descending </option>
			</select>
			</center>


	<br/>
	<input class="sidebutton" type="submit" name="submit" value="<?php echo t('button','ProcessQuery') ?>" tabindex=3 />
	</form></li>
				


				</ul>
				
				<br/><br/>
				
				
				
		
		</div>

<script type="text/javascript">
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>
