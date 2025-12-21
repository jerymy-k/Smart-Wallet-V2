<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config.php";
session_set_cookie_params(0);
session_start();

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $reslt = $conn->query("SELECT stat FROM userinfo WHERE id = $id");
    $auth = $reslt->fetch_assoc();
}

if (!isset($auth['stat']) || !$auth['stat']) {
    header("location: authentication.php");
    exit;
}

$user_id = $_SESSION['id'];

// Get user info
$user_query = $conn->prepare("SELECT FullName, email FROM userinfo WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_info = $user_query->get_result()->fetch_assoc();

// Get principal card balance
$card_query = $conn->prepare("SELECT balance, bank_name FROM cards WHERE user_id = ? AND principal = 1 LIMIT 1");
$card_query->bind_param("i", $user_id);
$card_query->execute();
$card_info = $card_query->get_result()->fetch_assoc();

$has_principal_card = !empty($card_info); // ✅ NEW

$current_balance = $card_info['balance'] ?? 0;
$card_name = $card_info['bank_name'] ?? 'Principal Card';

// Get transfer statistics for current month
$current_month = date('Y-m');
$sent_query = $conn->prepare("SELECT SUM(amount) as total FROM transfertss WHERE sender_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$sent_query->bind_param("is", $user_id, $current_month);
$sent_query->execute();
$sent_result = $sent_query->get_result()->fetch_assoc();
$total_sent = $sent_result['total'] ?? 0;

$received_query = $conn->prepare("SELECT SUM(amount) as total FROM transfertss WHERE recipient_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$received_query->bind_param("is", $user_id, $current_month);
$received_query->execute();
$received_result = $received_query->get_result()->fetch_assoc();
$total_received = $received_result['total'] ?? 0;

// Get recent transfers (last 10)
$transfers_query = $conn->prepare("
    SELECT 
        t.*,
        sender.FullName as sender_name,
        sender.email as sender_email,
        recipient.FullName as recipient_name,
        recipient.email as recipient_email
    FROM transfertss t
    LEFT JOIN userinfo sender ON t.sender_id = sender.id
    LEFT JOIN userinfo recipient ON t.recipient_id = recipient.id
    WHERE t.sender_id = ? OR t.recipient_id = ?
    ORDER BY t.created_at DESC
    LIMIT 10
");
$transfers_query->bind_param("ii", $user_id, $user_id);
$transfers_query->execute();
$transfers_result = $transfers_query->get_result();

// Get monthly transfer data for chart (last 12 months)
$monthly_sent_query = $conn->prepare("
    SELECT SUM(amount) as total, DATE_FORMAT(created_at, '%Y-%m') as month 
    FROM transfertss 
    WHERE sender_id = ? 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY month DESC 
    LIMIT 12
");
$monthly_sent_query->bind_param("i", $user_id);
$monthly_sent_query->execute();
$monthly_sent = $monthly_sent_query->get_result();

$monthly_received_query = $conn->prepare("
    SELECT SUM(amount) as total, DATE_FORMAT(created_at, '%Y-%m') as month 
    FROM transfertss 
    WHERE recipient_id = ? 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY month DESC 
    LIMIT 12
");
$monthly_received_query->bind_param("i", $user_id);
$monthly_received_query->execute();
$monthly_received = $monthly_received_query->get_result();

$sent_data = [];
$received_data = [];

while ($row = $monthly_sent->fetch_assoc()) {
    $sent_data[] = $row;
}
while ($row = $monthly_received->fetch_assoc()) {
    $received_data[] = $row;
}

// Handle success/error messages
$message = '';
$message_type = '';
if (isset($_SESSION['transfer_success'])) {
    $message = $_SESSION['transfer_success'];
    $message_type = 'success';
    unset($_SESSION['transfer_success']);
}
if (isset($_SESSION['transfer_error'])) {
    $message = $_SESSION['transfer_error'];
    $message_type = 'error';
    unset($_SESSION['transfer_error']);
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartWallet - Transfers</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#13ec5b",
                        "background-light": "#f6f8f6",
                        "background-dark": "#102216",
                        "card-light": "#ffffff",
                        "card-dark": "#182c1f",
                        "border-light": "#e5e7eb",
                        "border-dark": "#374151"
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    boxShadow: {
                        'subtle': '0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05)',
                        'subtle-hover': '0 10px 15px -3px rgb(0 0 0 / 0.07), 0 4px 6px -4px rgb(0 0 0 / 0.07)',
                    }
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 antialiased min-h-screen">

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
            <div class="flex flex-col flex-grow p-4">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-14" style="background-image:url('img/SmartWallet.png')"></div>
                        <div>
                            <h1 class="text-gray-900 dark:text-white text-base font-bold">SmartWallet</h1>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Personal Finance</p>
                        </div>
                    </div>
                    <button id="close-sidebar" class="lg:hidden text-gray-600 dark:text-gray-400">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <nav class="flex flex-col gap-2 flex-grow">
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="index.php">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-medium">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="incomes.php">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                        <p class="text-sm font-medium">Incomes</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="expenses.php">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p class="text-sm font-medium">Expenses</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="categories.php">
                        <span class="material-symbols-outlined">category</span>
                        <p class="text-sm font-medium">Categories</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="cards.php">
                        <span class="material-symbols-outlined">credit_card</span>
                        <p class="text-sm font-medium">Cards</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary transition-colors" href="transferts.php">
                        <span class="material-symbols-outlined">send_money</span>
                        <p class="text-sm font-semibold">Transfers</p>
                    </a>
                </nav>

                <div class="flex flex-col gap-2 pt-4 border-t border-border-light dark:border-border-dark">
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="#">
                        <span class="material-symbols-outlined">settings</span>
                        <p class="text-sm font-medium">Settings</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="logout.php">
                        <span class="material-symbols-outlined">logout</span>
                        <p class="text-sm font-medium">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-card-light/80 dark:bg-card-dark/80 backdrop-blur-sm border-b border-border-light dark:border-border-dark p-4 flex items-center justify-between">
                <button id="open-sidebar" class="lg:hidden"><span class="material-symbols-outlined">menu</span></button>

                <div>
                    <h2 class="text-gray-900 dark:text-white text-xl font-bold">Dashboard</h2>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($user_info['FullName']); ?>
                        </p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user_info['email']); ?></p>
                    </div>
                    <div class="bg-primary/20 text-primary rounded-full size-10 flex items-center justify-center font-bold border border-primary/30">
                        <?php echo strtoupper(substr($user_info['FullName'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">

                <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <?php if (!$has_principal_card): ?>
                    <!-- ✅ NO PRINCIPAL CARD MESSAGE -->
                    <div class="mb-8 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">info</span>
                            <div>
                                <p class="font-semibold text-yellow-800 dark:text-yellow-400 text-sm mb-1">No principal card found</p>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    You must add a card and set it as principal before you can send money.
                                </p>
                                <a href="cards.php" class="inline-flex mt-4 items-center gap-2 px-4 py-2 rounded-lg bg-primary text-white font-semibold">
                                    <span class="material-symbols-outlined">credit_card</span>
                                    Go to Cards
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($has_principal_card): ?>
                <!-- ✅ SHOW ONLY IF PRINCIPAL CARD EXISTS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Transfer Form -->
                    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-subtle p-6 border border-border-light dark:border-border-dark">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="bg-primary/20 rounded-full p-3">
                                <span class="material-symbols-outlined text-primary text-2xl">send_money</span>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Send Money</h2>
                        </div>

                        <form method="POST" action="process_transfer.php" class="space-y-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">person</span>
                                        Recipient (Email or ID)
                                    </span>
                                </label>
                                <input type="text" name="recipient" placeholder="example@email.com or user ID" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">payments</span>
                                        Amount
                                    </span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">MAD</span>
                                    <input type="number" name="amount" step="0.01" min="0.01" max="<?php echo $current_balance; ?>" placeholder="0.00" required class="w-full pl-16 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">note</span>
                                        Note (Optional)
                                    </span>
                                </label>
                                <textarea name="note" rows="3" placeholder="Add a note..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 resize-none"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-primary hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition-colors shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">send</span>
                                Send Money
                            </button>
                        </form>
                    </div>

                    <!-- Stats -->
                    <div class="space-y-6">
                        <!-- Principal Card -->
                        <div class="bg-gradient-to-br from-blue-500 to-blue-700 dark:from-blue-600 dark:to-blue-900 rounded-xl p-6 shadow-lg text-white">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-2xl">credit_card</span>
                                    <h3 class="text-lg font-bold">Principal Card</h3>
                                </div>
                                <span class="material-symbols-outlined text-yellow-400">star</span>
                            </div>
                            <p class="text-blue-100 text-sm mb-1">Card Name</p>
                            <p class="text-xl font-bold mb-4"><?php echo htmlspecialchars($card_name); ?></p>

                            <p class="text-blue-100 text-sm mb-1">Available Balance</p>
                            <p class="text-3xl font-bold"><?php echo number_format($current_balance, 2); ?> <span class="text-lg">MAD</span></p>
                        </div>

                        <!-- Transfer Stats -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-card-light dark:bg-card-dark rounded-xl p-5 border border-border-light dark:border-border-dark">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-red-500">arrow_upward</span>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Sent</p>
                                </div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($total_sent, 2); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">MAD this month</p>
                            </div>

                            <div class="bg-card-light dark:bg-card-dark rounded-xl p-5 border border-border-light dark:border-border-dark">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-green-500">arrow_downward</span>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Received</p>
                                </div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($total_received, 2); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">MAD this month</p>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">info</span>
                                <div>
                                    <p class="font-semibold text-yellow-800 dark:text-yellow-400 text-sm mb-1">Important</p>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">Transfers are instant. Make sure you enter the correct recipient details.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart + Transfer History -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <!-- Chart -->
                    <div class="xl:col-span-2 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle p-6">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Transfer Overview</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Monthly sent vs received trend</p>
                        </div>
                        <canvas id="transferChart" class="max-h-[350px]"></canvas>
                    </div>

                    <!-- Recent Transfers -->
                    <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Transfers</h3>
                        <div class="space-y-4 max-h-[400px] overflow-y-auto">
                            <?php
                            if ($transfers_result->num_rows > 0):
                                while ($transfer = $transfers_result->fetch_assoc()):
                                    $is_sent = ($transfer['sender_id'] == $user_id);
                                    $other_party = $is_sent ? $transfer['recipient_name'] : $transfer['sender_name'];
                                    $other_email = $is_sent ? $transfer['recipient_email'] : $transfer['sender_email'];
                            ?>
                                <div class="flex items-center justify-between p-3 <?php echo $is_sent ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'; ?> rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="<?php echo $is_sent ? 'bg-red-500/20' : 'bg-green-500/20'; ?> rounded-full p-2">
                                            <span class="material-symbols-outlined <?php echo $is_sent ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'; ?> text-sm"><?php echo $is_sent ? 'arrow_upward' : 'arrow_downward'; ?></span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                <?php echo $is_sent ? 'Sent to' : 'Received from'; ?> <?php echo htmlspecialchars($other_party); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <?php echo htmlspecialchars($other_email); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                <?php echo date('M d, Y \a\t g:i A', strtotime($transfer['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold <?php echo $is_sent ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'; ?>">
                                        <?php echo $is_sent ? '-' : '+'; ?><?php echo number_format($transfer['amount'], 2); ?>
                                    </p>
                                </div>
                            <?php
                                endwhile;
                            else:
                            ?>
                                <div class="text-center py-8">
                                    <span class="material-symbols-outlined text-6xl text-gray-400 mb-4">receipt_long</span>
                                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No transfers yet</h3>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Your transfer history will appear here</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <!-- ✅ END principal card check -->

            </main>
        </div>
    </div>

    <script>
        // ✅ Only initialize chart if principal card exists (and canvas exists)
        <?php if ($has_principal_card): ?>
        const sentData = <?php echo json_encode(array_reverse($sent_data)); ?>;
        const receivedData = <?php echo json_encode(array_reverse($received_data)); ?>;

        const allMonths = [...new Set([
            ...sentData.map(d => d.month),
            ...receivedData.map(d => d.month)
        ])].sort();

        const sentValues = allMonths.map(month => {
            const item = sentData.find(d => d.month === month);
            return item ? parseFloat(item.total) : 0;
        });

        const receivedValues = allMonths.map(month => {
            const item = receivedData.find(d => d.month === month);
            return item ? parseFloat(item.total) : 0;
        });

        const labels = allMonths.map(month => {
            const [year, monthNum] = month.split('-');
            return new Date(year, monthNum - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });

        const canvas = document.getElementById('transferChart');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Sent',
                            data: sentValues,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Received',
                            data: receivedValues,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(0) + ' MAD';
                                }
                            }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');

        openBtn?.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        });

        closeBtn?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>
</body>
</html>
