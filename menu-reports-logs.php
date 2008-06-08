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
	$m_active = "Reports";
    include_once ("include/menu/menu-items.php");
	include_once ("include/menu/reports-subnav.php");
?>      

<div id="sidebar">

	<h2>Logs</h2>
		<ul class="subnav">

		<h3>Log Files</h3>

			<li><a href="javascript:document.daloradius_log.submit();"><b>&raquo;</b><?php echo $l['button']['daloRADIUSLog'] ?></a>
                        <form name="daloradius_log" action="rep-logs-daloradius.php" method="get" class="sidebar">
	                        <select class="generic" name="linecount" type="text">
					<?php if (isset($lineCount)) {
						echo "<option value='$lineCount'> $lineCount Lines </option>";
					      } else {
						echo "<option value='50'> 50 Lines Output Limit </option>";
					      }
					?>
        	                        <option value="50"></option>
        	                        <option value="5"> 5 Lines </option>
                                        <option value="10"> 10 Lines </option>
                                        <option value="20"> 20 Lines </option> 
                                        <option value="50"> 50 Lines </option>
                                        <option value="100"> 100 Lines </option>
                                        <option value="500"> 500 Lines </option>
                                        <option value="1000"> 1000 Lines </option>
                                </select>
	                        <select class="generic" name="filter" type="text">
					<?php if (isset($filter)) {
						echo "<option value='$filter'> $filter </option>";
					      } else {
						echo "<option value='.'> No filter </option>";
					      }
					?>
        	                        <option value="."></option>
        	                        <option value="QUERY"> Query Only </option>
                                        <option value="NOTICE"> Notice Only </option>
                                        <option value="INSERT"> SQL INSERT Only </option> 
                                        <option value="SELECT"> SQL SELECT Only </option>
                                </select>
                        </form></li>
			<li><a href="rep-logs-radius.php"><b>&raquo;</b><?php echo $l['button']['RadiusLog'] ?></a></li>
			<li><a href="rep-logs-system.php"><b>&raquo;</b><?php echo $l['button']['SystemLog'] ?></a></li>
			<li><a href="rep-logs-boot.php"><b>&raquo;</b><?php echo $l['button']['BootLog'] ?></a></li>

		</ul>

	
	<br/><br/>
	<h2>Search</h2>
	<input name="" type="text" value="Search" />
	

</div>



