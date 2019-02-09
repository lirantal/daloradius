<?php
/*********************************************************************
* Name: userinfo.php
* Author: Liran tal <liran.tal@gmail.com>
*********************************************************************/
?>

<fieldset>

	<h302> Contact Info </h302>
	<br/>

	<label for='ownername' class='form'><?php echo t('ContactInfo','OwnerName') ?></label>
	<input name='owner' type='text' id='owner' value='<?php if (isset($owner)) echo $owner; ?>' 
		tabindex=300 />
	<br/>

	<label for='emailowner' class='form'><?php echo t('ContactInfo','OwnerEmail') ?></label>
	<input name='email_owner' type='text' id='email_owner' value='<?php if (isset($email_owner)) echo $email_owner; ?>'
		tabindex=301 />
	<br/>

	<label for='managername' class='form'><?php echo t('ContactInfo','ManagerName') ?></label>
	<input name='manager' type='text' id='manager' value='<?php if (isset($manager)) echo $manager; ?>'
		tabindex=302 />
	<br/>

	<label for='emailmanager' class='form'><?php echo t('ContactInfo','ManagerEmail') ?></label>
	<input name='email_manager' type='text' id='email_manager' value='<?php if (isset($email_manager)) echo $email_manager; ?>'
		tabindex=303 />
	<br/>

	<label for='company' class='form'><?php echo t('ContactInfo','Company') ?></label>
	<input name='company' type='text' id='company' value='<?php if (isset($company)) echo $company; ?>'
		tabindex=304 />
	<br/>

	<label for='address' class='form'><?php echo t('ContactInfo','Address') ?></label>
        <textarea class='form' name='address' value='<?php if (isset($address)) echo $address; ?>' tabindex=305 ></textarea>
	<br/>

	<label for='phone1' class='form'><?php echo t('ContactInfo','Phone1') ?></label>
	<input name='phone1' type='text' id='phone1' value='<?php if (isset($phone1)) echo $phone1; ?>'
		tabindex=306 />
	<br/>

	<label for='phone2' class='form'><?php echo t('ContactInfo','Phone2') ?></label>
	<input name='phone2' type='text' id='phone2' value='<?php if (isset($phone2)) echo $phone2; ?>'
		tabindex=307 />
	<br/>

	<label for='hotspot_type' class='form'><?php echo t('ContactInfo','HotspotType') ?></label>
	<input name='hotspot_type' type='text' id='hotspot_type' value='<?php if (isset($hotspot_type)) echo $hotspot_type; ?>'
		tabindex=308 />
	<br/>

	<label for='companywebsite' class='form'><?php echo t('ContactInfo','CompanyWebsite') ?></label>
	<input name='companywebsite' type='text' id='companywebsite' value='<?php if (isset($companywebsite)) echo $companywebsite; ?>'
		tabindex=309 />
	<br/>

	<label for='companyemail' class='form'><?php echo t('ContactInfo','CompanyEmail') ?></label>
	<input name='companyemail' type='text' id='companyemail' value='<?php if (isset($companyemail)) echo $companyemail; ?>'
		tabindex=310 />
	<br/>

	<label for='companyphone' class='form'><?php echo t('ContactInfo','CompanyPhone') ?></label>
	<input name='companyphone' type='text' id='companyphone' value='<?php if (isset($companyphone)) echo $companyphone; ?>'
		tabindex=311 />
	<br/>

	<label for='companycontact' class='form'><?php echo t('ContactInfo','CompanyContact') ?></label>
	<input name='companycontact' type='text' id='companycontact' value='<?php if (isset($companycontact)) echo $companycontact; ?>'
		tabindex=312 />
	<br/>

        <br/>
        <h301> Other </h301>
        <br/>

        <br/>
        <label for='creationdate' class='form'><?php echo t('all','CreationDate') ?></label>
        <input disabled value='<?php if (isset($creationdate)) echo $creationdate ?>' tabindex=313 />
        <br/>

        <label for='creationby' class='form'><?php echo t('all','CreationBy') ?></label>
        <input disabled value='<?php if (isset($creationby)) echo $creationby ?>' tabindex=314 />
        <br/>

        <label for='updatedate' class='form'><?php echo t('all','UpdateDate') ?></label>
        <input disabled value='<?php if (isset($updatedate)) echo $updatedate ?>' tabindex=315 />
        <br/>

        <label for='updateby' class='form'><?php echo t('all','UpdateBy') ?></label>
        <input disabled value='<?php if (isset($updateby)) echo $updateby ?>' tabindex=316 />
        <br/>


        <br/><br/>
        <hr><br/>

        <input type='submit' name='submit' value='<?php echo t('buttons','apply') ?>' tabindex=10000
		class='button' />

</fieldset>
