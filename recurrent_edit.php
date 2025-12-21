<?php
require_once("config.php");
session_start();

if (!isset($_SESSION['id'])) { header("location: login.php"); exit; }
$user_id = (int)$_SESSION['id'];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: recurrents.php"); exit; }

// user info
$reslt = $conn->query("SELECT stat, FullName, email FROM userinfo WHERE id = $user_id");
$user_info = $reslt ? $reslt->fetch_assoc() : ['FullName'=>'User','email'=>''];
if (!$user_info['stat']) { header("location: authentication.php"); exit; }

// load recurrent
$stmt = $conn->prepare("SELECT id, type, title, category_id, amount, is_active FROM monthly_recurrents WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$r) { header("Location: recurrents.php"); exit; }

// categories
$stmt = $conn->prepare("SELECT id, cate FROM categorie WHERE user_id=? ORDER BY cate ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cats = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html class="light" lang="fr">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>SmartWallet - Modifier récurrent</title>

  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet" />
  <style>.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; }</style>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: { extend: {
        colors: {
          "primary": "#13ec5b","background-light":"#f6f8f6","background-dark":"#102216",
          "card-light":"#ffffff","card-dark":"#182c1f","border-light":"#e5e7eb","border-dark":"#374151"
        },
        fontFamily: { "display": ["Manrope", "sans-serif"] },
        boxShadow: { 'subtle': '0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05)' }
      }}
    }
  </script>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 antialiased min-h-screen">
  <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

  <div class="flex h-screen">
    <!-- Sidebar (same links as recurrents.php) -->
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
          <button id="close-sidebar" class="lg:hidden text-gray-600 dark:text-gray-400"><span class="material-symbols-outlined">close</span></button>
        </div>

        <nav class="flex flex-col gap-2 flex-grow">
          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="index.php"><span class="material-symbols-outlined">dashboard</span><p class="text-sm font-medium">Dashboard</p></a>
          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="incomes.php"><span class="material-symbols-outlined">account_balance_wallet</span><p class="text-sm font-medium">Incomes</p></a>
          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="expenses.php"><span class="material-symbols-outlined">receipt_long</span><p class="text-sm font-medium">Expenses</p></a>
          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="categories.php"><span class="material-symbols-outlined">category</span><p class="text-sm font-medium">Categories</p></a>
          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="cards.php"><span class="material-symbols-outlined">credit_card</span><p class="text-sm font-medium">Cards</p></a>
          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="transferts.php"><span class="material-symbols-outlined">send_money</span><p class="text-sm font-semibold">Transfers</p></a>
          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary transition-colors" href="recurrents.php"><span class="material-symbols-outlined">repeat</span><p class="text-sm font-medium">Recurrents</p></a>
        </nav>

        <div class="flex flex-col gap-2 pt-4 border-t border-border-light dark:border-border-dark">
          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors" href="logout.php"><span class="material-symbols-outlined">logout</span><p class="text-sm font-medium">Logout</p></a>
        </div>
      </div>
    </aside>

    <div class="flex-1 flex flex-col">
      <header class="bg-card-light/80 dark:bg-card-dark/80 backdrop-blur-sm border-b border-border-light dark:border-border-dark p-4 flex items-center justify-between">
        <button id="open-sidebar" class="lg:hidden"><span class="material-symbols-outlined">menu</span></button>
        <h2 class="text-gray-900 dark:text-white text-xl font-bold">Modifier récurrent</h2>
        <div class="flex items-center gap-3">
          <div class="hidden md:block text-right">
            <p class="text-sm font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($user_info['FullName']); ?></p>
            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user_info['email']); ?></p>
          </div>
          <div class="bg-primary/20 text-primary rounded-full size-10 flex items-center justify-center font-bold border border-primary/30">
            <?php echo strtoupper(substr($user_info['FullName'], 0, 1)); ?>
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
        <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle overflow-hidden max-w-2xl">
          <div class="p-6 border-b border-border-light dark:border-border-dark">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier la récurrence</h1>
          </div>

          <form action="recurrent_update.php" method="POST" class="p-6 space-y-5">
            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Type</label>
                <select name="type" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg">
                  <option value="income" <?php echo $r['type']==='income'?'selected':''; ?>>Revenu</option>
                  <option value="expense" <?php echo $r['type']==='expense'?'selected':''; ?>>Dépense</option>
                </select>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Montant (MAD)</label>
                <input type="number" step="0.01" min="0.01" name="amount" required
                       value="<?php echo htmlspecialchars($r['amount']); ?>"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg">
              </div>
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Titre</label>
              <input name="title" required value="<?php echo htmlspecialchars($r['title']); ?>"
                     class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg">
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Catégorie (optionnel)</label>
              <select name="category_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-border-light dark:border-border-dark rounded-lg">
                <option value="">-- Aucune --</option>
                <?php while($c = $cats->fetch_assoc()): ?>
                  <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)$r['category_id'] === (int)$c['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['cate']); ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
              <button type="submit" class="flex-1 bg-primary hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition shadow-lg hover:shadow-xl">
                Sauvegarder
              </button>
              <a href="recurrents.php" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold py-3 rounded-lg transition text-center">
                Annuler
              </a>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>

  <script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobile-overlay');
    const openBtn = document.getElementById('open-sidebar');
    const closeBtn = document.getElementById('close-sidebar');

    openBtn.addEventListener('click', () => { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); });
    closeBtn.addEventListener('click', () => { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); });
    overlay.addEventListener('click', () => { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); });
  </script>
</body>
</html>
