<?php
require_once('config.php');
session_start();
$id = $_SESSION["id"];
$stmt = $conn->prepare("SELECT code_exp , otp FROM userinfo WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resulta = $stmt->get_result();
$row = $resulta->fetch_assoc();
$code_exp = $row["code_exp"];
$otp = $row["otp"];
if (isset($_GET["otp"])) {
    $get_otp = $_GET["otp"];
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $OTP = $_POST["otp"];
    if ($otp == $OTP && $code_exp > date("Y-m-d H:i:s")) {
        $stat = 1;
        $stmt = $conn->prepare("UPDATE userinfo SET stat = ? , code_exp = NULL , otp = NULL WHERE id = ?");
        $stmt->bind_param("ii", $stat, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
    } else {
        echo "<script>alert('otp wrong')</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartWallet - OTP Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Configuration de base de Tailwind pour la couleur verte */
        tailwind.config= {
            theme: {
                extend: {
                    colors: {
                        'primary-green': '#10b981',
                            /* Un vert proche du bouton */
                            'light-green': '#d1fae5',
                    }
                }
            }
        }

        /* Styling pour le mode sombre (simple toggler) */
        .dark {
            background-color: #1f2937;
            /* Gris foncé pour le fond */
            color: #f3f4f6;
            /* Texte clair */
        }

        .dark .card {
            background-color: #374151;
            /* Gris plus clair pour la carte */
        }

        .dark .input-field {
            background-color: #4b5563;
            color: #f3f4f6;
            border-color: #4b5563;
        }

        .dark .text-gray-600 {
            color: #d1d5db;
        }

        .dark .svg-icon {
            color: #10b981;
            /* Garder l'icône verte pour le contraste */
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 transition-colors duration-300">

    <div class="w-full max-w-sm">

        <div class="flex flex-col items-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800">SmartWallet</h1>
            <p class="text-sm text-gray-600">Personal Finance Manager</p>
        </div>

        <div class="card bg-white p-8 rounded-xl shadow-lg w-full transition-colors duration-300">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Verify Your Account</h2>
            <p class="text-sm text-gray-600 mb-6 text-center">Enter the 6-digit code sent to your email.</p>

            <form method="POST" id="myForm">
                <div class="mb-6">
                    <label for="otp" class="sr-only">One-Time Password (OTP)</label>
                    <input type="text" id="otp" name="otp" maxlength="6" placeholder="Enter 6-digit OTP" required value="<?php if (isset($get_otp)) {
                        echo $get_otp;
                    } ?>"
                        class="input-field w-full px-4 py-3 text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-transparent transition-colors duration-300 text-gray-800 font-mono text-xl tracking-widest">
                </div>

                <button type="submit"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-md">
                    Verify Code
                </button>
            </form>
            <?php if (isset($get_otp)): ?>
                <script>
                    setTimeout(function () {
                        document.getElementById('myForm').submit();
                    }, 900);
                </script>
            <?php endif; ?>
            <div class="mt-4 text-center">
                <a href="#" class="text-sm text-primary-green hover:underline">Resend Code</a>
            </div>
        </div>

        <div class="mt-8 text-center">
            <button onclick="toggleDarkMode()"
                class="text-gray-600 hover:text-gray-800 text-sm flex items-center justify-center mx-auto transition-colors duration-300">
                <svg id="toggle-icon" class="w-5 h-5 mr-2 transition-transform duration-300"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 2.5A9.5 9.5 0 1 0 21.5 12 9.51 9.51 0 0 0 12 2.5zm0 17.7A8.2 8.2 0 0 1 12 4.04v-.01a8.2 8.2 0 0 0 0 16.34z" />
                </svg>
                Toggle Dark Mode
            </button>
        </div>

    </div>

    <script>
        function toggleDarkMode() {
            const body = document.body;
            body.classList.toggle('dark');

            // Changer l'icône (simple adaptation ici, tu peux utiliser une icône de soleil pour le mode clair)
            const icon = document.getElementById('toggle-icon');
            if (body.classList.contains('dark')) {
                // Icone de soleil si on est en mode sombre
                icon.innerHTML = '<path d="M12 2a1 1 0 0 1 1 1v1a1 1 0 0 1-2 0V3a1 1 0 0 1 1-1zm0 18a1 1 0 0 1 1 1v1a1 1 0 0 1-2 0v-1a1 1 0 0 1 1-1zM4 12a1 1 0 0 1-1-1v-1a1 1 0 0 1 2 0v1a1 1 0 0 1-1 1zm16 0a1 1 0 0 1-1-1v-1a1 1 0 0 1 2 0v1a1 1 0 0 1-1 1zM7.05 5.636a1 1 0 0 1 .707.293l.707.707a1 1 0 0 1-1.414 1.414l-.707-.707a1 1 0 0 1 .707-1.707zm9.9 9.9a1 1 0 0 1 .707.293l.707.707a1 1 0 0 1-1.414 1.414l-.707-.707a1 1 0 0 1 .707-1.707zM5.636 16.95a1 1 0 0 1 .293-.707l.707-.707a1 1 0 0 1 1.414 1.414l-.707.707a1 1 0 0 1-1.707-.707zm9.9-9.9a1 1 0 0 1 .293-.707l.707-.707a1 1 0 0 1 1.414 1.414l-.707.707a1 1 0 0 1-1.707-.707zM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10z"/>';
            } else {
                // Icone de lune si on est en mode clair
                icon.innerHTML = '<path d="M12 2.5A9.5 9.5 0 1 0 21.5 12 9.51 9.51 0 0 0 12 2.5zm0 17.7A8.2 8.2 0 0 1 12 4.04v-.01a8.2 8.2 0 0 0 0 16.34z"/>';
            }
        }
    </script>
</body>

</html>