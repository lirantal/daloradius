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
 * Authors:	Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];
        
	include_once('library/config_read.php');
    $log = "visited page: ";
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','repstatus.php');
    $help = t('helpPage','repstatus');
    
    print_html_prologue($title, $langCode);

    include("menu-reports-status.php");

?>
		<div id="contentnorightbar">
<?php
    print_title_and_help($title, $help);
?>
		
        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    include_once('include/config/logging.php');
    include('page-footer.php');
?>

        </div><!-- #footer -->
    </div>
</div>

</body>
</html>
