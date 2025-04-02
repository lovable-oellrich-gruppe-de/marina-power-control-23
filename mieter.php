
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Löschen eines Mieters, wenn ID übergeben wurde
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $db->query("DELETE FROM mieter WHERE id = ?", [$id]);
    
    if ($db->affectedRows() > 0) {
        $success = "Mieter wurde erfolgreich gelöscht.";
    } else {
        $error = "Fehler beim Löschen des Mieters.";
    }
}

// Alle Mieter aus der Datenbank abrufen
$mieter = $db->fetchAll("SELECT * FROM mieter ORDER BY nachname, vorname");

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Mieter verwalten</h1>
            <a href="mieter_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                Neuer Mieter
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

        <!-- Mieter-Tabelle -->
        <div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-Mail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bootsname</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($mieter)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Keine Mieter gefunden
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mieter as $m): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($m['vorname'] . ' ' . $m['nachname']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['email']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['telefon']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['bootsname']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="mieter_form.php?id=<?= $m['id'] ?>" class="text-marina-600 hover:text-marina-900 mr-3">Bearbeiten</a>
                                        <a href="#" onclick="confirmDelete(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['vorname'] . ' ' . $m['nachname'])) ?>')" class="text-red-600 hover:text-red-900">Löschen</a>
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
        <h3 class="text-lg font-medium text-gray-900 mb-2">Mieter löschen</h3>
        <p class="text-gray-500 mb-4">Möchten Sie den Mieter <span id="mieterName"></span> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
            <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('mieterName').textContent = name;
    document.getElementById('deleteLink').href = 'mieter.php?delete=' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php
require_once 'includes/footer.php';
?>
