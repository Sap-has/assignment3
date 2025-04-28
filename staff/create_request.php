<?php
session_start();
require_once('../config.php');

// ——————————————————————
// Helper: assign a random staff member
// ——————————————————————
if (!function_exists('assignRandomStaff')) {
    function assignRandomStaff($conn) {
        $result = $conn->query("SELECT SId FROM Staff ORDER BY RAND() LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            return $row['SId'];
        }
        return false;
    }
}

// ——————————————————————
// Ensure staff is logged in
// ——————————————————————
if (!isset($_SESSION['SId'])) {
    header('Location: staff_login.php');
    exit;
}
$loggedInStaffId = $_SESSION['SId'];

$visitor_found  = false;
$visitor        = null;
$error_message  = '';
$success_message = '';
$create_success = '';
$search_error   = '';

// ——————————————————————
// Step 1A: Search for existing visitor
// ——————————————————————
if (isset($_POST['search_visitor'])) {
    $search_email = trim($_POST['search_email'] ?? '');
    if (empty($search_email)) {
        $search_error = "Please enter an email to search.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM Visitor WHERE VEmail = ?");
        $stmt->bind_param("s", $search_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $visitor       = $result->fetch_assoc();
            $visitor_found = true;
        } else {
            $search_error = "No visitor found with that email.";
        }
    }
}

// ——————————————————————
// Step 1B: Create a new visitor
// ——————————————————————
if (isset($_POST['create_visitor'])) {
    $fname        = trim($_POST['first_name'] ?? '');
    $minit        = trim($_POST['middle_initial'] ?? '');
    $lname        = trim($_POST['last_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $visitor_type = $_POST['visitor_type'] ?? '';

    if (empty($fname) || empty($lname) || empty($email) || empty($visitor_type) || empty($phone)) {
        $error_message = "Please fill all required visitor information fields.";
    } else {
        // Insert into Visitor
        $stmt = $conn->prepare("
            INSERT INTO Visitor (VFname, VMinit, VLName, VEmail, VType)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $fname, $minit, $lname, $email, $visitor_type);
        if ($stmt->execute()) {
            $visitor_id = $conn->insert_id;
            // Insert phone
            $stmt2 = $conn->prepare("
                INSERT INTO VisitorPhone (VId, VPhoneNumber)
                VALUES (?, ?)
            ");
            $stmt2->bind_param("is", $visitor_id, $phone);
            if ($stmt2->execute()) {
                // Fetch the newly created visitor
                $stmt3 = $conn->prepare("SELECT * FROM Visitor WHERE VId = ?");
                $stmt3->bind_param("i", $visitor_id);
                $stmt3->execute();
                $res = $stmt3->get_result();
                if ($res->num_rows > 0) {
                    $visitor       = $res->fetch_assoc();
                    $visitor_found = true;
                    $create_success = "Visitor created successfully! You can now submit a request for them.";
                }
            } else {
                $error_message = "Error saving phone: " . $stmt2->error;
            }
        } else {
            $error_message = "Error creating visitor: " . $stmt->error;
        }
    }
}

// ——————————————————————
// Step 2: Create a new help-desk request
// ——————————————————————
if (isset($_POST['submit_request'])) {
    $visitor_id     = $_POST['visitor_id']        ?? '';
    $description    = trim($_POST['description'] ?? '');
    $department     = $_POST['department']        ?? '';
    $request_method = $_POST['request_method']    ?? '';

    if (empty($visitor_id) || empty($description) || empty($department) || empty($request_method)) {
        $error_message = "Please fill in all request fields.";
    } else {
        // assign staff (random or current)
        $assigned_staff_id = assignRandomStaff($conn) ?: $loggedInStaffId;

        $stmt = $conn->prepare("
            INSERT INTO Request
              (VId, SId, Description, Status, Department, RequestMethodology, Timestamp)
            VALUES
              (?, ?, ?, 'pending', ?, ?, NOW())
        ");
        $stmt->bind_param(
            "iisss",
            $visitor_id,
            $assigned_staff_id,
            $description,
            $department,
            $request_method
        );

        if ($stmt->execute()) {
            $success_message = "Request submitted successfully! Request ID: " . $conn->insert_id;
            // reset to allow a fresh flow
            $visitor_found = false;
            $visitor       = null;
        } else {
            $error_message = "Error submitting request: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Help Desk Request</title>
  <link rel="stylesheet" href="../path/to/bootstrap.css">
  <script src="https://kit.fontawesome.com/yourkit.js"></script>
</head>
<body>
  <div class="container mt-4">
    <h1>Create Help Desk Request</h1>
    <a href="staff_view.php" class="btn btn-outline-secondary mb-3">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
      <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if ($create_success): ?>
      <div class="alert alert-success"><?= $create_success ?></div>
    <?php endif; ?>
    <?php if ($search_error): ?>
      <div class="alert alert-warning"><?= $search_error ?></div>
    <?php endif; ?>

    <?php if (! $visitor_found): ?>
      <!-- Step 1: Find or Create Visitor -->
      <div class="card mb-4">
        <div class="card-header"><h4>Step 1: Find Visitor</h4></div>
        <div class="card-body">
          <form method="post">
            <div class="form-group">
              <label for="search_email">Email</label>
              <input type="email" class="form-control" id="search_email" name="search_email" required>
            </div>
            <button type="submit" name="search_visitor" class="btn btn-primary">
              <i class="fas fa-search"></i> Search
            </button>
          </form>
          <hr>
          <h5>Or Create New Visitor</h5>
          <form method="post">
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
              </div>
              <div class="form-group col-md-2">
                <label for="middle_initial">Middle Initial</label>
                <input type="text" class="form-control" id="middle_initial" name="middle_initial" maxlength="1">
              </div>
              <div class="form-group col-md-6">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="form-group col-md-6">
                <label for="phone">Phone</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
              </div>
            </div>
            <div class="form-group">
              <label for="visitor_type">Visitor Type</label>
              <select class="form-control" id="visitor_type" name="visitor_type" required>
                <option value="">-- Select --</option>
                <option value="student">Student</option>
                <option value="staff">Staff</option>
                <option value="guest">Guest</option>
              </select>
            </div>
            <button type="submit" name="create_visitor" class="btn btn-success">
              <i class="fas fa-user-plus"></i> Create Visitor
            </button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <!-- Step 2: Create Request for Found Visitor -->
      <div class="card mb-4">
        <div class="card-header"><h4>Step 2: Create Request</h4></div>
        <div class="card-body">
          <p>
            <strong>Visitor:</strong>
            <?= htmlspecialchars($visitor['VFname'] . ' ' . $visitor['VMinit'] . ' ' . $visitor['VLName']) ?>
            (<?= htmlspecialchars($visitor['VEmail']) ?>)
          </p>
          <form method="post">
            <input type="hidden" name="visitor_id" value="<?= $visitor['VId'] ?>">
            <div class="form-group">
              <label for="description">Description</label>
              <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="form-group">
              <label for="department">Department</label>
              <select class="form-control" id="department" name="department" required>
                <option value="">-- Select Department --</option>
                <option value="Technical Support">Technical Support</option>
                <option value="Academic Assistance">Academic Assistance</option>
                <option value="Financial Aid">Financial Aid</option>
              </select>
            </div>
            <div class="form-group">
              <label for="request_method">Request Method</label>
              <select class="form-control" id="request_method" name="request_method" required>
                <option value="">-- Select Method --</option>
                <option value="walk-in">Walk-in</option>
                <option value="phone">Phone</option>
                <option value="email">Email</option>
              </select>
            </div>
            <button type="submit" name="submit_request" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> Submit Request
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
