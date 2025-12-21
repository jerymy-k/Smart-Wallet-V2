<?php
require_once("config.php");
session_start();

if (!isset($_SESSION['id'])) { header("location: login.php"); exit; }
$user_id = (int)$_SESSION['id'];

// principal card obligatoire
$stmt = $conn->prepare("SELECT id FROM cards WHERE user_id=? AND principal=1 LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$principal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$principal) { header("Location: cards.php"); exit; }

$type = $_POST['type'] ?? '';
$title = trim($_POST['title'] ?? '');
$amount = (float)($_POST['amount'] ?? 0);
$category_id = (isset($_POST['category_id']) && $_POST['category_id'] !== '') ? (int)$_POST['category_id'] : null;

if (!in_array($type, ['income','expense'], true) || $title === '' || $amount <= 0) {
  $_SESSION['rec_msg'] = "Données invalides.";
  header("Location: recurrent_add.php");
  exit;
}

$card_id = (int)$principal['id'];

$stmt = $conn->prepare("
  INSERT INTO monthly_recurrents (user_id, card_id, type, title, category_id, amount)
  VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("iissid", $user_id, $card_id, $type, $title, $category_id, $amount);
$stmt->execute();
$stmt->close();

$_SESSION['rec_msg'] = "Transaction récurrente ajoutée.";
header("Location: recurrents.php");
exit;
