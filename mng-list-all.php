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

	// set session's page variable
	$_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

	include_once('library/config_read.php');
    
    include_once("lang/main.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS :: <?= t('Intro','mnglistall.php') ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="css/1.css" media="screen">
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">
    <link rel="stylesheet" href="library/js_date/datechooser.css">
    <!--[if lte IE 6.5]>
    <link rel="stylesheet" href="library/js_date/select-free.css">
    <![endif]-->
    
    <script src="library/javascript/pages_common.js"></script>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>

    <script src="library/javascript/ajax.js"></script>
    <script src="library/javascript/ajaxGeneric.js"></script>
</head>

<?php
    include("menu-mng-users.php");
    
    // the array $cols has multiple purposes:
    // - its keys (when non-numerical) can be used
    //   - for validating user input
    //   - for table ordering purpose
    // - its value can be used for table headings presentation
    $cols = array(
                    "id" => t('all','ID'),
                    t('all','Name'),
                    "username" => t('all','Username'),
                 );

    if (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) === "yes") {
        $cols[] = t('all','Password');
    } else {
        $cols["auth"] = t('all','Password');
    }
    
    $cols[] = t('title','Groups');

    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
                 
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }
    
    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "asc";
?>

		<div id="contentnorightbar">
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','mnglistall.php') ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>
            
            <div id="helpPage" style="display:none;visibility:visible"><?= t('helpPage','mnglistall') ?><br></div>
            <div id="returnMessages"></div>
            <br>

<?php

    include('library/opendb.php');
    include('include/management/pages_common.php');
    include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                          // the CONFIG_IFACE_TABLES_LISTING variable from the config file

	// setup php session variables for exporting
	$_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADCHECK'];
	$_SESSION['reportQuery'] = "";
	$_SESSION['reportType'] = "usernameListGeneric";

    // we use this simplified query just to initialize $numrows
    $sql0 = sprintf("SELECT COUNT(DISTINCT(username)) AS username
                       FROM %s
                      WHERE attribute='Auth-Type' OR attribute LIKE '%%-Password'", $configValues['CONFIG_DB_TBL_RADCHECK']);
    $res = $dbSocket->query($sql0);
    $numrows = $res->fetchrow()[0];
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        $maxPage = ceil($numrows/$rowsPerPage);
        /* END */
        
        # sql1 get id, username, password, firstname and lastname
        $sql1 = sprintf("SELECT DISTINCT(rc.username) AS username, rc.value AS auth, rc.id AS id, ui.firstname, ui.lastname
                           FROM %s AS rc, %s AS ui
                          WHERE rc.username=ui.username
                            AND (rc.attribute='Auth-Type' OR rc.attribute LIKE '%%-Password')
                          ORDER BY %s %s LIMIT %s, %s",
                        $configValues['CONFIG_DB_TBL_RADCHECK'], $configValues['CONFIG_DB_TBL_DALOUSERINFO'],
                        $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql1);
        $logDebugSQL = "$sql1;\n";
        
        $per_page_numrows = $res->numRows();
        
        // init $records and $usernamelist arrays
        $records = array();
        $usernamelist = array();
        
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            // we start storing data...
            // the enable flag is initialized to true
            // and the groups list is empty
            $this_username = $row['username'];
            
            $records[$this_username] = array(
                'auth' => $row['auth'],
                'id' => intval($row['id']),
                'fullname' => $row['firstname'] . " " . $row['lastname'],
                'enabled' => true,
                'groups' => array()
            );
            // in the same pass we init the $usernamelist
            $usernamelist[] = sprintf("'%s'", $dbSocket->escapeSimple($this_username));
        }
        
        // with this second query we retrieve user status (enabled/disabled) and user groups list
        $sql2 = sprintf("SELECT username, groupname FROM %s WHERE username IN (%s)",
                        $configValues['CONFIG_DB_TBL_RADUSERGROUP'], implode(", ", $usernamelist));
        $res = $dbSocket->query($sql2);
        $logDebugSQL .= "$sql2;\n";

        // foreach user we update the enabled flag and the grouplist
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $this_username = $row['username'];
            $this_groupname = $row['groupname'];

            if ($this_groupname === 'daloRADIUS-Disabled-Users') {
                $records[$this_username]['enabled'] = false;
            } else {
                array_push($records[$this_username]['groups'],
                           htmlspecialchars($this_groupname, ENT_QUOTES, 'UTF-8'));
            }
        }
?>

<form name="listallusers" method="GET" action="mng-del.php">

    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
    // page numbers are shown only if there is more than one page
    if ($maxPage > 1) {
        printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
        setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType);
        echo '</td>';
    }
?>
                <td style="text-align: right" colspan="<?= ($maxPage > 1) ? $half_colspan : $colspan ?>">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>

            </tr>

            <tr>
                <th style="text-align: left" colspan="<?= $colspan ?>">
                    Select:
                    <a title="Select All" class="table" href="javascript:SetChecked(1,'username[]','listallusers')">All</a> 
                    <a title="Select None" class="table" href="javascript:SetChecked(0,'username[]','listallusers')">None</a>
                    <br>
                    <input class="button" type="button" value="Delete"
                        onclick="javascript:removeCheckbox('listallusers','mng-del.php')">
                    <input class="button" type="button" value="Disable"
                        onclick="javascript:disableCheckbox('listallusers','include/management/userOperations.php')">
                    <input class="button" type="button" value="Enable"
                        onclick="javascript:enableCheckbox('listallusers','include/management/userOperations.php')">
                </th>
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
        foreach ($records as $username => $data) {
            $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
            
            $img = (!$data['enabled'])
                 ? '<img title="user is disabled" src="images/icons/userStatusDisabled.gif" alt="[disabled]">'
                 : '<img title="user is enabled" src="images/icons/userStatusActive.gif" alt="[enabled]">';
            
            $auth = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) === "yes")
                  ? "[Password is hidden]" : htmlspecialchars($data['auth'], ENT_QUOTES, 'UTF-8');
            
            $fullname = htmlspecialchars($data['fullname'], ENT_QUOTES, 'UTF-8');
            $grouplist = implode("<br>", $data['groups']);
            $id = $data['id'];
            
            // tooltip and ajax stuff
            $onclick = sprintf("javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo',"
                             . "'divContainerUserInfo','username=%s');", urlencode($username));
            $content = sprintf('<a class="toolTip" href="mng-edit.php?username=%s">%s</a>',
                               urlencode($username), t('Tooltip','UserEdit'));
            $arr = array(
                            'content' => $content,
                            'onClick' => $onclick,
                            'value' => urlencode($username),
                            'divId' => 'divContainerUserInfo'
                        );
            $tooltip = addToolTipBalloon($arr);
?>
            <tr>
                <td><input type="checkbox" name="username[]" value="<?= $username ?>"><?= $id ?></td>
                <td><?= "$fullname" ?></td>
                <td><?= "$img $tooltip" ?></td>
                <td><?= $auth ?></td>
                <td><?= $grouplist ?></td>
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
</form>

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
    $logQuery = "performed query for listing of records on page: ";

    include('include/config/logging.php');
    include('page-footer.php');
?>
		</div><!-- #footer -->
    </div>
</div>

<script>
	var tooltipObj = new DHTMLgoodies_formTooltip();
	tooltipObj.setTooltipPosition('right');
	tooltipObj.setPageBgColor('#EEEEEE');
	tooltipObj.setTooltipCornerSize(15);
	tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
