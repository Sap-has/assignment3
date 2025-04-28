<?php
session_start();
require_once('../config.php');
if (empty($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit;
}
$visitor_id = $_SESSION['visitor_id'];
$sql = "SELECT r.RId, r.Description, r.Status, r.Department, r.RequestMethodology, r.Timestamp,
               s.SFName, s.SLName
          FROM Request r
     LEFT JOIN Staff s ON r.SId = s.SId
         WHERE r.VId = ?
      ORDER BY r.Timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $visitor_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Your Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center">Your Requests</h2>
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Department</th>
                <th>Description</th>
                <th>Status</th>
                <th>Method</th>
                <th>Date</th>
                <th>Staff</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?=htmlspecialchars($row['RId'])?></td>
                    <td><?=htmlspecialchars($row['Department'])?></td>
                    <td><?=htmlspecialchars(substr($row['Description'],0,50))?>...</td>
                    <td><span class="badge badge-<?= $row['Status']=='resolved' ? 'success' : 'warning' ?>"><?=htmlspecialchars(ucfirst($row['Status']))?></span></td>
                    <td><?=htmlspecialchars(ucfirst($row['RequestMethodology']))?></td>
                    <td><?=htmlspecialchars(date('M d, Y g:i A', strtotime($row['Timestamp'])))?></td>
                    <td><?=htmlspecialchars($row['SFName'] ? $row['SFName'].' '.$row['SLName'] : 'Not Assigned')?></td>
                    <td><a href="request_details.php?id=<?=urlencode($row['RId'])?>" class="btn btn-sm btn-info">View</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" class="text-center">You haven't submitted any requests yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <div class="text-center mt-3">
        <a href="user_view.php" class="btn btn-primary">Back</a>
    </div>
</div>
</body>
</html>