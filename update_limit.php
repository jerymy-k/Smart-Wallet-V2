<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("config.php");
session_start();
  
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $limit_id  = (int) $_POST['limit_id'];
    $new_limit = (float) $_POST['new_limit'];
    $new_rest  = (float) $_POST['new_limit'];

    if ($new_limit == 0) {
        $stmt = $conn->prepare("UPDATE categorie SET IsActive = 0 WHERE id = ?");
        $stmt->bind_param("i", $limit_id);
        $stmt->execute();
    }

    $stmt = $conn->prepare(
        "UPDATE categorie SET limite = ?, rest = ? WHERE id = ?"
    );
    $stmt->bind_param("ddi", $new_limit, $new_rest, $limit_id);
    $stmt->execute();

    header("Location: categories.php");
    exit;
}
?>