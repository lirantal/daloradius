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

    isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
    isset($_REQUEST['password']) ? $password = $_REQUEST['password'] : $password = "";
    isset($_REQUEST['radius']) ? $radius = $_REQUEST['radius'] : $radius = $configValues['CONFIG_MAINT_TEST_USER_RADIUSSERVER'];
    isset($_REQUEST['radiusport']) ? $radiusport = $_REQUEST['radiusport'] : $radiusport = $configValues['CONFIG_MAINT_TEST_USER_RADIUSPORT'];
    isset($_REQUEST['nasport']) ? $nasport = $_REQUEST['nasport'] : $nasport = $configValues['CONFIG_MAINT_TEST_USER_NASPORT'];
    isset($_REQUEST['secret']) ? $secret = $_REQUEST['secret'] : $secret = $configValues['CONFIG_MAINT_TEST_USER_RADIUSSECRET'];
    isset($_REQUEST['dictionaryPath']) ? $dictionaryPath = $_REQUEST['dictionaryPath'] : $dictionaryPath = $configValues['CONFIG_PATH_RADIUS_DICT'];
        
    if (isset($_REQUEST['submit'])) {

        include_once('library/exten-maint-radclient.php');
        
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];

        // process advanced options to pass to radclient
        isset($_REQUEST['debug']) ? $debug = $_REQUEST['debug'] : $debug = "no";
        isset($_REQUEST['timeout']) ? $timeout = $_REQUEST['timeout'] : $timeout = 3;
        isset($_REQUEST['retries']) ? $retries = $_REQUEST['retries'] : $retries = 3;
        isset($_REQUEST['count']) ? $count = $_REQUEST['count'] : $count = 1;
        isset($_REQUEST['retries']) ? $requests = $_REQUEST['requests'] : $requests = 3;

        // create the optional arguments variable

        // convert the debug = yes to the actual debug option which is "-x" to pass to radclient
        if ($debug == "yes")
            $debug = "-x";
        else
            $debug = "";

        $options = array("count" => $count, "requests" => $requests,
                    "retries" => $retries, "timeout" => $timeout,
                    "debug" => $debug, "dictionary" => $dictionaryPath
                    );

        $successMsg = user_auth($options, $username, $password, $radius, $radiusport, $secret);
        $logAction = "Informative action performed on user [$username] on page: ";    
    }

    
    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configmainttestuser.php');
    $help = t('helpPage','configmainttestuser');
    
    print_html_prologue($title, $langCode);

    include("menu-config-maint.php");
    include_once("library/tabber/tab-layout.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);
    
    include_once('include/management/actionMessages.php');
?>

<form name="mainttestuser" method="POST">
    <div class="tabber">
        
        <div class="tabbertab" title="<?= t('title','Settings') ?>">
            <fieldset>

                <h302> Test User Connectivity </h302>
                <br/>

                <label for="username" class="form"><?= t('all','Username')?></label>
                <input name="username" type="text" id="username" value='<?= $username ?>' tabindex="100">
                <br>


                <label for="password" class="form"><?= t('all','Password')?></label>
                <input name="password" type="text" id="password" value='<?= $password ?>' tabindex="101">
                <br>

                <label for="radius" class="form"><?= t('all','RadiusServer') ?></label>
                <input name="radius" type="text" id="radius" value='<?= $radius ?>' tabindex="102">
                <br>

                <label for="radiusport" class="form"><?= t('all','RadiusPort') ?></label>
                <input name="radiusport" type="text" id="radiusport" value='<?= $radiusport ?>' tabindex="103">
                <br>

                <label for="nasport" class="form"><?= t('all','NasPorts') ?></label>
                <input name="nasport" type="text" id="nasport" value='<?= $nasport ?>' tabindex="104">
                <br>

                <label for="secret" class="form"><?= t('all','NasSecret') ?></label>
                <input name="secret" type="text" id="secret" value='<?= $secret ?>' tabindex="105">
                <br>

            </fieldset>
        </div>
        
        <div class="tabbertab" title="<?= t('title','Advanced') ?>">
            <fieldset>

                <h302><?= t('title','Advanced') ?></h302>
                <br/>

                <label for="debug" class="form"><?= t('all','Debug') ?></label>
                <select name="debug" id="debug" class="form" tabindex="106">
                    <option value="yes"> Yes </option>
                    <option value="no"> No </option>
                </select>
                <br/>

                <label for="timeout" class="form"><?= t('all','Timeout') ?></label>
                <input name="timeout" type="number" id="timeout" value="3" tabindex="107">
                <br/>

                <label for="retries" class="form"><?= t('all','Retries') ?></label>
                <input name="retries" type="number" id="retries" value="3" tabindex="108">
                <br/>

                <label for="count" class="form"><?= t('all','Count') ?></label>
                <input name="count" type="number" id="count" value="1" tabindex="109">
                <br/>

                <label for="requests" class="form"><?= t('all','Requests') ?></label>
                <input name="requests" type="number" id="requests" value="3" tabindex="110">
                <br/>

                <label for="dictionaryPath" class="form"><?= t('all','RADIUSDictionaryPath') ?></label>
                <input name="dictionaryPath" type="text" id="dictionaryPath" value='<?= $dictionaryPath ?>' tabindex="111">
                <br>

            </fieldset>
        </div>
    </div><!-- .tabber -->
    
    <input type="submit" name="submit" value='Perform Test' class="button">
    
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
