
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
	include_once("include/management/autocomplete.php");
?>	

<div id="sidebar">

	<h2>Plan Accounting</h2>
	
	<h3>Accounting</h3>
	<ul class="subnav">
	
		<li><a href="javascript:document.acctdate.submit();"><b>&raquo;</b><?php echo t('button','PlanUsage') ?></a>
			<form name="acctdate" action="acct-plans-usage.php" method="get" class="sidebar">
			<input name="username" type="text" id="usernamePlan" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                tooltipText='<?php echo t('Tooltip','Username'); ?>'
				value="<?php if (isset($accounting_plan_username)) echo $accounting_plan_username;  ?>">

			<input name="startdate" type="text" id="startdate" 
                                tooltipText='<?php echo t('Tooltip','Date'); ?>'
				value="<?php if (isset($accounting_plan_startdate)) echo $accounting_plan_startdate;
			else echo date("Y-m-01"); ?>">
			
			<img src="library/js_date/calendar.gif" 
				onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
			<div id="chooserSpan" class="dateChooser select-free" 
				style="display: none; visibility: hidden; 	width: 160px;"></div>

			<input name="enddate" type="text" id="enddate" 
                                tooltipText='<?php echo t('Tooltip','Date'); ?>'
				value="<?php if (isset($accounting_plan_enddate)){ echo $accounting_plan_enddate;}
				else { echo date("Y-m-t");} ?>">
			<img src="library/js_date/calendar.gif" 
				onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
			<div id="chooserSpan" class="dateChooser select-free" 
				style="display: none; visibility: hidden; width: 160px;"></div>

			<br/><br/>
			<?php   
				include 'include/management/populate_selectbox.php';
				populate_plans("Select Plan","planname","generic");
			?>
			</li>
			</form>

	</ul>

	<br/><br/>
	
	

</div>

<?php
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('usernamePlan','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
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
