
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
	
	<h3>Attributes Management</h3>
	<ul class="subnav">
	
		<li><a href="javascript:document.mngradattributeslist.submit();"><b>&raquo;</b><?php echo $l['button']['ListAttributesforVendor'] ?>
			</a>
                        <form name="mngradattributeslist" action="mng-rad-attributes-list.php" method="get" class="sidebar">
                        <?php
                                include 'include/management/populate_selectbox.php';
                                populate_vendors("Select Vendor","vendor","generic");
                        ?>
			</form>
		</li>

		<li><a href="mng-rad-attributes-new.php" tabindex=2><b>&raquo;</b><?php echo $l['button']['NewVendorAttribute'] ?></a></li>
		<li><a href="javascript:document.mngradattributesedit.submit();" tabindex=3 ><b>&raquo;</b><?php echo $l['button']['EditVendorAttribute'] ?></a>
			<form name="mngradattributesedit" action="mng-rad-attributes-edit.php" method="get" class="sidebar">
			<input name="vendor" type="text" value="[vendor]" tabindex=4>
			<input name="attribute" type="text" value="[attribute]" tabindex=5>
			</form></li>
		<li><a href="javascript:document.mngradattributessearch.submit();" tabindex=6 ><b>&raquo;</b><?php echo $l['button']['SearchVendorAttribute'] ?></a>
			<form name="mngradattributessearch" action="mng-rad-attributes-search.php" method="get" class="sidebar">
			<input name="attribute" type="text" value="[attribute]" tabindex=7>
			</form></li>
		<li><a href="mng-rad-attributes-del.php" tabindex=8><b>&raquo;</b><?php echo $l['button']['RemoveVendorAttribute'] ?></a></li>
		
	</ul>

</div>
