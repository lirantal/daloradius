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
 
    isset($_REQUEST['enddate']) ? $enddate = $_REQUEST['enddate'] : $enddate = "";
    isset($_REQUEST['startdate']) ? $startdate = $_REQUEST['startdate'] : $startdate = "";
    isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";

    $logAction =  "";
    $logDebugSQL = "";

    if (isset($_POST['submit'])) {

        if ( (trim($startdate) != "") || (trim($enddate) != "") || (trim($username) != "") ) {
            
            include 'library/opendb.php';

            $deleteUsername = "";
            if (trim($username) != "")
                $deleteUsername = " AND Username='$username'";

            $deleteEnddate = "";
            if (trim($enddate) != "")
                $deleteEnddate = " AND AcctStartTime<'".$dbSocket->escapeSimple($enddate)."'";

            $deleteStartdate = "";
            if (trim($startdate) != "")
                $deleteStartdate = " AND AcctStartTime>'".$dbSocket->escapeSimple($startdate)."'";


            $sql = "DELETE FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
                " WHERE 1=1".
                " $deleteStartdate".
                " $deleteEnddate".
                " $deleteUsername ";
            $res = $dbSocket->query($sql);
            $logDebugSQL .= $sql . "\n";

            $successMsg = "Deleted records between <b>$startdate</b> to <b>$enddate</b> for user <b>$username</b>";
            $logAction .= "Successfully deleted records between [$startdate] and [$enddate] for user [$username] on page: ";

            include 'library/closedb.php';

        }  else { 
            $failureMsg = "no username, ending date or starting date was provided, please at least one of those";
            $logAction .= "Failed deleting records from database, missing fields on page: ";
        }

    }


    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','acctmaintenancedelete.php');
    $help = t('helpPage','acctmaintenancedelete');
    
    print_html_prologue($title, $langCode);

    include("menu-accounting-maintenance.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
?>

        
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <fieldset>

        <h302> <?php echo t('title','DeleteRecords') ?> </h302>
        <br/>

        <label for='username' class='form'><?php echo t('all','Username')?></label>
        <input name='username' type='text' id='username' value='<?php echo $username ?>' tabindex=100 />
        <br />

        <label for='startdate' class='form'><?php echo t('all','StartingDate')?></label>
        <input name='startdate' type='text' id='startdate' value='<?php echo $startdate ?>' tabindex=100 />
        <img src="library/js_date/calendar.gif" onclick=
        "showChooser(this, 'startdate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d H:i:s', true);" >
        <br />

        <label for='enddate' class='form'><?php echo t('all','EndingDate')?></label>
        <input name='enddate' type='text' id='enddate' value='<?php echo $enddate ?>' tabindex=100 />
        <img src="library/js_date/calendar.gif" onclick=
        "showChooser(this, 'enddate', 'chooserSpan', 1950, <?php echo date('Y', time());?>, 'Y-m-d H:i:s', true);" >
        <br />

        <br/><br/>
        <hr><br/>
        <input type="submit" name="submit" value="<?php echo t('buttons','apply') ?>" tabindex=1000 class='button' />

    </fieldset>

    <div id="chooserSpan" class="dateChooser select-free" 
        style="display: none; visibility: hidden; width: 160px;">
    </div>

</form>

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
