<?php
include "page_head.php";
?>
<body onload="document.login.operator_user.focus()">
<div class="container">
  <div class="row dalo_login_panel">
    <div class="col-lg-4 col-lg-offset-2 dalo_login_panel_left"> <img class="center-block" src="images/daloradius_small.png"> </img> <br/>
      <h4> <?php echo _('RADIUS Management'); ?> <br/>
        <?php echo _('Reporting | Accounting | Billing'); ?> </h4>
      <br/>
      <h5> <?php echo _('daloRADIUS &#169; 2007 by Liran Tal'); ?> <br/>
        <?php echo _('Template design by TerenceChuen.'); ?> </h5>
    </div>
    <div class="col-lg-4 dalo_login_panel_right">
      <form action="dologin.php" class="form-signin" method="post" name="login">
        <h2 class="form-signin-heading"> <?php echo _('Please sign in'); ?> </h2>
        <label class="sr-only" for="inputusername"> <?php echo _('User Name'); ?> </label>
        <div class="input-group">
          <div class="input-group-addon"><?php echo _('Username'); ?></div>
          <input autofocus class="form-control" id="inputusername" placeholder="<?php echo _('User Name'); ?>" required name="operator_user" tabindex="1" type="text">
        </div>
        <div class="input-group">
          <div class="input-group-addon"><?php echo _('Password'); ?></div>
          <input class="form-control" id="inputPassword" placeholder="Password" required type="password" name="operator_pass" tabindex="2"  value="">
        </div>
        <div class="input-group">
          <div class="input-group-addon"><?php echo _('Language'); ?></div>
          <select class="form-control" onchange="MM_jumpMenu('parent',this,0)">
            <option value="?locale=en_US" <?php if ($locale == 'en_US') {echo "selected=\"selected\"";}?>> <?php echo _('English (United States)'); ?> </option>
            <option value="?locale=zh_CN" <?php if ($locale == 'zh_CN') {echo "selected=\"selected\"";}?> > <?php echo _('Chinese (PRC)'); ?> </option>
          </select>
        </div>
        <div class="input-group">
          <div class="input-group-addon"><?php echo _('Locations'); ?></div>
          <select class="form-control" name="location" tabindex="3">
            <option value="default"> Default </option>
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
        <button class="btn btn-lg btn-primary btn-block" tabindex="3" type="submit" value="Login"> <?php echo _('Sign in'); ?> </button>
        </input>
        </input>
      </form>
    </div>
  </div>
</div>
<?php
include "page_footer.php";
?>
</body>
