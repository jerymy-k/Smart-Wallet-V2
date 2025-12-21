<?php
require_once('config.php');
session_start();

$user_id = $_SESSION['id'];
$card_id = $_POST['card_id'];

// Mettre toutes les cartes à principal = 0
$conn->query("UPDATE cards SET principal = 0 WHERE user_id = $user_id");

// Mettre UNE SEULE carte à principal = 1
$conn->query("UPDATE cards SET principal = 1 WHERE id = $card_id AND user_id = $user_id");

header("Location: cards.php");
?>