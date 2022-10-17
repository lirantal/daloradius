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

		if (isset($_REQUEST['config_dbengine']))
			$configValues['CONFIG_DB_ENGINE'] = $_REQUEST['config_dbengine'];
	
		if (isset($_REQUEST['config_dbhost']))
			$configValues['CONFIG_DB_HOST'] = $_REQUEST['config_dbhost'];
			
		if (isset($_REQUEST['config_dbport']))
			$configValues['CONFIG_DB_PORT'] = $_REQUEST['config_dbport'];

		if (isset($_REQUEST['config_dbuser']))
			$configValues['CONFIG_DB_USER'] = $_REQUEST['config_dbuser'];

		if (isset($_REQUEST['config_dbpass']))
			$configValues['CONFIG_DB_PASS'] = $_REQUEST['config_dbpass'];

		if (isset($_REQUEST['config_dbname']))
			$configValues['CONFIG_DB_NAME'] = $_REQUEST['config_dbname'];

		if (isset($_REQUEST['config_dbtbl_node']))
			$configValues['CONFIG_DB_TBL_DALONODE'] = $_REQUEST['config_dbtbl_node'];

		if (isset($_REQUEST['config_dbtbl_radcheck']))
			$configValues['CONFIG_DB_TBL_RADCHECK'] = $_REQUEST['config_dbtbl_radcheck'];

		if (isset($_REQUEST['config_dbtbl_radcheck']))
			$configValues['CONFIG_DB_TBL_RADREPLY'] = $_REQUEST['config_dbtbl_radreply'];

		if (isset($_REQUEST['config_dbtbl_radcheck']))
			$configValues['CONFIG_DB_TBL_RADGROUPCHECK'] = $_REQUEST['config_dbtbl_radgroupcheck'];

		if (isset($_REQUEST['config_dbtbl_radcheck']))
			$configValues['CONFIG_DB_TBL_RADGROUPREPLY'] = $_REQUEST['config_dbtbl_radgroupreply'];

		if (isset($_REQUEST['config_dbtbl_usergroup']))
			$configValues['CONFIG_DB_TBL_RADUSERGROUP'] = $_REQUEST['config_dbtbl_usergroup'];

		if (isset($_REQUEST['config_dbtbl_radacct']))
			$configValues['CONFIG_DB_TBL_RADACCT'] = $_REQUEST['config_dbtbl_radacct'];

		if (isset($_REQUEST['config_dbtbl_operators']))
			$configValues['CONFIG_DB_TBL_DALOOPERATORS'] = $_REQUEST['config_dbtbl_operators'];
		
		if (isset($_REQUEST['config_dbtbl_operators_acl']))
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'] = $_REQUEST['config_dbtbl_operators_acl'];
		
		if (isset($_REQUEST['config_dbtbl_operators_acl_files']))
			$configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'] = $_REQUEST['config_dbtbl_operators_acl_files'];

		if (isset($_REQUEST['config_dbtbl_rates']))
			$configValues['CONFIG_DB_TBL_DALOBILLINGRATES'] = $_REQUEST['config_dbtbl_rates'];

		if (isset($_REQUEST['config_dbtbl_hotspots']))
			$configValues['CONFIG_DB_TBL_DALOHOTSPOTS'] = $_REQUEST['config_dbtbl_hotspots'];

		if (isset($_REQUEST['config_dbtbl_nas']))
			$configValues['CONFIG_DB_TBL_RADNAS'] = $_REQUEST['config_dbtbl_nas'];

		if (isset($_REQUEST['config_dbtbl_radpostauth']))
			$configValues['CONFIG_DB_TBL_RADPOSTAUTH'] = $_REQUEST['config_dbtbl_radpostauth'];

		if (isset($_REQUEST['config_dbtbl_radippool']))
			$configValues['CONFIG_DB_TBL_RADIPPOOL'] = $_REQUEST['config_dbtbl_radippool'];

		if (isset($_REQUEST['config_dbtbl_userinfo']))
			$configValues['CONFIG_DB_TBL_DALOUSERINFO'] = $_REQUEST['config_dbtbl_userinfo'];

		if (isset($_REQUEST['config_dbtbl_dictionary']))
			$configValues['CONFIG_DB_TBL_DALODICTIONARY'] = $_REQUEST['config_dbtbl_dictionary'];

		if (isset($_REQUEST['config_dbtbl_realms']))
			$configValues['CONFIG_DB_TBL_DALOREALMS'] = $_REQUEST['config_dbtbl_realms'];

		if (isset($_REQUEST['config_dbtbl_proxys']))
			$configValues['CONFIG_DB_TBL_DALOPROXYS'] = $_REQUEST['config_dbtbl_proxys'];

		if (isset($_REQUEST['config_dbtbl_billingmerchant']))
			$configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'] = $_REQUEST['config_dbtbl_billingmerchant'];
			
		if (isset($_REQUEST['config_dbtbl_billingpaypal']))
			$configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'] = $_REQUEST['config_dbtbl_billingpaypal'];

		if (isset($_REQUEST['config_dbtbl_billinghistory']))
			$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'] = $_REQUEST['config_dbtbl_billinghistory'];

		if (isset($_REQUEST['config_dbtbl_billinginfo']))
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'] = $_REQUEST['config_dbtbl_billinginfo'];

		if (isset($_REQUEST['config_dbtbl_hunt']))
			$configValues['CONFIG_DB_TBL_RADHG'] = $_REQUEST['config_dbtbl_hunt'];

        if (isset($_REQUEST['config_dbtbl_billinginvoice']))
            $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'] = $_REQUEST['config_dbtbl_billinginvoice'];
            
        if (isset($_REQUEST['config_dbtbl_billinginvoice_items']))
            $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'] = $_REQUEST['config_dbtbl_billinginvoice_items'];
            
        if (isset($_REQUEST['config_dbtbl_billinginvoice_status']))
            $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'] = $_REQUEST['config_dbtbl_billinginvoice_status'];
            
        if (isset($_REQUEST['config_dbtbl_billinginvoice_type']))
            $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'] = $_REQUEST['config_dbtbl_billinginvoice_type'];
            
        if (isset($_REQUEST['config_dbtbl_payment_type']))
            $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'] = $_REQUEST['config_dbtbl_payment_type'];

        if (isset($_REQUEST['config_dbtbl_payments']))
        	$configValues['CONFIG_DB_TBL_DALOPAYMENTS'] = $_REQUEST['config_dbtbl_payments'];

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
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo t('Intro','configdb.php'); ?>
				<h144>&#x2754;</h144></a></h2>

                <div id="helpPage" style="display:none;visibility:visible" >
					<?php echo t('helpPage','configdb') ?>
					<br/>
				</div>
                <?php
					include_once('include/management/actionMessages.php');
                ?>

				<form name="dbsettings" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<div class="tabber">

     <div class="tabbertab" title="<?php echo t('title','Settings'); ?>">

        <fieldset>

                <h302><?php echo t('title','Settings'); ?></h302>
		<br/>

		<ul>

                <li class='fieldset'>
                <label for='config_dbengine' class='form'><?php echo t('all','DBEngine')?></label>
		<select class='form' name="config_dbengine">
			<option value="<?php echo $configValues['CONFIG_DB_ENGINE'] ?>"> <?php echo $configValues['CONFIG_DB_ENGINE'] ?> </option>
			<option value=""></option>
			<option value="mysql"> MySQL </option>
			<option value="pgsql"> PostgreSQL </option>
			<option value="odbc"> ODBC </option>
			<option value="mssql"> MsSQL </option>
			<option value="mysqli"> MySQLi </option>
			<option value="msql"> MsQL </option>
			<option value="sybase"> Sybase </option>
			<option value="sqlite"> Sqlite </option>
			<option value="oci8"> Oci8  </option>
			<option value="ibase"> ibase </option>
			<option value="fbsql"> fbsql </option>
			<option value="informix"> informix </option>
		</select>
		</li>

		<li class='fieldset'>
		<label for='config_dbhost' class='form'><?php echo t('all','DatabaseHostname') ?></label>
		<input type='text' value="<?php echo $configValues['CONFIG_DB_HOST'] ?>" name="config_dbhost" />
		</li>
		
		<li class='fieldset'>
		<label for='config_dbport' class='form'><?php echo t('all','DatabasePort') ?></label>
		<input type='text' value="<?php echo $configValues['CONFIG_DB_PORT'] ?>" name="config_dbport" />
		</li>

		<li class='fieldset'>
		<label for='config_dbuser' class='form'><?php echo t('all','DatabaseUser') ?></label>
		<input value="<?php echo $configValues['CONFIG_DB_USER'] ?>" name="config_dbuser" />
		</li>

		<li class='fieldset'>
		<label for='config_dbpass' class='form'><?php echo t('all','DatabasePass') ?></label>
		<input value="<?php echo $configValues['CONFIG_DB_PASS'] ?>" name="config_dbpass" />
		</li>

		<li class='fieldset'>
		<label for='db_name' class='form'><?php echo  t('all','DatabaseName') ?></label>
		<input value="<?php echo $configValues['CONFIG_DB_NAME'] ?>" name="config_dbname" />
		</li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
                </li>

                </ul>
	
	</fieldset>

	</div>

     <div class="tabbertab" title="<?php echo t('title','DatabaseTables'); ?>">

		<fieldset>

                <h302><?php echo t('title','DatabaseTables'); ?></h302>
		<br/>

		<ul>

		<li class='fieldset'>
                <label for='config_dbtbl_radcheck' class='form'><?php echo t('all','radcheck')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADCHECK'] ?>" name="config_dbtbl_radcheck"/>
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_radreply' class='form'><?php echo t('all','radreply')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADREPLY'] ?>" name="config_dbtbl_radreply" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_radgroupreply' class='form'><?php echo t('all','radgroupreply')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADGROUPREPLY'] ?>" name="config_dbtbl_radgroupreply" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_radgroupcheck' class='form'><?php echo t('all','radgroupcheck')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADGROUPCHECK'] ?>" name="config_dbtbl_radgroupcheck" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_usergroup' class='form'><?php echo t('all','usergroup')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADUSERGROUP'] ?>" name="config_dbtbl_usergroup" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_radacct' class='form'><?php echo t('all','radacct')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADACCT'] ?>" name="config_dbtbl_radacct" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_nas' class='form'><?php echo t('all','nas')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADNAS'] ?>" name="config_dbtbl_nas" />
		</li>


				 <li class='fieldset'>
                <label for='config_dbtbl_hunt' class='form'><?php echo t('all','hunt')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADHG'] ?>" name="config_dbtbl_hunt" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_radpostauth' class='form'><?php echo t('all','radpostauth')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADPOSTAUTH'] ?>" name="config_dbtbl_radpostauth" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_radippool' class='form'><?php echo t('all','radippool')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_RADIPPOOL'] ?>" name="config_dbtbl_radippool" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_userinfo' class='form'><?php echo t('all','userinfo')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOUSERINFO'] ?>" name="config_dbtbl_userinfo" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_dictionary' class='form'><?php echo t('all','dictionary')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALODICTIONARY'] ?>" name="config_dbtbl_dictionary" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_realms' class='form'><?php echo t('all','realms')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOREALMS'] ?>" name="config_dbtbl_realms" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_proxys' class='form'><?php echo t('all','proxys')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOPROXYS'] ?>" name="config_dbtbl_proxys" />
		</li>

				<li class='fieldset'>
                <label for='config_dbtbl_billingmerchant' class='form'><?php echo t('all','billingmerchant')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGMERCHANT'] ?>" name="config_dbtbl_billingmerchant" />
		</li>
		
                <li class='fieldset'>
                <label for='config_dbtbl_billingpaypal' class='form'><?php echo t('all','billingpaypal')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGPAYPAL'] ?>" name="config_dbtbl_billingpaypal" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_billingplans' class='form'><?php echo t('all','billingplans')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'] ?>" name="config_dbtbl_billingplans" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_rates' class='form'><?php echo t('all','billingrates')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGRATES'] ?>" name="config_dbtbl_rates" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_billinghistory' class='form'><?php echo t('all','billinghistory')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'] ?>" name="config_dbtbl_billinghistory" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_billinginfo' class='form'><?php echo t('all','billinginfo')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'] ?>" name="config_dbtbl_billinginfo" />
		</li>

		<li class='fieldset'>
        <label for='config_dbtbl_billinginvoice' class='form'><?php echo t('all','Invoice')?></label>
        <input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICE'] ?>" name="config_dbtbl_billinginvoice" />
        </li>

		<li class='fieldset'>
        <label for='config_dbtbl_billinginvoice_items' class='form'><?php echo t('all','InvoiceItems')?></label>
        <input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICEITEMS'] ?>" name="config_dbtbl_billinginvoice_items" />
        </li>
        
        <li class='fieldset'>
        <label for='config_dbtbl_billinginvoice_status' class='form'><?php echo t('all','InvoiceStatus')?></label>
        <input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICESTATUS'] ?>" name="config_dbtbl_billinginvoice_status" />
        </li>
        
        <li class='fieldset'>
        <label for='config_dbtbl_billinginvoice_type' class='form'><?php echo t('all','InvoiceType')?></label>
        <input value="<?php echo $configValues['CONFIG_DB_TBL_DALOBILLINGINVOICETYPE'] ?>" name="config_dbtbl_billinginvoice_type" />
        </li>

		<li class='fieldset'>
        <label for='config_dbtbl_payment_type' class='form'><?php echo t('all','payment_type')?></label>
        <input value="<?php echo $configValues['CONFIG_DB_TBL_DALOPAYMENTTYPES'] ?>" name="config_dbtbl_payment_type" />
        </li>

        <li class='fieldset'>
        <label for='config_dbtbl_payments' class='form'><?php echo t('all','payments')?></label>
        <input value="<?php echo $configValues['CONFIG_DB_TBL_DALOPAYMENTS'] ?>" name="config_dbtbl_payments" />
        </li>



                <li class='fieldset'>
                <label for='config_dbtbl_operators' class='form'><?php echo t('all','operators')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOOPERATORS'] ?>" name="config_dbtbl_operators" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_operators_acl' class='form'><?php echo t('all','operators_acl')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL'] ?>" name="config_dbtbl_operators_acl" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_operators_acl_files' class='form'><?php echo t('all','operators_acl_files')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOOPERATORS_ACL_FILES'] ?>" name="config_dbtbl_operators_acl_files" />
		</li>

                <li class='fieldset'>
                <label for='config_dbtbl_hotspots' class='form'><?php echo t('all','hotspots')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALOHOTSPOTS'] ?>" name="config_dbtbl_hotspots" />
		</li>
		
                <li class='fieldset'>
                <label for='config_dbtbl_node' class='form'><?php echo t('all','node')?></label>
		<input value="<?php echo $configValues['CONFIG_DB_TBL_DALONODE'] ?>" name="config_dbtbl_node" />
		</li>

                <li class='fieldset'>
                <br/>
                <hr><br/>
                <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' class='button' />
                </li>

                </ul>

	</table>

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
