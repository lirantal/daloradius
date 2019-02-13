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

	include_once('library/config_read.php');
    $log = "visited page: ";
    include('include/config/logging.php');


    include ("library/config_read.php");

    if (isset($_REQUEST['submit'])) {

		if (isset($_REQUEST['config_user_allowedrandomchars'])) {
			$config_user_allowedrandomchars = str_replace('\'', '', $_REQUEST['config_user_allowedrandomchars']);
			$config_user_allowedrandomchars = str_replace('"', '', $config_user_allowedrandomchars);
			$configValues['CONFIG_USER_ALLOWEDRANDOMCHARS'] = $config_user_allowedrandomchars;
		}
		
		// this should probably move to some other page at some point
		if (isset($_REQUEST['config_db_pass_encrypt']))
			$configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] = $_REQUEST['config_db_pass_encrypt'];
		
        include ("library/config_write.php");
    }	

	
?>		
		
<?php
    include ("menu-config.php");
?>

<?php
        include_once ("library/tabber/tab-layout.php");
?>
		
			
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configuser.php'); ?>
				<h144>&#x2754;</h144></a></h2>

                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configuser') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="usersettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','Settings'); ?>">

        <fieldset>

                <h302><?php echo t('title','Settings'); ?></h302>
		<br/>

		<ul>

		<li class='fieldset'>
		<label for='' class='form'><?php echo t('all','DBPasswordEncryption')?></label>
		<select class='form' name="config_db_pass_encrypt">
			<option value="<?php echo $configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] ?>"> <?php echo $configValues['CONFIG_DB_PASSWORD_ENCRYPTION'] ?> </option>
			<option value=""></option>
			<option value="cleartext">cleartext</option>
			<option value="crypt">unix crypt</option>
			<option value="md5">md5</option>
		</select>
		</li>
		

		<li class='fieldset'>
		<label for='config_user_allowedrandomchars' class='form'><?php echo t('all','RandomChars') ?></label>
		<input type='text' value="<?php echo htmlentities($configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']) ?>" name="config_user_allowedrandomchars" />
		</li>





		<li class='fieldset'>
		<br/>
		<hr><br/>
		<input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
		</li>

		</ul>
	
	</fieldset>

	</div>

</div>


				</form>

	
				<br/><br/>






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
