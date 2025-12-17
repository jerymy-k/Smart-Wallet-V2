<?php
require_once('config.php');
require_once('otp.php');
session_start();
$email = $_POST['email'];
$password = $_POST['password'];
$auton = false;
$result_email_pass = $conn->query("SELECT * FROM userinfo WHERE Email = '$email'");
if ($result_email_pass->num_rows > 0) {
    $row = $result_email_pass->fetch_assoc();
    if (password_verify($password, $row["Passw"])) {
        $id = $row['id'];
        $_SESSION["id"] = $id;
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
header('location: authentication.php');
?>