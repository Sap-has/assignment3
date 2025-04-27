<?php
session_start();
require_once('../config.php');

$error_message = '';
if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $success_message = 'Account created successfully! Please log in.';
}

if (isset($_POST['Submit'])) {
    $fname = trim($_POST['fname'] ?? '');
    $minit = trim($_POST['minit'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($fname === '' || $lname === '' || $email === '') {
        $error_message = 'First name, last name, and email are required.';
    } else {
        try {
            $stmt = $conn->prepare("CALL create_visitor(?, ?, ?, ?, 'student')");
            $stmt->bind_param('ssss', $fname, $minit, $lname, $email);
            if ($stmt->execute()) {
                // Get new visitor ID
                $res = $conn->query("SELECT LAST_INSERT_ID() AS vid");
                $row = $res->fetch_assoc();
                $visitor_id = $row['vid'];
                if (!empty($phone)) {
                    $phoneStmt = $conn->prepare("CALL add_visitor_phone(?, ?)");
                    $phoneStmt->bind_param('is', $visitor_id, $phone);
                    $phoneStmt->execute();
                }
                header('Location: user_login.php?signup=success');
                exit;
            } else {
                $error_message = 'Error creating account: ' . $stmt->error;
            }
        } catch (Exception $e) {
            $error_message = 'Error creating account: ' . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Visitor Sign Up</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Visitor Sign Up</h2>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?=htmlspecialchars($success_message)?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($error_message)?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="fname">First Name</label>
                <input type="text" class="form-control" id="fname" name="fname" required>
            </div>
            <div class="form-group col-md-2">
                <label for="minit">M.I.</label>
                <input type="text" class="form-control" id="minit" name="minit" maxlength="1">
            </div>
            <div class="form-group col-md-4">
                <label for="lname">Last Name</label>
                <input type="text" class="form-control" id="lname" name="lname" required>
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone (optional)</label>
            <input type="text" class="form-control" id="phone" name="phone">
        </div>
        <button type="submit" name="Submit" class="btn btn-primary">Create Account</button>
    </form>
    <p class="mt-3">Already have an account? <a href="user_login.php">Log in</a></p>
</div>
</body>
</html>
