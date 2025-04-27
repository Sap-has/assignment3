<!doctype html>
<html>
<head>…</head>
<body>
  <h1>Sign In</h1>
  <?php if($error): ?>
    <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>
  <form method="post">
    <label>Visitor ID or Email</label>
    <input name="identifier" class="form-control" required>
    <button class="btn btn-primary mt-2">Log In</button>
  </form>
  <p><a href="user_signup.php">Create an account</a></p>
</body>
</html>

<?php
session_start();
require_once('../config.php');

// if they’re already logged in, send them to the dashboard:
if (!empty($_SESSION['user_logged_in'])) {
  header('Location: user_view.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $ident = trim($_POST['identifier']);    // could be numeric ID or email
  
  // choose query based on whether it's all digits
  if (ctype_digit($ident)) {
    $sql = "SELECT * 
              FROM Visitor 
             WHERE VId = ? 
               AND VType = 'student'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $ident);
  } else {
    $sql = "SELECT * 
              FROM Visitor 
             WHERE VEmail = ? 
               AND VType = 'student'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $ident);
  }
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res->num_rows===1) {
    $u = $res->fetch_assoc();
    $_SESSION['visitor_id']     = $u['VId'];
    $_SESSION['visitor_name']   = $u['VFname'].' '.$u['VLName'];
    $_SESSION['user_logged_in'] = true;
    header('Location: user_view.php');
    exit;
  } else {
    $error = 'Not found—please check your ID or email.';
  }
}