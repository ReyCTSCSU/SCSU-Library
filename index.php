<?php
require_once 'config.php';

// ✅ If user is already logged in → go to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// ✅ Otherwise → go to login
header('Location: login.php');
exit();
?>
