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

	<h2>Billing</h2>
	
	<h3>Plans Management</h3>
	<ul class="subnav">
	
		<li><a href="bill-plans-list.php"><b>&raquo;</b><?php echo $l['button']['ListPlans'] ?></a></li>
		<li><a href="bill-plans-new.php"><b>&raquo;</b><?php echo $l['button']['NewPlan'] ?></a></li>
		<li><a href="javascript:document.billplansedit.submit();""><b>&raquo;</b><?php echo $l['button']['EditPlan'] ?><a>
			<form name="billplansedit" action="bill-plans-edit.php" method="get" class="sidebar">
			<input name="planName" type="text" autocomplete="off"
				value="<?php if (isset($edit_planname)) echo $edit_planname; ?>" tabindex=3>
			</form></li>
			
		<li><a href="bill-plans-del.php"><b>&raquo;</b><?php echo $l['button']['RemovePlan'] ?></a></li>
		
	</ul>
	
	<br/><br/>
	<h2>Search</h2>
	
	<input name="" type="text" value="Search" tabindex=4 />

</div>
