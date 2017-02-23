<?php
/*********************************************************************
* Name: userinfo.php
* Author: Liran tal <liran.tal@gmail.com>
*********************************************************************/
?>

<fieldset>

	<h302> Contact Info </h302>
	<br/>

	<label for='ownername' class='form'><?php echo $l['ContactInfo']['OwnerName'] ?></label>
	<input name='owner' type='text' id='owner' value='<?php if (isset($owner)) echo htmlspecialchars($owner, ENT_QUOTES); ?>' 
		tabindex=300 />
	<br/>

	<label for='emailowner' class='form'><?php echo $l['ContactInfo']['OwnerEmail'] ?></label>
	<input name='email_owner' type='text' id='email_owner' value='<?php if (isset($email_owner)) echo htmlspecialchars($email_owner, ENT_QUOTES); ?>'
		tabindex=301 />
	<br/>

	<label for='managername' class='form'><?php echo $l['ContactInfo']['ManagerName'] ?></label>
	<input name='manager' type='text' id='manager' value='<?php if (isset($manager)) echo htmlspecialchars($manager, ENT_QUOTES); ?>'
		tabindex=302 />
	<br/>

	<label for='emailmanager' class='form'><?php echo $l['ContactInfo']['ManagerEmail'] ?></label>
	<input name='email_manager' type='text' id='email_manager' value='<?php if (isset($email_manager)) echo htmlspecialchars($email_manager, ENT_QUOTES); ?>'
		tabindex=303 />
	<br/>

	<label for='company' class='form'><?php echo $l['ContactInfo']['Company'] ?></label>
	<input name='company' type='text' id='company' value='<?php if (isset($company)) echo htmlspecialchars($company, ENT_QUOTES); ?>'
		tabindex=304 />
	<br/>

	<label for='address' class='form'><?php echo $l['ContactInfo']['Address'] ?></label>
        <textarea class='form' name='address' value='<?php if (isset($address)) echo htmlspecialchars($address, ENT_QUOTES); ?>' tabindex=305 ></textarea>
	<br/>

	<label for='phone1' class='form'><?php echo $l['ContactInfo']['Phone1'] ?></label>
	<input name='phone1' type='text' id='phone1' value='<?php if (isset($phone1)) echo htmlspecialchars($phone1, ENT_QUOTES); ?>'
		tabindex=306 />
	<br/>

	<label for='phone2' class='form'><?php echo $l['ContactInfo']['Phone2'] ?></label>
	<input name='phone2' type='text' id='phone2' value='<?php if (isset($phone2)) echo htmlspecialchars($phone2, ENT_QUOTES); ?>'
		tabindex=307 />
	<br/>

	<label for='hotspot_type' class='form'><?php echo $l['ContactInfo']['HotspotType'] ?></label>
	<input name='hotspot_type' type='text' id='hotspot_type' value='<?php if (isset($hotspot_type)) echo htmlspecialchars($hotspot_type, ENT_QUOTES); ?>'
		tabindex=308 />
	<br/>

	<label for='companywebsite' class='form'><?php echo $l['ContactInfo']['CompanyWebsite'] ?></label>
	<input name='companywebsite' type='text' id='companywebsite' value='<?php if (isset($companywebsite)) echo htmlspecialchars($companywebsite, ENT_QUOTES); ?>'
		tabindex=309 />
	<br/>

	<label for='companyemail' class='form'><?php echo $l['ContactInfo']['CompanyEmail'] ?></label>
	<input name='companyemail' type='text' id='companyemail' value='<?php if (isset($companyemail)) echo htmlspecialchars($companyemail, ENT_QUOTES); ?>'
		tabindex=310 />
	<br/>

	<label for='companyphone' class='form'><?php echo $l['ContactInfo']['CompanyPhone'] ?></label>
	<input name='companyphone' type='text' id='companyphone' value='<?php if (isset($companyphone)) echo htmlspecialchars($companyphone, ENT_QUOTES); ?>'
		tabindex=311 />
	<br/>

	<label for='companycontact' class='form'><?php echo $l['ContactInfo']['CompanyContact'] ?></label>
	<input name='companycontact' type='text' id='companycontact' value='<?php if (isset($companycontact)) echo htmlspecialchars($companycontact, ENT_QUOTES); ?>'
		tabindex=312 />
	<br/>

        <br/>
        <h301> Other </h301>
        <br/>

        <br/>
        <label for='creationdate' class='form'><?php echo $l['all']['CreationDate'] ?></label>
        <input disabled value='<?php if (isset($creationdate)) echo htmlspecialchars($creationdate, ENT_QUOTES) ?>' tabindex=313 />
        <br/>

        <label for='creationby' class='form'><?php echo $l['all']['CreationBy'] ?></label>
        <input disabled value='<?php if (isset($creationby)) echo htmlspecialchars($creationby, ENT_QUOTES) ?>' tabindex=314 />
        <br/>

        <label for='updatedate' class='form'><?php echo $l['all']['UpdateDate'] ?></label>
        <input disabled value='<?php if (isset($updatedate)) echo htmlspecialchars($updatedate, ENT_QUOTES) ?>' tabindex=315 />
        <br/>

        <label for='updateby' class='form'><?php echo $l['all']['UpdateBy'] ?></label>
        <input disabled value='<?php if (isset($updateby)) echo htmlspecialchars($updateby, ENT_QUOTES) ?>' tabindex=316 />
        <br/>


        <br/><br/>
        <hr><br/>

        <input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' tabindex=10000
		class='button' />

</fieldset>
