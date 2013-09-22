<?php

define('ROOT', '../');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

if (is_loggedin()) {
    header('Location: /admin/');
    exit;
}

cacheCurrentPage();

admin_header();

if (isset($_POST['submit'])) {
    // Process login info
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if (strlen($password) < 3) {
        err('Password is too short');
        send_registration(htmlentities($username));
    } elseif ($password != $password2) {
        err('Passwords do not match');
        send_registration(htmlentities($username));
    } elseif ($password == $username) {
        err('Password cannot equal your username');
        send_registration(htmlentities($username));
    } elseif (!preg_match('/[a-zA-Z0-9 ]/', $username)) {
        err('Usernames can only be alphanumeric (contains: A-Z,a-z,0-9)');
        send_registration(htmlentities($username));
    } else {
        // the unique key prevents duplicate usernames but check first
        $statement = $master_db_handle->prepare('SELECT 1 FROM Author WHERE Name = ?');
        $statement->execute(array($username));

        if ($statement->fetch()) {
            err('That username is already taken!');
            send_registration(htmlentities($username));
        } else {
            // Hash the password
            $hashed_password = sha1($password);

            // Create a database entry
            $statement = $master_db_handle->prepare('INSERT INTO Author (Name, Password, Created) VALUES (?, ?, ?)');
            $statement->execute(array($username, $hashed_password, time()));

            // Redirect them
            echo '<div class="alert alert-success row-fluid" style="margin-left: 35%; width: 30%; text-align: center;">Registration complete! If you are not automatically redirected, click <a href="/admin/">here</a></div>
              <meta http-equiv="refresh" content="2; /admin/" /> ';
        }
    }


} else {
    send_registration();
}

admin_footer();

function send_registration($username = '') {
    echo '
        <div id="loginbox" style="height: 235px">
            <form id="loginform" class="form-vertical" action="" method="post">
				<p>You can add plugins once you have registered.</p>
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
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-lock"></i></span><input type="password" name="password2" placeholder="Confirm password" />
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <span class="pull-left"><a href="login.php">Already have an account?</a></span>
                    <span class="pull-right"><input type="submit" name="submit" class="btn btn-inverse" value="Register" /></span>
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
        <title>MCStats :: Registration</title>
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