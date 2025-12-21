<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('config.php');
session_start();

$Active = 1;

$stmt = $conn->prepare('SELECT * FROM categorie WHERE IsActive = ?');
$stmt->bind_param('i', $Active);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $limite = $row['limite'];
    $cate_id = $row['id'];
    $cate = $row['cate'];
    
    $stmt_card = $conn->prepare("SELECT * FROM cards WHERE principal = 1 AND user_id = ?");
    $stmt_card->bind_param('i', $user_id);
    $stmt_card->execute();
    $pric_card = $stmt_card->get_result()->fetch_assoc();
    $stmt_card->close();
    
    if(!$pric_card) {
        error_log("No principal card found for user $user_id</br>");
        continue;
    }
    
    $id_pric_card = $pric_card['id'];
    
    if($pric_card['balance'] >= $limite) {
        // Insert expense
        $stmt_exp = $conn->prepare('INSERT INTO expenses(montant, cate_id, card_id, user_id) VALUES (?,?,?,?)');
        $stmt_exp->bind_param('diii', $limite, $cate_id, $id_pric_card, $user_id);
        $stmt_exp->execute();
        $stmt_exp->close();
        
        $new_balance = $pric_card['balance'] - $limite;
        $stmt_upd = $conn->prepare('UPDATE cards SET balance = ? WHERE id = ?');
        $stmt_upd->bind_param('di', $new_balance, $id_pric_card);
        $stmt_upd->execute();
        $stmt_upd->close();
        
        $_SESSION['msg_trans'] =  "Processed expense for user $user_id, category $cate</br>";
    } else {
       $_SESSION['msg_trans'] = "Insufficient balance for user $user_id, category $cate_id</br>";
    }
}

$stmt->close();
$conn->close();
?>