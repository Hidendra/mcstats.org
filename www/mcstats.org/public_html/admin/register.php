<?php

define('ROOT', '../');
session_start();

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

if (is_loggedin())
{
    header('Location: /admin/');
    exit;
}

send_header();

if (isset($_POST['submit']))
{
    // Process login info
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if (strlen($password) < 3 || $password != $password2  || $password == $username || !preg_match('/[a-zA-Z0-9 ]/', $username))
    {
        err ('Care to try again? :-)');
        send_registration(htmlentities($username));
    } else
    {
        // the unique key prevents duplicate usernames but check first
        $statement = $master_db_handle->prepare('SELECT 1 FROM Author WHERE Name = ?');
        $statement->execute(array($username));

        if ($statement->fetch())
        {
            err ('That username is already taken!');
            send_registration(htmlentities($username));
        } else
        {
            // Hash the password
            $hashed_password = sha1($password);

            // Create a database entry
            $statement = $master_db_handle->prepare('INSERT INTO Author (Name, Password, Created) VALUES (?, ?, ?)');
            $statement->execute(array($username, $hashed_password, time()));

            // Redirect them
            echo '<div class="alert alert-success">Registration complete! If you are not automatically redirected, click <a href="/admin/">here</a></div>
              <meta http-equiv="refresh" content="2; /admin/" /> ';
        }
    }


}
else
{
    send_registration();
}

send_footer();

function send_registration($username = '')
{
    echo '
            <div class="row-fluid">

                <div class="hero-unit">
                    <div class="offset4">
                        <p>Once you complete registration, you will be able to add plugins to your account.</p>

                        <form action="" method="post" class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label" for="username">Username</label>
                                <div class="controls">
                                    <input type="text" name="username" value="' . $username . '" />
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label" for="password">Password</label>
                                <div class="controls">
                                    <input type="password" name="password" />
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label" for="password2">Confirm password</label>
                                <div class="controls">
                                    <input type="password" name="password2" />
                                </div>
                            </div>

                            <div class="control-group">
                                <div class="controls">
                                    <input type="submit" name="submit" value="Register" class="btn btn-success btn-large" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
';
}