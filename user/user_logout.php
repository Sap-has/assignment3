<?php
session_start();
unset($_SESSION['user_logged_in']);
unset($_SESSION['visitor_id']);
unset($_SESSION['visitor_name']);

header("Location: user_login.php");
exit();
?>