<?php
 
/**********************************************************************************
 * daloRADIUS - advanced RADIUS web management application
 * Author: Liran Tal <liran.tal@gmail.com>
 *
 *
 **********************************************************************************
 ************************ Configuration Settings **********************************/
 
$configValues['CONFIG_DB_ENGINE'] = "mysql";
$configValues['CONFIG_DB_USER'] = "root";
$configValues['CONFIG_DB_PASS'] = "";
$configValues['CONFIG_DB_HOST'] = "127.0.0.1";
$configValues['CONFIG_DB_NAME'] = "radius";
 
/**********************************************************************************/
 
 
 
 
function createPassword($length) {
 
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;
 
    while ($i <= ($length - 1)) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
 
    return $pass;
 
}
 
 
        if (isset($_REQUEST['submit'])) {
 
 
                isset($_REQUEST['firstname']) ? $firstname = $_REQUEST['firstname'] : $firstname = "";
                isset($_REQUEST['lastname']) ? $lastname = $_REQUEST['lastname'] : $lastname = "";
                isset($_REQUEST['email']) ? $email = $_REQUEST['email'] : $email = "";
 
 
                if ( ($firstname) && ($lastname) ) {
 
 
                        include_once ('DB.php');
                        $dbConnectString = $configValues['CONFIG_DB_ENGINE'] . "://".$configValues['CONFIG_DB_USER'].":".
                                $configValues['CONFIG_DB_PASS']."@".$configValues['CONFIG_DB_HOST']."/".$configValues['CONFIG_DB_NAME'];
 
                        $dbSocket = DB::connect($dbConnectString);
 
                        /* let's generate a random username and password
                           of length 4 and with username prefix 'guest' */
                        $rand = createPassword(4);
                        $username = "guest" . $rand;
                        $password = $rand;
 
                        /* let's add the user to the database */
                        $sql = "INSERT INTO radcheck values (0, '$username', 'User-Password', '==', '$password')";
                        $res = $dbSocket->query($sql);
 
                        $sql = "INSERT INTO userinfo (username, firstname, lastname, email) values ('$username', '$firstname', '$lastname', '
$email')";
                        $res = $dbSocket->query($sql);
 
                        echo "<br/><br/>
                                Welcome ". $_REQUEST['firstname']  . ",<br/>"
                                ."Your username is: $username <br/>and your password is: $password <br/>";
 
                        $dbSocket->disconnect();
 
                        exit;
                } else {
 
                        echo "<br/><br/>
                                Please fill in your first and last name <br/>";
                        exit;
                }
 
        }
 
 
 
 
 
?>
 
 
<html>
<head>
<title>
Self-Provision User Registration Page
</title>
</head>
 
<body>
 
<br/><br/>
<br/><br/>
<h3>Contact Details </h3>
<form name="selfcare" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
First name <input type="text" value="" name="firstname" /> <br/>
Last name <input type="text" value="" name="lastname" /> <br/>
Email: <input type="text" value="" name="email" /> <br/><br/>
<input type="submit" name="submit" value="Register" /> <br/>
</form>
<br/><br/>
 
 
</body>
</html>

