<?php
session_start();
require_once('../config.php');

$error_message   = '';
$success_message = '';

if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $success_message = 'Account created successfully! Please log in.';
}

if (isset($_POST['Submit'])) {
    $fname = trim($_POST['fname'] ?? '');
    $minit = trim($_POST['minit'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $vtype  = $_POST['type']      ?? '';
    $phones = $_POST['phone']     ?? [];

    if ($fname === '' || $lname === '' || $email === '' || $vtype === '') {
        $error_message = 'First name, last name, email, and visitor type are required.';
    } elseif (! in_array($vtype, ['student','staff','guest'], true)) {
        $error_message = 'Invalid visitor type selected.';
    } else {
        try {
            $stmt = $conn->prepare("CALL create_visitor(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fname, $minit, $lname, $email, $vtype);
            $stmt->execute();

            $stmt->bind_result($visitor_id);
            $stmt->fetch();
            $stmt->close();

            while ($conn->more_results()) {
                $conn->next_result();
            }

            foreach ($phones as $raw) {
                $raw = trim($raw);
                if ($raw === '') {
                    continue;
                }
                $phoneStmt = $conn->prepare("CALL add_visitor_phone(?, ?)");
                $phoneStmt->bind_param("is", $visitor_id, $raw);
                $phoneStmt->execute();
                $phoneStmt->close();

                // again, clear any extra result sets
                while ($conn->more_results()) {
                    $conn->next_result();
                }
            }

            header("Location: user_signup.php?signup=success");
            exit;

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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Visitor Sign Up</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container my-5">
  <h1>Visitor Sign Up</h1>

  <?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
  <?php elseif ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="fname">First Name</label>
        <input type="text" class="form-control" id="fname" name="fname"
               value="<?php echo htmlspecialchars($fname ?? ''); ?>" required>
      </div>
      <div class="form-group col-md-2">
        <label for="minit">M.I.</label>
        <input type="text" class="form-control" id="minit" name="minit"
               value="<?php echo htmlspecialchars($minit ?? ''); ?>" maxlength="1">
      </div>
      <div class="form-group col-md-4">
        <label for="lname">Last Name</label>
        <input type="text" class="form-control" id="lname" name="lname"
               value="<?php echo htmlspecialchars($lname ?? ''); ?>" required>
      </div>
      <div class="form-group col-md-4">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
      </div>
      <div class="form-group col-md-4">
        <label for="type">Visitor Type</label>
        <select id="type" name="type" class="form-control" required>
          <option value="">-- Select type --</option>
          <option value="student" <?php if (($vtype??'')==='student') echo 'selected'; ?>>Student</option>
          <option value="staff"   <?php if (($vtype??'')==='staff')   echo 'selected'; ?>>Staff</option>
          <option value="guest"   <?php if (($vtype??'')==='guest')   echo 'selected'; ?>>Guest</option>
        </select>
      </div>
    </div>

    <div id="phone-container" class="mb-3">
      <div class="form-group phone-field">
        <label>Phone (optional)</label>
        <input type="text" class="form-control" name="phone[]" placeholder="e.g. 915-555-1212">
      </div>
    </div>
    <button type="button" id="add-phone"
            class="btn btn-sm btn-outline-secondary mb-3">
      + Add another phone
    </button>

    <button type="submit" name="Submit" class="btn btn-primary">
      Create Account
    </button>
    <a href="user_login.php" class="btn btn-secondary ml-2">Log In</a>
  </form>
</div>

<script>
  document.getElementById('add-phone').addEventListener('click', () => {
    const tpl = document.querySelector('.phone-field').cloneNode(true);
    tpl.querySelector('input').value = '';
    document.getElementById('phone-container').appendChild(tpl);
  });
</script>
</body>
</html>