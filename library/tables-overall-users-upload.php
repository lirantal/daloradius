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
 *		 this graph extension procduces a query of the alltime uploads made by all users on a daily, monthly and yearly basis.
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

	$sql = "SELECT sum(AcctInputOctets) as Uploads, day(AcctStartTime) AS day, UserName from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." where UserName='$username' AND acctstoptime>0 AND AcctStartTime>DATE_SUB(curdate(),INTERVAL (DAY(curdate())-1) DAY) AND AcctStartTime< now() group by day ORDER BY $orderBy $orderType;";
	$res = $dbSocket->query($sql);

	$total_uploads = 0;		// initialize variables
	$count = 0;			

	$array_uploads = array();
	$array_days = array();

	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +--------+------+
		// | Upload | Day  |
		// +--------+------+

		$uploads = floor($row[0]/1024/1024);	// total uploads on that specific day
		$day = $row[1];		// day of the month [1-31]

		$total_uploads = $total_uploads + $uploads;
		$count = $count + 1;

		array_push($array_uploads, "$uploads");
		array_push($array_days, "$day");

	}

	echo "<br/><br/>";
	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
				<th colspan='10'>All-time Upload statistics</th>
			</tr>
		</thead>
			";
	echo "<thread> <tr>
			<th scope='col'> Uploads count in MB
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=uploads&orderType=asc\"> > </a>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=uploads&orderType=desc\"> < </a>
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
				<td> $array_uploads[$i] </td>
				<td> $a_day </td>
		</tr>";
		$i++;
	}


	echo "<tr> <td> <b> $total_uploads </b> </td> </tr>";
	echo "</table>";

	include 'closedb.php';
}





function monthly($username, $orderBy, $orderType) {

	include 'opendb.php';
        
	$sql = "SELECT sum(AcctInputOctets) as Uploads, monthname(AcctStartTime) AS month, UserName from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." where UserName='$username' group by month ORDER BY $orderBy $orderType;";
	$res = $dbSocket->query($sql);

	$total_uploads = 0;		// initialize variables
	$count = 0;			

	$array_uploads = array();
	$array_months = array();

	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +--------+--------+
		// | Upload | Month  |
		// +--------+--------+

		$uploads = floor($row[0]/1024/1024);	// total uploads on that specific month
		$month = $row[1];	// Month of year [1-12]

		$total_uploads = $total_uploads + $uploads;
		$count = $count + 1;

		array_push($array_uploads, "$uploads");
		array_push($array_months, "$month");

	}

	echo "<br/><br/>";
	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
				<th colspan='10'>All-time Upload statistics</th>
			</tr>
		</thead>
	";

	echo "<thread> <tr>
				<th scope='col'> Uploads count in MB
				<br/>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=uploads&orderType=asc\"> > </a>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=uploads&orderType=desc\"> < </a>
				</th>
				<th scope='col'> Month of year
				<br/>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=month&orderType=asc\"> > </a>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=month&orderType=desc\"> < </a>
				</th>
		</tr> </thread>";


	$i=0;
	foreach ($array_months as $a_month) {
		echo "<tr>
				<td> $array_uploads[$i] </td>
				<td> $a_month </td>
		</tr>";
		$i++;
	}

	echo "<tr> <td> <b> $total_uploads </b> </td> </tr>";
	echo "</table>";

	include 'closedb.php';
}








function yearly($username, $orderBy, $orderType) {

	include 'opendb.php';

	$sql = "SELECT sum(AcctInputOctets) as Uploads, year(AcctStartTime) AS year, UserName from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." where UserName='$username' group by year ORDER BY $orderBy $orderType;";

	$res = $dbSocket->query($sql);

	$total_uploads = 0;		// initialize variables
	$count = 0;			

	$array_uploads = array();
	$array_years = array();


	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +--------+-------+
		// | Upload | Year  |
		// +--------+-------+

		$uploads = floor($row[0]/1024/1024);	// total uploads on that specific month
		$year = $row[1];	// Year

		$total_uploads = $total_uploads + $uploads;
		$count = $count + 1;

		array_push($array_uploads, "$uploads");
		array_push($array_years, "$year");

	}

	echo "<br/><br/>";

	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
				<th colspan='10'>All-Time Upload statistics</th>
			</tr>
		</thead>
			";
			
	echo "<thread> <tr>
			<th scope='col'> Uploads count in MB
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=uploads&orderType=asc\"> > </a>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=uploads&orderType=desc\"> < </a>
			</th>
			<th scope='col'> Year
			<br/>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=year&orderType=asc\"> > </a>
			<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=year&orderType=desc\"> < </a>
			</th>
	</tr> </thread>";

	$i=0;
	foreach ($array_years as $a_year) {
		echo "<tr>
			<td> $array_uploads[$i] </td>
			<td> $a_year </td>
		</tr>";
		$i++;
	}

	echo "<tr> <td> <b> $total_uploads </b> </td> </tr>";
	echo "</table>";

	include 'closedb.php';
}

?>
