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
	
	<h3>Point of Sales Management</h3>
	<ul class="subnav">
	
		<li>
		<a href="javascript:document.billposlist.submit();"><b>&raquo;</b><?php echo $l['button']['ListUsers'] ?></a>
		<form name="billposlist" action="bill-pos-list.php" method="get" class="sidebar">
		<br/>
			<?php   
				include 'include/management/populate_selectbox.php';
				populate_plans("Select Plan","planname","generic");
			?>
		</form>
		</li>
		<li><a href="bill-pos-new.php"><b>&raquo;</b><?php echo $l['button']['NewUser'] ?></a></li>
		<li><a href="javascript:document.billposedit.submit();"><b>&raquo;</b><?php echo $l['button']['EditUser'] ?><a>
			<form name="billposedit" action="bill-pos-edit.php" method="get" class="sidebar">
			<input name="username" type="text" id="usernameEdit" <?php if ($autoComplete) echo "autocomplete='off'"; ?>
                                onClick='javascript:__displayTooltip();'
                                tooltipText='<?php echo $l['Tooltip']['Username']; ?> <br/>'
                                value="<?php if (isset($edit_username)) echo $edit_username; ?>" tabindex=1>
			</form></li>
			
		<li><a href="bill-pos-del.php"><b>&raquo;</b><?php echo $l['button']['RemoveUsers'] ?></a></li>
		
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
