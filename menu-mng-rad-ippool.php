
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
	
		<li><a href="mng-rad-ippool-list.php" tabindex=1><b>&raquo;</b><?php echo $l['button']['ListIPPools'] ?></a></li>
		<li><a href="mng-rad-ippool-new.php" tabindex=2><b>&raquo;</b><?php echo $l['button']['NewIPPool'] ?></a></li>
		<li><a href="javascript:document.mngradippooledit.submit();" tabindex=3 ><b>&raquo;</b><?php echo $l['button']['EditIPPool'] ?></a>
			<form name="mngradippooledit" action="mng-rad-ippool-edit.php" method="get" class="sidebar">
			<input name="poolname" type="text" 
				value="<?php (isset($poolname)) ? print $poolname : print '[poolname]' ?>" tabindex=4>
			<input name="ipaddressold" type="text" 
				value="<?php (isset($ipaddressold)) ? print $ipaddressold : print '[ipaddress]' ?>" tabindex=4>
			</form></li>
		<li><a href="mng-rad-ippool-del.php" tabindex=5><b>&raquo;</b><?php echo $l['button']['RemoveIPPool'] ?></a></li>
		
	</ul>

</div>
