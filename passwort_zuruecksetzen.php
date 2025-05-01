<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$resetDone = false;

$userId = $_GET['id'] ?? null;

if (!$userId || !is_numeric($userId)) {
    $error = 'Ungültiger Link. Keine Benutzer-ID gefunden.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new) < 6) {
        $error = 'Passwort muss mindestens 6 Zeichen lang sein.';
    } elseif ($new !== $confirm) {
        $error = 'Die Passwörter stimmen nicht überein.';
    } else {
        if ($auth->updatePassword($userId, $new)) {
            $success = 'Das Passwort wurde erfolgreich zurückgesetzt.';
            $resetDone = true;
        } else {
            $error = 'Fehler beim Speichern. Bitte kontaktieren Sie den Administrator.';
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="flex min-h-screen flex-col items-center justify-center bg-muted/20 p-4">
    <div class="w-full max-w-md space-y-6 rounded-lg border bg-white p-6 shadow-lg">

        <div class="space-y-2 text-center">
            <h1 class="text-2xl font-bold text-marina-800">Passwort zurücksetzen</h1>
            <p class="text-sm text-gray-500">Geben Sie Ihr neues Passwort ein.</p>
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

        <?php if (!$resetDone): ?>
        <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="POST" class="space-y-4">
            <div class="space-y-2">
                <label for="new_password" class="text-sm font-medium">Neues Passwort</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required 
                    class="flex h-10 w-full rounded-md border border-gray-300 px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500"
                />
            </div>

            <div class="space-y-2">
                <label for="confirm_password" class="text-sm font-medium">Bestätigen</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required 
                    class="flex h-10 w-full rounded-md border border-gray-300 px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500"
                />
            </div>

            <button type="submit" class="w-full rounded-md bg-marina-600 px-4 py-2 text-white hover:bg-marina-700">
                Passwort setzen
            </button>
        </form>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
