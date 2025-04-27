<?php
session_start();
// Clear only our user session variables
unset($_SESSION['user_logged_in'], $_SESSION['visitor_id'], $_SESSION['visitor_name']);
header('Location: user_login.php');
exit;
?>
