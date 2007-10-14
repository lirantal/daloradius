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

<table border='2' class='table1'>
<tr><td>
                                                <b>First name</b>
</td><td>
                                                <input value='"; if (isset($ui_firstname)) echo $ui_firstname; echo "' name='firstname'/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Last name</b>
</td><td>
                                                <input value='"; if (isset($ui_lastname)) echo $ui_lastname; echo "' name='lastname'/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Email</b>
</td><td>
                                                <input value='"; if (isset($ui_email)) echo $ui_email; echo "' name='email'/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Department</b>
</td><td>
                                                <input value='"; if (isset($ui_department)) echo $ui_department; echo "' name='department'/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Company</b>
</td><td>
                                                <input value='"; if (isset($ui_company)) echo $ui_company; echo "' name='company'/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Work phone</b>
</td><td>
                                                <input value='"; if (isset($ui_workphone)) echo $ui_workphone; echo "' name='workphone'/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Home phone</b>
</td><td>
                                                <input value='"; if (isset($ui_homephone)) echo $ui_homephone; echo "' name='homephone'/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Mobile phone</b>
</td><td>
                                                <input value='"; if (isset($ui_mobilephone)) echo $ui_mobilephone; echo "' name='mobilephone'/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Notes</b>
</td><td>
                                                <input value='"; if (isset($ui_notes)) echo $ui_notes; echo "' name='notes'/>
                                                </font>
</td></tr>
</table>

";



?>
