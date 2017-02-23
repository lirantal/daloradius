<?php

?>

        <input type='button' onclick="self.location='config-maint-test-user.php?username=<?php 
		echo htmlspecialchars($username, ENT_QUOTES) ?>&password=<?php echo htmlspecialchars($user_password, ENT_QUOTES) ?>'" value='Test Connectivity' class='button'/>

        <input type='button' onclick="self.location='config-maint-disconnect-user.php?username=<?php 
		echo htmlspecialchars($username, ENT_QUOTES) ?>'" value='Disconnect User' class='button'/>

        <input type='button' onclick="self.location='acct-username.php?username=<?php echo htmlspecialchars($username, ENT_QUOTES) ?>'" value='Accounting' class='button'/>

        <input type='button' onclick="self.location='graphs-overall_logins.php?type=monthly&username=<?php
                echo htmlspecialchars($username, ENT_QUOTES) ?>'" value='Graphs - Logins' class='button'/>

        <input type='button' onclick="self.location='graphs-overall_download.php?type=monthly&username=<?php
                echo htmlspecialchars($username, ENT_QUOTES) ?>'" value='Graphs - Downloads' class='button'/>

        <input type='button' onclick="self.location='graphs-overall_upload.php?type=monthly&username=<?php
                echo htmlspecialchars($username, ENT_QUOTES) ?>'" value='Graphs - Uploads' class='button'/>

