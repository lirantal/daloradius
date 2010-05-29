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
	include_once("include/management/autocomplete.php");
?>

<div id="sidebar">

	<h2>Billing</h2>
	
	<h3>Invoice Management</h3>
	<ul class="subnav">
	
		<li><a href="bill-invoice-list.php"><b>&raquo;</b><?php echo $l['button']['ListInvoices'] ?></a></li>
		<li><a href="bill-invoice-new.php"><b>&raquo;</b><?php echo $l['button']['NewInvoice'] ?></a></li>
		<li><a href="javascript:document.billinvoiceedit.submit();""><b>&raquo;</b><?php echo $l['button']['EditInvoice'] ?><a>
			<form name="billinvoiceedit" action="bill-invoice-edit.php" method="get" class="sidebar">
			<input name="invoice_id" type="text" id="invoiceIdEdit" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                onClick='javascript:__displayTooltip();'
                                tooltipText='<?php echo $l['Tooltip']['invoiceID']; ?> <br/>'
				value="<?php if (isset($edit_invoiceid)) echo $edit_invoiceid; ?>" tabindex=3>
			</form></li>
			
		<li><a href="bill-invoice-del.php"><b>&raquo;</b><?php echo $l['button']['RemoveInvoice'] ?></a></li>
		
	</ul>
	
	<br/><br/>
	<h2>Search</h2>
	
	<input name="" type="text" value="Search" tabindex=4 />

</div>

<?php
/*
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('planNameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteBillingPlans');
                      </script>";
        }

<script type="text/javascript">
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>
*/
?>