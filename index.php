<?php
session_start();
include('./connection.php');
date_default_timezone_set('Asia/Singapore');
$adminPath = './admin/authentication'; // Change this according to your file structure
header("Location: $adminPath/admin-login.php");
exit;