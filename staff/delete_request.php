<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['SId'])) {
  header('Location: staff_login.php');
  exit;
}
$loggedInStaffId = $_SESSION['SId'];

// Check if request ID is provided
if (!isset($_GET['rid']) || empty($_GET['rid'])) {
    $_SESSION['message'] = "No request ID provided.";
    $_SESSION['message_type'] = "danger";
    header("Location: view_requests.php");
    exit();
}

$request_id = $_GET['rid'];

// Delete the request
$sql = "DELETE FROM Request WHERE RId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Request #$request_id has been successfully deleted.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Error deleting request: " . $conn->error;
    $_SESSION['message_type'] = "danger";
}

// Redirect back to requests page
header("Location: view_requests.php");
exit();
?>