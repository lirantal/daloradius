<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";


	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for all accounting records on page: ";
    include('include/config/logging.php');

?>

<?php
	
	include("menu-accounting.php");
	
?>

		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][acctall.php]?></a></h2>
				
				<p>
				</p>



<?php

                include 'library/opendb.php';

	$sql = "SELECT ".$configValues['CONFIG_DB_TBL_RADACCT'].".RadAcctId, ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".name as hotspot, ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName, ".$configValues['CONFIG_DB_TBL_RADACCT'].".FramedIPAddress, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctStopTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctInputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctOutputOctets, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctTerminateCause, ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." LEFT JOIN ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS']." ON ".$configValues['CONFIG_DB_TBL_RADACCT'].".calledstationid = ".$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'].".mac  ORDER BY $orderBy $orderType;";
	$res = mysql_query($sql) or die('<font color="#FF0000"> Query failed: ' . mysql_error() . "</font>");

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='15'>".$l[all][Records]."</th>
                                </tr>
                        </thead>
                ";

        echo "<thread> <tr>
		<th scope='col'> ".$l[all][ID]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=radacctid&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=radacctid&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][HotSpot]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=hotspot&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=hotspot&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Username]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][IPAddress]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=framedipaddress&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=framedipaddress&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][StartTime]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstarttime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstarttime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][StopTime]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstoptime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctstoptime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][TotalTime]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctsessiontime&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctsessiontime&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Upload]." (".$l[all][Bytes].")
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctinputoctets&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctinputoctets&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Download]." (".$l[all][Bytes].")
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctoutputoctets&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctoutputoctets&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][Termination]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctterminatecause&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=acctterminatecause&orderType=desc\"> < </a>
		</th>
		<th scope='col'> ".$l[all][NASIPAddress]."
		<br/>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasipaddress&orderType=asc\"> > </a>
		<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=nasipaddress&orderType=desc\"> < </a>
		</th>
        <th scope='col'> ".$l[all][Action]." </th>
                </tr> </thread>";
        while($nt = mysql_fetch_array($res)) {
                echo "<tr>
                        <td> $nt[0] </td>
                        <td> $nt[1] </td>
                        <td> $nt[2] </td>
                        <td> $nt[3] </td>
                        <td> $nt[4] </td>
                        <td> $nt[5] </td>
                        <td> $nt[6] </td>
                        <td> $nt[7] </td>
                        <td> $nt[8] </td>
                        <td> $nt[9] </td>
                        <td> $nt[10] </td>
                        <td> <a href='mng-edit.php?username=$nt[UserName]'> ".$l[all][edit]." </a> </td>
                </tr>";
        }
        echo "</table>";

        mysql_free_result($res);
        include 'library/closedb.php';
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
