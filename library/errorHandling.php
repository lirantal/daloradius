<?php

	/* global error handling for all PEAR packages, it isn't too wise to do
	   that as I've no idea what other php applications are running on this machine
	   even though this is really just about handling the error but still.
           So instead we're using the object's error handling method (see library/opendb.php)
	   PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'errorHandler');
	*/

	function errorHandler($err) {
		echo("<br/><b>Database error</b><br>
	        	<b>Error Message: </b>" . $err->getMessage() . "<br><b>Debug info: </b>" . $err->getDebugInfo() . "<br>");
	}

?>
