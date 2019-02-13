<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
</head>

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
	$m_active = "Reports";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/reports-subnav.php");
	include_once("include/management/autocomplete.php");
?>


<div id="sidebar">

	<h2>Batch Users</h2>
		<ul class="subnav">

		<h3>List</h3>

			<li>
				<a href="rep-batch-list.php"><b>&raquo;</b><?php echo t('button','BatchHistory') ?></a>

			</li>
			
			
			
			<li>
				<a href="javascript:document.batch_name_details.submit();"><b>&raquo;</b><?php echo t('button','BatchDetails') ?></a>
				<form name="batch_name_details" action="rep-batch-details.php" method="get" class="sidebar">
				<input name="batch_name" type="text" id="batchNameDetails" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
									tooltipText='<?php echo t('Tooltip','BatchName'); ?>'
					value="<?php if (isset($batch_name_details)) echo $batch_name_details; ?>">
				</form>
			</li>

		</ul>

	
	<br/><br/>
	
	
	

</div>

<?php

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('batchNameDetails','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteBatchNames');
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
