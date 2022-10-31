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

    $logAction = "";

    isset($_REQUEST['username']) ? $username = $_REQUEST['username'] : $username = "";
    isset($_REQUEST['nasaddr']) ? $nasaddr = $_REQUEST['nasaddr'] : $nasaddr = "";
    isset($_REQUEST['nasport']) ? $nasport = $_REQUEST['nasport'] : $nasport = "";
    isset($_REQUEST['nassecret']) ? $nassecret = $_REQUEST['nassecret'] : $nassecret = "";
    isset($_REQUEST['packettype']) ? $packettype = $_REQUEST['packettype'] : $packettype = "";
    isset($_REQUEST['customattributes']) ? $customAttributes = $_REQUEST['customattributes'] : $customAttributes = "";
    

    if (isset($_REQUEST['submit'])) {

    if ( ($nasaddr == "") || ($nasport == "") || ($nassecret == "") ) {

        $failureMsg = "One of NAS Address, NAS Port or NAS Secret fields were left empty";
        $logAction .= "Failed performing disconnect on user [$username] because of missing NAS fields on page: ";

    } else if ($username == "") {

        $failureMsg = "The User-Name to disconnect was not provided";
        $logAction .= "Failed performing disconnect on user [$username] because of missing User-Name on page: ";

    } else {

        include_once('library/exten-maint-radclient.php');
        
        $username = $_REQUEST['username'];

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
                                        "debug" => $debug,
                                        );

        $successMsg = user_disconnect($options,$username,$nasaddr,$nasport,$nassecret,$packettype,$customAttributes);
        $logAction .= "Informative action performed on user [$username] on page: ";

    } 

    } //if submit


    include_once('library/config_read.php');
    $log = "visited page: ";

    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $title = t('Intro','configmaintdisconnectuser.php');
    $help = t('helpPage','configmaintdisconnectuser');
    
    print_html_prologue($title, $langCode);

    include("menu-config-maint.php");
    include_once("library/tabber/tab-layout.php");

    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    include_once('include/management/actionMessages.php');
?>

<form name="maintdisconnectuser" method="POST">
    <div class="tabber">
        <div class="tabbertab" title="<?= t('title','Settings') ?>">
            <fieldset>

                <h302><?= t('title','Settings') ?></h302>
                <br>

                <label for="username" class="form"><?= t('all','Username')?></label>
                <input name="username" type="text" id="usernameEdit" autocomplete="off"
                    tooltipText='<?= t('Tooltip','Username') ?> <br>'
                    value="<?= (isset($username)) ? $username : "" ?>" tabindex="101">
                
                <br>
                    
                <label for="packettype" class="form"><?= t('all','PacketType') ?></label>
                <select name="packettype" id="packettype" class="form" tabindex="102">
                    <option value="disconnect"> PoD - Packet of Disconnect </option>
                    <option value="coa"> CoA - Change of Authorization &nbsp;</option>
                </select>
                
                <br>

                <label for="nasaddr" class="form"><?= t('all','NasIPHost') ?></label>
                <input name="nasaddr" type="hidden" id="nasaddr" value='<?= $nasaddr ?>' tabindex="103">

                <select onchange="javascript:setStringTextMulti(this.id,'nasaddr','nassecret')"
                    id="naslist" tabindex="104"  class="form">
                    <option value=""> Choose NAS... </option>
<?php

        include('library/opendb.php');

        // Grabing the group lists from usergroup table
        $sql = "SELECT distinct(nasname), shortname, secret FROM ".$configValues['CONFIG_DB_TBL_RADNAS'].";";
        $res = $dbSocket->query($sql);

        while ($row = $res->fetchRow()) {
            echo "<option value='$row[0]||$row[2]'> $row[1] - $row[0]</option>";
        }

        include('library/closedb.php');
?>
                </select>

                <br>

                <input name="nassecret" type="hidden" type="hidden" id="nassecret" value="" tabindex="105">
                <label for="nasport" class="form"><?= t('all','NasPorts') ?></label>
                <input name="nasport" type="hidden" id="nasport" value="3799" tabindex="106">
                
                <select onChange="javascript:setStringText(this.id,'nasport')" id="nasportlist" tabindex="107" 
                    class="form">
                    <option value="3799"> Choose Port... </option>
                    <option value="3799"> 3799 </option>
                    <option value="1700"> 1700 </option>
                </select>
                
                <br>

                <label for="customattributes" class="form"><?= t('all','customAttributes') ?></label>
                <textarea class="form" name="customattributes" tabindex="108"><?= $customAttributes ?></textarea>

                <br><br><hr><br>

                <input type="submit" name="submit" value='<?= t('button','DisconnectUser') ?>' class="button" tabindex="109">

            </fieldset>
        </div>


        <div class="tabbertab" title="<?= t('title','Advanced') ?>">

            <fieldset>

                <h302><?= t('title','Advanced') ?></h302>

                <br>

                <label for="debug" class="form"><?= t('all','Debug') ?></label>
                <select name="debug" id="debug" class="form" tabindex="110">
                    <option value="yes"> Yes </option>
                    <option value="no"> No </option>
                </select>
                
                <br>

                <label for="timeout" class="form"><?= t('all','Timeout') ?></label>
                <input name="timeout" type="number" id="timeout" value="3" tabindex="111">
                <br>

                <label for="retries" class="form"><?= t('all','Retries') ?></label>
                <input name="retries" type="number" id="retries" value="3" tabindex="112">
                <br>

                <label for="count" class="form"><?= t('all','Count') ?></label>
                <input name="count" type="number" id="count" value="1" tabindex="113">
                <br>

                <label for="requests" class="form"><?= t('all','Requests') ?></label>
                <input name="requests" type="number" id="requests" value="3" tabindex="114">
                <br>

                <br><br>
                <hr><br>

                <input type="submit" name="submit" value='<?= t('button','DisconnectUser') ?>' class="button">

            </fieldset>

        </div>
    </div><!-- .tabber -->
    
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

<?php
    include_once("include/management/autocomplete.php");

    if ($autoComplete) {
        echo "<script>
                /** Making usernameEdit interactive **/
                autoComEdit = new DHTMLSuite.autoComplete();
                autoComEdit.add('usernameEdit','include/management/dynamicAutocomplete.php','_small','getAjaxAutocompleteUsernames');
                </script>";
    } 
?>

</body>
</html>
