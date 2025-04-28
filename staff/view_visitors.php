<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['SId'])) {
  header('Location: staff_login.php');
  exit;
}
$loggedInStaffId = $_SESSION['SId'];

// Check if visitor ID is provided
if (!isset($_GET['vid']) || empty($_GET['vid'])) {
    header("Location: view_visitors.php");
    exit();
}

$visitor_id = $_GET['vid'];

// Get visitor information
$visitor_query = "SELECT * FROM Visitor WHERE VId = ?";
$stmt = $conn->prepare($visitor_query);
$stmt->bind_param("i", $visitor_id);
$stmt->execute();
$visitor_result = $stmt->get_result();

if ($visitor_result->num_rows === 0) {
    header("Location: view_visitors.php");
    exit();
}

$visitor = $visitor_result->fetch_assoc();

// Get visitor phone numbers
$phone_query = "SELECT VPhoneNumber FROM VisitorPhone WHERE VId = ?";
$stmt = $conn->prepare($phone_query);
$stmt->bind_param("i", $visitor_id);
$stmt->execute();
$phone_result = $stmt->get_result();
$phones = array();

if ($phone_result && $phone_result->num_rows > 0) {
    while ($phone = $phone_result->fetch_assoc()) {
        $phones[] = $phone['VPhoneNumber'];
    }
}

// Get visitor requests
$request_query = "SELECT r.*, s.SFName, s.SLName 
                 FROM Request r 
                 LEFT JOIN Staff s ON r.SId = s.SId 
                 WHERE r.VId = ? 
                 ORDER BY r.Timestamp DESC";
$stmt = $conn->prepare($request_query);
$stmt->bind_param("i", $visitor_id);
$stmt->execute();
$request_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Visitor Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>

<body>
    <div class="container mt-4">
        <h1>Visitor Details</h1>
        
        <div class="mb-3">
            <a href="view_visitors.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Visitors</a>
            <a href="create_request.php?visitor_id=<?php echo $visitor_id; ?>" class="btn btn-success ml-2"><i class="fas fa-plus"></i> Create Request</a>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4>Visitor Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">ID:</div>
                            <div class="col-md-8"><?php echo $visitor['VId']; ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Name:</div>
                            <div class="col-md-8"><?php echo $visitor['VFname'] . ' ' . $visitor['VMinit'] . ' ' . $visitor['VLName']; ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Email:</div>
                            <div class="col-md-8"><?php echo $visitor['VEmail']; ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Type:</div>
                            <div class="col-md-8"><?php echo ucfirst($visitor['VType']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4>Contact Information</h4>
                    </div>
                    <div class="card-body">
                        <h5>Phone Numbers:</h5>
                        <?php if (!empty($phones)): ?>
                            <ul class="list-group">
                                <?php foreach ($phones as $phone): ?>
                                    <li class="list-group-item"><?php echo $phone; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No phone numbers on record</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Request History</h4>
            </div>
            <div class="card-body">
                <?php if ($request_result && $request_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Department</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Date/Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($request = $request_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $request['RId']; ?></td>
                                        <td><?php echo $request['Department']; ?></td>
                                        <td><?php echo ucfirst($request['RequestMethodology']); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo ($request['Status'] == 'pending') ? 'badge-warning' : 
                                                    (($request['Status'] == 'in progress') ? 'badge-info' : 'badge-success'); 
                                            ?>">
                                                <?php echo ucfirst($request['Status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                echo (!empty($request['SFName'])) ? 
                                                    $request['SFName'] . ' ' . $request['SLName'] : 
                                                    'Not Assigned'; 
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($request['Timestamp'])); ?></td>
                                        <td>
                                            <a href="view_request_details.php?rid=<?php echo $request['RId']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="update_request.php?rid=<?php echo $request['RId']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">This visitor has no requests on record.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"></script>
</body>
</html>