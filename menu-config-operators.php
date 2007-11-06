<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
</head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<body>
<?php
        include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">
		
<?php
    $m_active = "Config";
    include_once ("include/menu/menu-items.php");
    include_once ("include/menu/config-subnav.php");
?>      

		<div id="sidebar">
		
				<h2>Configuration</h2>
				
				<h3>Management</h3>
				

                                <ul class="subnav">
                                
					<li><a href="config-operators-list.php"><b>&raquo;</b>List Operators</a></li>
					<li><a href="config-operators-new.php"><b>&raquo;</b>New Operator</a></li>
					<li><a href="javascript:document.mngedit.submit();""><b>&raquo;</b>Edit Operator<a>
						<form name="mngedit" action="config-operators-edit.php" method="get" class="sidebar">
							<input name="operator_username" type="text">
						</form></li>

					<li><a href="config-operators-del.php"><b>&raquo;</b>Remove Operator</a></li>


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


        if ((isset($actionStatus)) && ($actionStatus == "informational")) {
                echo <<<EOF
                        <div id="contentnorightbar">
                        <h2 id="Intro"> Informational </h2>
                        <br/><br/>
                        <font color='#666'>
EOF;
        echo $actionMsg;

        echo "</font></div><br/>";

        }


?>
