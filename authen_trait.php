<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('config.php');
session_start();
$ereur = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $ereur = '';

    $FullName = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $conf_pass = $_POST['confirme_password'];

    // check email
    $stmt = $conn->prepare("SELECT id FROM userinfo WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $email_check = $stmt->get_result();

    if (strlen($FullName) < 4) {
        $ereur = 'name sgher mn 4 caracter';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $email_check->num_rows > 0) {
        $ereur = 'email ghalet or deja kayn';
    } else if (strlen($password) < 8) {
        $ereur = 'password sgher mn 8';
    } else if ($password !== $conf_pass) {
        $ereur = 'password machi bhal bhal';
    }

    if ($ereur == '') {

        // insert user
        $hach_pass = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO userinfo (FullName, Email, Passw) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $FullName, $email, $hach_pass);
        $stmt->execute();

        // get last inserted id
        $user_id = $conn->insert_id;

        // insert default expense categories
        $sql = "
            INSERT INTO categorie (cate, user_id) VALUES
            ('Food', ?),
            ('Transport', ?),
            ('Rent', ?),
            ('Electricity', ?),
            ('Water', ?),
            ('Internet', ?),
            ('Phone', ?),
            ('Fuel', ?),
            ('Health', ?),
            ('Education', ?),
            ('Shopping', ?)
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiiiiiiiiii",
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id
        );
        $stmt->execute();

        // success
        $_SESSION['success'] = 'Account created successfully';

    } else {
        $_SESSION['ereur'] = $ereur;
        $_SESSION['fullname'] = $FullName;
        $_SESSION['email'] = $email;
    }
}
header("location: authentication.php");
?>