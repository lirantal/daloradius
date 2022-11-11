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

    isset($_GET['realmname']) ? $realmname = $_GET['realmname'] : $realmname = "";

    $logAction = "";
    $logDebugSQL = "";

    if (isset($_POST["submit"])) {

        isset($_POST['realmname']) ? $realmname = $_POST['realmname'] : $realmname = "";
        isset($_POST['type']) ? $type = $_POST['type'] : $type = "";
        isset($_POST['authhost']) ?$authhost = $_POST['authhost'] : $authhost = "";
        isset($_POST['accthost']) ? $accthost = $_POST['accthost'] : $accthost = "";
        isset($_POST['secret']) ? $secret = $_POST['secret'] : $secert = "";
        isset($_POST['ldflag']) ? $ldflag = $_POST['ldflag'] : $ldflag = "";
        isset($_POST['nostrip']) ? $nostrip = $_POST['nostrip'] : $nostrip = "";
        isset($_POST['hints']) ? $hints = $_POST['hints'] : $hints = "";
        isset($_POST['notrealm']) ? $notrealm = $_POST['notrealm'] :  $notrealm = "";
        
        include 'library/opendb.php';

        if (isset($configValues['CONFIG_FILE_RADIUS_PROXY'])) {
            $filenameRealmsProxys = $configValues['CONFIG_FILE_RADIUS_PROXY'];
            $fileFlag = 1;
        } else {
            $filenameRealmsProxys = "";
            $fileFlag = 0;
        }

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS'].
                " WHERE realmname='".$dbSocket->escapeSimple($realmname)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if (trim($realmname) != "") {

            if (!(file_exists($filenameRealmsProxys))) {
                $logAction .= "Failed non-existed realms configuration file [$filenameRealmsProxys] on page: ";
                $failureMsg = "the file $filenameRealmsProxys doesn't exist, I can't save realms information to the file";
                $fileFlag = 0;
            }

            if (!(is_writable($filenameRealmsProxys))) {
                $logAction .= "Failed writing realms configuration to file [$filenameRealmsProxys] on page: ";
                $failureMsg = "the file $filenameRealmsProxys isn't writable, I can't save realms information to the file";
                $fileFlag = 0;
            }

            $currDate = date('Y-m-d H:i:s');
            $currBy = $_SESSION['operator_user'];

            // update realm entry in database
            $sql = "UPDATE ".$configValues['CONFIG_DB_TBL_DALOREALMS']." SET ".
                " type='".$dbSocket->escapeSimple($type)."', ".
                " authhost='".$dbSocket->escapeSimple($authhost)."', ".
                " accthost='".$dbSocket->escapeSimple($accthost)."', ".
                " secret='".$dbSocket->escapeSimple($secret)."', ".
                " ldflag='".$dbSocket->escapeSimple($ldflag)."', ".
                " nostrip='".$dbSocket->escapeSimple($nostrip)."', ".
                " hints='".$dbSocket->escapeSimple($hints)."', ".
                " notrealm='".$dbSocket->escapeSimple($notrealm)."', ".
                " updatedate='$currDate', updateby='$currBy' ".
                " WHERE realmname='$realmname';";
            $res = $dbSocket->query($sql);
            $logDebugSQL .= $sql . "\n";

            $successMsg = "Updated database with realm: <b>$realmname</b>";
            $logAction .= "Updated realm [$realmname] on page: ";

            /*******************************************************************/
            /* enumerate from database all realm entries */
            include_once('include/management/saveRealmsProxys.php');
            /*******************************************************************/

        } else {
            $failureMsg = "you must provide atleast a realm name";
            $logAction .= "Updated realm [$realmname] on page: ";    
        }

        include 'library/closedb.php';

    }

    include 'library/opendb.php';

        // fill-in realm information in html elements
        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOREALMS']." WHERE realmname='".$dbSocket->escapeSimple($realmname)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $type = $row['type'];
        $authhost = $row['authhost'];
        $accthost = $row['accthost'];
        $secret = $row['secret'];
        $ldflag = $row['ldflag'];
        $nostrip = $row['nostrip'];
        $hints = $row['hints'];
        $notrealm = $row['notrealm'];

    include 'library/closedb.php';


    include_once('library/config_read.php');
    $log = "visited page: ";
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/pages_common.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );

    $title = t('Intro','mngradrealmsedit.php');
    $help = t('helpPage','mngradrealmsedit');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-mng-rad-realms.php");
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');

    // set navbar stuff
    $navbuttons = array(
                          'RealmInfo-tab' => t('title','RealmInfo'),
                          'Advanced-tab' => t('title','Advanced'),
                       );

    print_tab_navbuttons($navbuttons);

