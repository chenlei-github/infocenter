<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("location: http://{$_SERVER['SERVER_NAME']}/google-auth/login.php");
}
