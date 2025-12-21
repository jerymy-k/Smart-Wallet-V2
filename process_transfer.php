<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    $_SESSION['transfer_error'] = "Access denied. Please log in.";
    header("Location: authentication.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['id'];
    $recipient = trim($_POST['recipient']);
    $amount = (float) $_POST['amount'];
    $note = trim($_POST['note'] ?? '');

    // Validate amount
    if ($amount <= 0) {
        $_SESSION['transfer_error'] = "Invalid amount. Amount must be greater than 0.";
        header("Location: transferts.php");
        exit;
    }

    // Get sender's principal card balance
    $stmt = $conn->prepare("SELECT balance FROM cards WHERE user_id = ? AND principal = 1");
    $stmt->bind_param("i", $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sender = $result->fetch_assoc();
    $stmt->close();

    if (!$sender) {
        $_SESSION['transfer_error'] = "Sender card not found. Please add a principal card.";
        header("Location: transferts.php");
        exit;
    }

    // Check sufficient balance
    if ($sender['balance'] < $amount) {
        $_SESSION['transfer_error'] = "Insufficient balance. Your available balance is " . number_format($sender['balance'], 2) . " MAD.";
        header("Location: transferts.php");
        exit;
    }

    // Find recipient by email or ID
    if (is_numeric($recipient)) {
        $stmt = $conn->prepare("SELECT id, FullName FROM userinfo WHERE id = ?");
        $stmt->bind_param("i", $recipient);
    } else {
        $stmt = $conn->prepare("SELECT id, FullName FROM userinfo WHERE email = ?");
        $stmt->bind_param("s", $recipient);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $receiver = $result->fetch_assoc();
    $stmt->close();

    if (!$receiver) {
        $_SESSION['transfer_error'] = "Recipient not found. Please check the email or ID and try again.";
        header("Location: transferts.php");
        exit;
    }

    // Check if trying to send to self
    if ($receiver['id'] == $sender_id) {
        $_SESSION['transfer_error'] = "You cannot send money to yourself.";
        header("Location: transferts.php");
        exit;
    }

    // Check if recipient has a principal card
    $stmt = $conn->prepare("SELECT id FROM cards WHERE user_id = ? AND principal = 1");
    $stmt->bind_param("i", $receiver['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['transfer_error'] = "Recipient does not have a principal card to receive money.";
        header("Location: transferts.php");
        exit;
    }
    $stmt->close();

    // Start transaction
    try {
        $conn->begin_transaction();

        // Deduct from sender
        $stmt = $conn->prepare("UPDATE cards SET balance = balance - ? WHERE user_id = ? AND principal = 1");
        $stmt->bind_param("di", $amount, $sender_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to deduct amount from sender");
        }
        $stmt->close();

        // Add to receiver
        $stmt = $conn->prepare("UPDATE cards SET balance = balance + ? WHERE user_id = ? AND principal = 1");
        $stmt->bind_param("di", $amount, $receiver['id']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to add amount to receiver");
        }
        $stmt->close();

        // Record transaction
        $status = 'completed';
        $stmt = $conn->prepare("INSERT INTO transfertss (sender_id, recipient_id, amount, status, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iids", $sender_id, $receiver['id'], $amount, $status);
        if (!$stmt->execute()) {
            throw new Exception("Failed to record transaction");
        }
        $stmt->close();

        $conn->commit();
        
        $_SESSION['transfer_success'] = "Transfer successful! " . number_format($amount, 2) . " MAD sent to " . htmlspecialchars($receiver['FullName']) . ".";
        header("Location: transferts.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['transfer_error'] = "Transfer failed: " . $e->getMessage();
        header("Location: transferts.php");
        exit;
    }
} else {
    $_SESSION['transfer_error'] = "Invalid request method.";
    header("Location: transferts.php");
    exit;
}
?>