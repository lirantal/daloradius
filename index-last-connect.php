<?php

    include ("menu-home.php");
	
?>		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">Last 50 Connection Attempts</a></h2>
				<p>

<?php
	include 'library/table-last_connection_attempts.php';
?>
				</p>
		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>
		
		</div>
		
</div>
</div>


</body>
</html>
