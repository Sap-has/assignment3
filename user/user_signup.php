<?php
session_start();
require_once('../config.php');

$error_message = '';
$success_message = '';

if (isset($_POST['Submit'])) {
    $fname = isset($_POST['fname']) ? $_POST['fname'] : "";
    $minit = isset($_POST['minit']) ? $_POST['minit'] : "";
    $lname = isset($_POST['lname']) ? $_POST['lname'] : "";
    $email = isset($_POST['email']) ? $_POST['email'] : "";
    $phone = isset($_POST['phone']) ? $_POST['phone'] : "";
    
    // Validate form data
    if (empty($fname) || empty($lname) || empty($email)) {
        $error_message = "Please fill all required fields.";
    } else {
        // Check if email already exists
        $query = "SELECT * FROM Visitor WHERE VEmail = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "This email is already registered. Please use a different email or login.";
        } else {
            // Insert new visitor with student type
            try {
                // Use stored procedure to create visitor
                $stmt = $conn->prepare("CALL create_visitor(?, ?, ?, ?, 'student')");
                $stmt->bind_param("ssss", $fname, $minit, $lname, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $visitor_id = $row['visitor_id'];
                
                // Add phone number if provided
                if (!empty($phone)) {
                    $phoneStmt = $conn->prepare("CALL add_visitor_phone(?, ?)");
                    $phoneStmt->bind_param("is", $visitor_id, $phone);
                    $phoneStmt->execute();
                }
                
                $success_message = "Account created successfully! You can now log in.";
            } catch (Exception $e) {
                $error_message = "Error creating account: " . $e->getMessage();
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Sign Up</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center">Student Sign Up</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <div class="text-center">
                        <a href="student_login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                <?php else: ?>
                
                <form action="student_signup.php" method="post">
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="fname">First Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="fname" name="fname" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="minit">Middle Initial</label>
                            <input class="form-control" type="text" id="minit" name="minit" maxlength="1">
                        </div>
                        <div class="form-group col-md-5">
                            <label for="lname">Last Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="lname" name="lname" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input class="form-control" type="email" id="email" name="email" required>
                        <small class="form-text text-muted">This will be used as your username for login</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password <span class="text-danger">*</span></label>
                        <input class="form-control" type="password" id="password" name="password" required>
                        <small class="form-text text-muted">For this system, use your last name as your password</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input class="form-control" type="tel" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group text-center mt-4">
                        <input class="btn btn-primary" name="Submit" type="submit" value="Create Account">
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="student_login.php">Log in</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="../index.php">Back to Home</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>