<?php
session_start();
require_once('../config.php');

// auth guard (adjust login page as appropriate)
if (!isset($_SESSION['SId'])) {
    header('Location: staff_login.php');
    exit;
}

// 1) Requests by Service Department
$sqlDept   = "SELECT Department, RequestCount FROM vw_requests_by_department";
$resDept   = $conn->query($sqlDept);
$byDept    = [];
while ($row = $resDept->fetch_assoc()) {
    $byDept[] = $row;
}
$resDept->free();

// 2) Requests by Method
$sqlMethod = "SELECT RequestMethodology, RequestCount FROM vw_requests_by_method";
$resMethod = $conn->query($sqlMethod);
$byMethod  = [];
while ($row = $resMethod->fetch_assoc()) {
    $byMethod[] = $row;
}
$resMethod->free();

// 3) Peak Request Day
$sqlPeakDay   = "SELECT RequestDate, RequestCount FROM vw_peak_request_day";
$resPeakDay   = $conn->query($sqlPeakDay);
$peakDay      = [];
while ($row = $resPeakDay->fetch_assoc()) {
    $peakDay[] = $row;
}
$resPeakDay->free();

// 4) Peak Request Hour
$sqlPeakHour  = "SELECT RequestHour, RequestCount FROM vw_peak_request_hour";
$resPeakHour  = $conn->query($sqlPeakHour);
$peakHour     = [];
while ($row = $resPeakHour->fetch_assoc()) {
    $peakHour[] = $row;
}
$resPeakHour->free();

// 5) Requests Processed Per Day
$sqlByDay   = "SELECT RequestDate, RequestCount
               FROM vw_requests_by_day
              ORDER BY RequestDate";
$resByDay   = $conn->query($sqlByDay);
$byDay      = [];
while ($row = $resByDay->fetch_assoc()) {
    $byDay[] = $row;
}
$resByDay->free();

// 6) Requests Processed By Hour
$sqlByHour  = "SELECT RequestHour, RequestCount
               FROM vw_requests_by_hour
              ORDER BY RequestHour";
$resByHour  = $conn->query($sqlByHour);
$byHour      = [];
while ($row = $resByHour->fetch_assoc()) {
    $byHour[] = $row;
}
$resByHour->free();

// 7) Requests by Visitor Type
$sqlVisitor = "SELECT VType, RequestCount
               FROM vw_requests_by_visitor_type";
$resVisitor = $conn->query($sqlVisitor);
$byVisitor  = [];
while ($row = $resVisitor->fetch_assoc()) {
    $byVisitor[] = $row;
}
$resVisitor->free();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Help Desk Analytics</title>
  <style>
    body { font-family: Arial, sans-serif; margin:2em; }
    table { border-collapse: collapse; width: 100%; margin-bottom:2em; }
    th, td { border: 1px solid #ccc; padding: 0.5em; text-align:left; }
    th { background:#f0f0f0; }
    h2 { margin-top: 1.5em; }
  </style>
</head>
<body>
  <h1>Help Desk Analytics</h1>

  <h2>Peak Request Day</h2>
  <table>
    <tr><th>Date</th><th>Requests</th></tr>
    <?php foreach ($peakDay as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestDate']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Peak Request Hour</h2>
  <table>
    <tr><th>Hour</th><th>Requests</th></tr>
    <?php foreach ($peakHour as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestHour']) ?>:00</td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Requests Processed Per Day</h2>
  <table>
    <tr><th>Date</th><th>Requests</th></tr>
    <?php foreach ($byDay as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestDate']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Requests Processed By Hour</h2>
  <table>
    <tr><th>Hour</th><th>Requests</th></tr>
    <?php foreach ($byHour as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestHour']) ?>:00</td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Requests by Service Department</h2>
  <table>
    <tr><th>Department</th><th>Requests</th></tr>
    <?php foreach ($byDept as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['Department']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Requests by Method</h2>
  <table>
    <tr><th>Method</th><th>Requests</th></tr>
    <?php foreach ($byMethod as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['RequestMethodology']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Requests by Visitor Type</h2>
  <table>
    <tr><th>Visitor Type</th><th>Requests</th></tr>
    <?php foreach ($byVisitor as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['VType']) ?></td>
        <td><?= htmlspecialchars($r['RequestCount']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
