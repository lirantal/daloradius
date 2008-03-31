
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->

</head>
<script src="library/js_date/date-functions.js" type="text/javascript"></script>
<script src="library/js_date/datechooser.js" type="text/javascript"></script>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>

<body>

<?php
    include_once ("lang/main.php");
?>

<div id="wrapper">
<div id="innerwrapper">

<?php
	$m_active = "Accounting";
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/accounting-subnav.php");
?>

<div id="sidebar">

	<h2>Accounting</h2>
	
	<h3>Maintenance</h3>
	<ul class="subnav">

		<li><a href="acct-maintenance-cleanup.php"><b>&raquo;</b><?php echo $l['button']['CleanupStaleSessions'] ?>
			</a></li>
		<li><a href="acct-maintenance-delete.php"><b>&raquo;</b><?php echo $l['button']['DeleteAccountingRecords'] ?>
			</a></li>
	</ul>

	<br/><br/>
	<h2>Search</h2>
	<input name="" type="text" value="Search" />
			
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

