<?php
/*********************************************************************
* Name: userinfo.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* This file extends the user management pages (new user, batch add
* users, edit user, quick add user and possibly others) by adding
* a section for user information
*
*********************************************************************/

echo "

<fieldset>
	
	<h302> Contact Info </h302>
	<br/>

	<h301> Personal </h301>
	<br/>

	<label for='username' class='form'>".t('ContactInfo','FirstName')."</label>
        <input value='"; if (isset($ui_firstname)) echo $ui_firstname; echo "' name='firstname' id='firstname' tabindex=300 />
	<br/>
	
	<label for='lastname' class='form'>".t('ContactInfo','LastName')."</label>
        <input value='"; if (isset($ui_lastname)) echo $ui_lastname; echo "' name='lastname' id='lastname' tabindex=301 />
	<br/>

	<label for='email' class='form'>".t('ContactInfo','Email')."</label>
        <input value='"; if (isset($ui_email)) echo $ui_email; echo "' name='email' id='email' tabindex=302 />
        <br/>

		<br/>
	 
	<label for='copycontact' class='form'> Copy contact information to billing </label>
		<input type='checkbox' name='copycontact' id='copycontact' onClick='copyUserBillInfo(this);'/>
		<br/>
		<br/>
		
	<br/>
	<h301> Business </h301>
	<br/>

	<label for='department' class='form'>".t('ContactInfo','Department')."</label>
        <input value='"; if (isset($ui_department)) echo $ui_department; echo "' name='department' tabindex=303 />
        <br/>

	<label for='company' class='form'>".t('ContactInfo','Company')."</label>
	<input value='"; if (isset($ui_company)) echo $ui_company; echo "' name='company' id='company' tabindex=304 />
        <br/>

	<label for='workphone' class='form'>".t('ContactInfo','WorkPhone')."</label>
	<input value='"; if (isset($ui_workphone)) echo $ui_workphone; echo "' name='workphone' id='workphone' tabindex=305 />
        <br/>

	<label for='homephone' class='form'>".t('ContactInfo','HomePhone')."</label>
	<input value='"; if (isset($ui_homephone)) echo $ui_homephone; echo "' name='homephone' tabindex=306 />
        <br/>

	<label for='mobilephone' class='form'>".t('ContactInfo','MobilePhone')."</label>
	<input value='"; if (isset($ui_mobilephone)) echo $ui_mobilephone; echo "' name='mobilephone' tabindex=307 />
        <br/>

	<label for='address' class='form'>".t('ContactInfo','Address')."</label>
	<input value='"; if (isset($ui_address)) echo $ui_address; echo "' name='address' id='address' tabindex=308 />
        <br/>

	<label for='city' class='form'>".t('ContactInfo','City')."</label>
	<input value='"; if (isset($ui_city)) echo $ui_city; echo "' name='city' id='city' tabindex=309 />
        <br/>

	<label for='state' class='form'>".t('ContactInfo','State')."</label>
	<input value='"; if (isset($ui_state)) echo $ui_state; echo "' name='state' id='state' tabindex=310 />
        <br/>
        
	<label for='country' class='form'>".t('ContactInfo','Country')."</label>
	<input value='"; if (isset($ui_country)) echo $ui_country; echo "' name='country' id='country' tabindex=310 />
        <br/>

	<label for='zip' class='form'>".t('ContactInfo','Zip')."</label>
	<input value='"; if (isset($ui_zip)) echo $ui_zip; echo "' name='zip' id='zip' tabindex=311 />
        <br/>

	<br/>
	<h301> Other </h301>
	<br/>

	<label for='notes' class='form'>".t('ContactInfo','Notes')."</label>
	<textarea class='form' name='notes' tabindex=312 >"; if (isset($ui_notes)) echo $ui_notes; echo "</textarea> 
        <br/>

"; // breaking echo

	if ($ui_changeuserinfo == 1) {
		$isUIChecked = "checked='yes'";
		$ui_changeuserinfo = 1;
	} else {
		$ui_changeuserinfo = 1;
		$isUIChecked = "";
	}


	if ($ui_enableUserPortalLogin == 1) {
		$isenableUserPortalLogin = "checked='yes'";
		$ui_enableUserPortalLogin = 1;
	} else {
		$ui_enableUserPortalLogin = 1;
		$isenableUserPortalLogin = "";
	}
	
echo "

	<label for='userupdate' class='form'>".t('ContactInfo','EnableUserUpdate')."</label>
	<input type='checkbox' class='form' name='changeUserInfo' value='$ui_changeuserinfo' $isUIChecked tabindex=313 />
        <br/>
        
	<label for='userupdate' class='form'>".t('ContactInfo','EnablePortalLogin')."</label>
	<input type='checkbox' class='form' name='enableUserPortalLogin' value='$ui_enableUserPortalLogin' $isenableUserPortalLogin tabindex=313 />
        <br/>

	<label for='portalLoginPassword' class='form'>".t('ContactInfo','PortalLoginPassword')."</label>
	<input name='portalLoginPassword' id='portalLoginPassword' value='"; if (isset($ui_PortalLoginPassword)) echo $ui_PortalLoginPassword; echo "' tabindex=314 />
        <br/>

	<br/>
	<label for='creationdate' class='form'>".t('all','CreationDate')."</label>
	<input disabled value='"; if (isset($ui_creationdate)) echo $ui_creationdate; echo "' tabindex=314 />
        <br/>

	<label for='creationby' class='form'>".t('all','CreationBy')."</label>
	<input disabled value='"; if (isset($ui_creationby)) echo $ui_creationby; echo "' tabindex=315 />
        <br/>

	<label for='updatedate' class='form'>".t('all','UpdateDate')."</label>
	<input disabled value='"; if (isset($ui_updatedate)) echo $ui_updatedate; echo "' tabindex=316 />
        <br/>

	<label for='updateby' class='form'>".t('all','UpdateBy')."</label>
	<input disabled value='"; if (isset($ui_updateby)) echo $ui_updateby; echo "' tabindex=317 />
        <br/>

	<br/>
	<hr><br/>

	$customApplyButton

</fieldset>

";



?>
