<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config.php";
session_set_cookie_params(0);
session_start();

// Authentication Check
if (!isset($_SESSION['id'])) {
    header("location: authentication.php");
    exit;
}

$user_id = $_SESSION['id'];

// Verify user status
$reslt = $conn->query("SELECT stat, FullName, email FROM userinfo WHERE id = $user_id");
$user_info = $reslt->fetch_assoc();

if (!$user_info['stat']) {
    header("location: authentication.php");
    exit;
}

// Handle error alerts
if (isset($_SESSION['ereur_tran'])) {
    $ereur = $_SESSION['ereur_tran'];
    echo "<script>alert('$ereur')</script>";
    unset($_SESSION['ereur_tran']);
}

// Financial Totals
$sum_inco = $conn->query("SELECT SUM(montant) AS total FROM incomes WHERE user_id = $user_id")->fetch_assoc();
$sum_expe = $conn->query("SELECT SUM(montant) AS total FROM expenses WHERE user_id = $user_id")->fetch_assoc();
$balance = $conn->query("SELECT balance FROM cards WHERE user_id = $user_id");
$total_incomes = $sum_inco['total'] ?? 0;
$total_expenses = $sum_expe['total'] ?? 0;
$current_balance = 0;
while ($row = $balance->fetch_assoc()) {
    $current_balance += $row['balance'];
}


// Monthly data for charts
$resultIncomes = $conn->query("SELECT SUM(montant) as totalIncomes, DATE_FORMAT(laDate, '%Y-%m') as ladate FROM incomes WHERE user_id = $user_id GROUP BY DATE_FORMAT(laDate, '%Y-%m') ORDER BY ladate DESC LIMIT 12");
$resultExpenses = $conn->query("SELECT SUM(montant) as totalExpenses, DATE_FORMAT(laDate, '%Y-%m') as ladate FROM expenses WHERE user_id = $user_id GROUP BY DATE_FORMAT(laDate, '%Y-%m') ORDER BY ladate DESC LIMIT 12");

$incomes = [];
$expenses = [];
while ($row = $resultIncomes->fetch_assoc())
    $incomes[] = $row;
while ($row = $resultExpenses->fetch_assoc())
    $expenses[] = $row;

// Recent transactions (Incomes)
$recent_incomes = $conn->prepare("SELECT i.montant, i.laDate, i.cate_name FROM incomes i WHERE i.user_id = ? ORDER BY i.laDate DESC LIMIT 5");
$recent_incomes->bind_param("i", $user_id);
$recent_incomes->execute();
$recent_incomes_result = $recent_incomes->get_result();

