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

    // parameter validation
    $bootLineCount = (array_key_exists('bootLineCount', $_GET) && isset($_GET['bootLineCount']) &&
                      intval($_GET['bootLineCount']) > 0)
                   ? intval($_GET['bootLineCount']) : 50;

    // preg quoted before usage
    $bootFilter = (array_key_exists('bootFilter', $_GET) && isset($_GET['bootFilter']))
                ? $_GET['bootFilter'] : "";

    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','replogsboot.php') . " :: $bootLineCount Lines Count";
    if (!empty($bootFilter) && $bootFilter !== '.+') {
        $title .= " with filter set to " . htmlspecialchars($bootFilter, ENT_QUOTES, 'UTF-8');
    }
    $help = t('helpPage','replogsboot');
    
    print_html_prologue($title, $langCode);

    include("menu-reports-logs.php");
      
?>    
    <div id="contentnorightbar">

<?php
    print_title_and_help($title, $help);

    include('library/exten-boot_log.php');
    include_once('include/management/actionMessages.php');
?>
         </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

</body>
</html>

