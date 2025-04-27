<?php
session_start();
require_once('../config.php');
if (empty($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Visitor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center">Visitor Dashboard</h1>
    <div class="text-center mb-3">
        <p>Welcome, <?=htmlspecialchars($_SESSION['visitor_name'])?>!</p>
        <p>Your Visitor ID: <?=htmlspecialchars($_SESSION['visitor_id'])?></p>
    </div>
    <div class="row">
        <div class="col text-center">
            <a href="user_submit_request.php" class="btn btn-primary">Make a Request</a>
        </div>
        <div class="col text-center">
            <a href="user_request.php" class="btn btn-primary">View Your Requests</a>
        </div>
        <div class="col text-center">
            <a href="user_logout.php" class="btn btn-secondary">Log Out</a>
        </div>
    </div>
</div>
</body>
</html>