?>

<form method="post">
    <div id="RealmInfo-tab" class="tabcontent" style="display: block">

    <fieldset>

        <h302> <?= t('title','RealmInfo'); ?> </h302>
        <br/>

        <ul>

        <input type='hidden' name='realmname' id='realmname' value='<?php if (isset($realmname)) echo $realmname; ?>' />

        <li class='fieldset'>
        <label for='realmname' class='form'><?= t('all','RealmName') ?></label>
        <input disabled name='realmname' type='text' id='realmname' 
            value='<?php if (isset($realmname)) echo $realmname; ?>' tabindex=100 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmNameTooltip')" />
        
        <div id='realmNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmNameTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='type' class='form'><?= t('all','Type') ?></label>
        <input name='type' type='text' id='type' value='<?php if (isset($type)) echo $type; ?>' tabindex=101 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmTypeTooltip')"/>
        
        <div id='realmTypeTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmTypeTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='authhost' class='form'><?= t('all','AuthHost') ?></label>
        <input name='authhost' type='text' id='authhost' value='<?php if (isset($authhost)) echo $authhost; ?>' tabindex=102 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmAuthhostTooltip')"/>
        
        <div id='realmAuthhostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmAuthhostTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='accthost' class='form'><?= t('all','AcctHost') ?></label>
        <input name='accthost' type='text' id='accthost' value='<?php if (isset($accthost)) echo $accthost; ?>' tabindex=103 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmAccthostTooltip')"/>
        
        <div id='realmAccthostTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmAccthostTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='secret' class='form'><?= t('all','RealmSecret') ?></label>
        <input name='secret' type='text' id='secret' value='<?php if (isset($secret)) echo $secret; ?>' tabindex=104 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmSecretTooltip')"/>
        
        <div id='realmSecretTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmSecretTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <br/>
        <hr><br/>
        <input type='submit' name='submit' value='<?= t('buttons','apply') ?>' tabindex=10000 class='button' />
        </li>

        </ul>
    </fieldset>

    </div>


     <div id="Advanced-tab" class="tabcontent">

    <fieldset>

        <h302> <?= t('title','RealmInfo'); ?> </h302>
        <br/>
        <ul>

        <li class='fieldset'>
        <label for='ldflag' class='form'><?= t('all','Ldflag') ?></label>
        <input name='ldflag' type='text' id='ldflag' value='<?php if (isset($ldflag)) echo $ldflag; ?>' tabindex=105 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmLdflagTooltip')"/>
        
        <div id='realmLdflagTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmLdflagTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='nostrip' class='form'><?= t('all','Nostrip') ?></label>
        <input name='nostrip' type='text' id='nostrip' value='<?php if (isset($nostrip)) echo $nostrip; ?>' tabindex=106 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmNostripTooltip')"/>
        
        <div id='realmNostripTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmNostripTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <label for='hints' class='form'><?= t('all','Hints') ?></label>
        <input name='hints' type='text' id='hints' value='<?php if (isset($hints)) echo $hints; ?>' tabindex=107 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmHintsTooltip')"/>
        
        <div id='realmHintsTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmHintsTooltip') ?>
        </div>
        </li>

                <li class='fieldset'>
        <label for='notrealm' class='form'><?= t('all','Notrealm') ?></label>
        <input name='notrealm' type='text' id='notrealm' value='<?php if (isset($notrealm)) echo $notrealm; ?>' tabindex=108 />
        <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('realmNotrealmTooltip')"/>
        
        <div id='realmNotrealmTooltip'  style='display:none;visibility:visible' class='ToolTip'>
            <img src='images/icons/comment.png' alt='Tip' border='0' />
            <?= t('Tooltip','realmNotrealmTooltip') ?>
        </div>
        </li>

        <li class='fieldset'>
        <br/>
        <hr><br/>
        <input type='submit' name='submit' value='<?= t('buttons','apply') ?>' tabindex=10000 class='button' /> 
        </li>

        </ul>
    </fieldset>

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