// Recent transactions (Expenses)
$recent_expenses = $conn->prepare("SELECT e.montant, e.laDate, cat.cate FROM expenses e LEFT JOIN categorie cat ON e.cate_id = cat.id WHERE e.user_id = ? ORDER BY e.laDate DESC LIMIT 5");
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
                    fontFamily: { "display": ["Manrope", "sans-serif"] },
                    boxShadow: { 'subtle': '0 4px 6px -1px rgb(0 0 0 / 0.05)', 'subtle-hover': '0 10px 15px -3px rgb(0 0 0 / 0.07)' }
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 antialiased min-h-screen">
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    <div class="flex h-screen">
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
                        <p class="text-sm font-medium">Dashboard</p>
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
                        href="transferts.php">
                        <span class="material-symbols-outlined">send_money</span>
                        <p class="text-sm font-semibold">Transfers</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="recurrents.php">
                        <span class="material-symbols-outlined">repeat</span>
                        <p class="text-sm font-medium">Recurrents</p>
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


        <div class="flex-1 flex flex-col overflow-hidden">
            <header
                class="bg-card-light/80 dark:bg-card-dark/80 backdrop-blur-sm border-b border-border-light dark:border-border-dark p-4 flex items-center justify-between">
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
                    <div
                        class="bg-primary/20 text-primary rounded-full size-10 flex items-center justify-center font-bold border border-primary/30">
                        <?php echo strtoupper(substr($user_info['FullName'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div
                        class="p-6 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                        <p class="text-green-700 text-sm font-medium">Total Incomes</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                            <?php echo number_format($total_incomes, 2); ?> MAD
                        </p>
                    </div>
                    <div class="p-6 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <p class="text-red-700 text-sm font-medium">Total Expenses</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                            <?php echo number_format($total_expenses, 2); ?> MAD
                        </p>
                    </div>
                    <div
                        class="p-6 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                        <p class="text-blue-700 text-sm font-medium">Current Balance</p>
                        <p
                            class="text-2xl font-bold <?php echo ($current_balance >= 0) ? 'text-blue-900' : 'text-red-600'; ?>">
                            <?php echo number_format($current_balance, 2); ?> MAD
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <div
                        class="xl:col-span-2 bg-card-light dark:bg-card-dark p-6 rounded-xl border border-border-light dark:border-border-dark">
                        <h3 class="font-bold mb-4">Financial Overview</h3>
                        <canvas id="myChart" class="max-h-[350px]"></canvas>
                    </div>

                    <div
                        class="bg-card-light dark:bg-card-dark p-6 rounded-xl border border-border-light dark:border-border-dark">
                        <h3 class="font-bold mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <?php while ($inc = $recent_incomes_result->fetch_assoc()): ?>
                                <div
                                    class="flex justify-between items-center p-3 bg-green-50 dark:bg-green-900/10 rounded-lg">
                                    <div>
                                        <p class="text-sm font-bold"><?php echo htmlspecialchars($inc['cate_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo $inc['laDate']; ?></p>
                                    </div>
                                    <p class="text-green-600 font-bold">+<?php echo number_format($inc['montant'], 2); ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                            <?php while ($exp = $recent_expenses_result->fetch_assoc()): ?>
                                <div class="flex justify-between items-center p-3 bg-red-50 dark:bg-red-900/10 rounded-lg">
                                    <div>
                                        <p class="text-sm font-bold"><?php echo htmlspecialchars($exp['cate']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo $exp['laDate']; ?></p>
                                    </div>
                                    <p class="text-red-600 font-bold">-<?php echo number_format($exp['montant'], 2); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const incomes = <?php echo json_encode(array_reverse($incomes)); ?>;
        const expenses = <?php echo json_encode(array_reverse($expenses)); ?>;
        const allDates = [...new Set([...incomes.map(i => i.ladate), ...expenses.map(e => e.ladate)])].sort();

        const incomesValue = allDates.map(d => parseFloat((incomes.find(i => i.ladate === d) || { totalIncomes: 0 }).totalIncomes));
        const expensesValue = allDates.map(d => parseFloat((expenses.find(e => e.ladate === d) || { totalExpenses: 0 }).totalExpenses));
        const balanceValue = incomesValue.map((inc, i) => (inc - expensesValue[i]).toFixed(2));
        const labels = allDates.map(d => {
            const [y, m] = d.split('-');
            return new Date(y, m - 1).toLocaleDateString('en-US', { month: 'short' });
        });

        new Chart(document.getElementById('myChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Incomes', data: incomesValue, borderColor: '#10b981', tension: 0.4, fill: false },
                    { label: 'Expenses', data: expensesValue, borderColor: '#ef4444', tension: 0.4, fill: false },
                    { label: 'Balance', data: balanceValue, borderColor: '#3b82f6', tension: 0.4, fill: true, backgroundColor: 'rgba(59, 130, 246, 0.05)' }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Mobile Menu
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('mobile-overlay');
        document.getElementById('open-sidebar').onclick = () => { sb.classList.remove('-translate-x-full'); ov.classList.remove('hidden'); };
        document.getElementById('close-sidebar').onclick = ov.onclick = () => { sb.classList.add('-translate-x-full'); ov.classList.add('hidden'); };
    </script>
</body>

</html>