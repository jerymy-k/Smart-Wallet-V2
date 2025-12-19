<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("config.php");
session_start();
  
if (!isset($_SESSION['id'])) {
    header("location: authentication.php");
    exit;
}
  
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $limit_id = $_POST['limit_id'];
    $new_limit = $_POST['new_limit'];
    $new_rest = $_POST['new_limit'];
    if($new_limit == 0) {
        $stmt = $conn->prepare('UPDATE categorie SET IsActive');
    }
    $cheak = $conn->query("SELECT rest FROM categorie where id = $limit_id");
    $cheak = $cheak->fetch_assoc();
    $rest = (float) $cheak['rest'];
    $stmt = $conn->prepare("UPDATE categorie SET limite = ? , rest = ? WHERE id = ? ");
    $stmt->bind_param("ddi", $new_limit, $new_rest, $limit_id);
    $stmt->execute();
    header("Location: categories.php");
}
?>