<?php
session_start();
require_once('../config.php');

// Check if staff is logged in
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: staffLogin.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];
$visitor_found = false;
$visitor = null;

// Handle visitor search
if (isset($_POST['search_visitor'])) {
    $visitor_search = isset($_POST['visitor_search']) ? $_POST['visitor_search'] : "";
    
    if (!empty($visitor_search)) {
        // Search by email or ID
        $search_query = "SELECT * FROM Visitor WHERE VEmail = ? OR VId = ?";
        $stmt = $conn->prepare($search_query);
        $stmt->bind_param("si", $visitor_search, $visitor_search);
        $stmt->execute();
        $search_result = $stmt->get_result();
        
        if ($search_result->num_rows > 0) {
            $visitor = $search_result->fetch_assoc();
            $visitor_found = true;
        } else {
            $search_error = "Visitor not found. Please check the email/ID or register a new visitor.";
        }
    } else {
        $search_error = "Please enter a visitor email or ID.";
    }
}

// Handle form submission for creating a new request
if (isset($_POST['Submit-request'])) {
    $visitor_id = isset($_POST['visitor_id']) ? $_POST['visitor_id'] : "";
    $description = isset($_POST['description']) ? $_POST['description'] : "";
    $department = isset($_POST['department']) ? $_POST['department'] : "";
    $request_method = isset($_POST['request_method']) ? $_POST['request_method'] : "";
    
    // Validate request info
    if (empty($visitor_id) || empty($description) || empty($department) || empty($request_method)) {
        $error_message = "Please fill all request information fields.";
    } else {
        // Assign a random staff member
        $assigned_staff_id = assignRandomStaff($conn);
        if (!$assigned_staff_id) $assigned_staff_id = $staff_id; // Default to current staff if no random staff
        
        // Insert request
        $queryRequest = "INSERT INTO Request (VId, SId, Description, Status, Department, RequestMethodology, Timestamp) 
                        VALUES (?, ?, ?, 'pending', ?, ?, NOW())";
        $stmt = $conn->prepare($queryRequest);
        $stmt->bind_param("iisss", $visitor_id, $assigned_staff_id, $description, $department, $request_method);
        
        if ($stmt->execute()) {
            $request_id = $conn->insert_id;
            $success_message = "Request submitted successfully! Request ID: " . $request_id;
            
            // Reset form
            $visitor_found = false;
            $visitor = null;
        } else {
            $error_message = "Error submitting request: " . $conn->error;
        }
    }
}

// Handle form submission for creating a new visitor
if (isset($_POST['create_visitor'])) {
    $fname = isset($_POST['first_name']) ? $_POST['first_name'] : "";
    $minit = isset($_POST['middle_initial']) ? $_POST['middle_initial'] : "";
    $lname = isset($_POST['last_name']) ? $_POST['last_name'] : "";
    $email = isset($_POST['email']) ? $_POST['email'] : "";
    $visitor_type = isset($_POST['visitor_type']) ? $_POST['visitor_type'] : "";
    $phone = isset($_POST['phone']) ? $_POST['phone'] : "";
    
    // Validate visitor info
    if (empty($fname) || empty($lname) || empty($email) || empty($visitor_type) || empty($phone)) {
        $error_message = "Please fill all required visitor information fields.";
    } else {
        // Insert visitor
        $queryVisitor = "INSERT INTO Visitor (VFname, VMinit, VLName, VEmail, VType) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($queryVisitor);
        $stmt->bind_param("sssss", $fname, $minit, $lname, $email, $visitor_type);
        
        if ($stmt->execute()) {
            $visitor_id = $conn->insert_id;
            
            // Insert phone number
            $queryPhone = "INSERT INTO VisitorPhone (VId, VPhoneNumber) VALUES (?, ?)";
            $stmt = $conn->prepare($queryPhone);
            $stmt->bind_param("is", $visitor_id, $phone);
            
            if ($stmt->execute()) {
                // Retrieve the new visitor
                $search_query = "SELECT * FROM Visitor WHERE VId = ?";
                $stmt = $conn->prepare($search_query);
                $stmt->bind_param("i", $visitor_id);
                $stmt->execute();
                $search_result = $stmt->get_result();
                
                if ($search_result->num_rows > 0) {
                    $visitor = $search_result->fetch_assoc();
                    $visitor_found = true;
                    $create_success = "Visitor created successfully! You can now submit a request for them.";
                }
            } else {
                $error_message = "Error adding phone number: " . $conn->error;
            }
        } else {
            $error_message = "Error creating visitor: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Create Help Desk Request</h1>
        
        <div class="mb-3">
            <a href="staffView.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($create_success)): ?>
            <div class="alert alert-success"><?php echo $create_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($search_error)): ?>
            <div class="alert alert-warning"><?php echo $search_error; ?></div>
        <?php endif; ?>
        
        <!-- Step 1: Find or Create Visitor -->
        <?php if (!$visitor_found): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h4>Step 1: Find Visitor</h4>
            </div>
            <div class="card-body">
                <form action="create_request.php" method="post" class="mb-4">
                    <div class="form-group">
                        <label for="visitor_search">Search by Visitor Email or ID:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="visitor_search" name="visitor_search" placeholder="Enter email or ID">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" name="search_visitor"><i class="fas fa-search"></i> Search</button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <hr>
                
                <h5 class="mb-3">Or Create New Visitor:</h5>
                <form action="create_request.php" method="post">
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
                    
                    <button type="submit" name="create_visitor" class="btn btn-success"><i class="fas fa-user-plus"></i> Create Visitor</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Step 2: Create Request for Found Visitor -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Step 2: Create Request</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5>Creating request for:</h5>
                    <p><strong>Name:</strong> <?php echo $visitor['VFname'] . ' ' . $visitor['VMinit'] . ' ' . $visitor['VLName']; ?></p>
                    <p><strong>Email:</strong> <?php echo $visitor['VEmail']; ?></p>
                    <p><strong>Type:</strong> <?php echo ucfirst($visitor['VType']); ?></p>
                    <p><strong>ID:</strong> <?php echo $visitor['VId']; ?></p>
                </div>
                
                <form action="create_request.php" method="post">
                    <input type="hidden" name="visitor_id" value="<?php echo $visitor['VId']; ?>">
                    
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