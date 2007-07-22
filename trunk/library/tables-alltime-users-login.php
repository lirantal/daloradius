<?php
/*******************************************************************
* Extension name: graph-alltime_logins.php                         *
*                                                                  *
* Description:    this graph extension procduces a query of the    *
* alltime logins made by all users on a daily, monthly and         *
* yearly basis.                                                    *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/


if ($type == "daily") {
	daily();
} elseif ($type == "monthly") {
	monthly();
} elseif ($type == "yearly") {
	yearly();
}



function daily() {

	include 'opendb.php';

	$sql = "SELECT count(username) as numberoflogins, day(AcctStartTime) AS day from ".$configValues['CONFIG_DB_TBL_RADACCT']." group by day;";
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	$total_logins = 0;		// initialize variables
	$count = 0;			

        $array_logins = array();
        $array_days = array();



        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +--------+------+
		// | Logins/Hits | Day  |
		// +--------+------+

        	$logins = $ent[0];	// total logins on that specific day
        	$day = $ent[1];		// day of the month [1-31]

		$total_logins = $total_logins + $logins;
		$count = $count + 1;

                array_push($array_logins, "$logins");
                array_push($array_days, "$day");

        }
        echo "<br/><br/>";


        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>All-time Logins/Hits statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Logins/Hitss count</th>
                        <th scope='col'> Day of month </th>
                </tr> </thread>";

        $i=0;
        foreach ($array_days as $a_day) {
                echo "<tr>
                        <td> $array_logins[$i] </td>
                        <td> $a_day </td>
                </tr>";

                $i++;
        }


		echo "</table>";

	echo "<br/> Total logins of <u>$total_logins</u> <br/>";

        mysql_free_result($res);
        include 'closedb.php';
}





function monthly() {

	include 'opendb.php';

        $sql = "SELECT count(username) as numberoflogins, monthname(AcctStartTime) AS month from ".$configValues['CONFIG_DB_TBL_RADACCT']." group by month;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	$total_logins = 0;		// initialize variables
	$count = 0;			

        $array_logins = array();
        $array_months = array();

        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +--------+--------+
		// | Logins/Hits | Month  |
		// +--------+--------+

        	$logins = $ent[0];	// total logins on that specific month
        	$month = $ent[1];	// Month of year [1-12]

		$total_logins = $total_logins + $logins;
		$count = $count + 1;

		array_push($array_logins, "$logins");
                array_push($array_months, "$month");
        }
        echo "<br/><br/>";


        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>All-time Logins/Hits statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Logins/Hitss count</th>
                        <th scope='col'> Month of year </th>
                </tr> </thread>";

        $i=0;
        foreach ($array_months as $a_month) {
                echo "<tr>
                        <td> $array_logins[$i] </td>
                        <td> $a_month </td>
                </tr>";

                $i++;
        }


	echo "</table>";
	echo "<br/> Total logins of <u>$total_logins</u> <br/>";

        mysql_free_result($res);
        include 'closedb.php';
}








function yearly() {

	include 'opendb.php';

	$sql = "SELECT count(username) as numberoflogins, year(AcctStartTime) AS year from ".$configValues['CONFIG_DB_TBL_RADACCT']." group by year;";
        $res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

	$total_logins = 0;		// initialize variables
	$count = 0;			

        $array_logins = array();
        $array_years = array();

        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +--------+-------+
		// | Logins/Hits | Year  |
		// +--------+-------+

        	$logins = $ent[0];	// total logins on that specific month
        	$year = $ent[1];	// Year

		$total_logins = $total_logins + $logins;
		$count = $count + 1;

                array_push($array_logins, "$logins");
                array_push($array_years, "$year");

        }
        echo "<br/><br/>";



	echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>All-Time Logins/Hits statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Logins/Hitss count</th>
                        <th scope='col'> Year </th>
                </tr> </thread>";

        $i=0;
        foreach ($array_years as $a_year) {
                echo "<tr>
                        <td> $array_logins[$i] </td>
                        <td> $a_year </td>
                </tr>";

                $i++;
        }


	echo "</table>";
	echo "<br/> Total logins of <u>$total_logins</u> <br/>";

        mysql_free_result($res);
        include 'closedb.php';
}





?>
