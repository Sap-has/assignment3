<?php
session_start();
// Unset student-specific session variables
unset($_SESSION['student_logged_in']);
unset($_SESSION['visitor_id']);
unset($_SESSION['visitor_name']);

// Redirect to login page
header("Location: student_login.php");
exit();
?>