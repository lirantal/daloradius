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
        isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "updatedate,creationdate";
        isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "desc";



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";


?>

<?php

    include ("menu-reports.php");
        	
?>		
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','rephistory.php'); ?>
		<h144>&#x2754;</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo t('helpPage','rephistory') ?>
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
        " (SELECT 'invoice' as section, id as item, creationdate,creationby,updatedate,updateby FROM invoice) UNION ".
        " (SELECT 'payment' as section, id as item, creationdate,creationby,updatedate,updateby FROM payment) UNION ".
		" (SELECT 'hotspot' as section, name as item, creationdate,creationby,updatedate,updateby FROM hotspots) ";
        $res = $dbSocket->query($sql);
	$numrows = $res->numRows();

        $sql = "(SELECT 'proxy' as section, proxyname as item, creationdate,creationby,updatedate,updateby FROM proxys) UNION ".
		" (SELECT 'realm' as section, realmname as item, creationdate,creationby,updatedate,updateby FROM realms) UNION ".
		" (SELECT 'userinfo' as section, username as item, creationdate,creationby,updatedate,updateby FROM userinfo) UNION ".
		" (SELECT 'operators' as section, username as item, creationdate,creationby,updatedate,updateby FROM operators) UNION ".
        " (SELECT 'invoice' as section, id as item, creationdate,creationby,updatedate,updateby FROM invoice) UNION ".
        " (SELECT 'payment' as section, id as item, creationdate,creationby,updatedate,updateby FROM payment) UNION ".
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
                $orderTypeNextPage = "desc";
        } else  if ($orderType == "desc") {
                $orderTypeNextPage = "asc";
        }

        echo "<thread> <tr>
                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=section&orderType=$orderTypeNextPage\">
		".t('all','Section')." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=item&orderType=$orderTypeNextPage\">
		".t('all','Item')." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=creationdate&orderType=$orderTypeNextPage\">
		".t('all','CreationDate')." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=creationby&orderType=$orderTypeNextPage\">
		".t('all','CreationBy')." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=updatedate&orderType=$orderTypeNextPage\">
		".t('all','UpdateDate')." 
		</th>

                <th scope='col'>
                <a title='Sort' class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=updateby&orderType=$orderTypeNextPage\">
		".t('all','UpdateBy')." 
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
