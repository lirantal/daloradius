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
*
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


        //setting values for the order by and order type variables
        isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
        isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "desc";

        isset($_REQUEST['usernameLastConnect']) ? $usernameLastConnect = $_GET['usernameLastConnect'] : $usernameLastConnect = "%";

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";


?>

<?php

    include ("menu-reports.php");
        	
?>		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['replastconnect.php']; ?>
		<h144>+</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['replastconnect'] ?>
			<br/>
		</div>
		<br/>

<?php

        include 'library/opendb.php';
        include 'include/management/pages_numbering.php';               // must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

        // setup php session variables for exporting
        $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
        $_SESSION['reportQuery'] = " WHERE (User LIKE '".$dbSocket->escapeSimple($usernameLastConnect)."%')";
        $_SESSION['reportType'] = "reportsLastConnectionAttempts";

	$sql = "SELECT rp.username FROM ".$row['postauth']['user']." as rp ".
		" WHERE (".$row['postauth']['user']." LIKE '".$dbSocket->escapeSimple($usernameLastConnect)."%') ".
        $res = $dbSocket->query($sql);
	$numrows = $res->numRows();

	$sql = "SELECT rp.".$row['postauth']['user'].", rp.pass, rp.reply, rp.".$row['postauth']['date'].", n.shortname FROM ".$configValues['CONFIG_DB_TBL_RADPOSTAUTH']." as rp ".
                " JOIN ".$configValues['CONFIG_DB_TBL_RADACCT']." AS r ON (r.username = rp.".$row['postauth']['user']." and r.acctstarttime = rp.authdate) ".
                " JOIN ".$configValues['CONFIG_DB_TBL_RADNAS']." AS n ON (n.nasname = r.nasipaddress) ".
                " WHERE (rp.".$row['postauth']['user']." LIKE '".$dbSocket->escapeSimple($usernameLastConnect)."%') ".
		" ORDER BY rp.$orderBy $orderType LIMIT $offset, $rowsPerPage";

        $res = $dbSocket->query($sql);
        $logDebugSQL = "";
        $logDebugSQL .= $sql . "\n";

        /* START - Related to pages_numbering.php */
        $maxPage = ceil($numrows/$rowsPerPage);
        /* END */

        $array_users = array();
        $array_pass = array();
        $array_starttime = array();
        $array_reply = array();
        $array_nasshortname = array();
	$count = 0;

        while($row = $res->fetchRow()) {

                // The table that is being procuded is in the format of:
                // +-------------+-------------+---------------+----------------------------------------+
                // | user        | pass        | reply         | date                | nasshortname     |
                // +-------------+-------------+---------------+----------------------------------------+


                $user = $row[0];
                $pass = $row[1];
                $starttime = $row[3];
                $reply = $row[2];
                $nasshortname = $row[4];	

                array_push($array_users, "$user");
                array_push($array_pass, "$pass");
                array_push($array_starttime, "$starttime");
                array_push($array_reply, "$reply");
		array_push($array_nasshortname, "$nasshortname");

                $count++;

        }
        // creating the table:
        echo "<table border='0' class='table1'>\n";
        echo "
                        <thead>

                                                        <tr>
                                                        <th colspan='10' align='left'>

                                <input class='button' type='button' value='CSV Export'
                                        onClick=\"javascript:window.location.href='include/management/fileExport.php?reportFormat=csv'\"
                                        />
		                <br/><br/>
        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, "&usernameLastConnect=$usernameLastConnect");

        echo " </th></tr>
                                        </thead>

                        ";

        if ($orderType == "asc") {
                $orderTypeNextPage = "desc";
        } else  if ($orderType == "desc") {
                $orderTypeNextPage = "asc";
        }

        echo "<thread> <tr>
                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameLastConnect=$usernameLastConnect&orderBy=".$row['postauth']['user']."&orderType=$orderTypeNextPage\">
		".$l['all']['Username']." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameLastConnect=$usernameLastConnect&orderBy=pass&orderType=$orderTypeNextPage\">
		".$l['all']['Password']." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameLastConnect=$usernameLastConnect&orderBy=".$row['postauth']['user']."&orderType=$orderTypeNextPage\">
		".$l['all']['StartTime']." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameLastConnect=$usernameLastConnect&orderBy=reply&orderType=$orderTypeNextPage\">
		".$l['all']['RADIUSReply']." 
		</th>

		<th scope='col'>
		<a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?usernameLastConnect=$usernameLastConnect&orderBy=reply&orderType=$orderTypeNextPage\">
		".$l['all']['NASShortName']."
		<th>

        </tr> </thread>";

        $i = 0;
        while ($i != $count) {

		if ($array_reply[$i] == "Access-Reject")
			$reply = "<font color='red'> $array_reply[$i] </font>";
		else
			$reply = $array_reply[$i];

                echo "<tr>
                        <td> $array_users[$i] </td>
                        <td> $array_pass[$i] </td>
                        <td> $array_starttime[$i] </td>
                        <td> $reply </td>
			<td> $array_nasshortname[$i] </td>
                </tr>";
                $i++;
        }

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='5' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType, "&usernameLastConnect=$usernameLastConnect");
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";


        echo "</table>";

        include 'library/closedb.php';

?>


<?php
	include('include/config/logging.php');
?>

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
