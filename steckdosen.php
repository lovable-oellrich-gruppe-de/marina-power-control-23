<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Löschen einer Steckdose, wenn ID übergeben wurde
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $db->query("DELETE FROM steckdosen WHERE id = ?", [$id]);
    
    if ($db->affectedRows() > 0) {
        $success = "Steckdose wurde erfolgreich gelöscht.";
    } else {
        $error = "Fehler beim Löschen der Steckdose.";
    }
}

// Status einer Steckdose ändern, wenn ID und Status übergeben wurden
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['aktiv', 'inaktiv', 'defekt'])) {
        $result = $db->query("UPDATE steckdosen SET status = ? WHERE id = ?", [$status, $id]);
        
        if ($db->affectedRows() >= 0) {
            $success = "Status wurde erfolgreich aktualisiert.";
        } else {
            $error = "Fehler beim Aktualisieren des Status.";
        }
    }
}

// Alle Steckdosen aus der Datenbank abrufen
$steckdosen = $db->fetchAll("
    SELECT s.*, 
           b.name AS bereich_name, 
           CONCAT(m.vorname, ' ', m.nachname) AS mieter_name
    FROM steckdosen s
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    LEFT JOIN mieter m ON s.mieter_id = m.id
    ORDER BY s.bezeichnung
");

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Steckdosen verwalten</h1>
            <a href="steckdosen_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                Neue Steckdose
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

        <!-- Filter-Optionen -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <form method="GET" action="steckdosen.php" class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-auto">
                    <label for="bereich" class="block text-sm font-medium text-gray-700 mb-1">Bereich</label>
                    <select id="bereich" name="bereich" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle Bereiche</option>
                        <!-- Hier könnten dynamisch Bereiche geladen werden -->
                    </select>
                </div>
                
                <div class="w-full sm:w-auto">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle Status</option>
                        <option value="aktiv">Aktiv</option>
                        <option value="inaktiv">Inaktiv</option>
                        <option value="defekt">Defekt</option>
                    </select>
                </div>
                
                <div class="w-full sm:w-auto">
                    <label for="zugewiesen" class="block text-sm font-medium text-gray-700 mb-1">Zugewiesen</label>
                    <select id="zugewiesen" name="zugewiesen" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle</option>
                        <option value="ja">Zugewiesen</option>
                        <option value="nein">Nicht zugewiesen</option>
                    </select>
                </div>
                
                <div class="ml-auto">
                    <button type="submit" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                        Filtern
                    </button>
                </div>
            </form>
        </div>

        <!-- Steckdosen-Tabelle -->
        <div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bezeichnung</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bereich</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mieter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($steckdosen)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Keine Steckdosen gefunden
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($steckdosen as $s): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($s['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($s['bezeichnung']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($s['status'] === 'aktiv'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aktiv
                                            </span>
                                        <?php elseif ($s['status'] === 'inaktiv'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Inaktiv
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Defekt
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($s['bereich_name'] ?? 'Nicht zugewiesen') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($s['mieter_name'] ?? 'Nicht zugewiesen') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <!-- Status-Dropdown -->
                                            <div class="relative">
                                                <button id="status-button-<?= $s['id'] ?>" class="text-gray-700 hover:text-marina-600" onclick="toggleStatusMenu(<?= $s['id'] ?>)">
                                                    Status ändern
                                                </button>
                                                <div id="status-menu-<?= $s['id'] ?>" class="hidden absolute z-10 mt-2 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 p-1 space-y-1">
                                                    <a href="steckdosen.php?id=<?= $s['id'] ?>&status=aktiv" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Aktiv</a>
                                                    <a href="steckdosen.php?id=<?= $s['id'] ?>&status=inaktiv" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Inaktiv</a>
                                                    <a href="steckdosen.php?id=<?= $s['id'] ?>&status=defekt" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Defekt</a>
                                                </div>
                                            </div>

                                            <a href="steckdosen_form.php?id=<?= $s['id'] ?>" class="text-marina-600 hover:text-marina-900">Bearbeiten</a>
                                            <a href="#" onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['bezeichnung'])) ?>')" class="text-red-600 hover:text-red-900">Löschen</a>
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
        <h3 class="text-lg font-medium text-gray-900 mb-2">Steckdose löschen</h3>
        <p class="text-gray-500 mb-4">Möchten Sie die Steckdose <span id="steckdosenName"></span> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
            <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
        </div>
    </div>
</div>

<script>
function toggleStatusMenu(id) {
    const menu = document.getElementById('status-menu-' + id);
    const allMenus = document.querySelectorAll('[id^="status-menu-"]');
    
    // Schließe alle anderen Menüs
    allMenus.forEach(function(m) {
        if (m.id !== 'status-menu-' + id) {
            m.classList.add('hidden');
        }
    });
    
    // Toggle das aktuelle Menü
    menu.classList.toggle('hidden');
}

function confirmDelete(id, name) {
    document.getElementById('steckdosenName').textContent = name;
    document.getElementById('deleteLink').href = 'steckdosen.php?delete=' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Schließe alle Status-Menüs, wenn irgendwo anders geklickt wird
document.addEventListener('click', function(event) {
    if (!event.target.closest('[id^="status-button-"]')) {
        const allMenus = document.querySelectorAll('[id^="status-menu-"]');
        allMenus.forEach(function(menu) {
            menu.classList.add('hidden');
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
