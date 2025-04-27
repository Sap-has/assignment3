<?php
session_start();
require_once('../config.php');

// Initialize error message variable
$error_message = '';

// Check if form was submitted
if (isset($_POST['Submit'])) {
    $staff_id = isset($_POST['staff_id']) ? $_POST['staff_id'] : '';
    
    // Validate staff ID
    if (empty($staff_id)) {
        $error_message = "Please enter your Staff ID";
    } else {
        // Verify staff ID in database
        $query = "SELECT * FROM Staff WHERE SId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $staff = $result->fetch_assoc();
            
            // Set session variables
            $_SESSION['staff_id'] = $staff['SId'];
            $_SESSION['staff_name'] = $staff['SFName'] . " " . $staff['SLName'];
            $_SESSION['staff_logged_in'] = true;
            
            // Redirect to staff dashboard
            header("Location: staff_dashboard.php");
            exit();
        } else {
            $error_message = "Invalid Staff ID. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Staff Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <form action="staff_login.php" method="post">
                            <div class="form-group">
                                <label for="staff_id">Staff ID</label>
                                <input type="text" class="form-control" id="staff_id" name="staff_id" required>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" name="Submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="../index.php">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>