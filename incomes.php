<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("config.php");
session_start();
if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $reslt = $conn->query("SELECT stat FROM userinfo WHERE id = $id");
    $auth = $reslt->fetch_assoc();
}
if (!$auth['stat']) {
    header("location: authentication.php");
}
$id = $_SESSION['id'];
$bank_name = $conn->prepare("SELECT id ,bank_name FROM cards WHERE user_id = ?");
$bank_name->bind_param("i", $id);
$bank_name->execute();
$bank_name = $bank_name->get_result();
$incomes = $conn->prepare("SELECT i.*, c.card_name, c.bank_name FROM incomes i LEFT JOIN cards c ON i.card_id = c.id WHERE i.user_id = ?");
$incomes->bind_param("i", $id);
$incomes->execute();
$incomes = $incomes->get_result();
$card_user = $conn->prepare("SELECT * FROM cards WHERE user_id = ?");
$card_user->bind_param("i", $id);
$card_user->execute();
$card_user = $card_user->get_result();

$cate_inco = $conn->prepare("SELECT id, cate_name FROM cate_inco");
$cate_inco->execute();
$cate_inco = $cate_inco->get_result();
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartWallet - Incomes</title>
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

<body
    class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 antialiased min-h-screen">

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    <div class="flex h-screen">

        <!-- Sidebar - Slides in on mobile -->
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
                        <p class="text-sm font-semibold">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary transition-colors"
                        href="incomes.php">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                        <p class="text-sm font-semibold">Incomes</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="expenses.php">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p class="text-sm font-medium">Expenses</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="#">
                        <span class="material-symbols-outlined">category</span>
                        <p class="text-sm font-medium">Categories</p>
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
                        href="#">
                        <span class="material-symbols-outlined">logout</span>
                        <p class="text-sm font-medium">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header with Hamburger -->
            <header
                class="bg-card-light/80 dark:bg-card-dark/80 backdrop-blur-sm border-b border-border-light dark:border-border-dark p-4 sticky top-0 z-30 flex items-center justify-between">
                <button id="open-sidebar" class="lg:hidden text-gray-700 dark:text-gray-300">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>

                <div>
                    <h2 class="text-gray-900 dark:text-white text-xl md:text-2xl font-bold">Incomes List</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-xs md:text-sm">Manage all your income entries</p>
                </div>

                <div class="flex items-center gap-4">
                    <button class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                    <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
                        style='background-image: url("https://intranet.youcode.ma/storage/users/profile/thumbnail/2050-1760996601.png");'>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 mb-6">
                    <button
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-green-600 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl AddIncomes">
                        <span class="material-symbols-outlined">add_circle</span>
                        Add Income
                    </button>
                    <button
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl AfficheCards">
                        <span class="material-symbols-outlined">credit_card</span>
                        View Cards
                    </button>
                </div>

                <!-- Incomes Table -->
                <div
                    class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle overflow-hidden">
                    <div class="p-6 border-b border-border-light dark:border-border-dark">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">All Incomes</h1>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Amount</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Category</th>
                                    <th class="px-6 py-4">Card Name</th>
                                    <th class="px-6 py-4">Bank Name</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                                <?php if ($incomes && $incomes->num_rows > 0): ?>
                                    <?php while ($row = $incomes->fetch_assoc()): ?>
                                        <tr class='hover:bg-gray-50 dark:hover:bg-white/5 transition-colors'>
                                            <td class='px-6 py-4 text-green-600 dark:text-green-400 font-semibold'>
                                                <?php echo isset($row['montant']) ? number_format($row['montant'], 2) . ' MAD' : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <?php echo !empty($row['laDate']) ? date("d M Y", strtotime($row['laDate'])) : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <?php echo !empty($row['cate_name']) ? htmlspecialchars($row['cate_name']) : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <?php echo !empty($row['card_name']) ? htmlspecialchars($row['card_name']) : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <?php echo !empty($row['bank_name']) ? htmlspecialchars($row['bank_name']) : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4 text-center'>
                                                <div class='flex justify-center gap-3'>
                                                    <a href='form_incomes_edit.php?id=<?php echo $row['id']; ?>'
                                                        class='inline-flex items-center gap-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition'>
                                                        <span class='material-symbols-outlined text-base'>edit</span> Edit
                                                    </a>
                                                    <a href='delete_incomes.php?id=<?php echo $row['id']; ?>'
                                                        onclick='return confirm("Are you sure you want to delete this income?")'
                                                        class='inline-flex items-center gap-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition'>
                                                        <span class='material-symbols-outlined text-base'>delete</span>
                                                        Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan='6' class='px-6 py-12 text-center text-gray-500 dark:text-gray-400'>No
                                            incomes found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Cards Modal -->
    <div id="cardModal"
        class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center backdrop-blur-sm">
        <div
            class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-4xl mx-4 border border-border-light dark:border-border-dark overflow-hidden max-h-[90vh] flex flex-col">

            <!-- Modal Header -->
            <div
                class="bg-gradient-to-r from-blue-500/10 to-blue-600/10 dark:from-blue-500/20 dark:to-blue-600/20 px-6 py-4 border-b border-border-light dark:border-border-dark">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-500/20 dark:bg-blue-500/30 rounded-full p-2">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-xl">credit_card</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Bank Cards</h2>
                    </div>
                    <button type="button" id="closeCardModalX"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left">Card Name</th>
                                <th class="px-6 py-4 text-left">Bank Name</th>
                                <th class="px-6 py-4 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-light dark:divide-border-dark">
                            <?php
                            $card_user->data_seek(0);
                            if ($card_user->num_rows > 0):
                                while ($row = $card_user->fetch_assoc()):
                                    ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            <?php echo !empty($row['card_name']) ? htmlspecialchars($row['card_name']) : 'not defined'; ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <?php echo !empty($row['bank_name']) ? htmlspecialchars($row['bank_name']) : 'not defined'; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-green-600 dark:text-green-400">
                                            <?php echo isset($row['balance']) ? number_format($row['balance'], 2) . ' MAD' : 'not defined'; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        No cards found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-border-light dark:border-border-dark bg-gray-50 dark:bg-gray-800/50">
                <div class="flex gap-3 justify-end">
                    <a href="cards.php"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-green-600 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl">
                        <span class="material-symbols-outlined text-base">add</span>
                        Add Card
                    </a>
                    <button type="button" id="closeCardModal"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-all">
                        <span class="material-symbols-outlined text-base">close</span>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Modal -->
    <div id="incomeModal"
        class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center backdrop-blur-sm">
        <div
            class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-md mx-4 border border-border-light dark:border-border-dark overflow-hidden">

            <!-- Modal Header -->
            <div
                class="bg-gradient-to-r from-primary/10 to-green-500/10 dark:from-primary/20 dark:to-green-500/20 px-6 py-4 border-b border-border-light dark:border-border-dark">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/20 dark:bg-primary/30 rounded-full p-2">
                            <span class="material-symbols-outlined text-primary text-xl">account_balance_wallet</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New Income</h3>
                    </div>
                    <button type="button" id="closeIncomeModalX"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form action="traitement.php" method="POST" class="p-6 space-y-5">

                <!-- Amount Input -->
                <div class="space-y-2">
                    <label for="montant_incomes"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">payments</span>
                            Amount
                        </span>
                    </label>
                    <div class="relative">
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">MAD</span>
                        <input type="number" id="montant_incomes" name="montant_incomes" placeholder="0.00" step="0.01"
                            min="0"
                            class="w-full pl-16 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                            required>
                    </div>
                </div>

                <!-- Bank Select -->
                <div class="space-y-2">
                    <label for="card_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">account_balance</span>
                            Bank Account
                        </span>
                    </label>
                    <select name="card_id" id="card_id"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white transition-all appearance-none cursor-pointer"
                        required>
                        <option value="" class="text-gray-500">Select a bank</option>
                        <?php
                        $bank_name->data_seek(0);
                        while ($row = $bank_name->fetch_assoc()) {
                            $card_id = $row["id"];
                            $card_name = htmlspecialchars($row["bank_name"]);
                            echo "<option value='$card_id'>$card_name</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Category Select -->
                <div class="space-y-2">
                    <label for="cate_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">category</span>
                            Category
                        </span>
                    </label>
                    <select name="cate_name" id="cate_name"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white transition-all appearance-none cursor-pointer"
                        required>
                        <option value="" class="text-gray-500">Select a category</option>
                        <?php
                        $cate_inco->data_seek(0);
                        while ($row = $cate_inco->fetch_assoc()) {
                            $cate_label = htmlspecialchars($row["cate_name"]);
                            echo "<option value='$cate_label'>$cate_label</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                        Save Income
                    </button>
                    <button type="button" id="closeIncomeModal"
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
        // Income Modal
        const btnIncome = document.querySelector('.AddIncomes');
        const modalIncome = document.getElementById('incomeModal');
        const closeBtnIncome = document.getElementById('closeIncomeModal');
        const closeBtnIncomeX = document.getElementById('closeIncomeModalX');

        btnIncome.addEventListener('click', () => {
            modalIncome.classList.remove('hidden');
            modalIncome.classList.add('flex');
        });

        closeBtnIncome.addEventListener('click', () => {
            modalIncome.classList.add('hidden');
            modalIncome.classList.remove('flex');
        });

        closeBtnIncomeX.addEventListener('click', () => {
            modalIncome.classList.add('hidden');
            modalIncome.classList.remove('flex');
        });

        modalIncome.addEventListener('click', (e) => {
            if (e.target === modalIncome) {
                modalIncome.classList.add('hidden');
                modalIncome.classList.remove('flex');
            }
        });

        // Card Modal
        const btnCard = document.querySelector('.AfficheCards');
        const modalCard = document.getElementById('cardModal');
        const closeBtnCard = document.getElementById('closeCardModal');
        const closeBtnCardX = document.getElementById('closeCardModalX');

        btnCard.addEventListener('click', () => {
            modalCard.classList.remove('hidden');
            modalCard.classList.add('flex');
        });

        closeBtnCard.addEventListener('click', () => {
            modalCard.classList.add('hidden');
            modalCard.classList.remove('flex');
        });

        closeBtnCardX.addEventListener('click', () => {
            modalCard.classList.add('hidden');
            modalCard.classList.remove('flex');
        });

        modalCard.addEventListener('click', (e) => {
            if (e.target === modalCard) {
                modalCard.classList.add('hidden');
                modalCard.classList.remove('flex');
            }
        });

        // Escape key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (!modalIncome.classList.contains('hidden')) {
                    modalIncome.classList.add('hidden');
                    modalIncome.classList.remove('flex');
                }
                if (!modalCard.classList.contains('hidden')) {
                    modalCard.classList.add('hidden');
                    modalCard.classList.remove('flex');
                }
            }
        });

        // Sidebar Toggle
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