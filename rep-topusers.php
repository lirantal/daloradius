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

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');
    
    include_once('library/config_read.php');
    
    $limit = (array_key_exists('limit', $_GET) && isset($_GET['limit']) && intval($_GET['limit']) > 0)
		   ? intval($_GET['limit']) : "";
    
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

    // and in other cases we partially strip some character,
    // and leave validation/escaping to other functions used later in the script
    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? str_replace("%", "", $_GET['username']) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";
    
    include ("menu-reports.php");

    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    'username' => t('all','Username'),
                    'framedipaddress' => t('all','IPAddress'),
                    'acctstarttime' => t('all','StartTime'),
                    'acctstoptime' => t('all','StopTime'),
                    'Time' => t('all','TotalTime'),
                    'Upload' => t('all','Upload') . " (" . t('all','Bytes') . ")",
                    'Download' => t('all','Download') . " (" . t('all','Bytes') . ")",
                    'acctterminatecause' => t('all','Termination'),
                    'nasipaddress' => t('all','NASIPAddress')
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

?>	
		<div id="contentnorightbar">
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','reptopusers.php'); ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>
				

            <div id="helpPage" style="display:none;visibility:visible">
                <?= t('helpPage','reptopusers') . " $orderBy" ?>
                <br>
            </div>
            <br>

<?php

    include('library/opendb.php');
    include('include/management/pages_common.php');
    include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                          // the CONFIG_IFACE_TABLES_LISTING variable from the config file
	
    // the partial query is built starting from user input
    // and for being passed to setupNumbering and setupLinks functions
    $partial_query_params = array();
    
    // creating $sql_WHERE for SQL query
    $sql_WHERE = array();
    $sql_WHERE[] = "AcctStopTime > '0000-00-00 00:00:01'";
    if (!empty($startdate)) {
        $partial_query_params[] = sprintf("startdate=%s", urlencode(htmlspecialchars($startdate, ENT_QUOTES, 'UTF-8')));
        $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($startdate));
    }
    
    if (!empty($enddate)) {
        $partial_query_params[] = sprintf("enddate=%s", urlencode(htmlspecialchars($enddate, ENT_QUOTES, 'UTF-8')));
        $sql_WHERE[] = sprintf("AcctStartTime > '%s'", $dbSocket->escapeSimple($enddate));
    }
    
    if (!empty($username)) {
        $partial_query_params[] = sprintf("username=%s", urlencode($username_enc));
        $sql_WHERE[] = sprintf("username LIKE '%s%'", $dbSocket->escapeSimple($username));
    }
    
    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
    $_SESSION['reportQuery'] = " WHERE " . implode(" AND ", $sql_WHERE);
    $_SESSION['reportType'] = "TopUsers";
    
    $sql = "SELECT DISTINCT(ra.username) AS username, ra.FramedIPAddress, ra.AcctStartTime, MAX(ra.AcctStopTime), 
                   SUM(ra.AcctSessionTime) AS Time, SUM(ra.AcctInputOctets) AS Upload,
                   SUM(ra.AcctOutputOctets) AS Download, ra.AcctTerminateCause, ra.NASIPAddress,
                   SUM(ra.AcctInputOctets + ra.AcctOutputOctets) AS Bandwidth
            FROM " . $configValues['CONFIG_DB_TBL_RADACCT'] . " AS ra";
    $sql .= " WHERE " . implode(" AND ", $sql_WHERE) . " GROUP BY username";
    
    if (!empty($limit)) {
        $partial_query_params[] = sprintf("limit=%d", $limit);
        $res = $dbSocket->query($sql . sprintf(" LIMIT %d", $limit));
    } else {
        $res = $dbSocket->query($sql);
    }
    
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        /* START - Related to pages_numbering.php */
        $maxPage = ceil($numrows/$rowsPerPage);
        /* END */
        
        $per_page_numrows = $res->numRows();
        
        $partial_query_string = ((count($partial_query_params) > 0) ? "&" . implode("&", $partial_query_params)  : "");
?>

    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
    // page numbers are shown only if there is more than one page
    if ($maxPage > 1) {
        printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
        setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
        echo '</td>';
    }
?>
                <td style="text-align: right" colspan="<?= ($maxPage > 1) ? $half_colspan : $colspan ?>">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>

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
?>
            <tr>
                <td><?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8') ?></td>
                
                <td><?= htmlspecialchars(time2str($row[4]), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(toxbyte($row[5]), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(toxbyte($row[6]), ENT_QUOTES, 'UTF-8') ?></td>
                
                <td><?= htmlspecialchars($row[7], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row[8], ENT_QUOTES, 'UTF-8') ?></td>
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
    $logQuery = "performed query for [$orderBy";
    if (!empty($limit)) {
        $logQuery .= " : $limit";
    }
    $logQuery .= "] on page: ";

    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
        
    </div>
</div>

</body>
</html>
