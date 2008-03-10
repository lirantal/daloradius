
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

	<h3>Realms Management</h3>
	<ul class="subnav">

		<li><a href="mng-rad-realms-list.php"><b>&raquo;</b><?php echo $l['button']['ListRealms'] ?></a></li>
		<li><a href="mng-rad-realms-new.php"><b>&raquo;</b><?php echo $l['button']['NewRealm'] ?></a></li>
		<li><a href="javascript:document.mngradrealmedit.submit();""><b>&raquo;</b><?php echo $l['button']['EditRealm'] ?><a>
			<form name="mngradrealmedit" action="mng-rad-realms-edit.php" method="get" class="sidebar">
			<?php   
				include 'include/management/populate_selectbox.php';
				populate_realms("Select Realm","realmname","");
			?>
			</form></li>

		<li><a href="mng-rad-realms-del.php"><b>&raquo;</b><?php echo $l['button']['RemoveRealm'] ?></a></li>
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
