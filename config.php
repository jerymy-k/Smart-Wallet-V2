<?php
$servername = "localhost";
$username = "root";
$password = "kerymy";
$dbname = "smartwallet";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result_incomes = $conn->query("SELECT * FROM incomes");
$result_expenses = $conn->query("SELECT * FROM expenses");
$auth = null;
$expenseCategories = [
    'Food',
    'Transport',
    'Rent',
    'Electricity',
    'Water',
    'Internet',
    'Phone',
    'Fuel',
    'Health',
    'Education',
    'Shopping'
];
?>

