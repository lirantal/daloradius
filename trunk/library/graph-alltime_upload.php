<?php
/*******************************************************************
* Extension name: graph-alltime_upload.php                         *
*                                                                  *
* Description:    this graph extension procduces a query of the    *
* alltime uploads made by all users on a daily, monthly and yearly *
* basis.                                                           *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/


if ($type == "daily") {
	daily($username);
} elseif ($type == "monthly") {
	monthly($username);
} elseif ($type == "yearly") {
	yearly($username);
}



function daily($username) {

	include 'library/config.php';
	include 'library/opendb.php';
        include 'libgraph/graphs.inc.php';

	$sql = "SELECT sum(AcctInputOctets) as Uploads, day(AcctStartTime) AS day, UserName from radacct where UserName='$username' group by day;";

        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$total_uploads = 0;		// initialize variables
	$count = 0;			

        $array_uploads = array();
        $array_days = array();

        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +--------+------+
		// | Upload | Day  |
		// +--------+------+

        	$uploads = ($ent[0]/1024/1024);	// total uploads on that specific day
        	$day = $ent[1];		// day of the month [1-31]

		$total_uploads = $total_uploads + $uploads;
		$count = $count + 1;

	        array_push($array_uploads, "$uploads");
	        array_push($array_days, "$day");

        }

	echo "<br/><br/>";
        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>All-time Upload statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Uploads count in MB</th>
                        <th scope='col'> Day of month </th>
                </tr> </thread>";



        $i=0;
        foreach ($array_days as $a_day) {
                echo "<tr>
                        <td> $array_uploads[$i] </td>
                        <td> $a_day </td>
                </tr>";

                $i++;
        }



	echo "</table>";
	echo "<br/> Total uploads of <u>$total_uploads</u> <br/>";

        mysql_free_result($res);
        include 'library/closedb.php';
}





function monthly($username) {

	include 'library/config.php';
	include 'library/opendb.php';
        include 'libgraph/graphs.inc.php';
        
	$sql = "SELECT sum(AcctInputOctets) as Uploads, monthname(AcctStartTime) AS month, UserName from radacct where UserName='$username' group by month;";
	$res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$total_uploads = 0;		// initialize variables
	$count = 0;			

        $array_uploads = array();
        $array_months = array();

        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +--------+--------+
		// | Upload | Month  |
		// +--------+--------+

        	$uploads = ($ent[0]/1024/1024);	// total uploads on that specific month
        	$month = $ent[1];	// Month of year [1-12]

		$total_uploads = $total_uploads + $uploads;
		$count = $count + 1;

                array_push($array_uploads, "$uploads");
                array_push($array_months, "$month");

        }

        echo "<br/><br/>";


        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>All-time Upload statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Uploads count in MB </th>
                        <th scope='col'> Month of year </th>
                </tr> </thread>";


        $i=0;
        foreach ($array_months as $a_month) {
                echo "<tr>
                        <td> $array_uploads[$i] </td>
                        <td> $a_month </td>
                </tr>";

                $i++;
        }


	echo "</table>";

	echo "<br/> Total uploads of <u>$total_uploads</u> <br/>";

        mysql_free_result($res);
        include 'library/closedb.php';
}








function yearly($username) {

	include 'library/config.php';
	include 'library/opendb.php';
        include 'libgraph/graphs.inc.php';

	$sql = "SELECT sum(AcctInputOctets) as Uploads, year(AcctStartTime) AS year, UserName from radacct where UserName='$username' group by year;";

        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$total_uploads = 0;		// initialize variables
	$count = 0;			

        $array_uploads = array();
        $array_years = array();


        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +--------+-------+
		// | Upload | Year  |
		// +--------+-------+

        	$uploads = ($ent[0]/1024/1024);	// total uploads on that specific month
        	$year = $ent[1];	// Year

		$total_uploads = $total_uploads + $uploads;
		$count = $count + 1;

                array_push($array_uploads, "$uploads");
                array_push($array_years, "$year");

        }

        echo "<br/><br/>";

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>All-Time Upload statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Uploads count in MB</th>
                        <th scope='col'> Year </th>
                </tr> </thread>";


        $i=0;
        foreach ($array_years as $a_year) {
                echo "<tr>
                        <td> $array_uploads[$i] </td>
                        <td> $a_year </td>
                </tr>";

                $i++;
        }


	echo "</table>";
	echo "<br/> Total uploads of <u>$total_uploads</u> <br/>";

        mysql_free_result($res);
        include 'library/closedb.php';
}





