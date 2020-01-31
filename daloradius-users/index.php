<?php
/*
 *******************************************************************************
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
 *******************************************************************************
 *
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *******************************************************************************
 */

include ("library/checklogin.php");
$login = $_SESSION['login_user'];

include ("menu-home.php");

include_once('library/config_read.php');
$log = "visited page: ";
include('include/config/logging.php');

?>

<script src="library/javascript/pages_common.js" type="text/javascript"></script>

                <div id="contentnorightbar">
                    <h2 id="Intro"><a href="#"></a></h2>
                    <p>

<?php
	include('library/exten-welcome_page.php');
	include_once('include/management/userReports.php');
	userPlanInformation($login, 1);
    // userSubscriptionAnalysis with argument set to 1 for drawing the table
	userSubscriptionAnalysis($login, 1);
    // userConnectionStatus (same as above)
	userConnectionStatus($login, 1);
?>
                    </p>
                </div>


	
                <div id="footer">
                    <?php include('page-footer.php'); ?>
                </div>

            </div>
        </div>
    </body>
</html>
