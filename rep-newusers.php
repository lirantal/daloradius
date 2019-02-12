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
 * Authors:     Liran Tal <liran@enginx.com>
 *              Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

        include('library/check_operator_perm.php');

        if (isset($_GET['startdate']))
                $startdate = $_GET['startdate'];
        if (isset($_GET['enddate']))
                $enddate = $_GET['enddate'];


        include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for listing of records on page: ";



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<script src="library/javascript/rounded-corners.js" type="text/javascript"></script>
<script src="library/javascript/form-field-tooltip.js" type="text/javascript"></script>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="css/form-field-tooltip.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="library/js_date/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="library/js_date/select-free.css"/>
<![endif]-->
</head>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

<?php

    include ("menu-reports.php");

?>

                <div id="contentnorightbar">
                
                                <h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','repnewusers.php'); ?>
                                <h144>&#x2754;</h144></a></h2>
                                
                <div id="helpPage" style="display:none;visibility:visible" >
                        <?php echo t('helpPage','repnewusers'); ?>
                        <br/>
                </div>
                <br/>


<div class="tabber">

     <div class="tabbertab" title="Statistics">
        <br/>   
        
<?php

        include 'library/opendb.php';
        include 'include/management/pages_common.php';
        include 'include/management/pages_numbering.php';               // must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file

        //orig: used as maethod to get total rows - this is required for the pages_numbering.php page
        $sql = "SELECT CONCAT(MONTH(CreationDate),'-',YEAR(Creationdate)) AS Month, ".
                        "COUNT(*) As Users FROM ".
                        $configValues['CONFIG_DB_TBL_DALOUSERINFO'].
                        " WHERE CreationDate >='$startdate' AND CreationDate <='$enddate' ".
                        " GROUP BY Month(Creationdate) ";
        $res = $dbSocket->query($sql);
        $numrows = $res->numRows();


        /* we are searching for both kind of attributes for the password, being User-Password, the more
           common one and the other which is Password, this is also done for considerations of backwards
           compatibility with version 0.7        */

           
        $sql = "SELECT CONCAT(YEAR(CreationDate),'-',MONTH(Creationdate), '-01') AS Month, ".
                        "COUNT(*) As Users FROM ".
                        $configValues['CONFIG_DB_TBL_DALOUSERINFO'].
                        " WHERE CreationDate >='$startdate' AND CreationDate <='$enddate' ".
                        " GROUP BY Month ORDER BY Date(Month);";
        $res = $dbSocket->query($sql);
        $logDebugSQL = "";
        $logDebugSQL .= $sql . "\n";


        echo "<form name='usersonline' method='get' >";

        echo "<table border='0' class='table1'>\n";
        echo "
                <thead>
                        <tr>
                        <th colspan='5' align='left'>

                        <br/>
                ";


        echo "</th></tr>
                        </thead>
        ";

        echo "<thread> <tr>
                <th scope='col'>
                ".t('all','Month'). "
                </th>

                <th scope='col'>
                        ".t('all','Users')."
                </th>

        </tr> </thread>";

        while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

                $Month = $row['Month'];
                $Users = $row['Users'];

                echo "<tr>
                                <td> $Month </td>
                                <td> $Users</td>
                </tr>";
        }

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='5' align='left'>
        ";
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";
        
        echo "</table>";
        include 'library/closedb.php';
                
?>

        </div>


     <div class="tabbertab" title="Graph">
        <br/>


<?php
        echo "<center>";
        echo "<img src=\"library/graphs-reports-new-users.php?startdate=$startdate&enddate=$enddate\" />";
        echo "</center>";
?>

        </div>
</div>



<?php
        include('include/config/logging.php');
?>
                
                </div>
                
                <div id="footer">
                
                                                                <?php
        include 'page-footer.php';
?>


<script type="text/javascript">
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip();
</script>
                
                </div>
                
</div>
</div>


</body>
</html>
