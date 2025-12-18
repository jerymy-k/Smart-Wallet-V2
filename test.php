<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$mailEmail = $_ENV['MAIL_EMAIL'];
$mailPassword = $_ENV['MAIL_PASSWORD'];
echo $mailEmail .''. $mailPassword .'';
?>