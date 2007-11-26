<?php
// we must never forget to start the session
session_start();

$errorMessage = '';
   include 'library/opendb.php';

   $operator_user = $_REQUEST['operator_user'];
   $operator_pass = $_REQUEST['operator_pass'];

   // check if the user id and password combination exist in database
   $sql = "SELECT username FROM ".$configValues['CONFIG_DB_TBL_DALOOPERATOR']." WHERE username = '".$dbSocket->escapeSimple($operator_user)."' AND
AND password = '".$dbSocket->escapeSimple($operator_pass)."'";

   $res = $dbSocket->query($sql);

   if ($res->numRows() == 1) {
      // the user id and password match,
      // set the session
      $_SESSION['logged_in'] = true;
      $_SESSION['operator_user'] = $operator_user;

      // after login we move to the main page
      header('Location: index.php');
      exit;
   } else {
      header('Location: login.php?error=an error occured');
      exit;
   }

   include 'library/closedb.php';

?>
