<?php
// we must never forget to start the session
session_start();

$errorMessage = '';
   include 'library/opendb.php';

   $operator_user = $_REQUEST['operator_user'];
   $operator_pass = $_REQUEST['operator_pass'];

   // check if the user id and password combination exist in database
   $sql = "SELECT username
           FROM ".CONFIG_DB_TBL_DALOOPERATOR."
           WHERE username = '$operator_user'
                  AND password = '$operator_pass'";

   $result = mysql_query($sql)
             or die('Query failed. ' . mysql_error());

   if (mysql_num_rows($result) == 1) {
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
