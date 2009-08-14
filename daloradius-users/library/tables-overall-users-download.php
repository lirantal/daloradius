<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 * Description:
 *		this graph extension procduces a query of the alltime downloads made by all users on a daily, monthly and yearly basis.
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */
 

if ($type == "daily") {
	daily($username, $orderBy, $orderType);
} elseif ($type == "monthly") {
	monthly($username, $orderBy, $orderType);
} elseif ($type == "yearly") {
	yearly($username, $orderBy, $orderType);
}



function daily($username, $orderBy, $orderType) {

	include 'opendb.php';

	$sql = "SELECT sum(AcctOutputOctets) as Downloads, day(AcctStartTime) AS day, UserName from ".
		$configValues['CONFIG_DB_TBL_RADACCT']." where UserName='$username' AND acctstoptime>0 AND AcctStartTime>DATE_SUB(curdate(),INTERVAL (DAY(curdate())-1) DAY) AND AcctStartTime< now() group by day ORDER BY $orderBy $orderType;";


	$res = $dbSocket->query($sql);

	$total_downloads = 0;		// initialize variables
	$count = 0;			

	$array_downloads = array();
	$array_days = array();

	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +--------+------+
		// | Download | Day  |
		// +--------+------+

		$downloads = floor($row[0]/1024/1024);	// total downloads on that specific day
		$day = $row[1];		// day of the month [1-31]

		$total_downloads = $total_downloads + $downloads;
		$count = $count + 1;

		array_push($array_downloads, "$downloads");
		array_push($array_days, "$day");

	}

	// creating the table:
	echo "<br/><br/>";
        echo "<table border='0' class='table1'>\n";
        echo "
			<thead>
				<tr>
					<th colspan='10'>All-time Download statistics</th>
				</tr>
			</thead>
		";
        echo "<thread> <tr>
				<th scope='col'> Downloads count in MB
				<br/>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=downloads&orderType=asc\"> > </a>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=downloads&orderType=desc\"> < </a>
				</th>
				<th scope='col'> Day of month
				<br/>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=day&orderType=asc\"> > </a>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=day&orderType=desc\"> < </a>
				</th>
		</tr> </thread>";

	$i=0;
	foreach ($array_days as $a_day) {
		echo "<tr>
				<td> $array_downloads[$i] </td>
				<td> $a_day </td>
		</tr>";
		$i++;
	}

	echo "<tr> <td> <b> $total_downloads </b> </td> </tr>";
	echo "</table>";

	include 'library/closedb.php';
}





function monthly($username, $orderBy, $orderType) {

	include 'opendb.php';
        
	$sql = "SELECT sum(AcctOutputOctets) as Downloads, monthname(AcctStartTime) AS month, UserName from ".
		$configValues['CONFIG_DB_TBL_RADACCT']." where UserName='$username' AND acctstoptime>0 group by month ORDER BY $orderBy $orderType;";
	$res = $dbSocket->query($sql);

	$total_downloads = 0;		// initialize variables
	$count = 0;			

	$array_downloads = array();
	$array_months = array();

	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +--------+--------+
		// | Download | Month  |
		// +--------+--------+

		$downloads = floor($row[0]/1024/1024);	// total downloads on that specific month
		$month = $row[1];	// Month of year [1-12]

		$total_downloads = $total_downloads + $downloads;
		$count = $count + 1;

		array_push($array_downloads, "$downloads");
		array_push($array_months, "$month");
	}

	// creating the table:
	echo "<br/><br/>";

	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
				<th colspan='10'>All-time Download statistics</th>
			</tr>
		</thead>
	";
	
	echo "<thread> <tr>
			<th scope='col'> Downloads count in MB
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=downloads&orderType=asc\"> > </a>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=downloads&orderType=desc\"> < </a>
			</th>
			<th scope='col'> Month of year
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=month&orderType=asc\"> > </a>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=month&orderType=desc\"> < </a>
			</th>
		</tr> </thread>
	";

	$i=0;
	foreach ($array_months as $a_month) {
		echo "<tr>
			<td> $array_downloads[$i] </td>
			<td> $a_month </td>
		</tr>";
		$i++;
	}


	echo "<tr> <td> <b> $total_downloads </b> </td> </tr>";
	echo "</table>";

	include 'library/closedb.php';
}








function yearly($username, $orderBy, $orderType) {

	include 'opendb.php';

	$sql = "SELECT sum(AcctOutputOctets) as Downloads, year(AcctStartTime) AS year, UserName from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." where UserName='$username' AND acctstoptime>0 group by year ORDER BY $orderBy $orderType;";

	$res = $dbSocket->query($sql);

	$total_downloads = 0;		// initialize variables
	$count = 0;			

	$array_downloads = array();
	$array_years = array();

	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +--------+-------+
		// | Download | Year  |
		// +--------+-------+

		$downloads = floor($row[0]/1024/1024);	// total downloads on that specific month
		$year = $row[1];	// Year

		$total_downloads = $total_downloads + $downloads;
		$count = $count + 1;

		array_push($array_downloads, "$downloads");
		array_push($array_years, "$year");

 	}

	echo "<br/><br/>";
	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
				<th colspan='10'>All-Time Download statistics</th>
			</tr>
		</thead>
	";

	echo "<thread> <tr>
				<th scope='col'> Downloads count in MB
				<br/>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=downloads&orderType=asc\"> > </a>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=downloads&orderType=desc\"> < </a>
				</th>
				<th scope='col'> Year
				<br/>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=year&orderType=asc\"> > </a>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=year&orderType=desc\"> < </a>
				</th>
		</tr> </thread>
	";


	$i=0;
	foreach ($array_years as $a_year) {
		echo "<tr>
			<td> $array_downloads[$i] </td>
			<td> $a_year </td>
		</tr>";

		$i++;
	}


	echo "<tr> <td> <b> $total_downloads </b> </td> </tr>";
	echo "</table>";

	include 'closedb.php';
}




?>
