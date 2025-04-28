<?php
session_start();
if (empty($_SESSION['SId'])) {
  header('Location: staff_login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['VId'])) {
    require '../config.php';

    $vid = (int) $_POST['VId'];
    if ($vid > 0) {
        $stmt = $pdo->prepare("DELETE FROM Visitor WHERE VId = ?");
        $stmt->execute([$vid]);
    }
}

header('Location: view_visitors.php');
exit;
