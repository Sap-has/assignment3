<?php
session_start();
require_once('../config.php');

// Check if staff is logged in
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: staffLogin.php");
    exit();
}

// Get request ID from URL
if (!isset($_GET['rid']) || empty($_GET['rid'])) {
    header("Location: view_requests.php");
    exit();
}

$request_id = $_GET['rid'];

// Get request details
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

// Handle form submission
$message = '';
$alert_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];
    $new_department = $_POST['department'];
    $new_staff_id = $_POST['staff_id'];
    
    // Update request
    $update_sql = "UPDATE Request SET Status = ?, Department = ?, SId = ? WHERE RId = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssii", $new_status, $new_department, $new_staff_id, $request_id);
    
    if ($update_stmt->execute()) {
        $message = "Request #$request_id has been successfully updated.";
        $alert_class = 'alert-success';
        
        // Refresh request data
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
    } else {
        $message = "Error updating request: " . $conn->error;
        $alert_class = 'alert-danger';
    }
}

// Get all staff members for assignment dropdown
$staff_sql = "SELECT SId, SFName, SLName FROM Staff ORDER BY SLName, SFName";
$staff_result = $conn->query($staff_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Update Request</title>

    <!-- Importing Bootstrap CSS library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>

<body>
    <div class="container mt-4">
        <h1>Update Request #<?php echo $request_id; ?></h1>
        
        <div class="mb-3">
            <a href="view_requests.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Requests</a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $alert_class; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Request Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Visitor:</strong> <?php echo "{$request['VFname']} {$request['VMinit']} {$request['VLName']}"; ?></p>
                        <p><strong>Email:</strong> <?php echo $request['VEmail']; ?></p>
                        <p><strong>Visitor Type:</strong> <?php echo ucfirst($request['VType']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Current Status:</strong> <span class="badge <?php echo ($request['Status'] == 'pending') ? 'badge-warning' : (($request['Status'] == 'in progress') ? 'badge-info' : 'badge-success'); ?>"><?php echo ucfirst($request['Status']); ?></span></p>
                        <p><strong>Department:</strong> <?php echo $request['Department']; ?></p>
                        <p><strong>Method:</strong> <?php echo ucfirst($request['RequestMethodology']); ?></p>
                        <p><strong>Timestamp:</strong> <?php echo date('M d, Y g:i A', strtotime($request['Timestamp'])); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <p><strong>Description:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($request['Description'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Update Request</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending" <?php echo ($request['Status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="in progress" <?php echo ($request['Status'] == 'in progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo ($request['Status'] == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department:</label>
                        <select class="form-control" id="department" name="department" required>
                            <option value="Technical Support" <?php echo ($request['Department'] == 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                            <option value="Academic Assistance" <?php echo ($request['Department'] == 'Academic Assistance') ? 'selected' : ''; ?>>Academic Assistance</option>
                            <option value="Financial Aid" <?php echo ($request['Department'] == 'Financial Aid') ? 'selected' : ''; ?>>Financial Aid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="staff_id">Assign Staff:</label>
                        <select class="form-control" id="staff_id" name="staff_id">
                            <option value="">-- Not Assigned --</option>
                            <?php
                            if ($staff_result && $staff_result->num_rows > 0) {
                                while ($staff = $staff_result->fetch_assoc()) {
                                    $selected = ($staff['SId'] == $request['SId']) ? 'selected' : '';
                                    echo "<option value='{$staff['SId']}' $selected>{$staff['SFName']} {$staff['SLName']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap and JavaScript libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"></script>
</body>
</html>