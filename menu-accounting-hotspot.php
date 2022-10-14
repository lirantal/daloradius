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

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/menu-accounting-hotspot.php') !== false) {
    header("Location: /index.php");
    exit;
}

include_once("lang/main.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $langCode ?>" lang="<?= $langCode ?>">
<head>
    <title>daloRADIUS :: Accounting / Hotspots </title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="css/1.css" media="screen">
    <link rel="stylesheet" href="css/form-field-tooltip.css" media="screen">
    <link rel="stylesheet" href="library/js_date/datechooser.css">
    <!--[if lte IE 6.5]>
    <link rel="stylesheet" href="library/js_date/select-free.css"/>
    <![endif]-->

    <script src="library/js_date/date-functions.js"></script>
    <script src="library/js_date/datechooser.js"></script>
    <script src="library/javascript/pages_common.js"></script>
    <script src="library/javascript/rounded-corners.js"></script>
    <script src="library/javascript/form-field-tooltip.js"></script>
    <script src="library/javascript/ajax.js"></script>
    <script src="library/javascript/ajaxGeneric.js"></script>
</head>

<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
	$m_active = "Accounting";
	include_once("include/menu/menu-items.php");
	include_once("include/menu/accounting-subnav.php");
?>

            <div id="sidebar">
                <h2>Accounting</h2>
                
                <h3>Hotspots Accounting</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','HotspotAccounting')) ?>" href="javascript:document.accthotspot.submit();">
                            <b>&raquo;</b><?= t('button','HotspotAccounting') ?>
                        </a>
                        <form name="accthotspot" action="acct-hotspot-accounting.php" method="POST" class="sidebar">
<?php

	include('library/opendb.php');

	$sql = sprintf("SELECT name FROM %s", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
	$res = $dbSocket->query($sql);
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        echo '<select name="hotspot" size="3">';
        while ($row = $res->fetchRow()) {
            $name_enc = htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
            printf('<option value="%s">%s</option>', $name_enc, $name_enc);
        }
        echo '</select>';
    }
	include('library/closedb.php');
	
?>	
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','HotspotsComparison')) ?>" href="acct-hotspot-compare.php">
                            <b>&raquo;</b><?= t('button','HotspotsComparison') ?>
                        </a>
                    </li>
                </ul>
			
                <br><br>
            </div>
