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

	
?>		

<?php
    include ("menu-config-maint.php");
?>

<?php
        include_once ("library/tabber/tab-layout.php");
?>

		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configmainttestuser.php') ?>
				<h144>&#x2754;</h144> </a></h2>

		                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configmainttestuser') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="mainttestuser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','Settings'); ?>">

        <fieldset>

                <h302> Test User Connectivity </h302>
                <br/>

                <label for='username' class='form'><?php echo t('all','Username')?></label>
                <input name='username' type='text' id='username' value='<?php echo $username ?>' tabindex=100 />
                <br />


                <label for='password' class='form'><?php echo t('all','Password')?></label>
                <input name='password' type='text' id='password' value='<?php echo $password ?>' tabindex=101 />
                <br />

                <label for='radius' class='form'><?php echo t('all','RadiusServer') ?>
			</label>
                <input name='radius' type='text' id='radius' value='<?php echo $radius ?>' tabindex=102 />
                <br />

                <label for='radiusport' class='form'><?php echo t('all','RadiusPort') ?>
			</label>
                <input name='radiusport' type='text' id='radiusport' value='<?php echo $radiusport ?>' tabindex=103 />
                <br />

                <label for='nasport' class='form'><?php echo t('all','NasPorts') ?>
			</label>
                <input name='nasport' type='text' id='nasport' value='<?php echo $nasport ?>' tabindex=104 />
                <br />

                <label for='secret' class='form'><?php echo t('all','NasSecret') ?>
			</label>
                <input name='secret' type='text' id='secret' value='<?php echo $secret ?>' tabindex=105 />
                <br />

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='Perform Test' class='button' />

        </fieldset>

	</div>


     <div class="tabbertab" title="<?php echo t('title','Advanced'); ?>">

	<fieldset>

                <h302> Advanced </h302>
                <br/>

                <label for='debug' class='form'><?php echo t('all','Debug') ?></label>
                <select name='debug' id='debug' class='form' tabindex=106 >
                        <option value="yes"> Yes </option>
                        <option value="no"> No </option>
                </select>
                <br/>

                <label for='timeout' class='form'><?php echo t('all','Timeout') ?></label>
                <input class="integer" name='timeout' type='text' id='timeout' value='3' tabindex=107 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('timeout','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('timeout','decrement')"/>
                <br/>

                <label for='retries' class='form'><?php echo t('all','Retries') ?></label>
                <input class="integer" name='retries' type='text' id='retries' value='3' tabindex=108 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('retries','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('retries','decrement')"/>
                <br/>

                <label for='count' class='form'><?php echo t('all','Count') ?></label>
                <input class="integer" name='count' type='text' id='count' value='1' tabindex=109 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('count','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('count','decrement')"/>
                <br/>

                <label for='requests' class='form'><?php echo t('all','Requests') ?></label>
                <input class="integer" name='requests' type='text' id='requests' value='3' tabindex=110 />
                <img src="images/icons/bullet_arrow_up.png" alt="+" onclick="javascript:changeInteger('requests','increment')" />
                <img src="images/icons/bullet_arrow_down.png" alt="-" onclick="javascript:changeInteger('requests','decrement')"/>
                <br/>

                <label for='dictionaryPath' class='form'><?php echo t('all','RADIUSDictionaryPath') ?></label>
                <input name='dictionaryPath' type='text' id='dictionaryPath' value='<?php echo $dictionaryPath ?>' tabindex=111 />
                <br />

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='Perform Test' class='button' />

	</fieldset>

	</div>

</div>




				</form>

	
				<br/><br/>
				

				
<?php
	include('include/config/logging.php');
?>				
		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>
		
		</div>
		
</div>
</div>


</body>
</html>
