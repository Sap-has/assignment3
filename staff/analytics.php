<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once('../config.php');

if (!isset($_SESSION['SId'])) {
    header('Location: staff_login.php');
    exit;
}

$loggedInStaffId = $_SESSION['SId'];

// 2) helper function to run a query and return all rows
function fetchAll($pdo, $sql) {
    $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// 3) load each dataset
$peakDay       = fetchAll($pdo, "SELECT * FROM vw_peak_request_day");
$peakHour      = fetchAll($pdo, "SELECT * FROM vw_peak_request_hour");
$byDay         = fetchAll($pdo, "SELECT * FROM vw_requests_by_day ORDER BY RequestDate");
$byHour        = fetchAll($pdo, "SELECT * FROM vw_requests_by_hour ORDER BY RequestHour");
$byDept        = fetchAll($pdo, "SELECT * FROM vw_requests_by_department");
$byMethod      = fetchAll($pdo, "SELECT * FROM vw_requests_by_method");
$byVisitorType = fetchAll($pdo, "SELECT * FROM vw_requests_by_visitor_type");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Help Desk Analytics</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2em; }
    h2 { margin-top: 1.5em; }
    table { border-collapse: collapse; width: 60%; margin-bottom: 1em; }
    th, td { border: 1px solid #ccc; padding: 0.5em 1em; text-align: left; }
    th { background: #f0f0f0; }
  </style>
</head>
<body>

  <h1>Help Desk Analytics</h1>

  <!-- Peak Day & Hour -->
  <h2>Peak Request Day</h2>
  <table>
    <tr><th>Date</th><th># Requests</th></tr>
    <?php foreach($peakDay as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestDate']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Peak Request Hour</h2>
  <table>
    <tr><th>Hour (0–23)</th><th># Requests</th></tr>
    <?php foreach($peakHour as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestHour']) ?>:00</td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- Requests per Day & Hour trend -->
  <h2>Requests Processed Per Day</h2>
  <table>
    <tr><th>Date</th><th># Requests</th></tr>
    <?php foreach($byDay as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestDate']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Requests Processed By Hour</h2>
  <table>
    <tr><th>Hour</th><th># Requests</th></tr>
    <?php foreach($byHour as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestHour']) ?>:00</td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- Department breakdown -->
  <h2>Requests by Service Department</h2>
  <table>
    <tr><th>Department</th><th># Requests</th></tr>
    <?php foreach($byDept as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['Department']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- Method breakdown -->
  <h2>Requests by Method</h2>
  <table>
    <tr><th>Method</th><th># Requests</th></tr>
    <?php foreach($byMethod as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestMethodology']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- Visitor demographics -->
  <h2>Requests by Visitor Type</h2>
  <table>
    <tr><th>Visitor Type</th><th># Requests</th></tr>
    <?php foreach($byVisitorType as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['VType']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

</body>
</html>
