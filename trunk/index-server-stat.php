<?php

    include ("menu-home.php");
	
?>	
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][indexserverstat.php]; ?></a></h2>
				<p>

<?php
//	include 'library/phpsysinfo/index.php';
	include 'library/exten-server_info.php';
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
