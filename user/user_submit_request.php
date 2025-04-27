<?php
session_start();
require_once('../config.php');
if (empty($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit;
}
$visitor_id = $_SESSION['visitor_id'];
$error_message = '';
$success_message = '';
if (isset($_POST['Submit-request'])) {
    $description = trim($_POST['description'] ?? '');
    $department  = trim($_POST['department'] ?? '');
    $request_method = trim($_POST['request_method'] ?? '');
    if ($description === '' || $department === '' || $request_method === '') {
        $error_message = 'Please fill all request information fields.';
    } else {
        try {
            $stmt = $conn->prepare("CALL create_request(?, ?, ?, ?, ?)");
            // leave staff unassigned (NULL)
            $staff_id = null;
            $stmt->bind_param('iisss', $visitor_id, $staff_id, $description, $department, $request_method);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $request_id = $row['request_id'];
            $success_message = "Request submitted successfully! Your request ID is: $request_id";
        } catch (Exception $e) {
            $error_message = 'Error submitting request: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>…</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Submit Help Desk Request</h2>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?=htmlspecialchars($success_message)?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($error_message)?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="department">Department</label>
            <select id="department" name="department" class="form-control" required>
                <option value="">-- Select Department --</option>
                <option value="Technical Support">Technical Support</option>
                <option value="Academic Assistance">Academic Assistance</option>
                <option value="Financial Aid">Financial Aid</option>
            </select>
        </div>
        <div class="form-group">
            <label for="request_method">Request Method</label>
            <select id="request_method" name="request_method" class="form-control" required>
                <option value="">-- Select Method --</option>
                <option value="walk-in">Walk-in</option>
                <option value="phone">Phone</option>
                <option value="email">Email</option>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" name="Submit-request" class="btn btn-primary">Submit Request</button>
    </form>
    <a href="user_request.php" class="btn btn-secondary mt-3">Back to My Requests</a>
</div>
</body>
</html>