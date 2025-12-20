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
if (!$auth['stat']) {
    header("location: authentication.php");
    exit;
}

$user_id = $_SESSION['id'];

// Get user info
$user_query = $conn->prepare("SELECT FullName , email FROM userinfo WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_info = $user_query->get_result()->fetch_assoc();

// Sum of incomes and expenses for current user
$sql_sum_inco = "SELECT SUM(montant) AS total FROM incomes WHERE user_id = $user_id";
$sql_sum_expe = "SELECT SUM(montant) AS total FROM expenses WHERE user_id = $user_id";

$sum_res_inco = $conn->query($sql_sum_inco);
$sum_inco = $sum_res_inco->fetch_assoc();
if (!$sum_inco['total']) {
    $sum_inco['total'] = 0;
}

$sum_res_expe = $conn->query($sql_sum_expe);
$sum_expe = $sum_res_expe->fetch_assoc();
if (!$sum_expe['total']) {
    $sum_expe['total'] = 0;
}

// Monthly data for charts
$sql = "SELECT SUM(montant) as totalIncomes, DATE_FORMAT(laDate, '%Y-%m') as ladate 
        FROM incomes WHERE user_id = $user_id 
        GROUP BY DATE_FORMAT(laDate, '%Y-%m') 
        ORDER BY ladate DESC LIMIT 12";
$sqle = "SELECT SUM(montant) as totalExpenses, DATE_FORMAT(laDate, '%Y-%m') as ladate 
         FROM expenses WHERE user_id = $user_id 
         GROUP BY DATE_FORMAT(laDate, '%Y-%m') 
         ORDER BY ladate DESC LIMIT 12";

$resultIncomes = $conn->query($sql);
$resultExpenses = $conn->query($sqle);
$incomes = [];
$expenses = [];
while ($row = $resultIncomes->fetch_assoc()) {
    $incomes[] = $row;
}
while ($row = $resultExpenses->fetch_assoc()) {
    $expenses[] = $row;
}

// Recent transactions
$recent_incomes = $conn->prepare("SELECT i.montant, i.laDate, i.cate_name, c.bank_name 
                                   FROM incomes i 
                                   LEFT JOIN cards c ON i.card_id = c.id 
                                   WHERE i.user_id = ? 
                                   ORDER BY i.laDate DESC LIMIT 5");
$recent_incomes->bind_param("i", $user_id);
$recent_incomes->execute();
$recent_incomes_result = $recent_incomes->get_result();

$recent_expenses = $conn->prepare("SELECT e.montant, e.laDate, cat.cate, c.bank_name 
                                    FROM expenses e 
                                    LEFT JOIN categorie cat ON e.cate_id = cat.id 
                                    LEFT JOIN cards c ON e.card_id = c.id 
                                    WHERE e.user_id = ? 
                                    ORDER BY e.laDate DESC LIMIT 5");
$recent_expenses->bind_param("i", $user_id);
$recent_expenses->execute();
$recent_expenses_result = $recent_expenses->get_result();
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartWallet - Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
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

<body
    class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 antialiased min-h-screen">

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside id="sidebar"
            class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
            <div class="flex flex-col flex-grow p-4">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-14"
                            style="background-image:url('img/SmartWallet.png')"></div>
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
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary transition-colors"
                        href="index.php">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-semibold">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="incomes.php">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                        <p class="text-sm font-medium">Incomes</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="expenses.php">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p class="text-sm font-medium">Expenses</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="categories.php">
                        <span class="material-symbols-outlined">category</span>
                        <p class="text-sm font-medium">Categories</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="cards.php">
                        <span class="material-symbols-outlined">credit_card</span>
                        <p class="text-sm font-medium">Cards</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="#">
                        <span class="material-symbols-outlined">ios_share</span>
                        <p class="text-sm font-medium">Export</p>
                    </a>
                </nav>

                <div class="flex flex-col gap-2 pt-4 border-t border-border-light dark:border-border-dark">
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="#">
                        <span class="material-symbols-outlined">settings</span>
                        <p class="text-sm font-medium">Settings</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="logout.php">
                        <span class="material-symbols-outlined">logout</span>
                        <p class="text-sm font-medium">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header
                class="bg-card-light/80 dark:bg-card-dark/80 backdrop-blur-sm border-b border-border-light dark:border-border-dark p-4 sticky top-0 z-30 flex items-center justify-between">
                <button id="open-sidebar" class="lg:hidden text-gray-700 dark:text-gray-300">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>

                <div>
                    <h2 class="text-gray-900 dark:text-white text-xl md:text-2xl font-bold">Dashboard</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-xs md:text-sm">
                        Welcome back, <?php echo htmlspecialchars($user_info['name'] ?? 'User'); ?>! Here's your financial overview.
                    </p>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    <button class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                    <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
                        style='background-image: url("https://intranet.youcode.ma/storage/users/profile/thumbnail/2050-1760996601.png");'>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-8">
                    <!-- Total Incomes -->
                    <div
                        class="flex flex-col justify-between gap-4 rounded-xl p-6 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-800 shadow-subtle hover:shadow-subtle-hover transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-green-700 dark:text-green-400 text-sm font-medium mb-2">Total Incomes</p>
                                <p class="text-green-900 dark:text-green-100 text-3xl font-bold">
                                    <?php echo number_format($sum_inco['total'], 2); ?> <span class="text-lg">MAD</span>
                                </p>
                            </div>
                            <div class="bg-green-500/20 dark:bg-green-500/30 rounded-full p-3">
                                <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-2xl">trending_up</span>
                            </div>
                        </div>
                        <p class="text-green-600 dark:text-green-400 text-xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">arrow_upward</span>
                            All time earnings
                        </p>
                    </div>

                    <!-- Total Expenses -->
                    <div
                        class="flex flex-col justify-between gap-4 rounded-xl p-6 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-800 shadow-subtle hover:shadow-subtle-hover transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-red-700 dark:text-red-400 text-sm font-medium mb-2">Total Expenses</p>
                                <p class="text-red-900 dark:text-red-100 text-3xl font-bold">
                                    <?php echo number_format($sum_expe['total'], 2); ?> <span class="text-lg">MAD</span>
                                </p>
                            </div>
                            <div class="bg-red-500/20 dark:bg-red-500/30 rounded-full p-3">
                                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">trending_down</span>
                            </div>
                        </div>
                        <p class="text-red-600 dark:text-red-400 text-xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">arrow_downward</span>
                            Total spending
                        </p>
                    </div>

                    <!-- Current Balance -->
                    <div
                        class="flex flex-col justify-between gap-4 rounded-xl p-6 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-800 shadow-subtle hover:shadow-subtle-hover transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-blue-700 dark:text-blue-400 text-sm font-medium mb-2">Current Balance</p>
                                <p class="<?php echo ($sum_inco['total'] - $sum_expe['total'] > 0) ? 'text-blue-900 dark:text-blue-100' : 'text-red-900 dark:text-red-100'; ?> text-3xl font-bold">
                                    <?php echo number_format($sum_inco['total'] - $sum_expe['total'], 2); ?> <span class="text-lg">MAD</span>
                                </p>
                            </div>
                            <div class="bg-blue-500/20 dark:bg-blue-500/30 rounded-full p-3">
                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">account_balance_wallet</span>
                            </div>
                        </div>
                        <p class="text-blue-600 dark:text-blue-400 text-xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">update</span>
                            Updated just now
                        </p>
                    </div>

                    <!-- Quick Actions -->
                    <div
                        class="flex flex-col gap-3 rounded-xl p-6 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-subtle">
                        <h3 class="font-semibold text-base text-gray-800 dark:text-gray-200 mb-1">Quick Actions</h3>
                        <a href="incomes.php"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary hover:bg-green-600 text-white font-semibold text-sm rounded-lg transition-all shadow-lg hover:shadow-xl">
                            <span class="material-symbols-outlined">add_circle</span> New Income
                        </a>
                        <a href="expenses.php"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm rounded-lg transition-all shadow-lg hover:shadow-xl">
                            <span class="material-symbols-outlined">remove_circle</span> New Expense
                        </a>
                        <a href="cards.php"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold text-sm rounded-lg transition-all shadow-lg hover:shadow-xl">
                            <span class="material-symbols-outlined">credit_card</span> Manage Cards
                        </a>
                    </div>
                </div>

                <!-- Chart + Recent Transactions -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <!-- Chart -->
                    <div class="xl:col-span-2 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle p-6">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Financial Overview</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Monthly income vs expenses trend</p>
                        </div>
                        <canvas id="myChart" class="max-h-[350px]"></canvas>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Transactions</h3>
                        <div class="space-y-4 max-h-[400px] overflow-y-auto">
                            <?php while ($income = $recent_incomes_result->fetch_assoc()): ?>
                                <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-green-500/20 rounded-full p-2">
                                            <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-sm">arrow_upward</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($income['cate_name'] ?? 'Income'); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <?php echo date('M d, Y', strtotime($income['laDate'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-green-600 dark:text-green-400">
                                        +<?php echo number_format($income['montant'], 2); ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>

                            <?php while ($expense = $recent_expenses_result->fetch_assoc()): ?>
                                <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-red-500/20 rounded-full p-2">
                                            <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-sm">arrow_downward</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($expense['cate'] ?? 'Expense'); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <?php echo date('M d, Y', strtotime($expense['laDate'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-red-600 dark:text-red-400">
                                        -<?php echo number_format($expense['montant'], 2); ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Chart Script -->
    <script>
        const incomes = <?php echo json_encode(array_reverse($incomes)); ?>;
        const expenses = <?php echo json_encode(array_reverse($expenses)); ?>;
        
        const allDates = [...new Set([
            ...incomes.map(i => i.ladate),
            ...expenses.map(e => e.ladate)
        ])].sort();

        const incomesValue = allDates.map(date => {
            const item = incomes.find(i => i.ladate === date);
            return item ? parseFloat(item.totalIncomes) : 0;
        });

        const expensesValue = allDates.map(date => {
            const item = expenses.find(e => e.ladate === date);
            return item ? parseFloat(item.totalExpenses) : 0;
        });

        const balance = allDates.map((date, i) => {
            return (incomesValue[i] - expensesValue[i]).toFixed(2);
        });

        const labels = allDates.map(date => {
            const [year, month] = date.split('-');
            return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });

        const ctx = document.getElementById('myChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { 
                        label: 'Incomes', 
                        data: incomesValue, 
                        borderColor: '#10b981', 
                        backgroundColor: 'rgba(16, 185, 129, 0.1)', 
                        tension: 0.4,
                        fill: true
                    },
                    { 
                        label: 'Expenses', 
                        data: expensesValue, 
                        borderColor: '#ef4444', 
                        backgroundColor: 'rgba(239, 68, 68, 0.1)', 
                        tension: 0.4,
                        fill: true
                    },
                    { 
                        label: 'Balance', 
                        data: balance, 
                        borderColor: '#3b82f6', 
                        backgroundColor: 'rgba(59, 130, 246, 0.1)', 
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
    </script>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');

        openBtn.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        });

        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>