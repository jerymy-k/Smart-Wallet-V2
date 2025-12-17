<?php
    require_once('config.php');
    session_start();
    $id = $_SESSION['id'];
    $sql = "UPDATE userinfo SET stat = 0 WHERE id = $id";
    $conn->query($sql);
    session_destroy();
    header("location: index.php");
?>