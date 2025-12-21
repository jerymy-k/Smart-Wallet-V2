<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('config.php');
session_start();
$Active = 1 ;
$stmt = $conn->prepare('SELECT * FROM categorie WHERE IsActive = ?');
$stmt->bind_param('i', $Active);
$stmt->execute() ;
$result = $stmt->get_result(); // the result of the categories active 
$pric_card = $conn->query('SELECT * FROM cards where principal = 1 ')->fetch_assoc();// id of the principal card
$id_pric_card = $pric_card['id']; // id of the principal card
$id_user = $_SESSION['id']; // user id
while($row = $result->fetch_assoc()) {
    $limite = $row['limite'];
    $cate_id = $row['id'];
    if($pric_card['balance']-$limite >= 0) {
        $stmt = $conn->prepare('INSERT INTO expenses(montant , cate_id , card_id , user_id) VALUES (?,?,?,?)');
        $stmt->bind_param('diii' , $limite, $cate_id ,$id_pric_card, $id_user );
        $stmt->execute() ;
        $stmt->close() ;
        $new_balance = $pric_card['balance']-$limite;
        $stmt = $conn->prepare('UPDATE cards SET balance = ? WHERE id = ?'); 
        $stmt->bind_param('di', $new_balance , $id_user) ;
        $stmt->execute() ;
        $stmt->close() ;
    }else{
        // $_SESSION['ereur_tran'] = "u dont have sold to pay $cate";
        // header("Location: index.php");
        echo 'no sold enough';
        exit();
    }
}
?>