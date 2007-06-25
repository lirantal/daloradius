<?php

    include ("library/checklogin.php");
    include_once ("lang/main.php");
    $operator = $_SESSION['operator_user'];

   if (isset($_REQUEST["submit"])) {
	$googleMapsCode = $_REQUEST['code'];
	writeGoogleMapsCode($googleMapsCode);
    }

    function writeGoogleMapsCode($googleMapsCode) {
	$myfile = "library/googlemaps.php";
	if ($fh = fopen($myfile, 'w') ) {
		$strCode = "<script src='http://maps.google.com/maps?file=api&amp;v=2&amp;key=" . $googleMapsCode . "'
			type='text/javascript'></script>";
		fwrite($fh, $strCode);
	        fclose($fh);
                echo "
                                <script language='JavaScript'>
                                <!--
				alert('".$l[messages][gismain1]."');
                                -->
                                </script>
                                ";

	} else {
                        echo "<font color='#FF0000'>".$l[messages][gismain2]."<b> $myfile </b><br/></font>";
			echo $l[messages][gismain3]."<br/>";
                        echo "
                                <script language='JavaScript'>
                                <!--
                                alert('could not open the file <b> $myfile </b> for writing!\\nCheck file permissions.');
                                -->
                                </script>
                                ";
	}
    }

?>

<?php
	
	include ("menu-gis.php");
	
?>


		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][gismain.php]; ?></a></h2>
				
				<p>

				<?echo $l[captions][gisinfo]; ?>	

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
