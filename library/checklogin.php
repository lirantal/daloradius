<?php
session_start();

// is the one accessing this page logged in or not?
if (!isset($_SESSION['logged_in'])
   || $_SESSION['logged_in'] !== true) {

   // not logged in, move to login page
   header('Location: login.php?error=please login first');
   exit;
}

?>
