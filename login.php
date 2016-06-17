<?php

isset($_REQUEST['error']) ? $error = $_REQUEST['error'] : $error = "";

// clean up error code to avoid XSS
$error = strip_tags(htmlspecialchars($error));

include 'library/daloradius.conf.php';

if (isset($_GET["locale"])) {
    $locale = $_GET["locale"];
} elseif (isset($configValues['CONFIG_LANG'])) {
    $locale = $configValues['CONFIG_LANG'];
} else {
    $locale = 'en_US';
}
putenv('LC_ALL=' . $locale);
setlocale(LC_ALL, $locale);

bindtextdomain("daloRADIUS", "./locale");
textdomain("daloRADIUS");
bind_textdomain_codeset("daloRADIUS", 'UTF-8');

include "page_head.php";
?>
<body>
<div class="container">

    <?php if ($error) {
        print "<div class=\"row dalo_login_error\">
        <div class=\"col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1\">
            <div class=\"alert alert-warning\">
                <a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>"
            . _('an error occured <br />either of the following:<br/>
1. bad username/password<br/>
2. an administrator is already logged-in (only one instance is allowed) <br/>
3. there appears to be more than one \'administrator\' user in the database <br/>') .
            " </div>
        </div>
    </div>";
    }
    ?>
    <div class="row <?php if ($error) {
        echo 'dalo_login_panel_error';
    } else {
        echo 'dalo_login_panel';
    } ?>">
        <div class="col-lg-4 col-lg-offset-2 col-md-4 col-md-offset-2 col-sm-5 col-sm-offset-1 dalo_login_panel_left">
            <img class="center-block"
                 src="images/daloradius_small.png"/> <br/>
            <h4> <?php echo _('RADIUS Management'); ?> <br/>
                <?php echo _('Reporting | Accounting | Billing'); ?> </h4>
            <br/>
            <h5> <?php echo _('daloRADIUS &#169; 2007 by Liran Tal'); ?> <br/>
                <?php echo _('Template design by TerenceChuen.'); ?> </h5>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-5 dalo_login_panel_right">
            <form action="dologin.php" method="post" name="login">
                <h2> <?php echo _('Please sign in'); ?> </h2>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo _('Username'); ?></div>
                    <input autofocus class="form-control" placeholder="<?php echo _('User Name'); ?>"
                           required name="operator_user" tabindex="1" type="text"/>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo _('Password'); ?></div>
                    <input class="form-control" placeholder="<?php echo _('Password'); ?>" required type="password"
                           name="operator_pass" tabindex="2" value=""/>
                </div>
                <script type="text/javascript">
                    function gotolocale() {
                        var locale = document.getElementById("locale").value;
                        window.location.href = "?locale=" + locale;
                    }
                </script>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo _('Language'); ?></div>
                    <select class="form-control" id="locale" name="locale" tabindex="3" onChange="gotolocale()">
                        <option value="en_US" <?php if ($locale == 'en_US') {
                            echo "selected=\"selected\"";
                        } ?> > <?php echo _('English (United States)'); ?> </option>
                        <option value="zh_CN" <?php if ($locale == 'zh_CN') {
                            echo "selected=\"selected\"";
                        } ?> > <?php echo _('Chinese (PRC)'); ?> </option>
                    </select>
                </div>
                <div class="input-group">
                    <div class="input-group-addon"><?php echo _('Locations'); ?></div>
                    <select class="form-control" name="location" tabindex="4">
                        <option value="default"> Default</option>
                        <?php
                        if (isset($configValues['CONFIG_LOCATIONS']) && is_array($configValues['CONFIG_LOCATIONS'])) {
                            foreach ($configValues['CONFIG_LOCATIONS'] as $locations => $val) {
                                echo "<option value=";
                                echo $locations;
                                echo " \"> ";
                                echo $locations;
                                echo "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <button class="btn btn-lg btn-primary btn-block" tabindex="5" type="submit"
                        value="Login"> <?php echo _('Sign in'); ?> </button>
            </form>
        </div>
    </div>
</div>

<?php
include "page_footer.php";
?>
</body>
