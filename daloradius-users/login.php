<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

include_once("library/sessions.php");
dalo_session_start();

if (array_key_exists('logged_in', $_SESSION)
    && $_SESSION['logged_in'] !== false) {
    header('Location: index.php');
    exit;
}

// this include "exports" $langCode that can be used later in this script
include("lang/main.php");

$dir = (strtolower($langCode) === 'ar') ? "rtl" : "ltr";

?>
<!DOCTYPE html>
<html lang="<?= $langCode ?>" dir="<?= $dir ?>">

<head>
    <title>daloRADIUS :: Login</title>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="copyright" content="Liran Tal & Filippo Lauria">
    <meta name="robots" content="noindex">

    <link rel="apple-touch-icon" sizes="180x180" href="static/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="static/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="static/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="static/images/favicon/site.webmanifest">

    <link rel="stylesheet" href="static/css/bootstrap.min.css">
    <link rel="stylesheet" href="static/css/icons/bootstrap-icons.css">

    <style>
html, body {
  height: 100%;
}

body {
  display: flex;
  align-items: center;
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #f5f5f5;
}

.form-login {
  max-width: 480px;
  padding: 15px;
}

.form-login .form-floating:focus-within {
  z-index: 2;
}

.form-login input[type="text"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}

.form-login input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

    </style>
</head>

<body>
    <main class="form-login w-100 m-auto">
    <form action="dologin.php" method="POST">
    <img class="mb-4" src="static/images/daloradius_small.png" alt="daloRADIUS" width="135" height="41">
    <h1 class="h3 mb-3 fw-normal d-flex align-items-center">
        <button class="btn btn-link btn-lg" data-bs-toggle="modal" data-bs-target="#welcome-modal">
            <i class="bi bi-info-circle-fill"></i>
        </button>
        <?= t('text','LoginRequired') ?>
    </h1>

    <div class="form-floating">
    <input type="text" class="form-control" id="login_user" name="login_user" placeholder="<?= t('all','Username') ?>" required>
    <label for="login_user"><?= t('all','Username') ?></label>
    </div>

    <div class="form-floating">
    <input type="password" class="form-control" id="login_pass" name="login_pass" placeholder="<?= t('all','Password') ?>" required>
    <label for="login_pass"><?= t('all','Password') ?></label>
    </div>

    <button class="w-100 btn btn-lg btn-primary" type="submit"><?= t('text','LoginPlease') ?></button>
    <small class="my-2 text-muted text-center d-block"><?= t('all','daloRADIUS') ?></small>

    <input name="csrf_token" type="hidden" value="<?= dalo_csrf_token() ?>">
    </form>


    <div class="modal modal-lg fade" id="welcome-modal" tabindex="-1" aria-labelledby="welcome-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="welcome-modal-label">Welcome!</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= t('helpPage','loginUsersPortal') ?>
                </div>
            </div>
        </div>
    </div>

<?php
    if (isset($_SESSION['login_error']) && $_SESSION['login_error'] !== false) {
        $message = t('messages','loginerror');
        echo <<<EOF

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="error-toast" class="toast align-items-start text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">{$message}</div>
                <button type="button" class="btn-close m-2" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>

window.onload = function() {
    var errorToast = document.getElementById('error-toast');
    var toast = new bootstrap.Toast(errorToast); toast.show();
}

    </script>

EOF;
        unset($_SESSION['login_error']);
    }
?>

    </main>

    <script src="static/js/bootstrap.bundle.min.js"></script>
</body>

</html>
