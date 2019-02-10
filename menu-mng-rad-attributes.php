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
	include_once("include/management/autocomplete.php");
?>
		
<div id="sidebar">

	<h2>Management</h2>
	
	<h3>Attributes Management</h3>
	<ul class="subnav">
	
		<li><a href="javascript:document.mngradattributeslist.submit();"><b>&raquo;</b><?php echo t('button','ListAttributesforVendor') ?>
			</a>
                        <form name="mngradattributeslist" action="mng-rad-attributes-list.php" method="get" class="sidebar">
                        <?php
                                include 'include/management/populate_selectbox.php';
                                populate_vendors("Select Vendor","vendor","generic");
                        ?>
			</form>
		</li>

		<li><a href="mng-rad-attributes-new.php" tabindex=2><b>&raquo;</b><?php echo t('button','NewVendorAttribute') ?></a></li>
		<li><a href="javascript:document.mngradattributesedit.submit();" tabindex=3 ><b>&raquo;</b><?php echo t('button','EditVendorAttribute') ?></a>
			<form name="mngradattributesedit" action="mng-rad-attributes-edit.php" method="get" class="sidebar">
			<input name="vendor" type="text" id="vendornameEdit" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                tooltipText='<?php echo t('Tooltip','VendorName'); ?> <br/>'
				value="<?php if (isset($vendor)) echo $vendor ?>" tabindex=4>
			<input name="attribute" type="text" id="attributenameEdit" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                tooltipText='<?php echo t('Tooltip','AttributeName'); ?> <br/>'
				value="<?php if (isset($attribute)) echo $attribute  ?>" tabindex=5>
			</form></li>
		<li><a href="javascript:document.mngradattributessearch.submit();" tabindex=6 ><b>&raquo;</b><?php echo t('button','SearchVendorAttribute') ?></a>
			<form name="mngradattributessearch" action="mng-rad-attributes-search.php" method="get" class="sidebar">
			<input name="attribute" type="text" id="attributenameSearch" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                tooltipText='<?php echo t('Tooltip','AttributeName'); ?> <br/>'
				value="<?php if (isset($attribute)) echo $attribute ?>" tabindex=7>
			</form></li>
		<li><a href="mng-rad-attributes-del.php" tabindex=8><b>&raquo;</b><?php echo t('button','RemoveVendorAttribute') ?></a></li>
		<li><a href="mng-rad-attributes-import.php" tabindex=8><b>&raquo;</b><?php echo t('button','ImportVendorDictionary') ?></a></li>
		
	</ul>

</div>

<?php
        include_once("include/management/autocomplete.php");

        if ($autoComplete) {
                echo "<script type=\"text/javascript\">
                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('attributenameSearch','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteAttributes');

                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('attributenameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteAttributes');

                      autoComEdit = new DHTMLSuite.autoComplete();
                      autoComEdit.add('vendornameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteVendorName');
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

