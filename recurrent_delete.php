<?php
require_once("config.php");
session_start();

if (!isset($_SESSION['id'])) { header("location: login.php"); exit; }
$user_id = (int)$_SESSION['id'];
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) { header("Location: recurrents.php"); exit; }

$stmt = $conn->prepare("DELETE FROM monthly_recurrents WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$stmt->close();

$_SESSION['rec_msg'] = "Récurrence supprimée.";
header("Location: recurrents.php");
exit;
