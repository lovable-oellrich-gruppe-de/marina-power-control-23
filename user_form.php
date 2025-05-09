<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Nur Administratoren dürfen auf die Benutzeranlage oder -bearbeitung zugreifen
if (!$auth->isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

$editing = false;
$user = [
    'id' => '',
    'name' => '',
    'email' => '',
    'rolle' => 'user'
];

// Bestehenden Benutzer laden
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editing = true;
    $userData = $db->fetchOne("SELECT * FROM benutzer WHERE id = ?", [$_GET['id']]);
    if ($userData) {
        $user = $userData;
    } else {
        $error = "Benutzer nicht gefunden.";
    }
}

// Wenn das Formular abgesendet wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rolle = $_POST['rolle'] ?? 'user';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    $user = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'rolle' => $rolle
    ];

    // Validierung
    if (empty($name)) {
        $error = "Bitte einen Namen eingeben.";
    } elseif (empty($email)) {
        $error = "Bitte eine E-Mail-Adresse eingeben.";
    } elseif (!$editing && empty($password)) {
        $error = "Bitte ein Passwort vergeben.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Passwort muss mindestens 6 Zeichen lang sein.";
    } elseif (!empty($password) && $password !== $passwordConfirm) {
        $error = "Die Passwörter stimmen nicht überein.";
    } else {
        if ($editing) {
            // Benutzer aktualisieren
            $sql = "UPDATE benutzer SET name = ?, email = ?, rolle = ?";
            $params = [$name, $email, $rolle];

            if (!empty($password)) {
                $sql .= ", passwort_hash = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $db->query($sql, $params);

            header('Location: users.php?success=Benutzer wurde aktualisiert.');
            exit;
        } else {
            // Neuen Benutzer anlegen
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $db->query(
                "INSERT INTO benutzer (name, email, rolle, passwort_hash, status, erstellt_am) VALUES (?, ?, ?, ?, 'active', NOW())",
                [$name, $email, $rolle, $passwordHash]
            );

            header('Location: users.php?created=1');
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                <?= $editing ? 'Benutzer bearbeiten' : 'Neuen Benutzer anlegen' ?>
            </h1>
            <a href="users.php" class="text-marina-600 hover:text-marina-700">
                Zurück zur Benutzerverwaltung
            </a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <form method="POST" action="user_form.php<?= $editing ? '?id=' . urlencode($user['id']) : '' ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="space-y-2 sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?= htmlspecialchars($user['name']) ?>"
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700">E-Mail *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($user['email']) ?>"
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>

                    <div class="space-y-2">
                        <label for="rolle" class="block text-sm font-medium text-gray-700">Rolle *</label>
                        <select 
                            id="rolle" 
                            name="rolle" 
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                            <option value="user" <?= $user['rolle'] === 'user' ? 'selected' : '' ?>>Benutzer</option>
                            <option value="admin" <?= $user['rolle'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            <?= $editing ? 'Neues Passwort' : 'Passwort *' ?>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2 sm:col-span-2">
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">
                            <?= $editing ? 'Passwort bestätigen' : 'Passwort bestätigen *' ?>
                        </label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="users.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Abbrechen
                    </a>
                    <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">
                        Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
