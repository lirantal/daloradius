<?php


// internal function to verify the custom session_id which we create upon logging in
function session_verify() {

	session_start();

	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
	if (substr(md5($REMOTE_ADDR), 0, 10+substr(session_id(), 0, 1)) == 
		substr(session_id(), 1, 10+substr(session_id(), 0, 1))) {
		$session_valid="yes";
	} else {
		$session_valid="no";
	}


	return $session_valid;

}



if (session_verify() == "yes") {

	if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
		header('Location: login.php');
		exit;
	}

} else {
	// maybe the session is verified but the user is not logged in
	header('Location: login.php');
	exit;
}


?>
