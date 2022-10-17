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

		if (isset($_REQUEST['config_iface_pass_hidden']))
			$configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] = $_REQUEST['config_iface_pass_hidden'];
		if (isset($_REQUEST['config_iface_tableslisting']))
			$configValues['CONFIG_IFACE_TABLES_LISTING'] = $_REQUEST['config_iface_tableslisting'];
		if (isset($_REQUEST['config_iface_tableslisting_num']))
			$configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] = $_REQUEST['config_iface_tableslisting_num'];
		if (isset($_REQUEST['config_iface_auto_complete']))
			$configValues['CONFIG_IFACE_AUTO_COMPLETE'] = $_REQUEST['config_iface_auto_complete'];
			
            include ("library/config_write.php");
    }
	

	
?>		

<?php

    include ("menu-config.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configinterface.php') ?>
				<h144>&#x2754;</h144></a></h2>

                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configinterface') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="interfacesettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<fieldset>

                <h302> <?php echo t('title','Settings'); ?> </h302>
                <br/>

                <ul>

                <li class='fieldset'>
                <label for='config_iface_pass_hidden' class='form'><?php echo t('all','PasswordHidden')?></label>
		<select name="config_iface_pass_hidden" class='form'>
			<option value="<?php echo $configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] ?>"> <?php echo $configValues['CONFIG_IFACE_PASSWORD_HIDDEN'] ?> </option>
			<option value="">  </option>
			<option value="no"> no </option>
			<option value="yes"> yes </option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='config_iface_tablelisting' class='form'><?php echo t('all','TablesListing') ?></label>
		<input value="<?php echo $configValues['CONFIG_IFACE_TABLES_LISTING'] ?>" name="config_iface_tableslisting" />
		</li>

                <li class='fieldset'>
                <label for='config_iface_tableslisting_num' class='form'><?php echo t('all','TablesListingNum') ?></label>
		<select class='form' name="config_iface_tableslisting_num">
			<option value="<?php echo $configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] ?>"> <?php echo $configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] ?> </option>
			<option value="">  </option>
			<option value="no"> no </option>
			<option value="yes"> yes </option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='config_iface_auto_complete' class='form'><?php echo t('all','AjaxAutoComplete') ?></label>
		<select class='form' name="config_iface_auto_complete">
			<option value="<?php echo $configValues['CONFIG_IFACE_AUTO_COMPLETE'] ?>"> <?php echo $configValues['CONFIG_IFACE_AUTO_COMPLETE'] ?> </option>
			<option value="">  </option>
			<option value="no"> no </option>
			<option value="yes"> yes </option>
		</select>
		</li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
                </li>

                </ul>

        </fieldset>


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
