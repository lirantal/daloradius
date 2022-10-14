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

	// validate (or pre-validate) parameters
    $type = (array_key_exists('type', $_GET) && isset($_GET['type']) &&
             in_array(strtolower($_GET['type']), array( "daily", "monthly", "yearly" )))
          ? strtolower($_GET['type']) : "daily";

    $size = (array_key_exists('size', $_GET) && isset($_GET['size']) &&
             in_array(strtolower($_GET['size']), array( "gigabytes", "megabytes" )))
          ? strtolower($_GET['size']) : "megabytes";

    $username = (array_key_exists('username', $_GET) && isset($_GET['username']))
              ? str_replace('%', '', $_GET['username']) : "";
    $username_enc = (!empty($username)) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : "";

	//feed the sidebar variables
	$overall_upload_username = $username_enc;
	$overall_upload_type = $type;
	$overall_upload_size = $size;

    include_once('library/config_read.php');

	include("menu-graphs.php");
    include_once ("library/tabber/tab-layout.php");
?>


		<div id="contentnorightbar">
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','graphsoverallupload.php'); ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>
            
            <div id="helpPage" style="display:none;visibility:visible"><?= t('helpPage','graphsoverallupload') ?><br></div>
            <br>
<?php
    if (!empty($username)) {
        $src = sprintf("library/graphs-overall-users-upload.php?type=%s&size=%s&user=%s", $type, $size, $username_enc);
        $alt = sprintf("%s of traffic in upload %s produced by %s", $size, $type, $username_enc);
?>

            <div class="tabber">
                <div class="tabbertab" title="Graph">
                    <div style="text-align: center; margin-top: 50px">
                        <img alt="<?= $alt ?>" src="<?= $src ?>">
                    </div>
                </div>

                <div class="tabbertab" title="Statistics">
                    <div style="margin-top: 50px">
<?php
        include("library/tables-overall-users-upload.php");
?>
                    </div>
                </div>
            </div>
<?php
    } else {
        $failureMsg = "You must provide a valid username";
        include_once("include/management/actionMessages.php");
    }
?>
        </div>

		<div id="footer">		
<?php
    $log = "visited page: ";
    if (!empty($username)) {
        $logQuery = "performed query for user [$username] of type [$type] on page: ";
    }
    
    include('include/config/logging.php');
    include('page-footer.php');
?>
		</div>
    </div>
</div>

</body>
</html>
