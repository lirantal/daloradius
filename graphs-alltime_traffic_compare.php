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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include("library/checklogin.php");
$operator = $_SESSION['operator_user'];

include('library/check_operator_perm.php');

// validate parameters
$type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
         in_array(strtolower($_GET['type']), array( "daily", "monthly", "yearly" )))
      ? strtolower($_GET['type']) : "daily";

$size = (array_key_exists('size', $_GET) && isset($_GET['size']) &&
         in_array(strtolower($_GET['size']), array( "gigabytes", "megabytes" )))
      ? strtolower($_GET['size']) : "megabytes";

$traffic_compare_type = $type;
$traffic_compare_size = $size;

include_once('library/config_read.php');

include("menu-graphs.php");	
include_once("library/tabber/tab-layout.php");
?>

		<div id="contentnorightbar">
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','graphsalltimetrafficcompare.php'); ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>
            
            <div id="helpPage" style="display:none;visibility:visible"><?= t('helpPage','graphsalltimetrafficcompare') ?><br></div>
            <br>

            <div class="tabber">
                <div class="tabbertab" title="Download Graph">
                    <div style="text-align: center; margin-top: 50px;">
<?php
    $download_src = sprintf("library/graphs-alltime-traffic-download.php?type=%s&size=%s", $type, $size);
    $download_alt = sprintf("%s all-time download traffic (in %s) statistics", ucfirst($type), $size);
?>
                        <img alt="<?= $download_alt ?>" src="<?= $download_src ?>">
                    </div>
                </div>
     
                <div class="tabbertab" title="Upload Graph">
                    <div style="text-align: center; margin-top: 50px">
<?php
    $upload_src = sprintf("library/graphs-alltime-traffic-upload.php?type=%s&size=%s", $type, $size);
    $upload_alt = sprintf("%s all-time upload traffic (in %s) statistics", ucfirst($type), $size);
?>
                        <img alt="<?= $upload_alt ?>" src="<?= $upload_src ?>">
                    </div>
                </div>
            </div>
        </div>

		<div id="footer">		
<?php
    $log = "visited page: ";
    $logQuery = "performed query of type [$type] and size [$size] on page: ";
    
    include('include/config/logging.php');
    include('page-footer.php');
?>
		</div>
    </div>
</div>

</body>
</html>
