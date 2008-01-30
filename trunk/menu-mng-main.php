
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
	
	<h3>Users Management</h3>
	<ul class="subnav">
	
		<li><a href="mng-list-all.php"><b>&raquo;</b>List Users</a></li>
		<li><a href="mng-new.php"><b>&raquo;</b>New User</a></li>
		<li><a href="mng-new-quick.php"><b>&raquo;</b>New User - Quick add </a></li>
		<li><a href="mng-batch.php"><b>&raquo;</b>Batch-Add Users <a></li>
		<li><a href="javascript:document.mngedit.submit();""><b>&raquo;</b>Edit User<a>
			<form name="mngedit" action="mng-edit.php" method="get" class="sidebar">
			<input name="username" type="text"
				value="<?php if (isset($edit_username)) echo $edit_username; ?>" tabindex=1>
			</form></li>
		<li><a href="javascript:document.mngsearch.submit();""><b>&raquo;</b>Search Users<a>
			<form name="mngsearch" action="mng-search.php" method="get" class="sidebar">
			<input name="username" type="text" 
				value="<?php if (isset($search_username)) echo $search_username; ?>" tabindex=2>
			</form></li>
			
		<li><a href="mng-del.php"><b>&raquo;</b>Remove User</a></li>
		
	</ul>

	<h3>Hotspots Management</h3>
	<ul class="subnav">
	
		<li><a href="mng-hs-list.php"><b>&raquo;</b>List Hotspots</a></li>
		<li><a href="mng-hs-new.php"><b>&raquo;</b>New Hotspot</a></li>
		<li><a href="javascript:document.mnghsedit.submit();""><b>&raquo;</b>Edit Hotspot<a>
			<form name="mnghsedit" action="mng-hs-edit.php" method="get" class="sidebar">
			<input name="name" type="text" 
				value="<?php if (isset($edit_hotspotname)) echo $edit_hotspotname; ?>" tabindex=3>
			</form></li>
			
		<li><a href="mng-hs-del.php"><b>&raquo;</b>Remove Hotspot</a></li>
		
	</ul>
	
	<br/><br/>
	<h2>Search</h2>
	
	<input name="" type="text" value="Search" tabindex=4 />

</div>

		
<?php

        if ((isset($actionStatus)) && ($actionStatus == "success")) {
                echo <<<EOF
                        <div id="contentnorightbar">
                        <h9 id="Intro"> Success </h9>
                        <br/><br/>
                        <font color='#0000FF'>
EOF;
        echo $actionMsg;

        echo "</font></div>";

        }


        if ((isset($actionStatus)) && ($actionStatus == "failure")) {
                echo <<<EOF
                        <div id="contentnorightbar">
                        <h8 id="Intro"> Failure </h8>
                        <br/><br/>
                        <font color='#FF0000'>
EOF;
        echo $actionMsg;

        echo "</font></div>";

        }


?>
