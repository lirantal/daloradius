<?php

?>

        <input type='button' onclick="self.location='config-maint-test-user.php?username=<?php 
		echo $username ?>&password=<?php echo $user_password ?>'" value='Test Connectivity' class='button'/>

        <input type='button' onclick="self.location='config-maint-disconnect-user.php?username=<?php 
		echo $username ?>'" value='Disconnect User' class='button'/>
		
		<input type='button' onclick="self.location='acct-maintenance-cleanup.php?username=<?php 
		echo $username ?>'" value='Cleanup Stale Sessions' class='button'/>

        <input type='button' onclick="self.location='acct-username.php?username=<?php echo $username ?>'" value='Accounting' class='button'/>

        <input type='button' onclick="self.location='graphs-overall_logins.php?type=monthly&username=<?php
                echo $username ?>'" value='Graphs - Logins' class='button'/>

        <input type='button' onclick="self.location='graphs-overall_download.php?type=monthly&username=<?php
                echo $username ?>'" value='Graphs - Downloads' class='button'/>

        <input type='button' onclick="self.location='graphs-overall_upload.php?type=monthly&username=<?php
                echo $username ?>'" value='Graphs - Uploads' class='button'/>

