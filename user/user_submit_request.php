<?php
session_start();
require_once('../config.php');

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: user_login.php");
    exit();
}

$visitor_id = $_SESSION['visitor_id'];

// Get user information
$query = "SELECT * FROM Visitor WHERE VId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $visitor_id);
$stmt->execute();
$result = $stmt->get_result();
$visitor = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['Submit-request'])) {
    $staff_id = 1; // Default staff ID or assign randomly
    $description = isset($_POST['description']) ? $_POST['description'] : "";
    $department = isset($_POST['department']) ? $_POST['department'] : "";
    $request_method = isset($_POST['request_method']) ? $_POST['request_method'] : "";
    
    // Validate request info
    if (empty($description) || empty($department) || empty($request_method)) {
        $error_message = "Please fill all request information fields.";
    } else {
        // Insert request
        $queryRequest = "INSERT INTO Request (VId, SId, Description, Status, Department, RequestMethodology, Timestamp) 
                        VALUES (?, ?, ?, 'pending', ?, ?, NOW())";
        $stmt = $conn->prepare($queryRequest);
        $stmt->bind_param("iisss", $visitor_id, $staff_id, $description, $department, $request_method);
        
        if ($stmt->execute()) {
            $request_id = $conn->insert_id;
            $success_message = "Request submitted successfully! Your request ID is: " . $request_id;
        } else {
            $error_message = "Error submitting request: " . $conn->error;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center">Submit Help Desk Request</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <div class="text-center mt-4">
                        <a href="user_requests.php" class="btn btn-primary">View Your Requests</a>
                    </div>
                <?php else: ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Your Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo $visitor['VFname'] . ' ' . $visitor['VMinit'] . ' ' . $visitor['VLName']; ?></p>
                        <p><strong>Email:</strong> <?php echo $visitor['VEmail']; ?></p>
                    </div>
                </div>
                
                <form action="user_submit_request.php" method="post">
                    <div class="form-group">
                        <label for="department">Department <span class="text-danger">*</span></label>
                        <select class="form-control" id="department" name="department" required>
                            <option value="">-- Select Department --</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Academic Assistance">Academic Assistance</option>
                            <option value="Financial Aid">Financial Aid</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="request_method">Request Method <span class="text-danger">*</span></label>
                        <select class="form-control" id="request_method" name="request_method" required>
                            <option value="">-- Select Request Method --</option>
                            <option value="walk-in">Walk-in</option>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Request Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                    </div>
                    
                    <div class="form-group text-center mt-4">
                        <input class="btn btn-primary" name="Submit-request" type="submit" value="Submit Request">
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="user_view.php">Back to Dashboard</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>