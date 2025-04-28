<?php
session_start();
require_once('../config.php');
if (empty($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit;
}

$visitor_id      = $_SESSION['visitor_id'];
$error_message   = '';
$success_message = '';

if (isset($_POST['Submit-request'])) {
    $department     = trim($_POST['department'] ?? '');
    $request_method = trim($_POST['request_method'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $staff_id       = 1;

    if ($department === '' || $request_method === '' || $description === '') {
        $error_message = 'Please fill all request information fields.';
    } else {
        try {
            $stmt = $conn->prepare("CALL create_request(?, ?, ?, ?, ?)");
            $stmt->bind_param('iisss',
                $visitor_id,
                $staff_id,
                $description,     
                $department,      
                $request_method   
            );
            
            if (! $stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $success_message = 'Your request has been submitted!';
        } catch (Exception $e) {
            $error_message = 'Error submitting request: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Submit Help Desk Request</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
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
    <div class="card mb-4">
      <div class="card-header">
        <h2 class="text-center mb-0">Submit Help Desk Request</h2>
      </div>
      <div class="card-body">

        <!-- Success / Error Alerts -->
        <?php if ($success_message): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Request Form -->
        <form method="post">
          <div class="form-group">
            <label for="department">Department <span class="text-danger">*</span></label>
            <select id="department" name="department" class="form-control" required>
              <option value="">-- Select Department --</option>
              <option value="Technical Support">Technical Support</option>
              <option value="Academic Assistance">Academic Assistance</option>
              <option value="Financial Aid">Financial Aid</option>
            </select>
          </div>

          <div class="form-group">
            <label for="request_method">Request Method <span class="text-danger">*</span></label>
            <select id="request_method" name="request_method" class="form-control" required>
              <option value="">-- Select Request Method --</option>
              <option value="walk-in">Walk-in</option>
              <option value="phone">Phone</option>
              <option value="email">Email</option>
            </select>
          </div>

          <div class="form-group">
            <label for="description">Description <span class="text-danger">*</span></label>
            <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
          </div>

          <div class="form-group text-center mt-4">
            <button type="submit"
                    name="Submit-request"
                    class="btn btn-primary submit-button">
              Submit Request
            </button>
          </div>
        </form>

        <div class="text-center mt-3">
          <a href="user_view.php" class="btn btn-secondary">Back</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
