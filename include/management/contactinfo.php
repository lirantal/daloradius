<?php
/*********************************************************************
* Name: userinfo.php
* Author: Liran tal <liran.tal@gmail.com>
*********************************************************************/
?>

<fieldset>

	<h302> Contact Info </h302>

	<label for='ownername'><?php echo $l['ContactInfo']['OwnerName'] ?></label>
	<input name='owner' type='text' id='owner' value='' tabindex=300 />
	<br/>

	<label for='emailowner'><?php echo $l['ContactInfo']['OwnerEmail'] ?></label>
	<input name='email_owner' type='text' id='email_owner' value='' tabindex=301 />
	<br/>

	<label for='managername'><?php echo $l['ContactInfo']['ManagerName'] ?></label>
	<input name='manager' type='text' id='manager' value='' tabindex=302 />
	<br/>

	<label for='emailmanager'><?php echo $l['ContactInfo']['ManagerEmail'] ?></label>
	<input name='email_manager' type='text' id='email_manager' value='' tabindex=303 />
	<br/>

	<label for='company'><?php echo $l['ContactInfo']['Company'] ?></label>
	<input name='company' type='text' id='company' value='' tabindex=304 />
	<br/>

	<label for='address'><?php echo $l['ContactInfo']['Address'] ?></label>
	<input name='address' type='text' id='address' value='' tabindex=305 />
	<br/>

	<label for='phone1'><?php echo $l['ContactInfo']['Phone1'] ?></label>
	<input name='phone1' type='text' id='phone1' value='' tabindex=306 />
	<br/>

	<label for='phone2'><?php echo $l['ContactInfo']['Phone2'] ?></label>
	<input name='phone1' type='text' id='phone2' value='' tabindex=307 />
	<br/>

	<label for='hotspot_type'><?php echo $l['ContactInfo']['HotspotType'] ?></label>
	<input name='hotspot_type' type='text' id='hotspot_type' value='' tabindex=308 />
	<br/>

	<label for='website'><?php echo $l['ContactInfo']['Website'] ?></label>
	<input name='website' type='text' id='website' value='' tabindex=309 />
	<br/>

        <br/><br/>
        <hr><br/>

        <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000
		class='button' />

</fieldset>
