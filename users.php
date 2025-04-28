<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Nur Administratoren dürfen auf die Benutzerverwaltung zugreifen
if (!$auth->isAdmin()) {
    header('Location: index.php');
    exit;
}

// Aktion für Statusänderung
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    
    // Aktuellen Status abrufen
    $sql = "SELECT status FROM benutzer WHERE id = ?";
    $user = $db->fetchOne($sql, [$userId]);
    
    if ($user) {
        // Status umschalten
        $newStatus = ($user['status'] === 'active') ? 'pending' : 'active';
        $sql = "UPDATE benutzer SET status = ? WHERE id = ?";
        $db->query($sql, [$newStatus, $userId]);
        
        // Weiterleiten, um Neuladen zu vermeiden
        header('Location: users.php?status_changed=1');
        exit;
    }
}

// Aktion für Rollenänderung
if (isset($_GET['action']) && $_GET['action'] === 'toggle_role' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    
    // Aktuelle Rolle abrufen
    $sql = "SELECT rolle FROM benutzer WHERE id = ?";
    $user = $db->fetchOne($sql, [$userId]);
    
    if ($user) {
        // Rolle umschalten
        $newRole = ($user['rolle'] === 'admin') ? 'user' : 'admin';
        $sql = "UPDATE benutzer SET rolle = ? WHERE id = ?";
        $db->query($sql, [$newRole, $userId]);
        
        // Weiterleiten, um Neuladen zu vermeiden
        header('Location: users.php?role_changed=1');
        exit;
    }
}

// Aktion für Benutzerlöschung
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    
    // Sicherstellen, dass der aktuelle Admin sich nicht selbst löscht
    if ($userId !== $_SESSION['user_id']) {
        $sql = "DELETE FROM benutzer WHERE id = ?";
        $db->query($sql, [$userId]);
        
        // Weiterleiten, um Neuladen zu vermeiden
        header('Location: users.php?deleted=1');
        exit;
    } else {
        // Fehler: Man kann sich nicht selbst löschen
        header('Location: users.php?error=self_delete');
        exit;
    }
}

// Alle Benutzer abrufen
$sql = "SELECT id, email, name, rolle, status, erstellt_am FROM benutzer ORDER BY erstellt_am DESC";
$users = $db->fetchAll($sql);

require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Benutzerverwaltung</h1>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Benutzerverwaltung</h1>
            <a href="user_form.php" class="inline-flex items-center px-4 py-2 bg-marina-600 border border-transparent rounded-md font-semibold text-white hover:bg-marina-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                Neuer Benutzer
            </a>
        </div>

        <?php if (isset($_GET['status_changed']) && $_GET['status_changed'] === '1'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Der Benutzerstatus wurde erfolgreich aktualisiert.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['role_changed']) && $_GET['role_changed'] === '1'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Die Benutzerrolle wurde erfolgreich aktualisiert.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Der Benutzer wurde erfolgreich gelöscht.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'self_delete'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                Sie können Ihren eigenen Account nicht löschen.
            </div>
        <?php endif; ?>

        <div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">E-Mail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Rolle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Erstellt am</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Keine Benutzer gefunden
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($user['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($user['rolle'] === 'admin'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Administrator
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Benutzer
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aktiv
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Ausstehend
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d.m.Y H:i', strtotime($user['erstellt_am'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-4">
                                            <a href="users.php?action=toggle_status&id=<?= $user['id'] ?>" class="text-marina-600 hover:text-marina-900 p-1" title="Status ändern">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                                </svg>
                                            </a>
                                            <a href="users.php?action=toggle_role&id=<?= $user['id'] ?>" class="text-marina-600 hover:text-marina-900 p-1" title="Rolle ändern">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="8.5" cy="7" r="4"></circle>
                                                    <polyline points="17 11 19 13 23 9"></polyline>
                                                </svg>
                                            </a>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <a href="users.php?action=delete&id=<?= $user['id'] ?>" class="text-red-600 hover:text-red-900 p-1" title="Löschen" onclick="return confirm('Möchten Sie diesen Benutzer wirklich löschen?');">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
