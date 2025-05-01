<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once('../config.php');

if (! isset($conn) ) {
    die('$conn is not defined! Check that db_config.php is in the right place and defines $conn.');
}


if (!isset($_SESSION['SId'])) {
  header('Location: staff_login.php');
  exit;
}

$loggedInStaffId = $_SESSION['SId'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sfname = trim($_POST['sfname'] ?? '');
    $slname = trim($_POST['slname'] ?? '');

    if ($sfname === '' || $slname === '') {
        $error = 'Both first and last name are required.';
    } else {
        // Prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO Staff (SFName, SLName) VALUES (?, ?)");
        if (!$stmt) {
            $error = 'Prepare failed: ' . $conn->error;
        } else {
            $stmt->bind_param('ss', $sfname, $slname);
            if ($stmt->execute()) {
                // Success — redirect back to staff list
                header('Location: staff_view.php');
                exit;
            } else {
                $error = 'Execute failed: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Staff Member</title>
  <style>
    body { font-family: sans-serif; max-width: 600px; margin: 2em auto; }
    form { display: flex; flex-direction: column; }
    label { margin-bottom: .5em; }
    input { padding: .5em; font-size: 1em; }
    .error { color: #a00; margin-bottom: 1em; }
    button { padding: .75em; font-size: 1em; margin-top: 1em; }
  </style>
</head>
<body>
  <h1>Add New Staff Member</h1>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" action="add_staff.php">
    <label>
      First Name:
      <input type="text" name="sfname" required value="<?= htmlspecialchars($sfname ?? '') ?>">
    </label>

    <label>
      Last Name:
      <input type="text" name="slname" required value="<?= htmlspecialchars($slname ?? '') ?>">
    </label>

    <button type="submit">Add Staff</button>
  </form>

  <p><a href="staff_view.php">← Back to Staff List</a></p>
</body>
</html>