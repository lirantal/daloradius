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
if (strpos($_SERVER['PHP_SELF'], '/menu-reports-logs.php') !== false) {
    header("Location: index.php");
    exit;
}

include_once("lang/main.php");

$m_active = "Reports";

?>


<?php
    include_once ("include/menu/menu-items.php");
    include_once ("include/menu/reports-subnav.php");
    
    function print_options($options, $selected_value="") {
        foreach ($options as $value => $label) {
            $selected = ($value == $selected_value) ? " selected" : "";
            printf('<option value="%s"%s>%s</option>', $value, $selected, $label);
        }
    }
    
    $lines_output_options = array(
                                    '20' => '20 Lines',
                                    '50' => '50 Lines',
                                    '100' => '100 Lines',
                                    '500' => '500 Lines',
                                    '1000' => '1000 Lines'
                                 );
    
?>      

            <div id="sidebar">
                <h2>Logs</h2>
                
                <h3>Log Files</h3>
                <ul class="subnav">
                    <li>
                        <a title="<?= strip_tags(t('button','daloRADIUSLog')) ?>" href="javascript:document.daloradius_log.submit();">
                            <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="static/images/icons/reportsLogs.png">
                            <?= t('button','daloRADIUSLog') ?>
                        </a>
                        <form name="daloradius_log" action="rep-logs-daloradius.php" method="GET" class="sidebar">
                            <select class="generic" name="daloradiusLineCount">
<?php
    $selected_value = (isset($daloradiusLineCount)) ? $daloradiusLineCount : "";
    print_options($lines_output_options, $selected_value);
?>
                            </select><!-- .generic -->
                            
                            <select class="generic" name="daloradiusFilter">
                    <?php if (isset($daloradiusFilter)) {
                        if ($daloradiusFilter == ".") 
                            echo "<option value='$daloradiusFilter'> Any </option>";
                        else
                            echo "<option value='$daloradiusFilter'> $daloradiusFilter </option>";
                          } else {
                        echo "<option value='.'> No filter </option>";
                          }
                        ?>
                                <option value="."></option>
                                <option value="QUERY"> Query Only </option>
                                <option value="NOTICE"> Notice Only </option>
                                <option value="INSERT"> SQL INSERT Only </option> 
                                <option value="SELECT"> SQL SELECT Only </option>
                            </select><!-- .generic -->
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','RadiusLog')) ?>" href="javascript:document.radius_log.submit();">
                            <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="static/images/icons/reportsLogs.png">
                            <?= t('button','RadiusLog') ?>
                        </a>
                        <form name="radius_log" action="rep-logs-radius.php" method="GET" class="sidebar">
                            <select class="generic" name="radiusLineCount">
<?php
                            $selected_value = (isset($radiusLineCount)) ? $radiusLineCount : "";
                            print_options($lines_output_options, $selected_value);
?>
                            </select><!-- .generic -->
                            
                            <select class="generic" name="radiusFilter">
                    <?php if (isset($radiusFilter)) {
                        if ($radiusFilter == ".") 
                            echo "<option value='$radiusFilter'> Any </option>";
                        else
                            echo "<option value='$radiusFilter'> $radiusFilter </option>";
                          } else {
                        echo "<option value='.'> No filter </option>";
                          }
                    ?>
                                <option value="."></option>
                                <option value="Auth"> Auth Only </option>
                                <option value="Info"> Info Only </option>
                                <option value="Error"> Error Only </option>
                            </select><!-- .generic -->
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','SystemLog')) ?>" href="javascript:document.system_log.submit();">
                            <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="static/images/icons/reportsLogs.png">
                            <?= t('button','SystemLog') ?>
                        </a>
                        <form name="system_log" action="rep-logs-system.php" method="GET" class="sidebar">
                            <select class="generic" name="systemLineCount">
<?php
                            $selected_value = (isset($systemLineCount)) ? $systemLineCount : "";
                            print_options($lines_output_options, $selected_value);
?>
                            </select><!-- .generic -->
                            <input type="text" name="systemFilter" tooltipText="<?= t('Tooltip','Filter'); ?><br>"
                                value="<?= (isset($systemFilter)) ? $systemFilter : "" ?>">
                        </form>
                    </li>

                    <li>
                        <a title="<?= strip_tags(t('button','BootLog')) ?>" href="javascript:document.boot_log.submit();">
                            <b>&raquo;</b><img style="border: 0; margin-right: 5px" src="static/images/icons/reportsLogs.png">
                            <?= t('button','BootLog') ?>
                        </a>
                        <form name="boot_log" action="rep-logs-boot.php" method="GET" class="sidebar">
                            <select class="generic" name="bootLineCount">
<?php
                            $selected_value = (isset($bootLineCount)) ? $bootLineCount : "";
                            print_options($lines_output_options, $selected_value);
?>
                            </select><!-- .generic -->
                            <input type="text" name="bootFilter" tooltipText="<?= t('Tooltip','Filter'); ?><br>"
                                value="<?= (isset($bootFilter)) ? $bootFilter : "" ?>">
                        </form>
                    </li>
                </ul><!-- .subnav -->
            </div><!-- #sidebar -->

<script>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
