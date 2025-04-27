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

/*
// Status einer Steckdose ändern, wenn ID und Status übergeben wurden
if (isset($_GET['assign_status']) && isset($_GET['steckdose_id']) && isset($_GET['status'])) {
    $steckdose_id = $_GET['steckdose_id'];
    $status = $_GET['status'];

    // Aktuellen Status holen
    $rows = $db->fetchAll("SELECT status FROM steckdosen WHERE id = ?", [$steckdose_id]);

    if (!empty($rows)) {
        $currentStatus = $rows[0]['status'];

        if ($currentStatus != $status) {
            // Unterschied -> UPDATE machen
            $updateResult = $db->query("UPDATE steckdosen SET status = ? WHERE id = ?", [$status, $steckdose_id]);

            if ($updateResult) {
                $success = "Status wurde erfolgreich geändert.";
            } else {
                $error = "Fehler bei der Änderung des Status: " . $db->error();
            }
        } else {
            $info = "Hinweis: Der ausgewählte Status war bereits gesetzt. Keine Änderung erforderlich.";
        }
    } else {
        $error = "Fehler: Steckdose wurde nicht gefunden.";
    }
}

// Mieter einer Steckdose zuordnen
if (isset($_POST['assign_mieter']) && isset($_POST['steckdose_id']) && isset($_POST['mieter_id'])) {
    $steckdose_id = $_POST['steckdose_id'];
    $mieter_id = $_POST['mieter_id'] ? $_POST['mieter_id'] : null;

    // Aktuellen Mieter aus der Datenbank holen
    $rows = $db->fetchAll("SELECT mieter_id FROM steckdosen WHERE id = ?", [$steckdose_id]);

    if (!empty($rows)) {
        $currentMieter = $rows[0]['mieter_id'];

        if ($currentMieter != $mieter_id) {
            // Unterschied -> UPDATE machen
            $updateResult = $db->query("UPDATE steckdosen SET mieter_id = ? WHERE id = ?", [$mieter_id, $steckdose_id]);

            if ($updateResult) {
                $success = "Mieter wurde erfolgreich geändert.";
            } else {
                $error = "Fehler bei der Zuordnung des Mieters: " . $db->error();
            }
        } else {
            $info = "Hinweis: Der ausgewählte Mieter war bereits zugewiesen. Keine Änderung erforderlich.";
        }
    } else {
        $error = "Fehler: Steckdose wurde nicht gefunden.";
    }
}

// Bereich einer Steckdose zuordnen
if (isset($_POST['assign_bereich']) && isset($_POST['steckdose_id']) && isset($_POST['bereich_id'])) {
    $steckdose_id = $_POST['steckdose_id'];
    $bereich_id = $_POST['bereich_id'] ? $_POST['bereich_id'] : null;

    // Aktuellen Bereich aus der Datenbank holen
    $rows = $db->fetchAll("SELECT bereich_id FROM steckdosen WHERE id = ?", [$steckdose_id]);

    if (!empty($rows)) {
        $currentBereich = $rows[0]['bereich_id'];

        if ($currentBereich != $bereich_id) {
            // Unterschied -> UPDATE machen
            $updateResult = $db->query("UPDATE steckdosen SET bereich_id = ? WHERE id = ?", [$bereich_id, $steckdose_id]);

            if ($updateResult) {
                $success = "Bereich wurde erfolgreich geändert.";
            } else {
                $error = "Fehler bei der Zuordnung des Bereichs: " . $db->error();
            }
        } else {
            $info = "Hinweis: Der ausgewählte Bereich war bereits zugewiesen. Keine Änderung erforderlich.";
        }
    } else {
        $error = "Fehler: Steckdose wurde nicht gefunden.";
    }
}
*/
/*SELECT 
    steckdosen.id,
    steckdosen.bezeichnung,
    steckdosen.status,
    COALESCE(bereiche.name, 'Nicht zugewiesen') AS bereich_name,
    COALESCE(CONCAT(COALESCE(mieter.vorname, ''), ' ', COALESCE(mieter.name, '')), 'Nicht zugewiesen') AS mieter_name
FROM steckdosen
LEFT JOIN bereiche ON steckdosen.bereich_id = bereiche.id
LEFT JOIN mieter ON steckdosen.mieter_id = mieter.id
ORDER BY steckdosen.bezeichnung;
*/
// Alle Steckdosen aus der Datenbank abrufen
$sql = ""SELECT steckdosen.id, steckdosen.bezeichnung, steckdosen.status, COALESCE(bereiche.name, 'Nicht zugewiesen') AS bereich_name, COALESCE(CONCAT(COALESCE(mieter.vorname, ''), ' ', COALESCE(mieter.name, '')), 'Nicht zugewiesen') AS mieter_name FROM steckdosen LEFT JOIN bereiche ON steckdosen.bereich_id = bereiche.id LEFT JOIN mieter ON steckdosen.mieter_id = mieter.id";
$params = [];

if (!empty($_GET['bereich'])) {
    $sql .= " AND bereich_id = ?";
    $params[] = $_GET['bereich'];
}

if (!empty($_GET['status'])) {
    $sql .= " AND status = ?";
    $params[] = $_GET['status'];
}

if (!empty($_GET['mieter'])) {
    $sql .= " AND mieter_id = ?";
    $params[] = $_GET['mieter'];
}

