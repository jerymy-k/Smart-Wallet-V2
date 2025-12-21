<?php
require_once("config.php");

// Ne faire que le 1er du mois
if ((int)date('j') !== 1) {
  exit;
}

$today = date('Y-m-d');
$currentMonth = date('Y-m'); // ex: 2025-12

// Récurrents actifs qui n'ont pas encore été générés ce mois
$sql = "
  SELECT id, user_id, card_id, type, title, category_id, amount, last_run
  FROM monthly_recurrents
  WHERE is_active = 1
    AND (last_run IS NULL OR DATE_FORMAT(last_run, '%Y-%m') <> ?)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentMonth);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

while ($r = $res->fetch_assoc()) {
  $user_id = (int)$r['user_id'];
  $card_id = (int)$r['card_id'];
  $category_id = $r['category_id'] !== null ? (int)$r['category_id'] : null;
  $amount = (float)$r['amount'];
  $title = $r['title'];
  $rid = (int)$r['id'];

  // ⚠️ ADAPTE ces INSERT à tes vraies tables/colonnes !
  // Exemple simple : tables incomes / expenses : (user_id, card_id, category_id, amount, label, date)
  if ($r['type'] === 'income') {
    $ins = $conn->prepare("
      INSERT INTO incomes (user_id, card_id, category_id, amount, label, date)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ins->bind_param("iiidss", $user_id, $card_id, $category_id, $amount, $title, $today);
    $ins->execute();
    $ins->close();
  } else {
    $ins = $conn->prepare("
      INSERT INTO expenses (user_id, card_id, category_id, amount, label, date)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ins->bind_param("iiidss", $user_id, $card_id, $category_id, $amount, $title, $today);
    $ins->execute();
    $ins->close();
  }

  // Marquer comme généré ce mois
  $up = $conn->prepare("UPDATE monthly_recurrents SET last_run=? WHERE id=?");
  $up->bind_param("si", $today, $rid);
  $up->execute();
  $up->close();
}
