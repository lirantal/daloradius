<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */
?> 
 
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

<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />


<body>

<?php
    include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">

<?php
	$m_active = "Billing";
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/billing-subnav.php");
?>	

<div id="sidebar">

	<h2>User Billing</h2>
	
	
	<h3>Invoice Report</h3>
	<ul class="subnav">
	
		<li>
			<form name="billinvoicereport" action="bill-invoice-report.php" method="get" class="sidebar">
			
				<h109><?php echo t('button','BetweenDates'); ?></h109> <br/>
				
				<input name="startdate" type="text" id="startdate" 
			                                tooltipText='<?php echo t('Tooltip','Date'); ?> <br/>'
					value="<?php if (isset($billinvoice_startdate)) echo $billinvoice_startdate;
									else echo date("Y-m-01"); ?>">
				<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
				<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
			
				<input name="enddate" type="text" id="enddate" 
			                                tooltipText='<?php echo t('Tooltip','Date'); ?> <br/>'
					value="<?php if (isset($billinvoice_enddate)) echo $billinvoice_enddate;
									else echo date("Y-m-t"); ?>">
				<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
				<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
			
				<br/>

				<?php
				        include_once('include/management/populate_selectbox.php');
				        populate_invoice_status_id("All Invoice Types", "invoice_status", "", "", "%");
				?>
					<br/>
					
					<br/>
				<input class="sidebutton" type="submit" name="submit" value="<?php echo t('button','GenerateReport') ?>" tabindex=3 />
				
			</form>
			</li>

			
		<li><a href="javascript:document.billinvoiceedit.submit();""><b>&raquo;</b><?php echo t('button','ShowInvoice') ?><a>
			<form name="billinvoiceedit" action="bill-invoice-show.php" method="get" class="sidebar">
			<input name="invoice_id" type="text" id="invoiceIdEdit" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                tooltipText='<?php echo t('Tooltip','invoiceID'); ?> <br/>'
				value="<?php if (isset($edit_invoiceid)) echo $edit_invoiceid; ?>" tabindex=3>
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

