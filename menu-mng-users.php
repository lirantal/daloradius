<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />

<body>
<?php
    include_once("lang/main.php");
?>
<div id="wrapper">
<div id="innerwrapper">

<?php
    $m_active = "Management";
    include_once("include/menu/menu-items.php");
	include_once("include/menu/management-subnav.php");
?>

<div id="sidebar">

	<h2>Management</h2>
	
	<h3>Users Management</h3>
	<ul class="subnav">
	
		<li><a href="mng-list-all.php"><b>&raquo;</b>
			<img src='images/icons/userList.gif' border='0'>
			<?php echo t('button','ListUsers') ?></a>
		</li>
		<li><a href="mng-new.php"><b>&raquo;</b>
			<img src='images/icons/userNew.gif' border='0'>
			<?php echo t('button','NewUser') ?></a>
		</li>
		<li><a href="mng-new-quick.php"><b>&raquo;</b>
			<img src='images/icons/userNew.gif' border='0'>
			<?php echo t('button','NewUserQuick') ?></a>
		</li>
		<li><a href="javascript:document.mngedit.submit();""><b>&raquo;</b>
			<img src='images/icons/userEdit.gif' border='0'>
			<?php echo t('button','EditUser') ?></a>
			<form name="mngedit" action="mng-edit.php" method="get" class="sidebar">
			<input name="username" type="text" id="usernameEdit" autocomplete="off"
				tooltipText='<?php echo t('Tooltip','Username'); ?> <br/>'
				value="<?php if (isset($edit_username)) echo $edit_username; ?>" tabindex=1>
			</form></li>
		<li><a href="javascript:document.mngsearch.submit();""><b>&raquo;</b>
			<img src='images/icons/userSearch.gif' border='0'>
			<?php echo t('button','SearchUsers') ?></a>
			<form name="mngsearch" action="mng-search.php" method="get" class="sidebar">
			<input name="username" type="text" id="usernameSearch" autocomplete="off"
				tooltipText='<?php echo t('Tooltip','Username'); ?> <br/> <?php echo t('Tooltip','UsernameWildcard'); ?>'
				value="<?php if (isset($search_username)) echo $search_username; ?>" tabindex=2>
			</form></li>
		
		<li><a href="mng-del.php"><b>&raquo;</b>
			<img src='images/icons/userRemove.gif' border='0'>
			<?php echo t('button','RemoveUsers') ?>
			</a>
		</li>
		
	</ul>

	<br/>
	<h3>Extended Capabilities</h3>
	<ul class="subnav">
	
		<li><a href="mng-import-users.php"><b>&raquo;</b>
			<img src='images/icons/userNew.gif' border='0'>
			<?php echo t('button','ImportUsers') ?></a>
		</li>
		
	</ul>
		
	<br/><br/>
	
	
	

</div>

<?php 
	include_once("include/management/autocomplete.php");

	if ($autoComplete) {
		echo "<script type=\"text/javascript\">
			/** Making usernameEdit interactive **/
	              autoComEdit = new DHTMLSuite.autoComplete();
	              autoComEdit.add('usernameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
			/** Making usernameSearch interactive **/
	              autoComEdit.add('usernameSearch','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
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

