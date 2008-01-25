
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
	
		<li><a href="mng-rad-nas-list.php" tabindex=1><b>&raquo;</b>List NAS</a></li>
		<li><a href="mng-rad-nas-new.php" tabindex=2><b>&raquo;</b>New NAS</a></li>
		<li><a href="javascript:document.mngradnasedit.submit();" tabindex=3 ><b>&raquo;</b>Edit NAS</a>
			<form name="mngradnasedit" action="mng-rad-nas-edit.php" method="get" class="sidebar">
			<input name="nashost" type="text" tabindex=4>
			</form></li>
		<li><a href="mng-rad-nas-del.php" tabindex=5><b>&raquo;</b>Remove NAS</a></li>
		
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
