<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['SId'])) {
    header('Location: staff_login.php');
    exit;
}
$loggedInStaffId = $_SESSION['SId'];

$sql  = "SELECT VId, VFname, VMinit, VLName, VEmail, VType 
         FROM Visitor
         ORDER BY VLName, VFname";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>All Visitors</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container py-4">
    <h2 class="mb-4">Visitor Management</h2>
    <table class="table table-striped table-bordered">
      <thead class="thead-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Type</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['VId']) ?></td>
            <td>
              <?= htmlspecialchars($row['VFname']) ?>
              <?= htmlspecialchars($row['VMinit']) ?>
              <?= htmlspecialchars($row['VLName']) ?>
            </td>
            <td><?= htmlspecialchars($row['VEmail']) ?></td>
            <td><?= htmlspecialchars(ucfirst($row['VType'])) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <div class="mt-3">
      <a href="staff_view.php" class="btn btn-secondary">
        ← Back to Dashboard
      </a>
      <a href="staff_logout.php" class="btn btn-danger float-right">
        Log Out
      </a>
    </div>
  </div>
</body>
</html>