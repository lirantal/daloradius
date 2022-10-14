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

    $date_check_regex = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';

    $logged_users_on_date = (array_key_exists('logged_users_on_date', $_GET) && isset($_GET['logged_users_on_date']) &&
                             preg_match($date_check_regex, $_GET['logged_users_on_date'], $m) !== false &&
                             checkdate($m[2], $m[3], $m[1])) ? $_GET['logged_users_on_date'] : date("Y-m-d");

    preg_match($date_check_regex, $logged_users_on_date, $match);
    $month = $match[2];
    $day = $match[3];
    $year = $match[1];

    include_once('library/config_read.php');
	
	include("menu-graphs.php");
    include_once ("library/tabber/tab-layout.php");

?>
        <div id="contentnorightbar">
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','graphsloggedusers.php'); ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>
            
            <div id="helpPage" style="display:none;visibility:visible"><?= t('helpPage','graphsloggedusers') ?><br></div>
            <br>

            <div class="tabber">
                <div class="tabbertab" title="Graph (day)">
                    <div style="text-align: center; margin-top: 50px;">
                        <img src="library/graphs-logged_users.php?day=<?= $day ?>&month=<?=$month ?>&year=<?= $year ?>">
                    </div>
                </div>

            
            
                <div class="tabbertab" title="Graph (month)">
                    <div style="text-align: center; margin-top: 50px;">
                        <img src="library/graphs-logged_users.php?month=<?=$month ?>&year=<?= $year ?>">
                    </div>
                </div>
            
            </div>

        </div>
		
        <div id="footer">
		
<?php
    $log = "visited page: ";
    $logQuery = "performed query on the following interval  [$day - $month - $year] on page: ";

    include('include/config/logging.php');
    include('page-footer.php');
?>

		</div>
    </div>
</div>

</body>
</html>
