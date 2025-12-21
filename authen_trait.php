<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
session_start();

$ereur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $FullName = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $conf_pass = $_POST['confirme_password'] ?? '';

    // check email
    $stmt = $conn->prepare("SELECT id FROM userinfo WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $email_check = $stmt->get_result();

    if (strlen($FullName) < 4) {
        $ereur = 'Full name must be at least 4 characters.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $ereur = 'Invalid email address.';
    } else if ($email_check->num_rows > 0) {
        $ereur = 'This email is already registered.';
    } else if (strlen($password) < 8) {
        $ereur = 'Password must be at least 8 characters.';
    } else if ($password !== $conf_pass) {
        $ereur = 'Passwords do not match.';
    }

    if ($ereur == '') {

        $hach_pass = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO userinfo (FullName, Email, Passw) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $FullName, $email, $hach_pass);
        $stmt->execute();

        $id = $conn->insert_id;

        $sql = "
            INSERT INTO categorie (cate, user_id) VALUES
            ('Food', $id),
            ('Transport', $id),
            ('Rent', $id),
            ('Electricity', $id),
            ('Water', $id),
            ('Internet', $id),
            ('Phone', $id),
            ('Fuel', $id),
            ('Health', $id),
            ('Education', $id),
            ('Shopping', $id)
        ";
        $conn->query($sql);

        // Store user ID in session to complete card setup
        $_SESSION['pending_user_id'] = $id;
        $_SESSION['pending_user_name'] = $FullName;
        $_SESSION['success'] = 'Account created! Please add your first card to complete signup.';

        header("location: add_first_card.php");
        exit();

    } else {
        $_SESSION['ereur'] = $ereur;
        $_SESSION['fullname'] = $FullName;
        $_SESSION['email'] = $email;
        header("location: authentication.php");
        exit();
    }
}

header("location: authentication.php");
exit;
?>
