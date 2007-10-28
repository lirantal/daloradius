<?php
/*********************************************************************
* Name: userinfo.php
* Author: Liran tal <liran.tal@gmail.com>
*********************************************************************/
?>
<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($owner) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Owner Name</b>
</td><td>
                                                <input value="<?php echo $owner ?>" name="owner" tabindex=300 /><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($email_owner) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Owner Email</b>
</td><td>
                                                <input value="<?php echo $email_owner ?>" name="email_owner" tabindex=301 /><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($manager) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Manager Name</b>
</td><td>
                                                <input value="<?php echo $manager ?>" name="manager" tabindex=302 /><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <?php if (trim($email_manager) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>Manager Email</b>
</td><td>
                                                <input value="<?php echo $email_manager ?>" name="email_manager" tabindex=303 /><br/>
                                                </font>
</td></tr>
<tr><td>
                                                <b>Company</b>
</td><td>
                                                <input value="<?php echo $company ?>" name="company" tabindex=304 /><br/>
</td></tr>
<tr><td>
                                                <b>Address</b>
</td><td>
                                                <input value="<?php echo $address ?>" name="address" tabindex=305 /><br/>
</td></tr>
<tr><td>
                                                <b>Phone 1</b>
</td><td>
</td></tr>
<tr><td>
                                                <b>Phone 2</b>
</td><td>
                                                <input value="<?php echo $phone2 ?>" name="phone2" tabindex=307 /><br/>
</td></tr>
<tr><td>
                                                <b>HotSpot Type</b>
</td><td>
                                                <input value="<?php echo $hotspot_type ?>" name="hotspot_type" tabindex=308 /><br/>
</td></tr>
<tr><td>
                                                <b>Website</b>
</td><td>
                                                <input value="<?php echo $website ?>" name="website" tabindex=309 /><br/>
</td></tr>

</table>

