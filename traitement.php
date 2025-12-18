<?php
require_once('config.php');
session_start();
if (isset($_POST['montant_incomes'])) {
    $montant_incomes = (float) $_POST['montant_incomes'];
    $incomes_desc = $_POST['incomes_desc'];
    $card_id = (int) $_POST['card_id'];
    $user_id = (int) $_SESSION['id'];

    $stmt = $conn->prepare("
    INSERT INTO incomes (montant, descri, card_id, user_id)
    VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("dsii", $montant_incomes, $incomes_desc, $card_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("SELECT balance FROM cards WHERE id = ?");
    $stmt->bind_param("i", $card_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $row_b = $result->fetch_assoc();
    
    $balance = (float) $row_b['balance'] + $montant_incomes;
    $add_incomes = $conn->prepare("UPDATE cards SET balance = ? where id=?");
    $add_incomes->bind_param("di", $balance , $card_id);
    $add_incomes->execute();
    $stmt->close();

}

if (isset($_POST['montant_expenses'])) {
    $montant_expenses = $_POST['montant_expenses'];
    $expenses_desc = $_POST['expenses_desc'];
    $sql = "INSERT INTO expenses (montant , descri ) VALUES ('$montant_expenses' , '$expenses_desc')";
    $conn->query($sql);
}
header("location: index.php");
