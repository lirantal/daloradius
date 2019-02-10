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
    $m_active = "Management";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/management-subnav.php");
?>
		
<div id="sidebar">

	<h2>Management</h2>
	
	<h3>IP Pools</h3>
	<ul class="subnav">
	
		<li><a href="mng-rad-ippool-list.php" tabindex=1><b>&raquo;</b><?php echo t('button','ListIPPools') ?></a></li>
		<li><a href="mng-rad-ippool-new.php" tabindex=2><b>&raquo;</b><?php echo t('button','NewIPPool') ?></a></li>
		<li><a href="javascript:document.mngradippooledit.submit();" tabindex=3 ><b>&raquo;</b><?php echo t('button','EditIPPool') ?></a>
			<form name="mngradippooledit" action="mng-rad-ippool-edit.php" method="get" class="sidebar">
			<input name="poolname" type="text" 
                                tooltipText='<?php echo t('Tooltip','PoolName'); ?> <br/>'
				value="<?php if (isset($poolname)) echo $poolname ?>" tabindex=4>
			<input name="ipaddressold" type="text" 
                                tooltipText='<?php echo t('Tooltip','IPAddress'); ?> <br/>'
				value="<?php if (isset($ipaddressold)) echo $ipaddressold  ?>" tabindex=4>
			</form></li>
		<li><a href="mng-rad-ippool-del.php" tabindex=5><b>&raquo;</b><?php echo t('button','RemoveIPPool') ?></a></li>
		
	</ul>

</div>

<script type="text/javascript">
        var tooltipObj = new DHTMLgoodies_formTooltip();
        tooltipObj.setTooltipPosition('right');
        tooltipObj.setPageBgColor('#EEEEEE');
        tooltipObj.setTooltipCornerSize(15);
        tooltipObj.initFormFieldTooltip();
</script>

