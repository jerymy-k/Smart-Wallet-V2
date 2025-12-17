<?php
require_once('config.php');
session_start();
$ereur = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $FullName = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $conf_pass = $_POST['confirme_password'];
    $email_cheak = $conn->query("SELECT Email FROM userinfo WHERE Email = '$email'");
    if (strlen($FullName) < 4) {
        $ereur = 'name sgher mn 4 caracter';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $email_cheak->num_rows > 0) {
        $ereur = 'email ghalet or deja kayn';
    }
    else if (strlen($password) < 8) {
        $ereur = 'password sgher mn 8';
    } else if ($password !== $conf_pass) {
        $ereur = 'password machi bhal bhal';
    }

    if ($ereur == '') {
        $hach_pass = password_hash($password, algo: PASSWORD_DEFAULT);
        $sql = "INSERT INTO userinfo (FullName , Email , Passw) VALUES ('$FullName' , '$email' , '$hach_pass')";
        $conn->query($sql);

    } else {
        $_SESSION['ereur'] = $ereur;
        $_SESSION['fullname'] = $FullName;
        $_SESSION['email'] = $email;
    }

}
header("location: authentication.php");
?>