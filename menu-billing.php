
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


<body>
<?php
    include_once ("lang/main.php");
?>
<div id="wrapper">
<div id="innerwrapper">

<?php
    $m_active = "Billing";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/billing-subnav.php");
?>      

		<div id="sidebar">
		
				<h2>Billing</h2>
				
				<h3>Billing Engine</h3>
				<ul class="subnav">
				
						<li><a href="javascript:document.billprepaidhotspot.submit();""><b>&raquo;</b>Prepaid Accounting</a>
							<form name="billprepaidhotspot" action="bill-prepaid.php" method="get" class="sidebar">
							<select name="hotspot" size="3">
								<option value='%'> all
<?php

        
        include 'library/opendb.php';

	// Grabing the last 

	$sql = "select name from ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']."";
	$res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "
                        <option value='$row[0]'> $row[0]
			";

	}
        include 'library/closedb.php';
?>							</select><br/>


						<br/>Filter by date
							<input name="startdate" type="text" id="startdate" value="2006-01-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'startdate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

							<input name="enddate" type="text" id="enddate" value="2006-12-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'enddate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>


							</form></li>


						<li><a href="javascript:document.billpersecondhotspot.submit();""><b>&raquo;</b>Per-second Accounting</a>
							<form name="billpersecondhotspot" action="bill-persecond.php" method="get" class="sidebar">
							<select name="ps-hotspot" size="3">
								<option value='%'> all
<?php

        
        include 'library/opendb.php';

	// Grabing the list of hotspots 

	$sql = "select name from ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']."";
	$res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                echo "
                        <option value='$row[0]'> $row[0]
			";

	}

        include 'library/closedb.php';
?>							</select><br/>


						<br/>Filter by date
							<input name="ps-startdate" type="text" id="ps-startdate" value="2006-01-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'ps-startdate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
							<input name="ps-enddate" type="text" id="ps-enddate" value="2006-12-01">
<img src="library/js_date/calendar.gif" onclick="showChooser(this, 'ps-enddate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d', false);">
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>

							</form></li>

				</ul>
		
				<h3>Rates Management</h3>
				<ul class="subnav">
				
						<li><a href="bill-rates-list.php"><b>&raquo;</b>Show rates</a></li>
						<li><a href="bill-rates-new.php"><b>&raquo;</b>New rate</a></li>
						<li><a href="javascript:document.billratesedit.submit();""><b>&raquo;</b>Edit Rate</a>
							<form name="billratesedit" action="bill-rates-edit.php" method="get" class="sidebar">
							<input name="type" type="text">
							</form></li>


						<li><a href="bill-rates-del.php"><b>&raquo;</b>Delete rate</a></li>
				</ul>
				
				<br/><br/>
				
				
				
		
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
