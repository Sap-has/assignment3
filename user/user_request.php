<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: user_login.php");
    exit();
}

$visitor_id = $_SESSION['visitor_id'];

$sql = "SELECT r.*, s.SFName, s.SLName 
        FROM Request r
        LEFT JOIN Staff s ON r.SId = s.SId
        WHERE r.VId = ?
        ORDER BY r.Timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $visitor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>My Help Desk Requests</h1>
        
        <div class="mb-3">
            <a href="user_view.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="user_submit_request.php" class="btn btn-primary float-right"><i class="fas fa-plus"></i> New Request</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Department</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Method</th>
                        <th>Timestamp</th>
                        <th>Assigned Staff</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_class = '';
                            switch ($row['Status']) {
                                case 'pending':
                                    $status_class = 'badge-warning';
                                    break;
                                case 'in progress':
                                    $status_class = 'badge-info';
                                    break;
                                case 'resolved':
                                    $status_class = 'badge-success';
                                    break;
                            }
                            
                            echo "<tr>";
                            echo "<td>{$row['RId']}</td>";
                            echo "<td>{$row['Department']}</td>";
                            echo "<td>" . substr(htmlspecialchars($row['Description']), 0, 50) . "...</td>";
                            echo "<td><span class='badge {$status_class}'>" . ucfirst($row['Status']) . "</span></td>";
                            echo "<td>" . ucfirst($row['RequestMethodology']) . "</td>";
                            echo "<td>" . date('M d, Y g:i A', strtotime($row['Timestamp'])) . "</td>";
                            echo "<td>" . ($row['SFName'] ? "{$row['SFName']} {$row['SLName']}" : "Not Assigned") . "</td>";
                            echo "<td><a href='request_details.php?id={$row['RId']}' class='btn btn-sm btn-info'>View</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>You haven't submitted any requests yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>