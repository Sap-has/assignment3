<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['SId'])) {
  header('Location: staff_login.php');
  exit;
}
$loggedInStaffId = $_SESSION['SId'];

// Get request ID from URL
if (!isset($_GET['rid']) || empty($_GET['rid'])) {
    header("Location: view_requests.php");
    exit();
}

$request_id = $_GET['rid'];

// Get request details with all related information
$sql = "SELECT r.*, v.VFname, v.VMinit, v.VLName, v.VEmail, v.VType, s.SFName, s.SLName 
        FROM Request r
        LEFT JOIN Visitor v ON r.VId = v.VId
        LEFT JOIN Staff s ON r.SId = s.SId
        WHERE r.RId = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_requests.php");
    exit();
}

$request = $result->fetch_assoc();

// Get visitor phone numbers
$phone_sql = "SELECT VPhoneNumber FROM VisitorPhone WHERE VId = ?";
$phone_stmt = $conn->prepare($phone_sql);
$phone_stmt->bind_param("i", $request['VId']);
$phone_stmt->execute();
$phone_result = $phone_stmt->get_result();
$phone_numbers = [];

if ($phone_result && $phone_result->num_rows > 0) {
    while ($phone_row = $phone_result->fetch_assoc()) {
        $phone_numbers[] = $phone_row['VPhoneNumber'];
    }
}

// Determine status badge class
$status_badge_class = '';
switch ($request['Status']) {
    case 'pending':
        $status_badge_class = 'badge-warning';
        break;
    case 'in progress':
        $status_badge_class = 'badge-info';
        break;
    case 'resolved':
        $status_badge_class = 'badge-success';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Request Details</title>

    <!-- Importing Bootstrap CSS library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    
    <style>
        .detail-section {
            margin-bottom: 30px;
        }
        .section-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1>Request #<?php echo $request_id; ?> Details</h1>
        
        <div class="mb-3">
            <a href="view_requests.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Requests</a>
            <a href="update_request.php?rid=<?php echo $request_id; ?>" class="btn btn-primary ml-2"><i class="fas fa-edit"></i> Update Request</a>
            
            <?php if ($request['Status'] !== 'resolved'): ?>
                <a href="mark_resolved.php?rid=<?php echo $request_id; ?>" class="btn btn-success ml-2" onclick="return confirm('Mark this request as resolved?');"><i class="fas fa-check"></i> Mark Resolved</a>
            <?php endif; ?>
        </div>
        
        <!-- Request Status Banner -->
        <div class="alert alert-secondary">
            <div class="row">
                <div class="col-md-4">
                    <span><strong>Status:</strong> <span class="badge <?php echo $status_badge_class; ?>"><?php echo ucfirst($request['Status']); ?></span></span>
                </div>
                <div class="col-md-4">
                    <span><strong>Department:</strong> <?php echo $request['Department']; ?></span>
                </div>
                <div class="col-md-4">
                    <span><strong>Method:</strong> <?php echo ucfirst($request['RequestMethodology']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Visitor Information -->
        <div class="detail-section">
            <div class="section-header">
                <h4>Visitor Information</h4>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> <?php echo "{$request['VFname']} {$request['VMinit']} {$request['VLName']}"; ?></p>
                    <p><strong>Email:</strong> <?php echo $request['VEmail']; ?></p>
                    <p><strong>Type:</strong> <?php echo ucfirst($request['VType']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Phone Number(s):</strong></p>
                    <?php if (!empty($phone_numbers)): ?>
                        <ul>
                            <?php foreach ($phone_numbers as $phone): ?>
                                <li><?php echo $phone; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No phone numbers on record</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Request Details -->
        <div class="detail-section">
            <div class="section-header">
                <h4>Request Details</h4>
            </div>
            <div class="row">
                <div class="col-12">
                    <p><strong>Timestamp:</strong> <?php echo date('M d, Y g:i A', strtotime($request['Timestamp'])); ?></p>
                    <p><strong>Description:</strong></p>
                    <div class="card">
                        <div class="card-body">
                            <?php echo nl2br(htmlspecialchars($request['Description'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Staff Assignment -->
        <div class="detail-section">
            <div class="section-header">
                <h4>Staff Assignment</h4>
            </div>
            <div class="row">
                <div class="col-12">
                    <?php if ($request['SFName']): ?>
                        <p><strong>Assigned To:</strong> <?php echo "{$request['SFName']} {$request['SLName']}"; ?></p>
                    <?php else: ?>
                        <p>No staff member assigned yet.</p>
                        <a href="assign_staff.php?rid=<?php echo $request_id; ?>" class="btn btn-warning"><i class="fas fa-user-plus"></i> Assign Staff</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap and JavaScript libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"></script>
</body>
</html>