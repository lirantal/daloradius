<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "radacctid";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";
	
	
	
	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    include('include/config/logging.php');


?>
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

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php

    include ("menu-reports.php");

?>

		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Listing Online Users</a></h2>
				
				<p>
				The following table lists users who are currently connected to
				the system. It is very much possible that there are stale connections,
				meaning that users got disconnected but the NAS didn't send or wasn't
				able to send a STOP accounting packet to the RADIUS server.
				</p>


<div class="tabber">

     <div class="tabbertab" title="Statistics">
        <br/>	
	
<?php

    include 'library/opendb.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page
	$sql = "SELECT Username, FramedIPAddress, AcctStartTime, AcctSessionTime, NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE (AcctStopTime is NULL)";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();


	/* we are searching for both kind of attributes for the password, being User-Password, the more
	   common one and the other which is Password, this is also done for considerations of backwards
	   compatibility with version 0.7        */
	
	$sql = "SELECT Username, FramedIPAddress, AcctStartTime, AcctSessionTime, NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE (AcctStopTime is NULL) ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
	$res = $dbSocket->query($sql);

	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	setupLinks($pageNum, $maxPage, $orderBy, $orderType);
	
	if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
	/* END */

	echo "<table border='2' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='10'>".$l[all][Records]."</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
					<th scope='col'> ".$l[all][Username]. " 
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=desc\"> < </a>
					</th>
					<th scope='col'> IP Address
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=framedipaddress&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=framedipaddress&orderType=desc\"> < </a>
					</th>
					<th scope='col'> Start Time
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstarttime&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstarttime&orderType=desc\"> < </a>
					</th>
					<th scope='col'> Session Time 
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctsessiontime&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctsessiontime&orderType=desc\"> < </a>
					</th>
					<th scope='col'> NAS IP Address
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasipaddress&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasipaddress&orderType=desc\"> < </a>
					</th>
			</tr> </thread>";

	while($row = $res->fetchRow()) {
		echo "<tr>
				<td> $row[0] </td>
				<td> $row[1] </td>
				<td> $row[2] </td>
				<td> $row[3] </td>
				<td> $row[4] </td>
		 </td>

		</tr>";
	}
	
	echo "</table>";
	include 'library/closedb.php';
		
?>

	</div>

     <div class="tabbertab" title="Graph">
        <br/>

<?php
	echo "<center>";
	echo "<img src=\"library/graphs-reports-online-users.php\" />";
	echo "</center>";
?>

	</div>
</div>



				
		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>

		
		</div>
		
</div>
</div>


</body>
</html>
