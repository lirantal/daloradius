<?php
// we must never forget to start the session
session_start();

$errorMessage = '';
   include 'library/opendb.php';

   $operator_user = $_REQUEST['operator_user'];
   $operator_pass = $_REQUEST['operator_pass'];

   // check if the user id and password combination exist in database
   $sql = "SELECT username FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username = '".$dbSocket->escapeSimple($operator_user)."' 
AND password = '".$dbSocket->escapeSimple($operator_pass)."'";

   $res = $dbSocket->query($sql);

   if ($res->numRows() == 1) {
      // the user id and password match,
      // set the session
      $_SESSION['logged_in'] = true;
      $_SESSION['operator_user'] = $operator_user;

	// lets update the lastlogint time for this operator
        $date = date("Y-m-d H:i:s");
	$sql = "UPDATE operators SET lastlogin='$date' WHERE username='$operator_user'";
	$res = $dbSocket->query($sql);

      // after login we move to the main page
      header('Location: index.php');
      exit;
   } else {
      header('Location: login.php?error=an error occured');
      exit;
   }

   include 'library/closedb.php';

?>
