
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Löschen eines Bereichs, wenn ID übergeben wurde
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Prüfen, ob Bereich mit Steckdosen verknüpft ist
    $linkedResources = $db->fetchOne("SELECT COUNT(*) as count FROM steckdosen WHERE bereich_id = ?", [$id]);
    
    if ($linkedResources['count'] > 0) {
        $error = "Der Bereich kann nicht gelöscht werden, da er noch mit Steckdosen verknüpft ist.";
    } else {
        $result = $db->query("DELETE FROM bereiche WHERE id = ?", [$id]);
        
        if ($db->affectedRows() > 0) {
            $success = "Bereich wurde erfolgreich gelöscht.";
        } else {
            $error = "Fehler beim Löschen des Bereichs.";
        }
    }
}

// Aktivieren/Deaktivieren eines Bereichs
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['aktiv'])) {
    $id = $_GET['id'];
    $aktiv = $_GET['aktiv'] === '1' ? 1 : 0;
    
    $result = $db->query("UPDATE bereiche SET aktiv = ? WHERE id = ?", [$aktiv, $id]);
    
    if ($db->affectedRows() >= 0) {
        $success = "Status wurde erfolgreich aktualisiert.";
    } else {
        $error = "Fehler beim Aktualisieren des Status.";
    }
}

// Alle Bereiche aus der Datenbank abrufen
$bereiche = $db->fetchAll("
    SELECT b.*, 
           (SELECT COUNT(*) FROM steckdosen WHERE bereich_id = b.id) AS steckdosen_count
    FROM bereiche b
    ORDER BY b.name
");

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Bereiche verwalten</h1>
            <a href="bereiche_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                Neuer Bereich
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Bereiche-Tabelle -->
        <div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beschreibung</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Steckdosen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($bereiche)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Keine Bereiche gefunden
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bereiche as $b): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($b['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($b['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= htmlspecialchars($b['beschreibung'] ?: '-') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($b['aktiv']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aktiv
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Inaktiv
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($b['steckdosen_count']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <?php if ($b['aktiv']): ?>
                                                <a href="bereiche.php?id=<?= $b['id'] ?>&aktiv=0" class="text-yellow-600 hover:text-yellow-900">Deaktivieren</a>
                                            <?php else: ?>
                                                <a href="bereiche.php?id=<?= $b['id'] ?>&aktiv=1" class="text-green-600 hover:text-green-900">Aktivieren</a>
                                            <?php endif; ?>
                                            <a href="bereiche_form.php?id=<?= $b['id'] ?>" class="text-marina-600 hover:text-marina-900">Bearbeiten</a>
                                            <a href="#" onclick="confirmDelete(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['name'])) ?>', <?= $b['steckdosen_count'] ?>)" class="text-red-600 hover:text-red-900">Löschen</a>
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

<!-- Bestätigungsdialog für das Löschen -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Bereich löschen</h3>
        <p class="text-gray-500 mb-4">Möchten Sie den Bereich <span id="bereichName"></span> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
        <p id="warningText" class="text-red-500 mb-4 hidden">Dieser Bereich kann nicht gelöscht werden, da er noch mit Steckdosen verknüpft ist.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
            <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name, steckdosenCount) {
    document.getElementById('bereichName').textContent = name;
    const warningText = document.getElementById('warningText');
    const deleteLink = document.getElementById('deleteLink');
    
    if (steckdosenCount > 0) {
        warningText.classList.remove('hidden');
        deleteLink.classList.add('opacity-50', 'cursor-not-allowed');
        deleteLink.href = '#';
        deleteLink.onclick = function(e) { e.preventDefault(); };
    } else {
        warningText.classList.add('hidden');
        deleteLink.classList.remove('opacity-50', 'cursor-not-allowed');
        deleteLink.href = 'bereiche.php?delete=' + id;
        deleteLink.onclick = null;
    }
    
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php
require_once 'includes/footer.php';
?>
