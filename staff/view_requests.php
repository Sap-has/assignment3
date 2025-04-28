<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['SId'])) {
  header('Location: staff_login.php');
  exit;
}
$loggedInStaffId = $_SESSION['SId'];

// Get staff ID from session
$staff_id = $_SESSION['staff_id'];

// Handle filter submission
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_department = isset($_GET['filter_department']) ? $_GET['filter_department'] : '';
$filter_method = isset($_GET['filter_method']) ? $_GET['filter_method'] : '';

// Build the query based on filters
$sql = "SELECT r.*, v.VFname, v.VMinit, v.VLName, v.VEmail, v.VType, s.SFName, s.SLName 
        FROM Request r
        LEFT JOIN Visitor v ON r.VId = v.VId
        LEFT JOIN Staff s ON r.SId = s.SId
        WHERE 1=1";

if (!empty($filter_status)) {
    $sql .= " AND r.Status = '$filter_status'";
}

if (!empty($filter_department)) {
    $sql .= " AND r.Department = '$filter_department'";
}

if (!empty($filter_method)) {
    $sql .= " AND r.RequestMethodology = '$filter_method'";
}

$sql .= " ORDER BY r.Timestamp DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Requests</title>

    <!-- Importing Bootstrap CSS library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    
    <style>
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-in-progress {
            background-color: #17a2b8;
            color: #fff;
        }
        .badge-resolved {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1>Help Desk Requests</h1>
        
        <!-- Navigation links -->
        <div class="mb-3">
            <a href="staff_view.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="create_request.php" class="btn btn-success float-right"><i class="fas fa-plus"></i> Add New Request</a>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="view_requests.php" class="form-inline">
                <div class="form-group mr-3">
                    <label for="filter_status" class="mr-2">Status:</label>
                    <select class="form-control" id="filter_status" name="filter_status">
                        <option value="">All</option>
                        <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="in progress" <?php echo ($filter_status == 'in progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo ($filter_status == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
                
                <div class="form-group mr-3">
                    <label for="filter_department" class="mr-2">Department:</label>
                    <select class="form-control" id="filter_department" name="filter_department">
                        <option value="">All</option>
                        <option value="Technical Support" <?php echo ($filter_department == 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                        <option value="Academic Assistance" <?php echo ($filter_department == 'Academic Assistance') ? 'selected' : ''; ?>>Academic Assistance</option>
                        <option value="Financial Aid" <?php echo ($filter_department == 'Financial Aid') ? 'selected' : ''; ?>>Financial Aid</option>
                    </select>
                </div>
                
                <div class="form-group mr-3">
                    <label for="filter_method" class="mr-2">Method:</label>
                    <select class="form-control" id="filter_method" name="filter_method">
                        <option value="">All</option>
                        <option value="walk-in" <?php echo ($filter_method == 'walk-in') ? 'selected' : ''; ?>>Walk-in</option>
                        <option value="phone" <?php echo ($filter_method == 'phone') ? 'selected' : ''; ?>>Phone</option>
                        <option value="email" <?php echo ($filter_method == 'email') ? 'selected' : ''; ?>>Email</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="view_requests.php" class="btn btn-secondary ml-2">Clear Filters</a>
            </form>
        </div>
        
        <!-- Requests Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Visitor</th>
                        <th>Department</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                        <th>Assigned Staff</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Determine badge class based on status
                            $status_badge_class = '';
                            switch ($row['Status']) {
                                case 'pending':
                                    $status_badge_class = 'badge-pending';
                                    break;
                                case 'in progress':
                                    $status_badge_class = 'badge-in-progress';
                                    break;
                                case 'resolved':
                                    $status_badge_class = 'badge-resolved';
                                    break;
                            }
                            
                            echo "<tr>";
                            echo "<td>{$row['RId']}</td>";
                            echo "<td>{$row['VFname']} {$row['VMinit']} {$row['VLName']}<br><small>{$row['VEmail']}</small><br><small>(" . ucfirst($row['VType']) . ")</small></td>";
                            echo "<td>{$row['Department']}</td>";
                            echo "<td>" . ucfirst($row['RequestMethodology']) . "</td>";
                            echo "<td><span class='badge {$status_badge_class}'>" . ucfirst($row['Status']) . "</span></td>";
                            echo "<td>" . date('M d, Y g:i A', strtotime($row['Timestamp'])) . "</td>";
                            echo "<td>" . ($row['SFName'] ? "{$row['SFName']} {$row['SLName']}" : "Not Assigned") . "</td>";
                            echo "<td class='text-center'>
                                    <div class='btn-group' role='group'>
                                        <a href='view_request_details.php?rid={$row['RId']}' class='btn btn-sm btn-info' title='View Details'><i class='fas fa-eye'></i></a>
                                        <a href='update_request.php?rid={$row['RId']}' class='btn btn-sm btn-primary' title='Update Status'><i class='fas fa-edit'></i></a>
                                        <a href='assign_staff.php?rid={$row['RId']}' class='btn btn-sm btn-warning' title='Assign Staff'><i class='fas fa-user-plus'></i></a>";
                            
                            // Only show delete button if current user has appropriate permissions
                            // You can add conditional checks based on role/permissions stored in session if needed
                            echo "<button type='button' class='btn btn-sm btn-danger' data-toggle='modal' data-target='#deleteModal{$row['RId']}' title='Delete Request'>
                                    <i class='fas fa-trash'></i>
                                  </button>";
                            
                            echo "</div>
                                  </td>";
                            echo "</tr>";
                            
                            // Delete confirmation modal for each request
                            echo "<div class='modal fade' id='deleteModal{$row['RId']}' tabindex='-1' role='dialog' aria-labelledby='deleteModalLabel{$row['RId']}' aria-hidden='true'>
                                    <div class='modal-dialog' role='document'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title' id='deleteModalLabel{$row['RId']}'>Confirm Deletion</h5>
                                                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                                    <span aria-hidden='true'>&times;</span>
                                                </button>
                                            </div>
                                            <div class='modal-body'>
                                                Are you sure you want to delete request #{$row['RId']}?
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                                                <a href='delete_request.php?rid={$row['RId']}' class='btn btn-danger'>Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                  </div>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>No requests found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Analytics & Reports Button -->
        <div class="mt-4 text-center">
            <a href="analytics.php" class="btn btn-info"><i class="fas fa-chart-bar"></i> View Analytics & Reports</a>
        </div>
    </div>

    <!-- Bootstrap and JavaScript libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"></script>
</body>
</html>