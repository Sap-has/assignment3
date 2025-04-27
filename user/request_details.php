<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: user_login.php");
    exit();
}

$visitor_id = $_SESSION['visitor_id'];

$request_id = isset($_GET['id']) ? $_GET['id'] : 0;

$sql = "SELECT r.*, s.SFName, s.SLName 
        FROM Request r
        LEFT JOIN Staff s ON r.SId = s.SId
        WHERE r.RId = ? AND r.VId = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $visitor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: user_requests.php");
    exit();
}

$request = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Request Details</h1>
        
        <div class="mb-3">
            <a href="user_requests.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Requests</a>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Request #<?php echo $request['RId']; ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Department:</strong> <?php echo $request['Department']; ?></p>
                        <p><strong>Request Method:</strong> <?php echo ucfirst($request['RequestMethodology']); ?></p>
                        <p><strong>Submitted:</strong> <?php echo date('F d, Y g:i A', strtotime($request['Timestamp'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $status_class = '';
                        switch ($request['Status']) {
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
                        ?>
                        <p><strong>Status:</strong> <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($request['Status']); ?></span></p>
                        <p><strong>Assigned To:</strong> <?php echo ($request['SFName'] ? "{$request['SFName']} {$request['SLName']}" : "Not Assigned"); ?></p>
                    </div>
                </div>
                
                <hr>
                
                <h5>Description</h5>
                <div class="card mb-3">
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($request['Description'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>