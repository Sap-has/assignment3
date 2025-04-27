<?php
session_start();
require_once('../config.php');

// Redirect if already logged in
if (!empty($_SESSION['user_logged_in'])) {
    header('Location: user_view.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ident = trim($_POST['identifier'] ?? '');
    if (ctype_digit($ident)) {
        $sql = "SELECT * FROM Visitor WHERE VId = ? AND VType = 'student'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $ident);
    } else {
        $sql = "SELECT * FROM Visitor WHERE VEmail = ? AND VType = 'student'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $ident);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $u = $res->fetch_assoc();
        $_SESSION['visitor_id']     = $u['VId'];
        $_SESSION['visitor_name']   = $u['VFname'] . ' ' . $u['VLName'];
        $_SESSION['user_logged_in'] = true;
        header('Location: user_view.php');
        exit;
    } else {
        $error = 'Visitor ID or email not found.';
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Visitor Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Visitor Login</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="identifier">Visitor ID or Email</label>
            <input type="text" class="form-control" id="identifier" name="identifier" required>
        </div>
        <button type="submit" class="btn btn-primary">Log In</button>
    </form>
    <p class="mt-3">Don't have an account? <a href="user_signup.php">Sign up here.</a></p>
</div>
</body>
</html>