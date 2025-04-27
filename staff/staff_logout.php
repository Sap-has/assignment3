<?php
session_start();
// Unset staff-specific session variables
unset($_SESSION['staff_logged_in']);
unset($_SESSION['staff_id']);
unset($_SESSION['staff_name']);

// Redirect to login page
header("Location: staff_login.php");
exit();
?>