<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');


        //setting values for the order by and order type variables
        isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "updatedate";
        isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "desc";



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";


?>

<?php

    include ("menu-reports.php");
        	
?>		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['rephistory.php']; ?>
		<h144>+</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['rephistory'] ?>
			<br/>
		</div>
		<br/>

<?php

        include 'include/management/pages_common.php';
        include 'library/opendb.php';
        include 'include/management/pages_numbering.php';               // must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

        $sql = "(SELECT 'proxy' as section, proxyname as item, creationdate,creationby,updatedate,updateby FROM proxys) UNION ".
		" (SELECT 'realm' as section, realmname as item, creationdate,creationby,updatedate,updateby FROM realms) UNION ".
		" (SELECT 'userinfo' as section, username as item, creationdate,creationby,updatedate,updateby FROM userinfo) UNION ".
		" (SELECT 'operators' as section, username as item, creationdate,creationby,updatedate,updateby FROM operators) UNION ".
		" (SELECT 'hotspot' as section, name as item, creationdate,creationby,updatedate,updateby FROM hotspots) ";
        $res = $dbSocket->query($sql);
	$numrows = $res->numRows();

        $sql = "(SELECT 'proxy' as section, proxyname as item, creationdate,creationby,updatedate,updateby FROM proxys) UNION ".
		" (SELECT 'realm' as section, realmname as item, creationdate,creationby,updatedate,updateby FROM realms) UNION ".
		" (SELECT 'userinfo' as section, username as item, creationdate,creationby,updatedate,updateby FROM userinfo) UNION ".
		" (SELECT 'operators' as section, username as item, creationdate,creationby,updatedate,updateby FROM operators) UNION ".
		" (SELECT 'hotspot' as section, name as item, creationdate,creationby,updatedate,updateby FROM hotspots) ".
		" ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage";
        $res = $dbSocket->query($sql);
        $logDebugSQL = "";
        $logDebugSQL .= $sql . "\n";

        /* START - Related to pages_numbering.php */
        $maxPage = ceil($numrows/$rowsPerPage);
        /* END */

        // creating the table:
        echo "<table border='0' class='table1'>\n";
        echo "
                        <thead>

                                                        <tr>
                                                        <th colspan='10' align='left'>
                <br/>
        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
                setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);

        echo " </th></tr>
                                        </thead>

                        ";

        if ($orderType == "asc") {
                $orderType = "desc";
        } else  if ($orderType == "desc") {
                $orderType = "asc";
        }

        echo "<thread> <tr>
                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=section&orderType=$orderType\">
		".$l['all']['Section']." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=item&orderType=$orderType\">
		".$l['all']['Item']." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=creationdate&orderType=$orderType\">
		".$l['all']['CreationDate']." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=creationby&orderType=$orderType\">
		".$l['all']['CreationBy']." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=updatedate&orderType=$orderType\">
		".$l['all']['UpdateDate']." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=updateby&orderType=$orderType\">
		".$l['all']['UpdateBy']." 
		</th>
        </tr> </thread>";


        while($row = $res->fetchRow()) {

	        printqn("<tr>
                                <td> $row[0] </td>
                                <td> $row[1] </td>
                                <td> $row[2] </td>
                                <td> $row[3] </td>
                                <td> $row[4] </td>
                                <td> $row[5] </td>
                </tr>");

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
