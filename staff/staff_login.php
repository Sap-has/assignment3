<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Staff Login</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
</head>

<body>
  <div style="margin-top: 20px" class="container">
    <h1>Staff Login</h1>
    <form action="staffLogin.php" method="post">
      <div class="form-group">
        <label for="sid">Staff ID</label>
        <input class="form-control" type="text" id="sid" name="sid" required>
      </div>
      <div class="form-group">
        <label for="sfname">First Name</label>
        <input class="form-control" type="text" id="sfname" name="sfname" required>
      </div>
      <div class="form-group">
        <input class="btn btn-primary" name="Submit" type="submit" value="Login">
      </div>
    </form>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>

<?php
// Handle staff login authentication

session_start();
require_once("../config.php");
$_SESSION['staff_logged_in'] = false;

if (!empty($_POST)) {
  if (isset($_POST['Submit'])) {
    $sid = isset($_POST['sid']) ? $_POST['sid'] : " ";
    $sfname = isset($_POST['sfname']) ? $_POST['sfname'] : " ";

    $queryStaff = "SELECT * FROM staff WHERE SId='" . $sid . "' AND SFName='" . $sfname . "';";
    $resultStaff = $conn->query($queryStaff);

    if ($resultStaff->num_rows > 0) {
      $_SESSION['staff_id'] = $sid;
      $_SESSION['staff_logged_in'] = true;

      echo "Staff session logged_in is: " . $_SESSION['staff_logged_in'];
      // Redirect to a staff dashboard or placeholder
      header("Location: staffView.php");
    } else {
      echo "Staff not found.";
    }
    die();
  }
}
?>