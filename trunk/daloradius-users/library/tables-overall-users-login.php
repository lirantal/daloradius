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
 *		 this graph extension procduces a query of the overall logins made by a particular user on a daily, monthly and yearly basis.
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

	$sql = "SELECT UserName, COUNT(DISTINCT AcctSessionID) as logins, DAY(AcctStartTime) AS Day from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' AND acctstoptime>0 group by Day ORDER BY $orderBy $orderType;";
	$res = $dbSocket->query($sql);

	$total_logins = 0;		// initialize variables
	$count = 0;			

	$array_logins = array();
	$array_days = array();

	$user = "";
	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +----------+----------------------+------+
		// | UserName | count(AcctStartTime) | Day  |
		// +----------+----------------------+------+

		$user = $row[0];	// username
		$logins = $row[1];	// total logins on that specific day
		$day = $row[2];		// day of the month [1-31]

		$total_logins = $total_logins + $logins;
		$count = $count + 1;

		array_push($array_logins, "$logins");
		array_push($array_days, "$day");

	}
	
	echo "<br/> <center> <h4>Logins/Hits statistics for user $user</h4> <br/> </center> ";

	echo "<br/><br/>";

	echo "<table border='0' class='table1'>\n";
	echo "
		<thead>
			<tr>
				<th colspan='10'>Logins/Hits statistics</th>
			</tr>
		</thead>
			";
	echo "<thread> <tr>
					<th scope='col'> Username </th>
					<th scope='col'> Logins/Hits count
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=logins&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=logins&orderType=desc\"> < </a>
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
				<td> $user </td>
				<td> $array_logins[$i] </td>
				<td> $a_day </td>
		</tr>";
		$i++;
	}

	echo "<tr> <td> </td> <td> <b> $total_logins </b> </td> </tr>";
	echo "</table>";

	include 'closedb.php';
}





function monthly($username, $orderBy, $orderType) {

	
	include 'library/opendb.php';

	$sql = "SELECT UserName, count(AcctStartTime) as logins, MONTHNAME(AcctStartTime) AS Month from ".
			$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by Month ORDER BY $orderBy $orderType;";
	$res = $dbSocket->query($sql);

	$total_logins = 0;		// initialize variables
	$count = 0;			

	$array_logins = array();
	$array_months = array();

	$user = "";

	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +----------+----------------------+--------+
		// | UserName | count(AcctStartTime) | Month  |
		// +----------+----------------------+--------+

		$user = $row[0];	// username
		$logins = $row[1];	// total logins on that specific month
		$month = $row[2];		// Month of year [1-12]

		$total_logins = $total_logins + $logins;
		$count = $count + 1;

		array_push($array_logins, "$logins");
		array_push($array_months, "$month");

        }

        echo "<br/> <center> <h4>Logins/Hits statistics for user $user</h4> <br/> </center> ";

        echo "<br/><br/>";


        echo "<table border='0' class='table1'>\n";
        echo "
			<thead>
				<tr>
					<th colspan='10'>Logins/Hits statistics</th>
				</tr>
			</thead>
                ";
        echo "<thread> <tr>
					<th scope='col'> Username </th>
					<th scope='col'> Logins/Hits count
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=logins&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=monthly&orderBy=logins&orderType=desc\"> < </a>
					</th>
					<th scope='col'> Month of year
					<br/>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=month&orderType=asc\"> > </a>
					<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=daily&orderBy=month&orderType=desc\"> < </a>
					</th>
			</tr> </thread>";

        $i=0;
        foreach ($array_months as $a_month) {
			echo "<tr>
					<td> $user </td>
					<td> $array_logins[$i] </td>
					<td> $a_month </td>
                </tr>";
			$i++;
        }

	echo "<tr> <td> </td> <td> <b> $total_logins </b> </td> </tr>";

	echo "</table>";

	include 'library/closedb.php';
}








function yearly($username, $orderBy, $orderType) {

	include 'opendb.php';


	$sql = "SELECT UserName, count(AcctStartTime) as logins, YEAR(AcctStartTime) AS Year from ".
		$configValues['CONFIG_DB_TBL_RADACCT']." where username='$username' group by Year ORDER BY $orderBy $orderType;";
	$res = $dbSocket->query($sql);

	$total_logins = 0;		// initialize variables
	$count = 0;			

	$array_logins = array();
	$array_years = array();

	$user = "";

	while($row = $res->fetchRow()) {
		// The table that is being procuded is in the format of:
		// +----------+----------------------+-------+
		// | UserName | count(AcctStartTime) | Year  |
		// +----------+----------------------+-------+

		$user = $row[0];	// username
		$logins = $row[1];	// total logins on that specific month
		$year = $row[2];	// Year

		$total_logins = $total_logins + $logins;
		$count = $count + 1;

		array_push($array_logins, "$logins");
		array_push($array_years, "$year");

	}

	echo "<br/> <center> <h4>Logins/Hits statistics for user $user</h4> <br/> </center> ";

	echo "<br/><br/>";

	echo "<table border='0' class='table1'>\n";
	echo "
					<thead>
							<tr>
							<th colspan='10'>Logins/Hits statistics</th>
							</tr>
					</thead>
			";

	echo "<thread> <tr>
				<th scope='col'> Username </th>
				<th scope='col'> Logins/Hits count
				<br/>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=logins&orderType=asc\"> > </a>
				<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&type=yearly&orderBy=logins&orderType=desc\"> < </a>
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
				<td> $user </td>
				<td> $array_logins[$i] </td>
				<td> $a_year </td>
		</tr>";
		$i++;
	}


	echo "<tr> <td> </td> <td> <b> $total_logins </b> </td> </tr>";

	echo "</table>";

	include 'closedb.php';
}


?>
