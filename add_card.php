<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    require_once('config.php');
    session_start();
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(isset($_POST['card_name'])){
            $id = $_SESSION['id'];
            $card_name = $_POST['card_name'];
            $bank_name = $_POST['bank_name'];
            echo $bank_name;
            $initial_balance = $_POST['initial_balance'];
            $stmt = $conn->prepare("INSERT INTO cards(user_id,card_name,bank_name,initial_balance,balance) VALUES (? , ? , ? , ? , ?)");
            $stmt->bind_param("issdd", $id, $card_name, $bank_name, $initial_balance , $initial_balance );
            $stmt->execute();
            $stmt->close();
            header("Location: index.php");
        }
    }
?>