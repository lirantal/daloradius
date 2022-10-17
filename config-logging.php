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

		if (isset($_REQUEST['config_pageslogging']))
			$configValues['CONFIG_LOG_PAGES'] = $_REQUEST['config_pageslogging'];
		if (isset($_REQUEST['config_querieslogging']))
			$configValues['CONFIG_LOG_QUERIES'] = $_REQUEST['config_querieslogging'];
		if (isset($_REQUEST['config_actionslogging']))
			$configValues['CONFIG_LOG_ACTIONS'] = $_REQUEST['config_actionslogging'];
		if (isset($_REQUEST['config_filenamelogging']))
			$configValues['CONFIG_LOG_FILE'] = $_REQUEST['config_filenamelogging'];

		if (isset($_REQUEST['config_debuglogging']))
			$configValues['CONFIG_DEBUG_SQL'] = $_REQUEST['config_debuglogging'];
		if (isset($_REQUEST['config_debugpageslogging']))
			$configValues['CONFIG_DEBUG_SQL_ONPAGE'] = $_REQUEST['config_debugpageslogging'];
			
            include ("library/config_write.php");
    }
	

	
?>		

<?php

    include ("menu-config.php");

?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configlogging.php') ?>
				<h144>&#x2754;</h144></a></h2>
                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configlogging') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="loggingsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">


        <fieldset>

                <h302> <?php echo t('title','Settings'); ?> </h302>
		<br/>

                <ul>

                <li class='fieldset'>
                <label for='config_pageslogging' class='form'><?php echo t('all','PagesLogging') ?></label>
		<select class='form' name="config_pageslogging">
			<option value="<?php echo $configValues['CONFIG_LOG_PAGES'] ?>"> <?php echo $configValues['CONFIG_LOG_PAGES'] ?> </option>
			<option value="">  </option>
			<option value="no"> no </option>
			<option value="yes"> yes </option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='config_querieslogging' class='form'><?php echo t('all','QueriesLogging') ?></label>
		<select class='form' name="config_querieslogging">
			<option value="<?php echo $configValues['CONFIG_LOG_QUERIES'] ?>"> <?php echo $configValues['CONFIG_LOG_QUERIES'] ?> </option>
			<option value="">  </option>
			<option value="no"> no </option>
			<option value="yes"> yes </option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='config_actionslogging' class='form'><?php echo t('all','ActionsLogging') ?></label>
		<select class='form' name="config_actionslogging">
			<option value="<?php echo $configValues['CONFIG_LOG_ACTIONS'] ?>"> <?php echo $configValues['CONFIG_LOG_ACTIONS'] ?> </option>
			<option value="">  </option>
			<option value="no"> no </option>
			<option value="yes"> yes </option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='config_debuglogging' class='form'><?php echo t('all','LoggingDebugInfo') ?></label>
		<select class='form' name="config_debuglogging">
			<option value="<?php echo $configValues['CONFIG_DEBUG_SQL'] ?>"> <?php echo $configValues['CONFIG_DEBUG_SQL'] ?> </option>
			<option value="">  </option>
			<option value="no"> no </option>
			<option value="yes"> yes </option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='config_debugpageslogging' class='form'><?php echo t('all','LoggingDebugOnPages') ?></label>
		<select class='form' name="config_debugpageslogging">
			<option value="<?php echo $configValues['CONFIG_DEBUG_SQL_ONPAGE'] ?>"> <?php echo $configValues['CONFIG_DEBUG_SQL_ONPAGE'] ?> </option>
			<option value="">  </option>
			<option value="no"> no </option>
			<option value="yes"> yes </option>
		</select>
		</li>

                <li class='fieldset'>
                <label for='config_filenamelogging' class='form'><?php echo t('all','FilenameLogging') ?></label>
		<input value="<?php echo $configValues['CONFIG_LOG_FILE'] ?>" name="config_filenamelogging" />
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
