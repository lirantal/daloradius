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

function drawUserInfo() {

echo <<<EOF

<table border='2' class='table1'>
<tr><td>
                                                <b>First name</b>
</td><td>
                                                <input value="" name="firstname"/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Last name</b>
</td><td>
                                                <input value="" name="lastname"/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Email</b>
</td><td>
                                                <input value="" name="email"/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Department</b>
</td><td>
                                                <input value="" name="department"/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Company</b>
</td><td>
                                                <input value="" name="company"/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Work phone</b>
</td><td>
                                                <input value="" name="workphone"/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Home phone</b>
</td><td>
                                                <input value="" name="homephone"/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Mobile phone</b>
</td><td>
                                                <input value="" name="mobilephone"/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Notes</b>
</td><td>
                                                <input value="" name="notes"/>
                                                </font>
</td></tr>
</table>

EOF;


}


?>
