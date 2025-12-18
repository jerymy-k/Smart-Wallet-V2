<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
    $add_incomes->bind_param("di", $balance, $card_id);
    $add_incomes->execute();
    $stmt->close();

}

if (isset($_POST['montant_expenses'])) {
    echo 'start exp';
    $montant_expenses = (float) $_POST['montant_expenses'];
    $categorie_id = $_POST['cate_id'];
    $card_id = $_POST['card_id'];
    $user_id = (int) $_SESSION['id'];
    $stmt = $conn->prepare('SELECT rest FROM categorie WHERE id = ?');
    $stmt->bind_param('i', $categorie_id);
    $stmt->execute();
    $reslt = $stmt->get_result();
    $row_lt = $reslt->fetch_assoc();
    if (($row_lt['rest'] - $montant_expenses) > 0) {
        echo 'enter condition';
        $stmt = $conn->prepare('INSERT INTO expenses(montant , cate_id , card_id , user_id) VALUES (?,?,?,?)');
        $stmt->bind_param('diii', $montant_expenses, $categorie_id, $card_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $stmt = $conn->prepare('SELECT balance FROM cards WHERE id = ?');
        $stmt->bind_param('i', $card_id);
        $stmt->execute();
        $row_b = $stmt->get_result();
        $row_b = $row_b->fetch_assoc();
        $newBalance = $row_b['balance'] - $montant_expenses;
        $NewBalance = $conn->prepare('UPDATE cards SET balance = ? where id=?');
        $NewBalance->bind_param('di', $newBalance, $card_id);
        $NewBalance->execute();
        $stmt->close();
        $NewBalance->close();
        $NewRest = $row_lt['rest'] - $montant_expenses;
        $stmt = $conn->prepare('UPDATE categorie ');
        echo 'done';
    }else{
        $stmt = $conn->prepare('SELECT cate FROM categorie WHERE id = ?');
        $stmt->bind_param('i', $categorie_id);
        $stmt->execute();
        $cate_name = $stmt->get_result();
        $cate_name = $cate_name->fetch_assoc();
        $cate_name = $cate_name['cate'];
        $_SESSION['message_ereur'] = "U have reached the maximum limit in $cate_name";
        header("Location: expenses.php");
    }
}
// header("location: index.php");
