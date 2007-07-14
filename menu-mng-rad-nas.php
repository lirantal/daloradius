
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
				
				<h3>NAS Management</h3>
				<ul class="subnav">
				
						<li><a href="mng-rad-nas-list.php"><b>&raquo;</b>List NAS</a></li>
						<li><a href="mng-rad-nas-new.php"><b>&raquo;</b>New NAS</a></li>
						<li><a href="javascript:document.mngradnasedit.submit();""><b>&raquo;</b>Edit NAS<a>
							<form name="mngradnasedit" action="mng-rad-nas-edit.php" method="get" class="sidebar">
							<input name="nashost" type="text">
							</form></li>


						<li><a href="mng-rad-nas-del.php"><b>&raquo;</b>Remove NAS</a></li>	
				</ul>
		
	
		</div>
		
		
