<!doctype html>
<html>
<title>Login with Google Account OAuth</title>
<body style="text-align: center">
    <h1>You are not allowed to view this page!</h1>
<?php
session_start();

if (!isset($_SESSION['google_data'])) {
    // Redirection to login page
    // echo ("no google_Data");exit;
    header('location: login.php');
} else {
    // print_r($userdata);exit;
    $userdata     = $_SESSION['google_data'];
    $email        = $userdata['email'];
    $email_suffix = stristr($email, '@');
    // var_dump($_SESSION);die;

    if ((! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
        || (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
        || (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) {
        $server_request_scheme = 'https';
    } else {
        $server_request_scheme = 'http';
    }

    if ($email_suffix == '@infolife.mobi' || $email_suffix == '@amberweather.com') {
        $_SESSION['user_email'] = $email;
        // var_dump($_SERVER['SERVER_NAME']);die;
        header("location: {$server_request_scheme}://{$_SERVER['SERVER_NAME']}");
    }
}
?>
</body>
</html>
