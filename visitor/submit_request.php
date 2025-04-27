<?php
session_start();
require_once('../config.php');
require_once('../util/assign_staff.php');

// Check if visitor is logged in
$visitor_logged_in = isset($_SESSION['visitor_id']);

// Handle form submission
if (isset($_POST['Submit-request'])) {
    // If not logged in as a visitor, we will need to create one first
    if (!$visitor_logged_in) {
        $fname = isset($_POST['first_name']) ? $_POST['first_name'] : "";
        $minit = isset($_POST['middle_initial']) ? $_POST['middle_initial'] : "";
        $lname = isset($_POST['last_name']) ? $_POST['last_name'] : "";
        $email = isset($_POST['email']) ? $_POST['email'] : "";
        $visitor_type = isset($_POST['visitor_type']) ? $_POST['visitor_type'] : "";
        $phone = isset($_POST['phone']) ? $_POST['phone'] : "";
        
        // Validate visitor info
        if (empty($fname) || empty($lname) || empty($email) || empty($visitor_type) || empty($phone)) {
            $error_message = "Please fill all visitor information fields.";
        } else {
            // Use stored procedure to create visitor
            $stmt = $conn->prepare("CALL create_visitor(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fname, $minit, $lname, $email, $visitor_type);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                $row = $result->fetch_assoc();
                $visitor_id = $row['visitor_id'];
                
                // Insert phone number
                $phoneStmt = $conn->prepare("CALL add_visitor_phone(?, ?)");
                $phoneStmt->bind_param("is", $visitor_id, $phone);
                
                if ($phoneStmt->execute()) {
                    // Set visitor session
                    $_SESSION['visitor_id'] = $visitor_id;
                    $_SESSION['visitor_name'] = $fname . " " . $lname;
                } else {
                    $error_message = "Error adding phone number: " . $conn->error;
                }
            } else {
                $error_message = "Error creating visitor: " . $conn->error;
            }
        }
    }
    
    // Now we can process the request
    if (!isset($error_message)) {
        $visitor_id = $_SESSION['visitor_id'];
        // Assign a random staff member
        $staff_id = assignRandomStaff($conn);
        $description = isset($_POST['description']) ? $_POST['description'] : "";
        $department = isset($_POST['department']) ? $_POST['department'] : "";
        $request_method = isset($_POST['request_method']) ? $_POST['request_method'] : "";
        
        // Validate request info
        if (empty($description) || empty($department) || empty($request_method)) {
            $error_message = "Please fill all request information fields.";
        } else {
            // Use stored procedure to create request
            $stmt = $conn->prepare("CALL create_request(?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $visitor_id, $staff_id, $description, $department, $request_method);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                $row = $result->fetch_assoc();
                $request_id = $row['request_id'];
                $success_message = "Request submitted successfully! Your request ID is: " . $request_id;
            } else {
                $error_message = "Error submitting request: " . $conn->error;
            }
        }
    }
}

// Get visitor information if logged in
if ($visitor_logged_in) {
    $visitor_id = $_SESSION['visitor_id'];
    $query = "SELECT * FROM Visitor WHERE VId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $visitor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $visitor = $result->fetch_assoc();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Submit Request</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">

    <style>
        html, body {
            height: 100%;
            background-color: #f8f9fa;
        }
        .container {
            padding-top: 30px;
            padding-bottom: 30px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .submit-button {
            width: 200px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center mb-0">Submit Help Desk Request</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <div class="text-center mt-4">
                        <a href="../index.php" class="btn btn-primary">Return to Home</a>
                    </div>
                <?php else: ?>
                
                <?php if (!$visitor_logged_in): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Returning Visitor?</h5>
                    </div>
                    <div class="card-body">
                        <p>If you've submitted a request before, you can login with your email:</p>
                        <a href="visitor_login.php" class="btn btn-secondary">Login as Returning Visitor</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <form action="submit_request.php" method="post">
                    <?php if (!$visitor_logged_in): ?>
                    <!-- Visitor Information Section -->
                    <h4 class="mb-3">Visitor Information</h4>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="first_name">First Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="middle_initial">Middle Initial</label>
                            <input class="form-control" type="text" id="middle_initial" name="middle_initial" maxlength="1">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last_name">Last Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input class="form-control" type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Phone Number <span class="text-danger">*</span></label>
                            <input class="form-control" type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="visitor_type">Visitor Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="visitor_type" name="visitor_type" required>
                            <option value="">-- Select Visitor Type --</option>
                            <option value="student">Student</option>
                            <option value="staff">Staff</option>
                            <option value="guest">Guest</option>
                        </select>
                    </div>
                    
                    <hr class="my-4">
                    <?php else: ?>
                    <!-- Visitor Information Display -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Visitor Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?php echo $visitor['VFname'] . ' ' . $visitor['VMinit'] . ' ' . $visitor['VLName']; ?></p>
                            <p><strong>Email:</strong> <?php echo $visitor['VEmail']; ?></p>
                            <p><strong>Type:</strong> <?php echo ucfirst($visitor['VType']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Request Information Section -->
                    <h4 class="mb-3">Request Information</h4>
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
                        <input class="btn btn-primary submit-button" name="Submit-request" type="submit" value="Submit Request">
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="../index.php">Back to Home</a>
        </div>
    </div>

    <!-- jQuery and JS bundle w/ Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>