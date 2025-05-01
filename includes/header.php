<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Überprüfen, ob die aktuelle Seite die Login- oder Registrierungsseite ist
$current_page = basename($_SERVER['SCRIPT_NAME']);
$auth_pages = ['login.php', 'register.php'];
$is_auth_page = in_array($current_page, $auth_pages);

// Wenn nicht auf einer Auth-Seite und nicht angemeldet, zur Login-Seite umleiten
if (!$is_auth_page && !$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Aktuellen Benutzer abrufen, falls angemeldet
$current_user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCD Steckdosen Verwaltung</title>
    <meta name="description" content="Verwaltung von Stromsteckdosen im Bootshafen">
    <meta name="author" content="Marina Power Control">
    
    <!-- Tailwind CSS über CDN einbinden -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tailwind Konfiguration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        marina: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                },
            },
        }
    </script>
    
    <!-- Allgemeine Styles -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body>
<?php if (!$is_auth_page && $current_user): ?>
    <!-- Navigation für angemeldete Benutzer -->
    <header class="bg-white shadow">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-2xl font-bold text-marina-700">SCD Steckdosen Verwaltung</h1>
                    </div>
                    <nav class="ml-6 flex space-x-4 items-center">
                        <a href="index.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-marina-700 hover:bg-gray-50">Dashboard</a>
                        <a href="mieter.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-marina-700 hover:bg-gray-50">Mieter</a>
                        <a href="steckdosen.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-marina-700 hover:bg-gray-50">Steckdosen</a>
                        <a href="zaehler.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-marina-700 hover:bg-gray-50">Zähler</a>
                        <a href="zaehlerstaende.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-marina-700 hover:bg-gray-50">Zählerstände</a>
                        <a href="bereiche.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-marina-700 hover:bg-gray-50">Bereiche</a>
                        <?php if ($auth->isAdmin()): ?>
                            <a href="users.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-marina-700 hover:bg-gray-50">Benutzerverwaltung</a>
                        <?php endif; ?>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-700 flex flex-col items-start leading-tight">
                        <span><?= htmlspecialchars($current_user['name']) ?></span>
                        <a href="passwort-aendern.php" class="text-xs text-marina-600 hover:underline">
                            Passwort ändern
                        </a>
                    </div>
                    <div>
                        <span class="h-8 w-8 rounded-full bg-marina-100 flex items-center justify-center text-sm font-semibold text-marina-800">
                            <?= substr(htmlspecialchars($current_user['name']), 0, 1) ?>
                        </span>
                    </div>
                    <a href="logout.php" class="text-sm font-medium text-gray-700 hover:text-marina-700">Abmelden</a>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>
    <main class="py-6">
        <div class="max-w-11xl mx-auto px-4 sm:px-6 lg:px-8">
