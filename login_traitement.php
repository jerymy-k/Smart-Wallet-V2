<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config.php";
require "otp.php";
require 'user_ip.php';
session_start();

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = "Please enter your email and password.";
    header('Location: authentication.php');
    exit;
}

$auton = false;

// Same logic, but safer query (prepared)
$stmt = $conn->prepare("SELECT * FROM userinfo WHERE Email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result_email_pass = $stmt->get_result();
$stmt->close();

if ($result_email_pass && $result_email_pass->num_rows > 0) {
    $row = $result_email_pass->fetch_assoc();

    if (password_verify($password, $row["Passw"])) {
        $id = (int)$row['id'];
        $_SESSION["id"] = $id;

        $ip_user = $conn->query("SELECT ip , user_id FROM user_ip WHERE user_id=$id");
        $ip = getUserIP();

        while ($r = $ip_user->fetch_assoc()) {
            if ($r['ip'] == $ip) {
                $stat = 1;
                $stmt = $conn->prepare("UPDATE userinfo SET stat = ? WHERE id = ?");
                $stmt->bind_param("ii", $stat, $id);
                $stmt->execute();
                $stmt->close();

                header("Location: index.php");
                exit();
            }
        }

        $date_exp = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $act_str = rand(100000, 999999);

        $stmt = $conn->prepare("UPDATE userinfo SET  code_exp = ? , otp = ? WHERE id = ?");
        $stmt->bind_param("ssi", $date_exp, $act_str, $id);
        $stmt->execute();
        $stmt->close();

        mailsender($email, $act_str);
        header("Location: verify_otp.php");
        exit();
    }
}

// Login failed
$_SESSION['login_error'] = "Invalid email or password.";
header('Location: authentication.php');
exit;
?>
