<?php 
session_start();
require_once('../config.php');

if (!isset($_SESSION['SId'])) {
  header('Location: staff_login.php');
  exit;
}
$loggedInStaffId = $_SESSION['SId'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
        <div class="container mt-4">
        <h1 class="text-center">Staff Dashboard</h1>
        <div class="alert alert-success">
            <?php echo "Logged in as Staff ID: " . $_SESSION['staff_id']; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-clipboard-list"></i> Request Management</h4>
                    </div>
                    <div class="card-body">
                        <p>View and manage help desk requests</p>
                        <a class="btn btn-outline-primary btn-block" href="view_requests.php">
                            <i class="fas fa-list"></i> View All Requests
                        </a>
                        <a class="btn btn-outline-success btn-block" href="create_request.php">
                            <i class="fas fa-plus"></i> Create New Request
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4><i class="fas fa-users"></i> Visitor Management</h4>
                    </div>
                    <div class="card-body">
                        <p>View and manage visitor information</p>
                        <a class="btn btn-outline-info btn-block" href="view_visitors.php">
                            <i class="fas fa-user-friends"></i> View All Visitors
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h4><i class="fas fa-user-shield"></i> Staff Management</h4>
                    </div>
                    <div class="card-body">
                        <p>Add new staff members to the system</p>
                        <a class="btn btn-outline-secondary btn-block" href="add_staff.php">
                            <i class="fas fa-user-plus"></i> Add New Staff
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a class="btn btn-danger" href="staff_logout.php">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>