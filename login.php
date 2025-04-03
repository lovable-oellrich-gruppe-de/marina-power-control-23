
<?php
// Wichtig: Keine Leerzeilen oder Whitespace vor dem öffnenden PHP-Tag
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Wenn bereits angemeldet, zum Dashboard weiterleiten
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Formular wurde abgesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validierung
    if (empty($email) || empty($password)) {
        $error = 'Bitte E-Mail und Passwort eingeben.';
    } else {
        // Anmeldeversuch
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            // Erfolgreich angemeldet, Weiterleitung zum Dashboard
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

require_once 'includes/header.php';
?>

<div class="flex min-h-screen flex-col items-center justify-center bg-muted/20 p-4">
    <div class="w-full max-w-md space-y-6 rounded-lg border bg-white p-6 shadow-lg">
        <div class="space-y-2 text-center">
            <h1 class="text-3xl font-bold text-marina-800">Marina Power Control</h1>
            <p class="text-gray-500">Melden Sie sich an, um fortzufahren</p>
        </div>
        
        <?php if ($error): ?>
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST" class="space-y-4">
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium">E-Mail</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-marina-500 focus:ring-offset-2"
                    placeholder="email@beispiel.de"
                    required
                />
            </div>
            
            <div class="space-y-2">
                <label for="password" class="text-sm font-medium">Passwort</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-marina-500 focus:ring-offset-2"
                    placeholder="******"
                    required
                />
            </div>
            
            <button
                type="submit"
                class="w-full rounded-md bg-marina-600 px-4 py-2 text-white hover:bg-marina-700 focus:outline-none focus:ring-2 focus:ring-marina-500 focus:ring-offset-2"
            >
                Anmelden
            </button>
        </form>
        
        <div class="mt-4 text-center text-sm">
            <p>
                Noch kein Konto?
                <a href="register.php" class="text-marina-600 hover:underline">
                    Registrieren
                </a>
            </p>
            
            <div class="mt-6 border-t pt-4">
                <p class="text-xs text-gray-500">
                    Demo-Zugangsdaten:
                </p>
                <p class="mt-1 text-xs">
                    Email: admin@marina-power.de<br />
                    Passwort: admin123
                </p>
                <p class="mt-1 text-xs">
                    Email: benutzer@marina-power.de<br />
                    Passwort: benutzer123
                </p>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
