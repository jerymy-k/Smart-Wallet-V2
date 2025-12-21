<?php
require_once('config.php');
session_start();

// Redirect if no pending signup
if(!isset($_SESSION['pending_user_id'])) {
    header("location: authentication.php");
    exit();
}

$user_id = $_SESSION['pending_user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $card_name = trim($_POST['card_name']);
    $bank_name = trim($_POST['bank_name']);
    $initial_balance = floatval($_POST['initial_balance']);
    
    if(strlen($card_name) < 2) {
        $_SESSION['ereur'] = 'Card name must be at least 2 characters';
    } else if(empty($bank_name)) {
        $_SESSION['ereur'] = 'Please select a bank';
    } else if($initial_balance < 0) {
        $_SESSION['ereur'] = 'Balance cannot be negative';
    } else {
        // Insert the first card WITHOUT making it principal
        $stmt = $conn->prepare("INSERT INTO cards (user_id, card_name, bank_name, initial_balance, balance) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issdd", $user_id, $card_name, $bank_name, $initial_balance, $initial_balance);
        
        if($stmt->execute()) {
            // Complete signup - login the user
            $_SESSION['id'] = $user_id;
            $_SESSION['name'] = $_SESSION['pending_user_name'];
            
            // Clean up pending session data
            unset($_SESSION['pending_user_id']);
            unset($_SESSION['pending_user_name']);
            
            $_SESSION['success'] = 'Welcome! Your account is ready.';
            header("location: index.php");
            exit();
        } else {
            $_SESSION['ereur'] = 'Error creating card. Please try again.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Your First Card</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#10b981',
                        'card-light': '#ffffff',
                        'card-dark': '#1f2937',
                        'border-light': '#e5e7eb',
                        'border-dark': '#374151'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-md border border-border-light dark:border-border-dark overflow-hidden">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500/10 to-blue-600/10 dark:from-blue-500/20 dark:to-blue-600/20 px-6 py-4 border-b border-border-light dark:border-border-dark">
            <div class="flex items-center gap-3">
                <div class="bg-blue-500/20 dark:bg-blue-500/30 rounded-full p-2">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-xl">credit_card</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add Your First Card</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Complete your signup</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <form method="POST" class="p-6 space-y-5">
            
            <?php if(isset($_SESSION['ereur'])): ?>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined">error</span>
                    <span><?php echo $_SESSION['ereur']; unset($_SESSION['ereur']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Card Name -->
            <div class="space-y-2">
                <label for="card_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">badge</span>
                        Card Name
                    </span>
                </label>
                <input type="text" id="card_name" name="card_name" placeholder="Ex: Personal Card" required
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all">
            </div>

            <!-- Bank Name -->
            <div class="space-y-2">
                <label for="bank_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">account_balance</span>
                        Bank Name
                    </span>
                </label>
                <select name="bank_name" id="bank_name" required
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white transition-all appearance-none cursor-pointer">
                    <option value="">Choose a bank</option>
                    <option value="Attijariwafa Bank">Attijariwafa Bank</option>
                    <option value="Banque Populaire">Banque Populaire</option>
                    <option value="BMCE Bank">BMCE Bank</option>
                    <option value="BMCI">BMCI</option>
                    <option value="CIH Bank">CIH Bank</option>
                    <option value="Crédit Agricole">Crédit Agricole du Maroc</option>
                    <option value="Crédit du Maroc">Crédit du Maroc</option>
                    <option value="Société Générale">Société Générale</option>
                    <option value="Autre">Other</option>
                </select>
            </div>

            <!-- Initial Balance -->
            <div class="space-y-2">
                <label for="initial_balance" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">payments</span>
                        Initial Balance
                    </span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">MAD</span>
                    <input type="number" id="initial_balance" name="initial_balance" placeholder="0.00" step="0.01" value="0" required
                        class="w-full pl-16 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit"
                    class="w-full bg-primary hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-xl">check_circle</span>
                    Complete Signup
                </button>
            </div>
        </form>
    </div>

</body>
</html>