if (isset($_GET['zugewiesen']) && $_GET['zugewiesen'] !== '') {
    if ($_GET['zugewiesen'] === '1') {
        $sql .= " AND mieter_id IS NOT NULL";
    } elseif ($_GET['zugewiesen'] === '0') {
        $sql .= " AND mieter_id IS NULL";
    }
}
$sql .= " ORDER BY bezeichnung";
$steckdosen = $db->fetchAll($sql, $params);
//$steckdosen = $db->fetchAll("SELECT steckdosen.id, steckdosen.bezeichnung, steckdosen.status, COALESCE(bereiche.name, 'Nicht zugewiesen') AS bereich_name, COALESCE(CONCAT(COALESCE(mieter.vorname, ''), ' ', COALESCE(mieter.name, '')), 'Nicht zugewiesen') AS mieter_name FROM steckdosen LEFT JOIN bereiche ON steckdosen.bereich_id = bereiche.id LEFT JOIN mieter ON steckdosen.mieter_id = mieter.id ORDER BY steckdosen.bezeichnung;");

// Alle Mieter für Dropdown abrufen
$mieter = $db->fetchAll("SELECT id, CONCAT(vorname, ' ', name) AS name FROM mieter ORDER BY name");

// Alle Bereiche für Dropdown abrufen
$bereiche = $db->fetchAll("SELECT id, name FROM bereiche ORDER BY name");

// Alle Zähler für Dropdown abrufen
$zaehler = $db->fetchAll("SELECT id, zaehlernummer FROM zaehler ORDER BY zaehlernummer");

echo '<pre>';
print_r($_GET);
echo '</pre>';

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
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

        <?php if (isset($info)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($info) ?>
            </div>
        <?php endif; ?>

        <!-- Filter-Optionen -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <form method="GET" action="steckdosen.php" class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-auto">
                    <label for="bereich" class="block text-sm font-medium text-gray-700 mb-1">Bereich</label>
                    <select id="bereich" name="bereich" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle Bereiche</option>
                        <?php foreach ($bereiche as $bereich): ?>
                            <option value="<?= $bereich['id'] ?>"><?= htmlspecialchars($bereich['name']) ?></option>
                        <?php endforeach; ?>
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
                                        <div class="flex items-center space-x-2">
                                            <span><?= htmlspecialchars($s['bereich_name'] ?? 'Nicht zugewiesen') ?></span>
                                            <!--<button type="button" onclick="openBereichModal(<?= $s['id'] ?>)" class="text-marina-600 hover:text-marina-800" title="Bereich zuweisen">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z"></path>
                                                </svg>
                                            </button>-->
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center space-x-2">
                                            <span><?= htmlspecialchars($s['mieter_name'] ?? 'Nicht zugewiesen') ?></span>
                                            <!--<button type="button" onclick="openMieterModal(<?= $s['id'] ?>)" class="text-marina-600 hover:text-marina-800" title="Mieter zuweisen">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z"></path>
                                                </svg>
                                            </button>-->
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <!--<button id="status-button-<?= $s['id'] ?>" class="text-gray-700 hover:text-marina-600" onclick="toggleStatusMenu(<?= $s['id'] ?>)" title="Status ändern">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                                </svg>
                                            </button>
                                            <div id="status-menu-<?= $s['id'] ?>" class="hidden absolute z-10 bg-white shadow-lg rounded-md py-1 mt-1 w-32 right-24">
                                                <a href="steckdosen.php?id=<?= $s['id'] ?>&status=aktiv" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Aktiv</a>
                                                <a href="steckdosen.php?id=<?= $s['id'] ?>&status=inaktiv" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Inaktiv</a>
                                                <a href="steckdosen.php?id=<?= $s['id'] ?>&status=defekt" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Defekt</a>
                                            </div>-->
                                            <a href="steckdosen_form.php?id=<?= $s['id'] ?>" class="text-marina-600 hover:text-marina-900" title="Bearbeiten">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['bezeichnung'])) ?>')" class="text-red-600 hover:text-red-900" title="Löschen">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                            </a>
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

<!-- Mieter zuweisen Modal -->
<div id="mieterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Mieter zuweisen</h3>
        <form method="POST" action="steckdosen.php">
            <input type="hidden" id="mieter_steckdose_id" name="steckdose_id" value="">
            <input type="hidden" name="assign_mieter" value="1">
            <div class="mb-4">
                <label for="mieter_id" class="block text-sm font-medium text-gray-700 mb-1">Mieter</label>
                <select id="mieter_id" name="mieter_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                    <option value="">-- Keiner --</option>
                    <?php foreach ($mieter as $m): ?>
                        <option value="<?= htmlspecialchars($m['id']) ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeMieterModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
                <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">Speichern</button>
            </div>
        </form>
    </div>
</div>

<!-- Bereich zuweisen Modal -->
<div id="bereichModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Bereich zuweisen</h3>
        <form method="POST" action="steckdosen.php">
            <input type="hidden" id="bereich_steckdose_id" name="steckdose_id" value="">
            <input type="hidden" name="assign_bereich" value="1">
            <div class="mb-4">
                <label for="bereich_id" class="block text-sm font-medium text-gray-700 mb-1">Bereich</label>
                <select id="bereich_id" name="bereich_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                    <option value="">-- Keiner --</option>
                    <?php foreach ($bereiche as $b): ?>
                        <option value="<?= htmlspecialchars($b['id']) ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeBereichModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
                <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">Speichern</button>
            </div>
        </form>
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

function openMieterModal(steckdoseId) {
    document.getElementById('mieter_steckdose_id').value = steckdoseId;
    document.getElementById('mieterModal').classList.remove('hidden');
}

function closeMieterModal() {
    document.getElementById('mieterModal').classList.add('hidden');
}

function openBereichModal(steckdoseId) {
    document.getElementById('bereich_steckdose_id').value = steckdoseId;
    document.getElementById('bereichModal').classList.remove('hidden');
}

function closeBereichModal() {
    document.getElementById('bereichModal').classList.add('hidden');
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
