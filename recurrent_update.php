<?php
require_once("config.php");
session_start();

if (!isset($_SESSION['id'])) { header("location: login.php"); exit; }
$user_id = (int)$_SESSION['id'];

$id = (int)($_POST['id'] ?? 0);
$type = $_POST['type'] ?? '';
$title = trim($_POST['title'] ?? '');
$amount = (float)($_POST['amount'] ?? 0);
$category_id = (isset($_POST['category_id']) && $_POST['category_id'] !== '') ? (int)$_POST['category_id'] : null;

if ($id <= 0 || !in_array($type, ['income','expense'], true) || $title === '' || $amount <= 0) {
  $_SESSION['rec_msg'] = "Données invalides.";
  header("Location: recurrents.php");
  exit;
}

$stmt = $conn->prepare("
  UPDATE monthly_recurrents
  SET type=?, title=?, category_id=?, amount=?
  WHERE id=? AND user_id=?
");
$stmt->bind_param("ssidii", $type, $title, $category_id, $amount, $id, $user_id);
$stmt->execute();
$stmt->close();

$_SESSION['rec_msg'] = "Récurrence modifiée.";
header("Location: recurrents.php");
exit;