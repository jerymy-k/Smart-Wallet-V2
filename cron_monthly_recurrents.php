<?php
require_once("config.php");

// run only on day 1
if ((int)date('j') !== 1) exit;

$now = date('Y-m-d H:i:s');
$monthStart = date('Y-m-01 00:00:00'); // first day of this month

// Recurrents not executed this month
$stmt = $conn->prepare("
  SELECT id, user_id, card_id, type, title, category_id, amount, last_run
  FROM monthly_recurrents
  WHERE is_active = 1
    AND (last_run IS NULL OR last_run < ?)
");
$stmt->bind_param("s", $monthStart);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

while ($r = $res->fetch_assoc()) {

  $rid = (int)$r['id'];
  $user_id = (int)$r['user_id'];
  $card_id = (int)$r['card_id'];
  $amount = (float)$r['amount'];
  $title = (string)$r['title'];
  $cate_id = ($r['category_id'] !== null && $r['category_id'] !== '') ? (int)$r['category_id'] : null;

  if ($amount <= 0) continue;

  // 1) Get card balance (and ensure card belongs to user)
  $c = $conn->prepare("SELECT balance FROM cards WHERE id=? AND user_id=? LIMIT 1");
  $c->bind_param("ii", $card_id, $user_id);
  $c->execute();
  $card = $c->get_result()->fetch_assoc();
  $c->close();

  if (!$card) continue;

  $balance = (float)$card['balance'];

  // 2) If expense: check balance first
  if ($r['type'] === 'expense' && $balance < $amount) {
    // not enough money -> skip and DO NOT update last_run
    continue;
  }

  $ok = false;

  // 3) Insert transaction in correct table/columns
  if ($r['type'] === 'income') {

    // incomes: montant, cate_name, card_id, user_id (laDate auto)
    $ins = $conn->prepare("
      INSERT INTO incomes (montant, cate_name, card_id, user_id)
      VALUES (?, ?, ?, ?)
    ");
    $ins->bind_param("dsii", $amount, $title, $card_id, $user_id);
    $ok = $ins->execute();
    $ins->close();

    // update card balance (+)
    if ($ok) {
      $upBal = $conn->prepare("UPDATE cards SET balance = balance + ? WHERE id=? AND user_id=?");
      $upBal->bind_param("dii", $amount, $card_id, $user_id);
      $ok = $upBal->execute();
      $upBal->close();
    }

  } else {

    // expenses: montant, cate_id (nullable), card_id, user_id (laDate auto)
    if ($cate_id !== null) {
      $ins = $conn->prepare("
        INSERT INTO expenses (montant, cate_id, card_id, user_id)
        VALUES (?, ?, ?, ?)
      ");
      $ins->bind_param("diii", $amount, $cate_id, $card_id, $user_id);
    } else {
      $ins = $conn->prepare("
        INSERT INTO expenses (montant, card_id, user_id)
        VALUES (?, ?, ?)
      ");
      $ins->bind_param("dii", $amount, $card_id, $user_id);
    }

    $ok = $ins->execute();
    $ins->close();

    // update card balance (-)
    if ($ok) {
      $upBal = $conn->prepare("UPDATE cards SET balance = balance - ? WHERE id=? AND user_id=?");
      $upBal->bind_param("dii", $amount, $card_id, $user_id);
      $ok = $upBal->execute();
      $upBal->close();
    }
  }

  // 4) Update last_run ONLY if everything worked
  if ($ok) {
    $up = $conn->prepare("UPDATE monthly_recurrents SET last_run=? WHERE id=? AND user_id=?");
    $up->bind_param("sii", $now, $rid, $user_id);
    $up->execute();
    $up->close();
  }
}
