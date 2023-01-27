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
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Accounting";



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
        echo '<select name="hotspot" size="3" class="generic">';
        while ($row = $res->fetchRow()) {
            $name_enc = htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8');
            printf('<option value="%s">%s</option>', $name_enc, $name_enc);
        }
        echo '</select><!-- .generic -->';
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
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->
