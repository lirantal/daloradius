<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


        //setting values for the order by and order type variables
        isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "id";
        isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "desc";



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

        $sql = "SELECT user, pass, reply, date from radpostauth";
        $res = $dbSocket->query($sql);
	$numrows = $res->numRows();

        $sql = "SELECT user, pass, reply, date from radpostauth ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
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
        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='10'>".$l['all']['Records']."</th>
                                </tr>

                                                        <tr>
                                                        <th colspan='10' align='left'>
                <br/>
        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

        echo " </th></tr>
                                        </thead>

                        ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l['all']['Username']." 
			<br/>
                        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=user&orderType=asc\"> > </a>
                        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=user&orderType=desc\"> < </a>
			</th>
                        <th scope='col'> ".$l['all']['Password']." 
			<br/>
                        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=pass&orderType=asc\"> > </a>
                        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=pass&orderType=desc\"> < </a>
			</th>
                        <th scope='col'> ".$l['all']['StartTime']." 
			<br/>
                        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=date&orderType=asc\"> > </a>
                        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=date&orderType=desc\"> < </a>
			</th>
                        <th scope='col'> ".$l['all']['RADIUSReply']." 
			<br/>
                        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=reply&orderType=asc\"> > </a>
                        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=reply&orderType=desc\"> < </a>
			</th>
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

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='10' align='left'>
        ";
        setupLinks($pageNum, $maxPage, $orderBy, $orderType);
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
