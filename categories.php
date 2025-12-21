<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("config.php");
session_start();

if (!isset($_SESSION['id'])) {
    header("location: login.php");
    exit;
}
$user_id = (int)$_SESSION['id'];
$there_is_a_card = $conn->query("SELECT count(id) as cardsCount FROM cards WHERE user_id = $user_id")->fetch_assoc();
if($there_is_a_card['cardsCount'] == 0){
    header("location: add_first_card.php");
    exit;
}

// Auth check
$reslt = $conn->query("SELECT stat FROM userinfo WHERE id = $user_id");
$auth = $reslt ? $reslt->fetch_assoc() : null;

if (!$auth || !$auth['stat']) {
    header("location: authentication.php");
    exit;
}

// User info
$reslt = $conn->query("SELECT stat, FullName, email FROM userinfo WHERE id = $user_id");
$user_info = $reslt ? $reslt->fetch_assoc() : ['FullName' => 'User', 'email' => ''];

// Fetch all category limits (NO IsActive anymore)
$limits_query = $conn->prepare("SELECT id, cate, limite, rest FROM categorie WHERE user_id=?");
$limits_query->bind_param("i", $user_id);
$limits_query->execute();
$limits = $limits_query->get_result();
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartWallet - Categories</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
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
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary transition-colors"
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

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">

                <!-- Info Card -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">info</span>
                        <div>
                            <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-1">How it works</h3>
                            <p class="text-sm text-blue-700 dark:text-blue-400">
                                Click on any limit amount to edit it. Categories do not have enable/disable anymore.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle overflow-hidden">
                    <div class="p-6 border-b border-border-light dark:border-border-dark">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                            Category Spending Limits
                        </h1>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Category</th>
                                    <th class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-base">trending_up</span>
                                            Limit (MAD)
                                        </div>
                                    </th>
                                    <th class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-base">account_balance_wallet</span>
                                            Remaining (MAD)
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                                <?php if ($limits && $limits->num_rows > 0): ?>
                                    <?php while ($row = $limits->fetch_assoc()): ?>
                                        <?php
                                        $id_Cat = (int)($row['id'] ?? 0);
                                        $limite = (float)($row['limite'] ?? 0);
                                        $rest = (float)($row['rest'] ?? 0);
                                        $percentage = $limite > 0 ? (($limite - $rest) / $limite) * 100 : 0;
                                        ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                <?php echo $row['id'] ?? 'not defined'; ?>
                                            </td>

                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-primary">label</span>
                                                    <span class="font-semibold text-gray-900 dark:text-white">
                                                        <?php echo !empty($row['cate']) ? htmlspecialchars($row['cate']) : 'not defined'; ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4">
                                                <button
                                                    class="limit-cell group flex items-center gap-2 text-orange-600 dark:text-orange-400 font-semibold hover:text-orange-700 dark:hover:text-orange-300 transition-colors"
                                                    data-id="<?php echo $row['id'] ?? ''; ?>"
                                                    data-limit="<?php echo $row['limite'] ?? ''; ?>"
                                                    data-category="<?php echo !empty($row['cate']) ? htmlspecialchars($row['cate']) : ''; ?>">
                                                    <span><?php echo $limite !== 0 ? number_format($limite, 2) : 'not defined'; ?></span>
                                                    <span class="material-symbols-outlined text-sm opacity-0 group-hover:opacity-100 transition-opacity">edit</span>
                                                </button>
                                            </td>

                                            <td class="px-6 py-4">
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold <?php echo $rest > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                                                            <?php echo $rest !== 0 ? number_format($rest, 2) : 'not defined'; ?>
                                                        </span>
                                                        <?php if ($limite > 0): ?>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                                (<?php echo number_format(100 - $percentage, 1); ?>%)
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($limite > 0): ?>
                                                        <div class="w-32 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                            <div class="h-full <?php echo $percentage > 80 ? 'bg-red-500' : ($percentage > 50 ? 'bg-orange-500' : 'bg-green-500'); ?> transition-all"
                                                                style="width: <?php echo min($percentage, 100); ?>%"></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-3">
                                                <span class="material-symbols-outlined text-4xl text-gray-400">category_off</span>
                                                <p class="text-gray-500 dark:text-gray-400">No categories defined yet</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Edit Limit Modal -->
    <div id="limitModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center backdrop-blur-sm">
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-md mx-4 border border-border-light dark:border-border-dark overflow-hidden">

            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-orange-500/10 to-orange-600/10 dark:from-orange-500/20 dark:to-orange-600/20 px-6 py-4 border-b border-border-light dark:border-border-dark">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-orange-500/20 dark:bg-orange-500/30 rounded-full p-2">
                            <span class="material-symbols-outlined text-orange-600 dark:text-orange-400 text-xl">trending_up</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Spending Limit</h3>
                    </div>
                    <button type="button" id="closeModalX"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form action="update_limit.php" method="POST" class="p-6 space-y-5">
                <input type="hidden" name="limit_id" id="limit_id">

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">label</span>
                            Category
                        </span>
                    </label>
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-border-light dark:border-border-dark">
                        <p id="category_name" class="text-lg font-semibold text-gray-900 dark:text-white"></p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="new_limit" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">payments</span>
                            New Limit
                        </span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">MAD</span>
                        <input type="number" step="0.01" min="0" name="new_limit" id="new_limit"
                            class="w-full pl-16 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                            placeholder="0.00" required>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                        Save Changes
                    </button>
                    <button type="button" id="closeModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold py-3 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-xl">cancel</span>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Edit Limit Modal
        const modal = document.getElementById('limitModal');
        const closeBtn = document.getElementById('closeModal');
        const closeBtnX = document.getElementById('closeModalX');

        document.querySelectorAll('.limit-cell').forEach(cell => {
            cell.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const limit = this.getAttribute('data-limit');
                const category = this.getAttribute('data-category');

                document.getElementById('limit_id').value = id;
                document.getElementById('new_limit').value = limit;
                document.getElementById('category_name').textContent = category;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        closeBtnX.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn2 = document.getElementById('close-sidebar');

        openBtn.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        });

        closeBtn2.addEventListener('click', () => {
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
