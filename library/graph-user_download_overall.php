<?php
/*******************************************************************
* Extension name: graph-user_download_overall.php                  *
*                                                                  *
* Description:    this graph extension procduces a query of the    *
* overall downloads made by a particular user on a daily, monthly, *
* and yearly basis.                                                *
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

	include ("library/checklogin.php");
	include 'library/config.php';
	include 'library/opendb.php';
        include 'libgraph/graphs.inc.php';

        $sql = "SELECT UserName, SUM(AcctOutputOctets) AS Download, DAY(AcctStartTime) AS Day from radacct where username='$username' group by Day;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$total_downloads = 0;		// initialize variables
	$count = 0;			

        $array_downloads = array();
        $array_days = array();

        $user = "";

        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +----------+----------+------+
		// | UserName | Download | Day  |
		// +----------+----------+------+

        	$user = $ent[0];	// username
        	$downloads = ($ent[1]/1024/1024);	// total downloads on that specific day
        	$day = $ent[2];		// day of the month [1-31]

		$total_downloads = $total_downloads + $downloads;
		$count = $count + 1;

                array_push($array_downloads, "$downloads");
                array_push($array_days, "$day");


        }

        echo "<br/> <center> <h4>Download statistics for user $user</h4> <br/> </center> ";

        //graph information to create
        $graph = new BAR_GRAPH("hBar");
        $graph->labels = $array_days;
        $graph->values = $array_downloads;

        $graph->showValues = 1;
        $graph->barWidth = 12;
        $graph->labelSize = 12;
        $graph->absValuesSize = 12;
        $graph->percValuesSize = 12;
        $graph->graphPadding = 12;
        $graph->graphBGColor = "#ABCDEF";
        $graph->graphBorder = "1px solid blue";
        $graph->barColors = "#A0C0F0";
        $graph->barBGColor = "#E0F0FF";
        $graph->barBorder = "2px outset white";
        $graph->labelColor = "#000000";
        $graph->labelBGColor = "#C0E0FF";
        $graph->labelBorder = "2px groove white";
        $graph->absValuesColor = "#000000";
        $graph->absValuesBGColor = "#FFFFFF";

        $graph->legend = "downloads";
        echo $graph->create();


        echo "<br/><br/>";


        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>Download statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Username </th>
                        <th scope='col'> Downloads count in MB</th>
                        <th scope='col'> Day of month </th>
                </tr> </thread>";

        $i=0;
        foreach ($array_days as $a_day) {
                echo "<tr>
			<td> $user </td>
                        <td> $array_downloads[$i] </td>
                        <td> $a_day </td>
                </tr>";

                $i++;
        }



	echo "</table>";
	echo "<br/> Total downloads of <u>$total_downloads</u> for user: <u>$user</u> <br/>";

        mysql_free_result($res);
        include 'library/closedb.php';
}





function monthly($username) {

	include ("library/checklogin.php");
	include 'library/config.php';
	include 'library/opendb.php';
        include 'libgraph/graphs.inc.php';

        $sql = "SELECT UserName, SUM(AcctOutputOctets) AS Download, MONTHNAME(AcctStartTime) AS Month from radacct where username='$username' group by Month;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$total_downloads = 0;		// initialize variables
	$count = 0;			

        $array_downloads = array();
        $array_months = array();
        $user = "";

        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +----------+----------+--------+
		// | UserName | Download | Month  |
		// +----------+----------+--------+

        	$user = $ent[0];	// username
        	$downloads = ($ent[1]/1024/1024);	// total downloads on that specific month
        	$month = $ent[2];	// Month of year [1-12]

		$total_downloads = $total_downloads + $downloads;
		$count = $count + 1;

                array_push($array_downloads, "$downloads");
                array_push($array_months, "$month");

        }
        echo "<br/> <center> <h4>Download statistics for user $user</h4> <br/> </center> ";

        //graph information to create
        $graph = new BAR_GRAPH("hBar");
        $graph->labels = $array_months;
        $graph->values = $array_downloads;

        $graph->showValues = 1;
        $graph->barWidth = 12;
        $graph->labelSize = 12;
        $graph->absValuesSize = 12;
        $graph->percValuesSize = 12;
        $graph->graphPadding = 12;
        $graph->graphBGColor = "#ABCDEF";
        $graph->graphBorder = "1px solid blue";
        $graph->barColors = "#A0C0F0";
        $graph->barBGColor = "#E0F0FF";
        $graph->barBorder = "2px outset white";
        $graph->labelColor = "#000000";
        $graph->labelBGColor = "#C0E0FF";
        $graph->labelBorder = "2px groove white";
        $graph->absValuesColor = "#000000";
        $graph->absValuesBGColor = "#FFFFFF";

        $graph->legend = "downloads";
        echo $graph->create();


        echo "<br/><br/>";


        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>Download statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Username </th>
                        <th scope='col'> Downloads count in MB </th>
                        <th scope='col'> Month of year </th>
                </tr> </thread>";

        $i=0;
        foreach ($array_months as $a_month) {
                echo "<tr>
			<td> $user </td>
                        <td> $array_downloads[$i] </td>
                        <td> $a_month </td>
                </tr>";

                $i++;
        }


	echo "</table>";
	echo "<br/> Total downloads of <u>$total_downloads</u> for user: <u>$user</u> <br/>";

        mysql_free_result($res);
        include 'library/closedb.php';
}








function yearly($username) {

	include ("library/checklogin.php");
	include 'library/config.php';
	include 'library/opendb.php';
        include 'libgraph/graphs.inc.php';

        $sql = "SELECT UserName, SUM(AcctOutputOctets) AS Download, YEAR(AcctStartTime) AS Year from radacct where username='$username' group by Year;";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

	$total_downloads = 0;		// initialize variables
	$count = 0;			

        $array_downloads = array();
        $array_years = array();

        $user = "";

        while($ent = mysql_fetch_array($res)) {

		// The table that is being procuded is in the format of:
		// +----------+----------+-------+
		// | UserName | Download | Year  |
		// +----------+----------+-------+

        	$user = $ent[0];	// username
        	$downloads = ($ent[1]/1024/1024);	// total downloads on that specific month
        	$year = $ent[2];	// Year

		$total_downloads = $total_downloads + $downloads;
		$count = $count + 1;

                array_push($array_downloads, "$downloads");
                array_push($array_years, "$year");
        }
        echo "<br/> <center> <h4>Download statistics for user $user</h4> <br/> </center> ";

        //graph information to create
        $graph = new BAR_GRAPH("hBar");
        $graph->labels = $array_years;
        $graph->values = $array_downloads;

        $graph->showValues = 1;
        $graph->barWidth = 12;
        $graph->labelSize = 12;
        $graph->absValuesSize = 12;
        $graph->percValuesSize = 12;
        $graph->graphPadding = 12;
        $graph->graphBGColor = "#ABCDEF";
        $graph->graphBorder = "1px solid blue";
        $graph->barColors = "#A0C0F0";
        $graph->barBGColor = "#E0F0FF";
        $graph->barBorder = "2px outset white";
        $graph->labelColor = "#000000";
        $graph->labelBGColor = "#C0E0FF";
        $graph->labelBorder = "2px groove white";
        $graph->absValuesColor = "#000000";
        $graph->absValuesBGColor = "#FFFFFF";

        $graph->legend = "downloads";
        echo $graph->create();


        echo "<br/><br/>";

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>Download statistics</th>
                                </tr>
                        </thead>
                ";
        echo "<thread> <tr>
                        <th scope='col'> Username </th>
                        <th scope='col'> Downloads count in MB</th>
                        <th scope='col'> Year </th>
                </tr> </thread>";

        $i=0;
        foreach ($array_years as $a_year) {
                echo "<tr>
			<td> $user </td>
                        <td> $array_downloads[$i] </td>
                        <td> $a_year </td>
                </tr>";

                $i++;
        }


	echo "</table>";
	echo "<br/> Total downloads of <u>$total_downloads</u> for user: <u>$user</u> <br/>";

        mysql_free_result($res);
        include 'library/closedb.php';
}





