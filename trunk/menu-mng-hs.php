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
	
	<h3>Hotspots Management</h3>
	<ul class="subnav">
	
		<li><a href="mng-hs-list.php"><b>&raquo;</b><?php echo $l['button']['ListHotspots'] ?></a></li>
		<li><a href="mng-hs-new.php"><b>&raquo;</b><?php echo $l['button']['NewHotspot'] ?></a></li>
		<li><a href="javascript:document.mnghsedit.submit();""><b>&raquo;</b><?php echo $l['button']['EditHotspot'] ?><a>
			<form name="mnghsedit" action="mng-hs-edit.php" method="get" class="sidebar">
			<input name="name" type="text"  id="hotspotEdit" autocomplete="off"
                                onClick='javascript:__displayTooltip();'
                                tooltipText='<?php echo $l['Tooltip']['HotspotName']; ?> <br/>'
				value="<?php if (isset($edit_hotspotname)) echo $edit_hotspotname; ?>" tabindex=3>
			</form></li>
			
		<li><a href="mng-hs-del.php"><b>&raquo;</b><?php echo $l['button']['RemoveHotspot'] ?></a></li>
		
	</ul>
	
	<br/><br/>
	<h2>Search</h2>
	
	<input name="" type="text" value="Search" tabindex=4 />

</div>


<?php
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('hotspotEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteHotspots');
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

