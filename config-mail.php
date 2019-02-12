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

		if (isset($_REQUEST['config_mail_smtpaddr']))
			$configValues['CONFIG_MAIL_SMTPADDR'] = $_REQUEST['config_mail_smtpaddr'];
		
		if (isset($_REQUEST['config_mail_smtpport']))
			$configValues['CONFIG_MAIL_SMTPPORT'] = $_REQUEST['config_mail_smtpport'];
			
		if (isset($_REQUEST['config_mail_smtp_fromemail']))
			$configValues['CONFIG_MAIL_SMTPFROM'] = $_REQUEST['config_mail_smtp_fromemail'];
		
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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configmail.php'); ?>
				<h144>&#x2754;</h144></a></h2>

                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configmail') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="mailsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','Settings'); ?>">

        <fieldset>

                <h302><?php echo t('title','Settings'); ?></h302>
		<br/>

		<ul>

		<li class='fieldset'>
		<label for='config_mail_smtpaddr' class='form'><?php echo t('all','SMTPServerAddress') ?></label>
		<input type='text' value="<?php echo $configValues['CONFIG_MAIL_SMTPADDR'] ?>" name="config_mail_smtpaddr" />
		</li>

		<li class='fieldset'>
		<label for='config_mail_smtpport' class='form'><?php echo t('all','SMTPServerPort') ?></label>
		<input type='text' value="<?php echo $configValues['CONFIG_MAIL_SMTPPORT'] ?>" name="config_mail_smtpport" />
		</li>

		<li class='fieldset'>
		<label for='config_mail_smtp_fromemail' class='form'><?php echo t('all','SMTPServerFromEmail') ?></label>
		<input type='text' value="<?php echo $configValues['CONFIG_MAIL_SMTPFROM'] ?>" name="config_mail_smtp_fromemail" />
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
