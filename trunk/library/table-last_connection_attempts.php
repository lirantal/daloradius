<?php
/*******************************************************************
* Extension name: last connection attempts                         *
*                                                                  *
* Description:                                                     *
* this extention table prints all the last 50 connection attempts  *
* made by users and displays the results                           *
* requires: radpostauth table                                      *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/

	include 'opendb.php';

	$sql = "SELECT user, pass, reply, date from radpostauth order by id desc limit 50;";
	$res = $dbSocket->query($sql);

	$array_users = array();
	$array_pass = array();
	$array_starttime = array();
	$array_reply = array();
	$count = 0;

	while($row = $res->fetchRow()) {

		// The table that is being procuded is in the format of:
		// +-------------+-------------+---------------+---------------------+
		// | user        | pass        | reply         | date                |
		// +-------------+-------------+---------------+---------------------+


		$user = $row[0];
		$pass = $row[1];
		$starttime = $row[3];
		$reply = $row[2];

		array_push($array_users, "$user");
		array_push($array_pass, "$pass");
		array_push($array_starttime, "$starttime");
		array_push($array_reply, "$reply");

		$count++;

        }

	// creating the table:
	echo "<br/>";
        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>Last 50 connection attempts</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Username</th>
                        <th scope='col'> Password </th>
                        <th scope='col'> Logged-In Time </th>
                        <th scope='col'> RADIUS Reply Packet </th>
                </tr> </thread>";

	$i = 0;
	while ($i != $count) {
                echo "<tr>
                        <td> $array_users[$i] </td>
                        <td> $array_pass[$i] </td>
                        <td> $array_starttime[$i] </td>
                        <td> $array_reply[$i] </td>
                </tr>";
		$i++;
	}

	echo "</table>";

        include 'closedb.php';


