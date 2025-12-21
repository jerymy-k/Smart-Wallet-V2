<?php
require_once("config.php");
session_start();

$sql = "SELECT * FROM userinfo;";
$result_auth = $conn->query($sql);
$email = [];
$pass = [];
while ($row = $result_auth->fetch_assoc()) {
    $email[] = $row["Email"];
    $pass[] = $row["Passw"];
}

// Signup error (already used by your UI)
$ereur = $_SESSION["ereur"] ?? "";
unset($_SESSION["ereur"]);

// Login error (new)
$login_error = $_SESSION["login_error"] ?? "";
unset($_SESSION["login_error"]);
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartWallet - Welcome</title>
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
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
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
    class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 antialiased min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <!-- Logo + Title -->
        <div class="flex flex-col items-center mb-10">
            <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-20 mb-4"
                style="background-image:url('img/SmartWallet.png')"></div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">SmartWallet</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Personal Finance Manager</p>
        </div>

        <!-- Tabs -->
        <div
            class="bg-card-light dark:bg-card-dark rounded-2xl shadow-subtle border border-border-light dark:border-border-dark overflow-hidden">
            <div class="flex">
                <button type="button"
                    class="<?= (empty($ereur) ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'); ?> tab-btn flex-1 py-4 text-center font-semibold text-lg transition-colors"
                    data-target="login">
                    Login
                </button>
                <button type="button"
                    class="<?= (!empty($ereur) ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'); ?> tab-btn flex-1 py-4 text-center font-semibold text-lg transition-colors"
                    data-target="signup">
                    Sign Up
                </button>
            </div>

            <div class="p-8">

                <!-- Login Form -->
                <form id="login"
                    class="space-y-6 tab-content <?= (!empty($ereur) ? 'hidden' : ''); ?>"
                    action="login_traitement.php" method="POST">

                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" required placeholder="you@example.com"
                            class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg transition transform hover:scale-[1.02]">
                        Login
                    </button>

                    <?php if (!empty($login_error)): ?>
                        <div class="p-3 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 flex items-center gap-2">
                            <span class="material-symbols-outlined">error</span>
                            <p class="text-sm font-semibold"><?php echo htmlspecialchars($login_error); ?></p>
                        </div>
                    <?php endif; ?>
                </form>

                <!-- Sign Up Form -->
                <form id="signup"
                    class="space-y-6 tab-content <?= (empty($ereur) ? 'hidden' : ''); ?>"
                    action="authen_trait.php" method="POST">

                    <div>
                        <label class="block text-sm font-medium mb-2">Full Name</label>
                        <input type="text" name="name" required placeholder="John Doe"
                            value="<?php
                            if (isset($_SESSION['fullname'])) {
                                echo htmlspecialchars($_SESSION['fullname']);
                                unset($_SESSION['fullname']);
                            }
                            ?>"
                            class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" required placeholder="you@example.com"
                            value="<?php
                            if (isset($_SESSION['email'])) {
                                echo htmlspecialchars($_SESSION['email']);
                                unset($_SESSION['email']);
                            }
                            ?>"
                            class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Confirm Password</label>
                        <input type="password" name="confirme_password" required placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg transition transform hover:scale-[1.02]">
                        Create Account
                    </button>

                    <?php if (!empty($ereur)): ?>
                        <div class="p-3 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 flex items-center gap-2">
                            <span class="material-symbols-outlined">error</span>
                            <p class="text-sm font-semibold"><?php echo htmlspecialchars($ereur); ?></p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Dark mode toggle -->
        <div class="mt-8 text-center">
            <button onclick="document.documentElement.classList.toggle('dark')"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary transition">
                <span class="material-symbols-outlined align-middle">dark_mode</span> Toggle Dark Mode
            </button>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('text-gray-900', 'dark:text-white');
                    b.classList.add('text-gray-500', 'dark:text-gray-400');
                });

                btn.classList.remove('text-gray-500', 'dark:text-gray-400');
                btn.classList.add('text-gray-900', 'dark:text-white');

                document.querySelectorAll('.tab-content').forEach(form => form.classList.add('hidden'));
                document.getElementById(btn.dataset.target).classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
