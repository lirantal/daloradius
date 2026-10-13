<?php

/**********************************************************************************************************
 * daloRADIUS - advanced RADIUS web management application
 * Author: Liran Tal <liran.tal@gmail.com>
 *
 * Credits to the implementation of captcha are due to G.Sujith Kumar of codewalkers
 **********************************************************************************************************
 ************************ Configuration Settings **********************************************************/

$configValues['CONFIG_DB_ENGINE'] = "mysql";
$configValues['CONFIG_DB_USER'] = "root";
$configValues['CONFIG_DB_PASS'] = "";
$configValues['CONFIG_DB_HOST'] = "127.0.0.1";
$configValues['CONFIG_DB_NAME'] = "radius097";

$configValues['CONFIG_GROUP_NAME'] = "somegroup";	/* the group name to add the user to */
$configValues['CONFIG_GROUP_PRIORITY'] = 0;		/* an integer only! */

$usernamePrefix = "guest";

 /**********************************************************************************************************/


header('Content-Type: text/html; charset=UTF-8');
session_start();
$configValues['CONFIG_DB_TBL_RADUSERGROUP'] = 'usergroup';
$configValues['CONFIG_USERNAME_PREFIX'] = $usernamePrefix;
$configValues['CONFIG_USERNAME_LENGTH'] = 4;
$configValues['CONFIG_PASSWORD_LENGTH'] = 4;
$configValues['CONFIG_USER_ALLOWEDRANDOMCHARS'] = 'abcdefghijkmnopqrstuvwxyz023456789';
require_once dirname(__DIR__, 2) . '/common/freeSignup.php';
$signup = dalo_chilli_signup_request($configValues);
if ($signup['status'] === 'success') {
    echo '<br/><br/>Welcome ' . htmlspecialchars($signup['firstname'], ENT_QUOTES, 'UTF-8') . ',<br/>' .
        'Your username is: ' . htmlspecialchars($signup['username'], ENT_QUOTES, 'UTF-8') .
        ' <br/>and your password is: ' . htmlspecialchars($signup['password'], ENT_QUOTES, 'UTF-8') . ' <br/>';
    exit;
}
if ($signup['status'] === 'fieldsFailure') {
    echo '<br/><br/>Please fill in your first and last name <br/>';
} elseif ($signup['status'] === 'captchaFailure') {
    echo 'bad capctah key...';
} elseif ($signup['status'] === 'databaseFailure') {
    echo 'Signup could not be completed';
}
?>


<html>
<head>
<title>
User Sign-up Page
</title>

<script type="text/javascript">

function setFocus() {
        document.signup.firstname.focus();
}

</script>
</head>

<body onLoad="return setFocus();">

<br/><br/>

<h2>Contact Details </h2>

<form name="signup" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
<?php echo dalo_chilli_signup_csrf_field(); ?>

	First name <input type="text" value="" name="firstname" /> <br/>
	Last name <input type="text" value="" name="lastname" /> <br/>
	Email: <input type="text" value="" name="email" /> <br/><br/>


<h3>Authenticate</h3>

	Enter the number in the image: <input name="formKey" type="text" id="formKey" />
	<img src="php_captcha.php">

	<br/><br/>
	<input type="submit" name="submit" value="Register" /> <br/>
</form>

<br/><br/>

</body>
</html>
