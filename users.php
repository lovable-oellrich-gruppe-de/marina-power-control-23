<?php
// Wichtig: Keine Leerzeilen oder Whitespace vor dem öffnenden PHP-Tag
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

// Seitentitel
$pageTitle = "Benutzerverwaltung";

require_once 'includes/header.php';
?>

<div class="bg-white shadow-md rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800"><?= $pageTitle ?></h1>
        </div>
        
        <?php if (isset($_GET['status_changed']) && $_GET['status_changed'] === '1'): ?>
            <div class="bg-green-50 text-green-800 p-4 rounded-md mb-4">
                Der Benutzerstatus wurde erfolgreich aktualisiert.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['role_changed']) && $_GET['role_changed'] === '1'): ?>
            <div class="bg-green-50 text-green-800 p-4 rounded-md mb-4">
                Die Benutzerrolle wurde erfolgreich aktualisiert.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
            <div class="bg-green-50 text-green-800 p-4 rounded-md mb-4">
                Der Benutzer wurde erfolgreich gelöscht.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'self_delete'): ?>
            <div class="bg-red-50 text-red-800 p-4 rounded-md mb-4">
                Sie können Ihren eigenen Account nicht löschen.
            </div>
        <?php endif; ?>
        
        <!-- Benutzertabelle -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-Mail</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rolle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erstellt am</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Keine Benutzer gefunden.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($user['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= date('d.m.Y H:i', strtotime($user['erstellt_am'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2 justify-end">
                                        <!-- Status umschalten -->
                                        <a href="users.php?action=toggle_status&id=<?= urlencode($user['id']) ?>" 
                                           class="text-indigo-600 hover:text-indigo-900 flex items-center"
                                           onclick="return confirm('Möchten Sie den Status dieses Benutzers wirklich ändern?');">
                                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <?= $user['status'] === 'active' ? 'Deaktivieren' : 'Aktivieren' ?>
                                        </a>
                                        
                                        <!-- Rolle umschalten -->
                                        <a href="users.php?action=toggle_role&id=<?= urlencode($user['id']) ?>" 
                                           class="text-blue-600 hover:text-blue-900 flex items-center"
                                           onclick="return confirm('Möchten Sie die Rolle dieses Benutzers wirklich ändern?');">
                                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                            </svg>
                                            <?= $user['rolle'] === 'admin' ? 'Zum Benutzer' : 'Zum Admin' ?>
                                        </a>
                                        
                                        <!-- Löschen - nicht für den aktuellen Benutzer anzeigen -->
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <a href="users.php?action=delete&id=<?= urlencode($user['id']) ?>" 
                                               class="text-red-600 hover:text-red-900 flex items-center"
                                               onclick="return confirm('Möchten Sie diesen Benutzer wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.');">
                                                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Löschen
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

<?php
require_once 'includes/footer.php';
?>
