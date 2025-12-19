<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "config.php";
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

// Banks
$bank_name = $conn->prepare("SELECT id, bank_name FROM cards WHERE user_id = ?");
$bank_name->bind_param("i", $id);
$bank_name->execute();
$bank_name = $bank_name->get_result();

// Categories
$cate_result = $conn->prepare("SELECT id, cate FROM categorie");
$cate_result->execute();
$cate_result = $cate_result->get_result();

// Fetch expense categories for the modal
$expenseCategories = [];
while ($row = $cate_result->fetch_assoc()) {
    $expenseCategories[] = $row['cate'];
}

// Fetch Expenses
$expenses_query = $conn->prepare("SELECT e.id, e.montant, e.laDate, 
                                         c.cate as category_name, 
                                         ca.card_name, ca.bank_name 
                                   FROM expenses e 
                                   LEFT JOIN categorie c ON e.cate_id = c.id 
                                   LEFT JOIN cards ca ON e.card_id = ca.id 
                                   WHERE e.user_id = ?
                                   ORDER BY e.laDate DESC");
$expenses_query->bind_param("i", $id);
$expenses_query->execute();
$result_expenses = $expenses_query->get_result();
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartWallet - Expenses</title>
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
                    <!-- Close button only on mobile -->
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
                    <a class="allincomes flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="incomes.php">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                        <p class="text-sm font-medium">Incomes</p>
                    </a>
                    <a class="allexpenses flex items-center gap-3 px-4 py-2.5 rounded-lg  hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
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
                    <h2 class="text-gray-900 dark:text-white text-xl md:text-2xl font-bold">Expenses List</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-xs md:text-sm">Manage all your expense entries</p>
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
                <button
                    class="mb-4 px-6 py-2 bg-primary hover:bg-green-600 text-white font-semibold rounded-lg transition-colors AddExpenses">
                    Add Expense
                </button>

                <?php if (isset($_SESSION['message_ereur'])): ?>
                    <div
                        class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg">
                        <?php
                        echo $_SESSION['message_ereur'];
                        unset($_SESSION['message_ereur']);
                        ?>
                    </div>
                <?php endif; ?>

                <div
                    class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle overflow-hidden">
                    <div class="p-6 border-b border-border-light dark:border-border-dark">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">All Expenses</h1>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Amount</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Category</th>
                                    <th class="px-6 py-4">Bank</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                                <?php if ($result_expenses && $result_expenses->num_rows > 0): ?>
                                    <?php while ($row = $result_expenses->fetch_assoc()): ?>
                                        <tr class='hover:bg-gray-50 dark:hover:bg-white/5 transition-colors'>
                                            <td class='px-6 py-4 font-medium'>
                                                <?php echo $row['id'] ?? 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4 text-red-600 dark:text-red-400 font-semibold'>
                                                <?php echo isset($row['montant']) ? '$' . number_format($row['montant'], 2) : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <?php echo !empty($row['laDate']) ? date("d M Y", strtotime($row['laDate'])) : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <?php echo !empty($row['category_name']) ? htmlspecialchars($row['category_name']) : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <?php echo !empty($row['bank_name']) ? htmlspecialchars($row['bank_name']) : 'not defined'; ?>
                                            </td>
                                            <td class='px-6 py-4 text-center'>
                                                <div class='flex justify-center gap-3'>
                                                    <a href='form_expenses_edit.php?id=<?php echo $row['id']; ?>'
                                                        class='inline-flex items-center gap-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition'>
                                                        <span class='material-symbols-outlined text-base'>edit</span> Edit
                                                    </a>
                                                    <a href='delete_expenses.php?id=<?php echo $row['id']; ?>'
                                                        onclick='return confirm("Are you sure you want to delete this expense?")'
                                                        class='inline-flex items-center gap-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition'>
                                                        <span class='material-symbols-outlined text-base'>delete</span> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan='7' class='px-6 py-12 text-center text-gray-500 dark:text-gray-400'>
                                            No expenses found
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
    <div id="ExpenseModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center backdrop-blur-sm">
        <div
            class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-md mx-4 border border-border-light dark:border-border-dark overflow-hidden">

            <!-- Modal Header -->
            <div
                class="bg-gradient-to-r from-primary/10 to-green-500/10 dark:from-primary/20 dark:to-green-500/20 px-6 py-4 border-b border-border-light dark:border-border-dark">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/20 dark:bg-primary/30 rounded-full p-2">
                            <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New Expense</h3>
                    </div>
                    <button type="button" id="closeModalX"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form action="traitement.php" method="POST" class="p-6 space-y-5">

                <!-- Amount Input -->
                <div class="space-y-2">
                    <label for="montant_expenses" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">payments</span>
                            Amount
                        </span>
                    </label>
                    <div class="relative">
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">$</span>
                        <input type="number" id="montant_expenses" name="montant_expenses" placeholder="0.00"
                            step="0.01" min="0"
                            class="w-full pl-8 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all"
                            required>
                    </div>
                </div>

                <!-- Category Select -->
                <div class="space-y-2">
                    <label for="cate_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">category</span>
                            Category
                        </span>
                    </label>
                    <select name="cate_id" id="cate_id"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white transition-all appearance-none cursor-pointer"
                        required>
                        <option value="" class="text-gray-500">Select a category</option>
                        <?php
                        foreach ($expenseCategories as $ec) {
                            echo "<option value='" .  . "'>" . htmlspecialchars($ec) . "</option>";
                        }
                        ?>
                    </select>
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
                        // Reset pointer for bank_name result
                        $bank_name->data_seek(0);
                        while ($rows = $bank_name->fetch_assoc()) {
                            $card_id = $rows["id"];
                            $card_name = htmlspecialchars($rows["bank_name"]);
                            echo "<option value='$card_id'>$card_name</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                        Save Expense
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
    <script>
        const btn = document.querySelector('.AddExpenses');
        const modal = document.getElementById('ExpenseModal');
        const closeBtn = document.getElementById('closeModal');

        btn.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // باش تسد popup إلا ضغطتي برا
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
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

        // Optional: Close sidebar when clicking a link on mobile
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