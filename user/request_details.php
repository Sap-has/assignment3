<?php
session_start();
require_once('../config.php');
if (empty($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit;
}
$request_id = intval($_GET['id'] ?? 0);
$visitor_id = $_SESSION['visitor_id'];
$sql = "SELECT r.*, s.SFName, s.SLName
          FROM Request r
     LEFT JOIN Staff s ON r.SId = s.SId
         WHERE r.RId = ? AND r.VId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $request_id, $visitor_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Request Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center">Request #<?=htmlspecialchars($request['RId'])?></h2>
    <ul class="list-group mt-3">
        <li class="list-group-item"><strong>Department:</strong> <?=htmlspecialchars($request['Department'])?></li>
        <li class="list-group-item"><strong>Status:</strong> <?=htmlspecialchars(ucfirst($request['Status']))?></li>
        <li class="list-group-item"><strong>Method:</strong> <?=htmlspecialchars(ucfirst($request['RequestMethodology']))?></li>
        <li class="list-group-item"><strong>Date:</strong> <?=htmlspecialchars(date('M d, Y g:i A', strtotime($request['Timestamp'])))?></li>
        <li class="list-group-item"><strong>Staff Assigned:</strong> <?=htmlspecialchars($request['SFName'] ? $request['SFName'].' '.$request['SLName'] : 'Not Assigned')?></li>
    </ul>
    <h5 class="mt-4">Description</h5>
    <div class="card mb-3"><div class="card-body"><?=nl2br(htmlspecialchars($request['Description']))?></div></div>
    <a href="user_request.php" class="btn btn-secondary">Back to Requests</a>
</div>
</body>
</html>