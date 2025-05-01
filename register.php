<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Wenn bereits angemeldet, zum Dashboard weiterleiten
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$registered = false;

// Formular wurde abgesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['passwordConfirm'] ?? '';
    
    // Validierung
    if (empty($name) || empty($email) || empty($password) || empty($passwordConfirm)) {
        $error = 'Bitte alle Felder ausfüllen.';
    } elseif (strlen($name) < 2) {
        $error = 'Name muss mindestens 2 Zeichen lang sein.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
    } elseif (strlen($password) < 6) {
        $error = 'Passwort muss mindestens 6 Zeichen lang sein.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwörter stimmen nicht überein.';
    } else {
        // Registrierungsversuch
        $result = $auth->register($email, $password, $name);
        
        if ($result['success']) {
            $success = $result['message'];
            $registered = true;
        } else {
            $error = $result['message'];
        }
    }
}

require_once 'includes/header.php';
?>

<div class="flex min-h-screen flex-col items-center justify-center bg-muted/20 p-4">
    <div class="w-full max-w-md space-y-6 rounded-lg border bg-white p-6 shadow-lg">
        <?php if ($registered): ?>
            <div class="space-y-2 text-center">
                <h1 class="text-3xl font-bold text-marina-800">Registrierung erfolgreich</h1>
                <p class="text-gray-500">
                    Ihr Konto wurde erstellt und muss nun von einem Administrator freigeschaltet werden.
                    Sie werden per E-Mail benachrichtigt, sobald Ihr Konto freigeschalten wurde.
                </p>
            </div>
            <a href="login.php" class="block w-full">
                <button class="w-full rounded-md bg-marina-600 px-4 py-2 text-white hover:bg-marina-700 focus:outline-none focus:ring-2 focus:ring-marina-500 focus:ring-offset-2">
                    Zurück zur Anmeldung
                </button>
            </a>
        <?php else: ?>
            <div class="space-y-2 text-center">
                <h1 class="text-3xl font-bold text-marina-800">Marina Power Control</h1>
                <p class="text-gray-500">Erstellen Sie ein neues Konto</p>
            </div>
            
            <?php if ($error): ?>
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form action="register.php" method="POST" class="space-y-4">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium">Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                        class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-marina-500 focus:ring-offset-2"
                        placeholder="Max Mustermann"
                        required
                    />
                </div>
                
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium">E-Mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
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
                
                <div class="space-y-2">
                    <label for="passwordConfirm" class="text-sm font-medium">Passwort bestätigen</label>
                    <input 
                        type="password" 
                        id="passwordConfirm" 
                        name="passwordConfirm" 
                        class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-marina-500 focus:ring-offset-2"
                        placeholder="******"
                        required
                    />
                </div>
                
                <button
                    type="submit"
                    class="w-full rounded-md bg-marina-600 px-4 py-2 text-white hover:bg-marina-700 focus:outline-none focus:ring-2 focus:ring-marina-500 focus:ring-offset-2"
                >
                    Registrieren
                </button>
            </form>
            
            <div class="mt-4 text-center text-sm">
                <p>
                    Bereits ein Konto?
                    <a href="login.php" class="text-marina-600 hover:underline">
                        Anmelden
                    </a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
