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

	<label for='username' class='form'>".$l['ContactInfo']['FirstName']."</label>
        <input value='"; if (isset($ui_firstname)) echo $ui_firstname; echo "' name='firstname' tabindex=300 />
	<br/>
	
	<label for='lastname' class='form'>".$l['ContactInfo']['LastName']."</label>
        <input value='"; if (isset($ui_lastname)) echo $ui_lastname; echo "' name='lastname' tabindex=301 />
	<br/>

	<label for='email' class='form'>".$l['ContactInfo']['Email']."</label>
        <input value='"; if (isset($ui_email)) echo $ui_email; echo "' name='email' tabindex=302 />
        <br/>

	<br/>
	<h301> Business </h301>
	<br/>

	<label for='department' class='form'>".$l['ContactInfo']['Department']."</label>
        <input value='"; if (isset($ui_department)) echo $ui_department; echo "' name='department' tabindex=303 />
        <br/>

	<label for='company' class='form'>".$l['ContactInfo']['Company']."</label>
	<input value='"; if (isset($ui_company)) echo $ui_company; echo "' name='company' tabindex=304 />
        <br/>

	<label for='workphone' class='form'>".$l['ContactInfo']['WorkPhone']."</label>
	<input value='"; if (isset($ui_workphone)) echo $ui_workphone; echo "' name='workphone' tabindex=305 />
        <br/>

	<label for='homephone' class='form'>".$l['ContactInfo']['HomePhone']."</label>
	<input value='"; if (isset($ui_homephone)) echo $ui_homephone; echo "' name='homephone' tabindex=306 />
        <br/>

	<label for='mobilephone' class='form'>".$l['ContactInfo']['MobilePhone']."</label>
	<input value='"; if (isset($ui_mobilephone)) echo $ui_mobilephone; echo "' name='mobilephone' tabindex=307 />
        <br/>

	<br/>
	<h301> Other </h301>
	<br/>

	<label for='notes' class='form'>".$l['ContactInfo']['Notes']."</label>
	<textarea class='form' name='notes' value='"; if (isset($ui_notes)) echo $ui_notes; echo "' tabindex=308 ></textarea> 
        <br/>

	<br/>
	<label for='creationdate' class='form'>".$l['all']['CreationDate']."</label>
	<input disabled value='"; if (isset($ui_creationdate)) echo $ui_creationdate; echo "' tabindex=309 />
        <br/>

	<label for='creationby' class='form'>".$l['all']['CreationBy']."</label>
	<input disabled value='"; if (isset($ui_creationby)) echo $ui_creationby; echo "' tabindex=310 />
        <br/>

	<label for='updatedate' class='form'>".$l['all']['UpdateDate']."</label>
	<input disabled value='"; if (isset($ui_updatedate)) echo $ui_updatedate; echo "' tabindex=311 />
        <br/>

	<label for='updateby' class='form'>".$l['all']['UpdateBy']."</label>
	<input disabled value='"; if (isset($ui_updateby)) echo $ui_updateby; echo "' tabindex=312 />
        <br/>

	<br/>
	<hr><br/>

	<input type='submit' name='submit' value=".$l['buttons']['apply']." class='button' />

</fieldset>

";



?>
