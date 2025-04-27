<?php
session_start();
// Unset user-specific session variables
unset($_SESSION['user_logged_in']);
unset($_SESSION['visitor_id']);
unset($_SESSION['visitor_name']);

// Redirect to login page
header("Location: user_login.php");
exit();
?>