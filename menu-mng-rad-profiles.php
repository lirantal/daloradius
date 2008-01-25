
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

	<h3>Profiles Management</h3>
	<ul class="subnav">

		<li><a href="mng-rad-profiles-list.php"><b>&raquo;</b>List Profiles</a></li>
		<li><a href="mng-rad-profiles-new.php"><b>&raquo;</b>New Profile</a></li>
		<li><a href="javascript:document.mngradprofileedit.submit();""><b>&raquo;</b>Edit Profile<a>
			<form name="mngradprofileedit" action="mng-rad-profiles-edit.php" method="get" class="sidebar">
			<?php   
				include 'include/management/populate_selectbox.php';
				populate_groups("Select Profile","profile","");
			?>
			</form></li>

		<li><a href="mng-rad-profiles-del.php"><b>&raquo;</b>Remove Profile</a></li>
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
