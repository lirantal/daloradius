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

    include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    include("menu-reports-status.php");
      
?>    
        <div id="contentnorightbar">            
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','repstatradius.php'); ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>

            <div id="helpPage" style="display:none;visibility:visible" ><?= t('helpPage','repstatradius') ?><br></div>
            <br>

<?php
    include('library/exten-radius_server_info.php');
?>

        </div>
        
        <div id="footer">        
<?php
        include('page-footer.php');
?>
        
        </div>
    </div>
</div>


</body>
</html>
