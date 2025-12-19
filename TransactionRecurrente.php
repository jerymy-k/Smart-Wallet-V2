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
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) {
    echo $row['id']  . '|     |';
    echo $row['cate'] . '|    |';
    echo $row['limite'] . '|    |';
    echo $row['rest'] . '|     |';
    echo $row['IsActive'] ;
    echo "<br>";
}
?>