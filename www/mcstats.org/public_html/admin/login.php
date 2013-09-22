<?php

define('ROOT', '../');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

cacheCurrentPage();

admin_header();

if (isset($_POST['submit'])) {
    // Process login info
    $username = $_POST['username'];
    $password = $_POST['password'];

    // At the moment we only we basic authentication
    $real_username = check_login($username, $password);

    if ($real_username === false) {
        /// Throw out an error first
        echo '<div class="alert alert-error row-fluid" style="margin-left: 35%; width: 30%; text-align: center;">The username or password you have entered is incorrect.</div>';

        /// Resend the login form
        send_login();
    } else {
        echo '<div class="alert alert-success row-fluid" style="margin-left: 35%; width: 30%; text-align: center;">You have now been logged in. If you are not automatically redirected, click <a href="/admin/">here</a></div>
              <meta http-equiv="refresh" content="2; /admin/" /> ';

        $_SESSION['loggedin'] = 1;
        $_SESSION['username'] = $real_username;
    }

} else {
    send_login();
}

admin_footer();

function send_login() {
    echo '
        <div id="loginbox">
            <form id="loginform" class="form-vertical" action="" method="post">
				<p>Enter username and password to continue.</p>
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-user"></i></span><input type="text" name="username" placeholder="Username" />
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-lock"></i></span><input type="password" name="password" placeholder="Password" />
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <span class="pull-left"><a href="register.php">Need an account?</a></span>
                    <span class="pull-right"><input type="submit" name="submit" class="btn btn-inverse" value="Login" /></span>
                </div>
            </form>
        </div>

';
}

function admin_header() {
    echo <<<END

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>MCStats :: Administration</title>
		<meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="https://d2jz01fyat1phn.cloudfront.net/css/combined.css" rel="stylesheet" />
        <link href="https://d2jz01fyat1phn.cloudfront.net/css/bootstrap-login.css" rel="stylesheet" />
        <script src="https://d2jz01fyat1phn.cloudfront.net/javascript/jquery.js" type="text/javascript"></script>
    </head>
    <body>
        <div id="logo">
            <img src="https://d2jz01fyat1phn.cloudfront.net/img/logo.png" alt="" />
        </div>


END;

}

function admin_footer() {
    echo <<<END
    </body>
</html>
END;

}