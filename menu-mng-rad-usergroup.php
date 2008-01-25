
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

	<h3>User-Group Management</h3>
	<ul class="subnav">

		<li><a href="mng-rad-usergroup-list.php"><b>&raquo;</b>List User-Group Mappings</a></li>
		<li><a href="javascript:document.mngradusrgrplist.submit();""><b>&raquo;</b>List User's Group Mapping<a>
			<form name="mngradusrgrplist" action="mng-rad-usergroup-list-user.php" method="get" 
				class="sidebar">
			<input name="username" type="text">
			</form></li>

		<li><a href="mng-rad-usergroup-new.php"><b>&raquo;</b>New User-Group Mapping</a></li>
		<li><a href="javascript:document.mngradusrgrpedit.submit();""><b>&raquo;</b>Edit User-Group Mapping<a>
			<form name="mngradusrgrpedit" action="mng-rad-usergroup-edit.php" method="get" class="sidebar">
			<input name="username" type="text" value="[username]">
			<input name="group" type="text" value="[groupname]">
			</form></li>


		<li><a href="mng-rad-usergroup-del.php"><b>&raquo;</b>Remove User-Group Mapping</a></li>
	</ul>

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