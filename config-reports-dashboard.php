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

    include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');

    if (isset($_POST['submit'])) {
        
        if (array_key_exists('config_dashboard_dalo_secretkey', $_POST) && isset($_POST['config_dashboard_dalo_secretkey'])) {
            $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] = $_POST['config_dashboard_dalo_secretkey'];
        }
        
        if (array_key_exists('config_dashboard_dalo_debug', $_POST) &&  isset($_POST['config_dashboard_dalo_debug']) &&
            in_array($_POST['config_dashboard_dalo_debug'], array( "0", "1" ))) {
            $configValues['CONFIG_DASHBOARD_DALO_DEBUG'] = $_POST['config_dashboard_dalo_debug'];
        }
            
        include ("library/config_write.php");
    }    

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configdashboard.php');
    $help = t('helpPage','configdashboard');
    
    print_html_prologue($title, $langCode);

    include("menu-config-reports.php");

    include_once("library/tabber/tab-layout.php");
?>
        
            
        <div id="contentnorightbar">
        
<?php
    print_title_and_help($title, $help);
    include_once('include/management/actionMessages.php');
?>

            <form name="dashboardsettings" method="POST">

                <div class="tabber">

                    <div class="tabbertab" title="<?= t('title','Dashboard') ?>">
                    
                        <fieldset>
                            <h302><?= t('title','Dashboard') ?></h302>

                            <ul style="margin: 30px auto">
                                <li class="fieldset">
                                    <label for="config_dashboard_dalo_secretkey" class="form">
                                        <?= t('all','DashboardSecretKey') ?>
                                    </label>
                                    <input type="text" value="<?= $configValues['CONFIG_DASHBOARD_DALO_SECRETKEY'] ?>"
                                        name="config_dashboard_dalo_secretkey" id="config_dashboard_dalo_secretkey">
                                </li>

                                <li class="fieldset">
                                    <label for="config_dashboard_dalo_debug" class="form">
                                        <?= t('all','DashboardDebug')?>
                                    </label>
                                    <select class="form" name="config_dashboard_dalo_debug" id="config_dashboard_dalo_debug">
                                        <option value="<?= $configValues['CONFIG_DASHBOARD_DALO_DEBUG'] ?>">
                                            <?= $configValues['CONFIG_DASHBOARD_DALO_DEBUG'] ?>
                                        </option>
                                        <option value=""></option>
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                    </select>
                                </li>
                            </ul>
                        </fieldset>

                    </div><!-- .tabbertab -->
                    
                    <div class="tabbertab" title="<?= t('title','Settings') ?>">
                    
                        <fieldset>
                            <h302><?= t('title','Settings') ?></h302>
        
                            <ul style="margin: 30px auto">
                                <li class="fieldset">
                                    <label for="config_dashboard_dalo_delay_soft" class="form">
                                        <?= t('all','DashboardDelaySoft') ?>
                                    </label>
                                    <input type="text" value="<?= $configValues['CONFIG_DASHBOARD_DALO_DELAYSOFT'] ?>"
                                        name="config_dashboard_dalo_delay_soft" id="config_dashboard_dalo_delay_soft">
                                </li>
                                
                                <li class="fieldset">
                                    <label for="config_dashboard_dalo_delay_hard" class="form">
                                        <?= t('all','DashboardDelayHard') ?>
                                    </label>
                                    <input type="text" value="<?= $configValues['CONFIG_DASHBOARD_DALO_DELAYHARD'] ?>"
                                        name="config_dashboard_dalo_delay_hard" id="config_dashboard_dalo_delay_hard">
                                </li>
                            
                            </ul>
                        </fieldset>

                    </div><!-- .tabbertab -->
                
                </div><!-- .tabber -->

                <input type="submit" name="submit" value="<?= t('buttons','apply') ?>" class="button">
            </form>


        </div><!-- #contentnorightbar -->
        
        <div id="footer">
<?php
    $log = "visited page: ";

    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div><!-- #footer -->
    </div>
</div>

</body>
</html>
