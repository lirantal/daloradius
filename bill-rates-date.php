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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');

    // init loggin variables
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";
    $logDebugSQL = "";

    //setting values for the order by and order type variables
    isset($_GET['orderBy']) ? $orderBy = $_GET['orderBy'] : $orderBy = "radacctid";
    isset($_GET['orderType']) ? $orderType = $_GET['orderType'] : $orderType = "asc";

    isset($_GET['ratename']) ? $ratename = $_GET['ratename'] : $ratename = "";
    
    //setting values for the order by and order type variables
    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? trim(str_replace("%", "", $_GET['username'])) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    // in other cases we just check that syntax is ok
    $date_regex = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';
    
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match($date_regex, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : "";

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match($date_regex, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : "";

    //feed the sidebar variables
    $billing_date_ratename = $ratename;
    $billing_date_username = $username;
    $billing_date_startdate = $startdate;
    $billing_date_enddate = $enddate;

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array();
    
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/ajaxGeneric.js",
    );
    
    $title = t('Intro','billratesdate.php');
    $help = t('helpPage','billratesdate');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);


    include("menu-bill-rates.php");
    
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include 'library/opendb.php';
    include 'include/management/pages_common.php';
    include 'include/management/pages_numbering.php';        // must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

    // we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object
    $username = $dbSocket->escapeSimple($username);
    $startdate = $dbSocket->escapeSimple($startdate);
    $enddate = $dbSocket->escapeSimple($enddate);
    $ratename = $dbSocket->escapeSimple($ratename);

        include_once('include/management/userBilling.php');
        userBillingRatesSummary($username, $startdate, $enddate, $ratename, 1);                // draw the billing rates summary table

        include 'library/opendb.php';

    // get rate type
    $sql = "SELECT rateType FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateName = '$ratename'";
    $res = $dbSocket->query($sql);

    if ($res->numRows() == 0)
        $failureMsg = "Rate was not found in database, check again please";
    else {

        $row = $res->fetchRow();
        list($ratetypenum, $ratetypetime) = explode("/",$row[0]);

        switch ($ratetypetime) {                    // we need to translate any kind of time into seconds, so a minute is 60 seconds, an hour is 3600,
            case "second":                        // and so on...
                $multiplicate = 1;
                break;
            case "minute":
                $multiplicate = 60;
                break;
            case "hour":
                $multiplicate = 3600;
                break;
            case "day":
                $multiplicate = 86400;
                break;
            case "week":
                $multiplicate = 604800;
                break;
            case "month":
                $multiplicate = 187488000;            // a month is 31 days
                break;
            default:
                $multiplicate = 0;
                break;
        }

        // then the rate cost would be the amount of seconds times the prefix multiplicator thus:
        $rateDivisor = ($ratetypenum * $multiplicate);
    }

    //orig: used as maethod to get total rows - this is required for the pages_numbering.php page
    $sql = "SELECT distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".username), ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, ".
        $configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".
        $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateCost ".
        " FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE (AcctStartTime >= '$startdate') and (AcctStartTime <= '$enddate') and (UserName = '$username') and (".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateName = '$ratename')";
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();


    $sql = "SELECT distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".username), ".$configValues['CONFIG_DB_TBL_RADACCT'].".NASIPAddress, ".
        $configValues['CONFIG_DB_TBL_RADACCT'].".AcctStartTime, ".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime, ".
        $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateCost ".
        " FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES']." WHERE (AcctStartTime >= '$startdate') and (AcctStartTime <= '$enddate') and (UserName = '$username') and (".$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'].".rateName = '$ratename')".
        " ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
    $res = $dbSocket->query($sql);
    $logDebugSQL = "";
    $logDebugSQL .= $sql . "\n";

    /* START - Related to pages_numbering.php */
    $maxPage = ceil($numrows/$rowsPerPage);
    /* END */



    if (isset($failureMsg)) {
        include_once('include/management/actionMessages.php');
        echo "<br/>";
    }


    echo "<table border='0' class='table1'>\n";
        echo "
                <thead>
                        <tr>
                        <th colspan='12' align='left'>

                        <br/>
                <br/>
        ";

    if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
        setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate");

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
        <br/>
        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate&orderBy=username&orderType=$orderTypeNextPage\">
        ".t('all','Username')."</a>
        </th>
        <th scope='col'>
        <br/>
        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate&orderBy=nasipaddress&orderType=$orderTypeNextPage\">
        ".t('all','NASIPAddress')."</a>
        </th>
        <th scope='col'>
        <br/>
        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate&orderBy=acctstarttime&orderType=$orderTypeNextPage\">
        ".t('all','LastLoginTime')."</a>
        </th>
        <th scope='col'>
        <br/>
        <a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate&orderBy=acctsessiontime&orderType=$orderTypeNextPage\">
        ".t('all','TotalTime')."</a>
        </th>
        <th scope='col'>
        <br/>
         ".t('all','Billed')."
        </th>
                </tr> </thread>";

    $sumBilled = 0;
    $sumSession = 0;

    while($row = $res->fetchRow()) {

        $sessionTime = $row[3];
        $rateCost = $row[4];
        $billed = (($sessionTime/$rateDivisor)*$rateCost);
        $sumBilled += $billed;
        $sumSession += $sessionTime;

        echo "<tr>
                <td> $row[0] </td>
                <td> $row[1] </td>
                <td> $row[2] </td>
                <td> ".time2str($row[3])." </td>
                <td> ".number_format($billed,2)." </td>
        </tr>";

    }

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='12' align='left'>
        ";
    setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&username=$username&ratename=$ratename&startdate=$startdate&enddate=$enddate");
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";

    echo "</table>";

    include 'library/closedb.php';
?>

        </div>


<?php
    include('include/config/logging.php');
?>

        <div id="footer">

                                <?php
        include 'page-footer.php';
?>


        </div>

</div>
</div>

</body>
</html>
