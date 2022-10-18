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
    
    include ("menu-reports.php");

    // these three variable can be used for validation an presentation purpose
    $cols = array(
                    "section" => t('all','Section'), 
                    "item" => t('all','Item'), 
                    "creationdate" => t('all','CreationDate'), 
                    "creationby" => t('all','CreationBy'), 
                    "updatedate" => t('all','UpdateDate'), 
                    "updateby" => t('all','UpdateBy')
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
                    <?= t('Intro','rephistory.php'); ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>
            
            <div id="helpPage" style="display:none;visibility:visible" ><?= t('helpPage','rephistory') ?><br></div>
            <br>

<?php

    include('include/management/pages_common.php');
    include('library/opendb.php');
    include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                          // the CONFIG_IFACE_TABLES_LISTING variable from the config file

    // we use this convenient way to build our SQL query
    $sql_piece_format = "SELECT '%s' AS section, %s AS item, creationdate, creationby, updatedate, updateby FROM %s";

    $sql_pieces = array(
        sprintf($sql_piece_format, 'proxy', 'proxyname', $configValues['CONFIG_DB_TBL_DALOPROXYS']),
        sprintf($sql_piece_format, 'realm', 'realmname', $configValues['CONFIG_DB_TBL_DALOREALMS']),
        sprintf($sql_piece_format, 'userinfo', 'username', $configValues['CONFIG_DB_TBL_DALOUSERINFO']),
        sprintf($sql_piece_format, 'operators', 'username', $configValues['CONFIG_DB_TBL_DALOOPERATORS']),
        sprintf($sql_piece_format, 'invoice', 'id', $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE']),
        sprintf($sql_piece_format, 'payment', 'id', $configValues['CONFIG_DB_TBL_DALOPAYMENTS']),
        sprintf($sql_piece_format, 'hotspot', 'name', $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'])
    );
    
    $sql = implode(" UNION ", $sql_pieces);
    
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();

    if ($numrows > 0) {
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";

        /* START - Related to pages_numbering.php */
        $maxPage = ceil($numrows/$rowsPerPage);
        /* END */
        
        $per_page_numrows = $res->numRows();
?>
  
          <table border="0" class="table1">
            <thead>
                <tr style="background-color: white">
<?php
        // page numbers are shown only if there is more than one page
        if ($maxPage > 1) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $colspan);
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
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
                $section = htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
                $item = htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8');
                $creationdate = htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8');
                $creationby = htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8');
                $updatedate = htmlspecialchars($row[4], ENT_QUOTES, 'UTF-8');
                $updateby = htmlspecialchars($row[5], ENT_QUOTES, 'UTF-8');
?>
                <tr>
                    <td><?= $section ?></td>
                    <td><?= $item ?></td>
                    <td><?= $creationdate ?></td>
                    <td><?= $creationby ?></td>
                    <td><?= $updatedate ?></td>
                    <td><?= $updateby ?></td>
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
                        <?= setupLinks($pageNum, $maxPage, $orderBy, $orderType) ?>
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
