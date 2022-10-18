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

    // setting table-related parameters first
    switch($configValues['FREERADIUS_VERSION']) {
    case '1':
        $tableSetting['postauth']['user'] = 'user';
        $tableSetting['postauth']['date'] = 'date';
        break;
        
    case '2':
    case '3':
    default:
        $tableSetting['postauth']['user'] = 'username';
        $tableSetting['postauth']['date'] = 'authdate';
        break;
    }
    
    // in other cases we just check that syntax is ok
    $date_regex = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';
    
    $startdate = (array_key_exists('startdate', $_GET) && isset($_GET['startdate']) &&
                  preg_match($date_regex, $_GET['startdate'], $m) !== false &&
                  checkdate($m[2], $m[3], $m[1]))
               ? $_GET['startdate'] : date("Y-m-01");

    $enddate = (array_key_exists('enddate', $_GET) && isset($_GET['enddate']) &&
                preg_match($date_regex, $_GET['enddate'], $m) !== false &&
                checkdate($m[2], $m[3], $m[1]))
             ? $_GET['enddate'] : date("Y-m-01", mktime(0, 0, 0, date('n') + 1, 1, date('Y')));

    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $usernameLastConnect = (array_key_exists('usernameLastConnect', $_GET) && isset($_GET['usernameLastConnect']))
                         ? str_replace("%", "", $_GET['usernameLastConnect']) : "";
    $username_enc = (!empty($usernameLastConnect)) ? htmlspecialchars($usernameLastConnect, ENT_QUOTES, 'UTF-8') : "";
    
    include("menu-reports.php");

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array( 
                   $tableSetting['postauth']['user'] => t('all','Username'),
                   "pass" => t('all','Password'),
                   $tableSetting['postauth']['date'] => t('all','StartTime'),
                   "reply" => t('all','RADIUSReply')
                 );
    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($cols)))
             ? $_GET['orderBy'] : array_keys($cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";

    $radiusReply = (array_key_exists('radiusReply', $_GET) && isset($_GET['radiusReply']) &&
                    in_array($_GET['orderType'], array( "Any", "Access-Accept", "Access-Reject" )))
                 ? $_GET['radiusReply'] : "Any";

?>        
    <div id="contentnorightbar">
        
        <h2 id="Intro">
            <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                <?= t('Intro','replastconnect.php') ?>
                <h144>&#x2754;</h144>
            </a>
        </h2>

        <div id="helpPage" style="display:none;visibility:visible"><?= t('helpPage','replastconnect') ?><br></div>
        <br>

<?php

    include('library/opendb.php');
    include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                          // the CONFIG_IFACE_TABLES_LISTING variable from the config file

    // pa is a placeholder in the SQL statements below
    // except for $usernameLastConnect, which has been only partially escaped,
    // all other query parameters have been validated earlier.
    $sql_WHERE = array();
    if (!empty($usernameLastConnect)) {
        $sql_WHERE[] = sprintf("pa.%s LIKE '%s%%'", $tableSetting['postauth']['user'],
                                                    $dbSocket->escapeSimple($usernameLastConnect));
    }
    $sql_WHERE[] = sprintf("pa.%s BETWEEN '%s' AND '%s'", $tableSetting['postauth']['date'],
                                                          $dbSocket->escapeSimple($startdate),
                                                          $dbSocket->escapeSimple($enddate));
    if ($radiusReply != "Any") {
        $sql_WHERE[] = sprintf("pa.reply='%s'", $dbSocket->escapeSimple($radiusReply));
    }
    
    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADPOSTAUTH'];
    $_SESSION['reportQuery'] = " WHERE " . implode(" AND ", $sql_WHERE);
    $_SESSION['reportType'] = "reportsLastConnectionAttempts";

    //orig: used as maethod to get total rows - this is required for the pages_numbering.php page 
    $sql_format = "SELECT pa.%s, pa.pass, pa.reply, pa.%s FROM %s AS pa";
    
    $sql = sprintf($sql_format, $tableSetting['postauth']['user'],
                                $tableSetting['postauth']['date'],
                                $configValues['CONFIG_DB_TBL_RADPOSTAUTH'])
         . $_SESSION['reportQuery'];
    
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        $sql .= sprintf(" ORDER BY pa.%s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        /* START - Related to pages_numbering.php */
        $maxPage = ceil($numrows/$rowsPerPage);
        /* END */
    
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_params = array();
        if (!empty($startdate)) {
            $partial_query_params[] = sprintf("startdate=%s", $startdate);
        }
        if (!empty($enddate)) {
            $partial_query_params[] = sprintf("enddate=%s", $enddate);
        }
        if (!empty($username_enc)) {
            $partial_query_params[] = sprintf("usernameLastConnect=%s", urlencode($username_enc));
        }
        if (!empty($radiusReply)) {
            $partial_query_params[] = sprintf("radiusReply=%s", $radiusReply);
        }
        
        $partial_query_string = ((count($partial_query_params) > 0) ? "&" . implode("&", $partial_query_params)  : "");

?>
    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($maxPage > 1) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>';
        }
?>
            </tr>
            
            <tr>
<?php

        // a standard way of creating table headings
        foreach ($cols as $param => $caption) {
            
            if (is_int($param)) {
                $ordering_controls = "";
            } else {
                $title_format = "order by %s, sort %s";
                $title_asc = sprintf($title_format, strip_tags($caption), "ascending");
                $title_desc = sprintf($title_format, strip_tags($caption), "descending");

                $href_format = "?orderBy=%s&orderType=%s" . $partial_query_string;
                $href_asc = sprintf($href_format, $param, "asc");
                $href_desc = sprintf($href_format, $param, "desc");

                $img_format = '<img src="%s" alt="%s">';
                $img_asc = sprintf($img_format, 'images/icons/arrow_up.png', '^');
                $img_desc = sprintf($img_format, 'images/icons/arrow_down.png', 'v');

                $enabled_a_format = '<a title="%s" class="novisit" href="%s">%s</a>';
                $disabled_a_format = '<a title="%s" role="link" aria-disabled="true">%s</a>';

                if ($orderBy == $param) {
                    if ($orderType == "asc") {
                        $link_asc = sprintf($disabled_a_format, $title_asc, $img_asc);
                        $link_desc = sprintf($enabled_a_format, $title_asc, $href_desc, $img_desc);
                    } else {
                        $link_asc = sprintf($enabled_a_format, $title_asc, $href_asc, $img_asc);
                        $link_desc = sprintf($disabled_a_format, $title_desc, $img_desc);
                    }
                } else {
                    $link_asc = sprintf($enabled_a_format, $title_asc, $href_asc, $img_asc);
                    $link_desc = sprintf($enabled_a_format, $title_asc, $href_desc, $img_desc);
                }
                
                $ordering_controls = $link_asc . $link_desc;
            }
            
            echo "<th>" . $caption . $ordering_controls . "</th>";
        }
?>
            </tr>
        </thead>

        <tbody>
<?php
        while ($row = $res->fetchRow()) {

        // The table that is being produced is in the format of:
        // +-------------+-------------+---------------+-----------+
        // | user        | pass        | reply         | date      |   
        // +-------------+-------------+---------------+-----------+

            list($user, $pass, $reply, $starttime) = $row;
?>

            <tr>
                <td><?= htmlspecialchars($user, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= ($configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] == "yes") ? "[Password is hidden]"
                                                                                 : htmlspecialchars($pass, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($starttime, ENT_QUOTES, 'UTF-8') ?></td>
                <td style="color: <?= ($reply == "Access-Reject") ? "red" : "green"?>"><?= htmlspecialchars($reply, ENT_QUOTES, 'UTF-8') ?></td>
            </tr>

<?php
        }
?>
        </tbody>

        <tfoot>
            <tr>
                <th scope="col" colspan="<?= $colspan ?>">
<?php
                    echo "displayed <strong>$per_page_numrows</strong> record(s)";
                    if ($maxPage > 1) {
                        echo " out of <strong>$numrows</strong>";
                    }
?>
                </th>
            </tr>

<?php
        // page navigation controls are shown only if there is more than one page
        if ($maxPage > 1) {
?>
            <tr>
                <th scope="col" colspan="<?= $colspan ?>" style="background-color: white; text-align: center">
                    <?= setupLinks($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string) ?>
                </th>
            </tr>
<?php
        }
?>
        </tfoot>

    </table>
<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');
?>

        </div><!-- #contentnorightbar -->
                
        <div id="footer">
                
<?php
    $log = "visited page: ";
    $logQuery = "performed query on page: ";

    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->

    </div>
</div>

</body>
</html>
