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
    exit;
}

$id = $_SESSION['id'];
$reslt = $conn->query("SELECT stat, FullName, email FROM userinfo WHERE id = $id");
$user_info = $reslt->fetch_assoc();
$card_user = $conn->prepare("SELECT * FROM cards WHERE user_id = ?");
$card_user->bind_param("i", $id);
$card_user->execute();
$card_user = $card_user->get_result();
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartWallet - Cards</title>
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
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                        href="categories.php">
                        <span class="material-symbols-outlined">category</span>
                        <p class="text-sm font-medium">Categories</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary transition-colors"
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

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                <!-- Action Button -->
                <div class="mb-6">
                    <button
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-green-600 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl addCardBtn">
                        <span class="material-symbols-outlined">add_circle</span>
                        Add New Card
                    </button>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php
                    if ($card_user->num_rows > 0):
                        while ($row = $card_user->fetch_assoc()):
                            $balance = $row['balance'] ?? 0;
                            $card_name = !empty($row['card_name']) ? htmlspecialchars($row['card_name']) : 'Unnamed Card';
                            $bank_name = !empty($row['bank_name']) ? htmlspecialchars($row['bank_name']) : 'Unknown Bank';
                            $is_principal = $row['principal'] == 1;
                            ?>
                            <div
                                class="bg-gradient-to-br from-blue-500 to-blue-700 dark:from-blue-600 dark:to-blue-900 rounded-xl p-6 shadow-subtle hover:shadow-subtle-hover transition-all text-white relative">

                                <!-- Principal Badge -->
                                <?php if ($is_principal): ?>
                                    <div
                                        class="absolute top-2 right-4 bg-yellow-400 text-yellow-900 px-3 py-1.5 rounded-full text-xs font-bold flex items-center gap-1 shadow-lg">
                                        <span class="material-symbols-outlined text-base">star</span>
                                        Principal
                                    </div>
                                <?php endif; ?>

                                <div class="flex justify-between items-start mb-8">
                                    <div>
                                        <p class="text-blue-100 text-sm mb-1">Card Name</p>
                                        <h3 class="text-xl font-bold"><?php echo $card_name; ?></h3>
                                    </div>
                                    <span class="material-symbols-outlined text-3xl opacity-80 pt-[20px]">credit_card</span>
                                </div>

                                <div class="mb-6">
                                    <p class="text-blue-100 text-sm mb-1">Bank</p>
                                    <p class="text-lg font-semibold"><?php echo $bank_name; ?></p>
                                </div>

                                <div class="flex justify-between items-end">
                                    <div>
                                        <p class="text-blue-100 text-sm mb-1">Balance</p>
                                        <p class="text-2xl font-bold">
                                            <?php echo number_format($balance, 2); ?> <span class="text-lg">MAD</span>
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <?php if (!$is_principal): ?>
                                            <!-- Set Principal Button -->
                                            <form method="POST" action="set_principal_card.php" style="display:inline;">
                                                <input type="hidden" name="card_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="set_principal"
                                                    onclick="return confirm('Set this card as your principal card?')"
                                                    class="p-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 rounded-lg transition-colors"
                                                    title="Set as principal card">
                                                    <span class="material-symbols-outlined text-sm">star</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <a href="edit_card.php?id=<?php echo $row['id']; ?>"
                                            class="p-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </a>
                                        <a href="delete_card.php?id=<?php echo $row['id']; ?>"
                                            onclick="return confirm('Are you sure you want to delete this card?')"
                                            class="p-2 bg-white/20 hover:bg-red-500 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full">
                            <div
                                class="bg-card-light dark:bg-card-dark border-2 border-dashed border-border-light dark:border-border-dark rounded-xl p-12 text-center">
                                <span class="material-symbols-outlined text-6xl text-gray-400 mb-4">credit_card_off</span>
                                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">No cards yet</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-6">Add your first card to start tracking your
                                    finances</p>
                                <button
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-green-600 text-white font-semibold rounded-lg transition-all addCardBtn">
                                    <span class="material-symbols-outlined">add</span>
                                    Add Your First Card
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Cards Table (Alternative View) -->
                <div
                    class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle overflow-hidden">
                    <div class="p-6 border-b border-border-light dark:border-border-dark">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">All Cards</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Card Name</th>
                                    <th class="px-6 py-4">Bank Name</th>
                                    <th class="px-6 py-4 text-right">Balance</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
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
                                                <div class="flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-blue-600">credit_card</span>
                                                    <?php echo !empty($row['card_name']) ? htmlspecialchars($row['card_name']) : 'not defined'; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                                <?php echo !empty($row['bank_name']) ? htmlspecialchars($row['bank_name']) : 'not defined'; ?>
                                            </td>
                                            <td class="px-6 py-4 text-right font-semibold text-green-600 dark:text-green-400">
                                                <?php echo isset($row['balance']) ? number_format($row['balance'], 2) . ' MAD' : 'not defined'; ?>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex justify-center gap-3">
                                                    <a href="edit_card.php?id=<?php echo $row['id']; ?>"
                                                        class="inline-flex items-center gap-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">
                                                        <span class="material-symbols-outlined text-base">edit</span> Edit
                                                    </a>
                                                    <a href="delete_card.php?id=<?php echo $row['id']; ?>"
                                                        onclick="return confirm('Are you sure you want to delete this card?')"
                                                        class="inline-flex items-center gap-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition">
                                                        <span class="material-symbols-outlined text-base">delete</span> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            No cards found
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

    <!-- Add Card Modal -->
    <div id="cardModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center backdrop-blur-sm">
        <div
            class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl w-full max-w-md mx-4 border border-border-light dark:border-border-dark overflow-hidden">

            <!-- Modal Header -->
            <div
                class="bg-gradient-to-r from-blue-500/10 to-blue-600/10 dark:from-blue-500/20 dark:to-blue-600/20 px-6 py-4 border-b border-border-light dark:border-border-dark">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-500/20 dark:bg-blue-500/30 rounded-full p-2">
                            <span
                                class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-xl">credit_card</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New Card</h3>
                    </div>
                    <button type="button" id="closeCardModalX"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form action="add_card.php" method="POST" class="p-6 space-y-5">

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
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">MAD</span>
                        <input type="number" id="initial_balance" name="initial_balance" placeholder="0.00" step="0.01"
                            value="0"
                            class="w-full pl-16 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                        Add Card
                    </button>
                    <button type="button" id="closeCardModal"
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
        // Add Card Modal
        const addCardBtns = document.querySelectorAll('.addCardBtn');
        const modalCard = document.getElementById('cardModal');
        const closeBtnCard = document.getElementById('closeCardModal');
        const closeBtnCardX = document.getElementById('closeCardModalX');

        addCardBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                modalCard.classList.remove('hidden');
                modalCard.classList.add('flex');
            });
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

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modalCard.classList.contains('hidden')) {
                modalCard.classList.add('hidden');
                modalCard.classList.remove('flex');
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