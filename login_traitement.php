<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "config.php";
require "otp.php";
require 'user_ip.php';
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
        $ip_user = $conn->query( "SELECT ip , user_id FROM user_ip WHERE user_id=$id");
        $ip = getUserIP();
        while($row = $ip_user->fetch_assoc()){
            if($row['ip'] == $ip){
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
header('Location: authentication.php');
?>