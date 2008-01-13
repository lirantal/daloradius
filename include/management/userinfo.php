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

//<th colspan='2'> ".$l['table']['UserInfo']." </th>

echo "

<fieldset>
	<label for='username'>".$l['ContactInfo']['FirstName']."</label>
        <input value='"; if (isset($ui_firstname)) echo $ui_firstname; echo "' name='firstname' tabindex=300 />
	<br/>
	
	<label for='lastname'>".$l['ContactInfo']['LastName']."</label>
        <input value='"; if (isset($ui_lastname)) echo $ui_lastname; echo "' name='lastname' tabindex=301 />
	<br/>

	<label for='email'>".$l['ContactInfo']['Email']."</label>
        <input value='"; if (isset($ui_email)) echo $ui_email; echo "' name='email' tabindex=302 />
        <br/>

	<label for='department'>".$l['ContactInfo']['Department']."</label>
        <input value='"; if (isset($ui_department)) echo $ui_department; echo "' name='department' tabindex=303 />
        <br/>

	<label for='company'>".$l['ContactInfo']['Company']."</label>
	<input value='"; if (isset($ui_company)) echo $ui_company; echo "' name='company' tabindex=304 />
        <br/>

	<label for='workphone'>".$l['ContactInfo']['WorkPhone']."</label>
	<input value='"; if (isset($ui_workphone)) echo $ui_workphone; echo "' name='workphone' tabindex=305 />
        <br/>

	<label for='homephone'>".$l['ContactInfo']['HomePhone']."</label>
	<input value='"; if (isset($ui_homephone)) echo $ui_homephone; echo "' name='homephone' tabindex=306 />
        <br/>

	<label for='mobilephone'>".$l['ContactInfo']['MobilePhone']."</label>
	<input value='"; if (isset($ui_mobilephone)) echo $ui_mobilephone; echo "' name='mobilephone' tabindex=307 />
        <br/>

	<label for='notes'>".$l['ContactInfo']['Notes']."</label>
	<input value='"; if (isset($ui_notes)) echo $ui_notes; echo "' name='notes' tabindex=308 />
        <br/>

	<br/>
	<hr><br/>

	<input type='submit' name='submit' value=".$l['buttons']['apply']." class='button' />

</fieldset>

";



?>
