<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>

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
	include_once ("include/management/autocomplete.php");
	include_once ("include/management/populate_selectbox.php");
?>

<div id="sidebar">

	<h2>Billing</h2>
	
	<h3>Invoice Management</h3>
	<ul class="subnav">
	
		<li><a href="javascript:document.invoicelist.submit();"><b>&raquo;</b><?php echo $l['button']['ListInvoices'] ?></a>
		
			<form name="invoicelist" action="bill-invoice-list.php" method="get" class="sidebar">
            	<input name="username" type="text" id="invoiceUsername" 
                autocomplete='off'
                onClick='javascript:__displayTooltip();'
				tooltipText='<?php echo $l['Tooltip']['Username']; ?> <br/>'
                value="<?php if (isset($edit_invoiceUsername)) echo $edit_invoiceUsername; ?>" tabindex=3>
                
			<?php
				if (!isset($edit_invoice_status_id))
					$edit_invoice_status_id = '';
				populate_invoice_status_id("Select Invoice Status","invoice_status_id","form", '', $edit_invoice_status_id);
			?>
                
			</form>
		
		</li>
		<li><a href="javascript:document.invoicenew.submit();"><b>&raquo;</b><?php echo $l['button']['NewInvoice'] ?></a>
		<form name="invoicenew" action="bill-invoice-new.php" method="get" class="sidebar">
            	<input name="username" type="text" id="invoiceUsernameNew" 
                autocomplete='off'
                onClick='javascript:__displayTooltip();'
				tooltipText='<?php echo $l['Tooltip']['Username']; ?> <br/>'
                value="<?php if (isset($edit_invoiceUsername)) echo $edit_invoiceUsername; ?>" tabindex=3>
		</form>
		</li>
		<li><a href="javascript:document.billinvoiceedit.submit();""><b>&raquo;</b><?php echo $l['button']['EditInvoice'] ?><a>
			<form name="billinvoiceedit" action="bill-invoice-edit.php" method="get" class="sidebar">
			<input name="invoice_id" type="text" id="invoiceIdEdit" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                onClick='javascript:__displayTooltip();'
                                tooltipText='<?php echo $l['Tooltip']['invoiceID']; ?> <br/>'
				value="<?php if (isset($edit_invoiceid)) echo $edit_invoiceid; ?>" tabindex=3>
			</form></li>
			
		<li><a href="bill-invoice-del.php"><b>&raquo;</b><?php echo $l['button']['RemoveInvoice'] ?></a></li>
		
	</ul>
	
	
	<br/>
	<h3>Invoice Report</h3>
	<ul class="subnav">
	
		
			<form name="billinvoicereport" action="bill-invoice-report.php" method="get" class="sidebar">
			
				<h109><?php echo $l['button']['BetweenDates']; ?></h109> <br/>
				
				<input name="startdate" type="text" id="startdate" 
			                                onClick='javascript:__displayTooltip();'
			                                tooltipText='<?php echo $l['Tooltip']['Date']; ?> <br/>'
					value="<?php if (isset($billinvoice_startdate)) echo $billinvoice_startdate;
									else echo date("Y-m-01"); ?>">
				<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, <?= date('Y', time());?>, 'Y-m-d', false);">
				<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
			
				<input name="enddate" type="text" id="enddate" 
			                                onClick='javascript:__displayTooltip();'
			                                tooltipText='<?php echo $l['Tooltip']['Date']; ?> <br/>'
					value="<?php if (isset($billinvoice_enddate)) echo $billinvoice_enddate;
									else echo date("Y-m-t"); ?>">
				<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, <?= date('Y', time());?>, 'Y-m-d', false);">
				<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
			
				<br/>

				<?php
				        include_once('include/management/populate_selectbox.php');
				        populate_invoice_status_id("All Invoice Types", "invoice_status", "form", "", "%");
				?>
			
				
				<input name="username" type="text" id="usernameEdit" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                onClick='javascript:__displayTooltip();'
                                tooltipText='<?php echo $l['Tooltip']['Username']; ?> <br/>'
                                value="<?php if (isset($billinvoice_username) && $billinvoice_username != '%') echo $billinvoice_username; ?>" tabindex=1>
				
				
					<br/>
				<input class="sidebutton" type="submit" name="submit" value="<?php echo $l['button']['GenerateReport'] ?>" tabindex=3 />
				
			</form></li>
					
	</ul>
	
	
	<br/><br/>
	<h2>Search</h2>
	
	<input name="" type="text" value="Search" tabindex=4 />

</div>

<?php
        include_once("include/management/autocomplete.php");
      
        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                        /** Making usernameEdit interactive **/
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('usernameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
                      autoComEdit.add('invoiceUsername','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
                      autoComEdit.add('invoiceUsernameNew','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
                      </script>";
        }


?>

<script type="text/javascript">
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>
