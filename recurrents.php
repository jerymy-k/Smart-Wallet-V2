<?php
require_once("config.php");
session_start();

if (!isset($_SESSION['id'])) {
  header("location: login.php");
  exit;
}
$user_id = (int) $_SESSION['id'];

// Auth check
$reslt = $conn->query("SELECT stat FROM userinfo WHERE id = $user_id");
$auth = $reslt ? $reslt->fetch_assoc() : null;
if (!$auth || !$auth['stat']) {
  header("location: authentication.php");
  exit;
}
$there_is_a_card = $conn->query("SELECT count(id) as cardsCount FROM cards WHERE user_id = $user_id")->fetch_assoc();
if ($there_is_a_card['cardsCount'] == 0) {
  header("location: add_first_card.php");
  exit;
}
// User info
$reslt = $conn->query("SELECT FullName, email FROM userinfo WHERE id = $user_id");
$user_info = $reslt ? $reslt->fetch_assoc() : ['FullName' => 'User', 'email' => ''];

// Fetch recurrents
$stmt = $conn->prepare("
  SELECT r.id, r.type, r.title, r.amount, r.category_id, r.is_active, r.last_run, r.created_at,
         c.cate AS category_name
  FROM monthly_recurrents r
  LEFT JOIN categorie c ON c.id = r.category_id
  WHERE r.user_id = ?
  ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recurrents = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html class="light" lang="fr">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>SmartWallet - Recurrents</title>

  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap"
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
        }
      }
    }
  </script>
</head>

<body
  class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 antialiased min-h-screen">

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

          <a class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary transition-colors"
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

    <!-- Main -->
    <div class="flex-1 flex flex-col">
      <header
        class="bg-card-light/80 dark:bg-card-dark/80 backdrop-blur-sm border-b border-border-light dark:border-border-dark p-4 flex items-center justify-between">
        <button id="open-sidebar" class="lg:hidden"><span class="material-symbols-outlined">menu</span></button>

        <div>
          <h2 class="text-gray-900 dark:text-white text-xl font-bold">Recurrents</h2>
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

      <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">

        <?php if (isset($_SESSION['rec_msg'])): ?>
          <div
            class="mb-6 p-4 bg-blue-100 dark:bg-blue-900/30 border border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-300 rounded-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span>
            <p><?php echo htmlspecialchars($_SESSION['rec_msg']);
            unset($_SESSION['rec_msg']); ?></p>
          </div>
        <?php endif; ?>

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">Transactions récurrentes</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Liste de toutes les récurrences enregistrées.</p>
          </div>
          <a href="recurrent_add.php"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white font-semibold shadow-lg hover:shadow-xl hover:bg-green-600 transition">
            <span class="material-symbols-outlined">add</span>
            Ajouter
          </a>
        </div>

        <div
          class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-subtle overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                <tr>
                  <th class="px-6 py-4">Type</th>
                  <th class="px-6 py-4">Titre</th>
                  <th class="px-6 py-4">Catégorie</th>
                  <th class="px-6 py-4">Montant</th>
                  <th class="px-6 py-4">Statut</th>
                  <th class="px-6 py-4">Dernier run</th>
                  <th class="px-6 py-4">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-border-light dark:divide-border-dark">
                <?php if ($recurrents && $recurrents->num_rows > 0): ?>
                  <?php while ($r = $recurrents->fetch_assoc()): ?>
                    <?php
                    $active = (int) $r['is_active'] === 1;
                    $type = $r['type'] === 'income' ? 'Revenu' : 'Dépense';
                    $cat = $r['category_name'] ? $r['category_name'] : '-';
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                      <td class="px-6 py-4 font-semibold">
                        <span
                          class="<?php echo $r['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                          <?php echo htmlspecialchars($type); ?>
                        </span>
                      </td>
                      <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($r['title']); ?>
                      </td>
                      <td class="px-6 py-4"><?php echo htmlspecialchars($cat); ?></td>
                      <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                        <?php echo number_format((float) $r['amount'], 2); ?> MAD
                      </td>
                      <td class="px-6 py-4">
                        <span
                          class="px-3 py-1 rounded-full text-xs font-semibold
                          <?php echo $active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'; ?>">
                          <?php echo $active ? '✓ Actif' : '✗ Arrêté'; ?>
                        </span>
                      </td>
                      <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                        <?php echo $r['last_run'] ? htmlspecialchars($r['last_run']) : '-'; ?>
                      </td>

                      <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-2">
                          <form action="recurrent_toggle.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                            <button
                              class="px-3 py-2 rounded-lg border border-border-light dark:border-border-dark hover:bg-gray-100 dark:hover:bg-white/10 transition text-sm font-semibold">
                              <?php echo $active ? 'Stop' : 'Start'; ?>
                            </button>
                          </form>

                          <a href="recurrent_edit.php?id=<?php echo (int) $r['id']; ?>"
                            class="px-3 py-2 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 hover:bg-orange-200 dark:hover:bg-orange-900/40 transition text-sm font-semibold">
                            Modifier
                          </a>

                          <form action="recurrent_delete.php" method="POST"
                            onsubmit="return confirm('Supprimer cette récurrence ?');">
                            <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                            <button
                              class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition text-sm font-semibold">
                              Supprimer
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                      <div class="flex flex-col items-center gap-3">
                        <span class="material-symbols-outlined text-4xl text-gray-400">repeat</span>
                        <p class="text-gray-500 dark:text-gray-400">Aucune transaction récurrente enregistrée</p>
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