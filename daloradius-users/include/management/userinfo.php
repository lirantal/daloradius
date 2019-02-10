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
        <input value='"; if (isset($ui_firstname)) echo $ui_firstname; echo "' name='firstname' tabindex=300 />
	<br/>
	
	<label for='lastname' class='form'>".t('ContactInfo','LastName')."</label>
        <input value='"; if (isset($ui_lastname)) echo $ui_lastname; echo "' name='lastname' tabindex=301 />
	<br/>

	<label for='email' class='form'>".t('ContactInfo','Email')."</label>
        <input value='"; if (isset($ui_email)) echo $ui_email; echo "' name='email' tabindex=302 />
        <br/>

	<br/>
	<h301> Business </h301>
	<br/>

	<label for='department' class='form'>".t('ContactInfo','Department')."</label>
        <input value='"; if (isset($ui_department)) echo $ui_department; echo "' name='department' tabindex=303 />
        <br/>

	<label for='company' class='form'>".t('ContactInfo','Company')."</label>
	<input value='"; if (isset($ui_company)) echo $ui_company; echo "' name='company' tabindex=304 />
        <br/>

	<label for='workphone' class='form'>".t('ContactInfo','WorkPhone')."</label>
	<input value='"; if (isset($ui_workphone)) echo $ui_workphone; echo "' name='workphone' tabindex=305 />
        <br/>

	<label for='homephone' class='form'>".t('ContactInfo','HomePhone')."</label>
	<input value='"; if (isset($ui_homephone)) echo $ui_homephone; echo "' name='homephone' tabindex=306 />
        <br/>

	<label for='mobilephone' class='form'>".t('ContactInfo','MobilePhone')."</label>
	<input value='"; if (isset($ui_mobilephone)) echo $ui_mobilephone; echo "' name='mobilephone' tabindex=307 />
        <br/>

        <label for='address' class='form'>".t('ContactInfo','Address')."</label>
        <input value='"; if (isset($ui_address)) echo $ui_address; echo "' name='address' tabindex=308 />
        <br/>

        <label for='city' class='form'>".t('ContactInfo','City')."</label>
        <input value='"; if (isset($ui_city)) echo $ui_city; echo "' name='city' tabindex=309 />
        <br/>

        <label for='state' class='form'>".t('ContactInfo','State')."</label>
        <input value='"; if (isset($ui_state)) echo $ui_state; echo "' name='state' tabindex=310 />
        <br/>

        <label for='country' class='form'>".t('ContactInfo','Country')."</label>
        <input value='"; if (isset($ui_country)) echo $ui_country; echo "' name='country' tabindex=310 />
        <br/>
        
        <label for='zip' class='form'>".t('ContactInfo','Zip')."</label>
        <input value='"; if (isset($ui_zip)) echo $ui_zip; echo "' name='zip' tabindex=311 />
        <br/>


	<br/>
	<hr><br/>

	<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />

</fieldset>

";



?>
