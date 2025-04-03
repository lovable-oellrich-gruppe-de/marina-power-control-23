
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Löschen eines Zählerstands, wenn ID übergeben wurde
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $db->query("DELETE FROM zaehlerstaende WHERE id = ?", [$id]);
    
    if ($db->affectedRows() > 0) {
        $success = "Zählerstand wurde erfolgreich gelöscht.";
    } else {
        $error = "Fehler beim Löschen des Zählerstands.";
    }
}

// Prüfen ob ein Zählerstand als abgerechnet markiert werden soll
if (isset($_GET['markAbgerechnet']) && is_numeric($_GET['markAbgerechnet'])) {
    $id = $_GET['markAbgerechnet'];
    $result = $db->query("UPDATE zaehlerstaende SET ist_abgerechnet = 1 WHERE id = ?", [$id]);
    
    if ($db->affectedRows() >= 0) {
        $success = "Zählerstand wurde als abgerechnet markiert.";
    } else {
        $error = "Fehler beim Markieren des Zählerstands.";
    }
}

// Prüfen ob ein Zählerstand als nicht abgerechnet markiert werden soll
if (isset($_GET['markUnabgerechnet']) && is_numeric($_GET['markUnabgerechnet'])) {
    $id = $_GET['markUnabgerechnet'];
    $result = $db->query("UPDATE zaehlerstaende SET ist_abgerechnet = 0 WHERE id = ?", [$id]);
    
    if ($db->affectedRows() >= 0) {
        $success = "Zählerstand wurde als nicht abgerechnet markiert.";
    } else {
        $error = "Fehler beim Markieren des Zählerstands.";
    }
}

// Vorbereitung der Filter-Parameter
$where_clauses = [];
$params = [];

// Suche nach Zählernummer oder Steckdose
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $where_clauses[] = "(z.zaehlernummer LIKE ? OR s.bezeichnung LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Filter nach Bereich
if (isset($_GET['bereich']) && !empty($_GET['bereich'])) {
    $bereich_id = $_GET['bereich'];
    $where_clauses[] = "s.bereich_id = ?";
    $params[] = $bereich_id;
}

// Filter nach Abrechnungsstatus
if (isset($_GET['abgerechnet']) && $_GET['abgerechnet'] !== '') {
    $abgerechnet = $_GET['abgerechnet'] === '1' ? 1 : 0;
    $where_clauses[] = "zs.ist_abgerechnet = ?";
    $params[] = $abgerechnet;
}

// Filter nach Mieter
if (isset($_GET['mieter']) && !empty($_GET['mieter'])) {
    $mieter_id = $_GET['mieter'];
    $where_clauses[] = "s.mieter_id = ?";
    $params[] = $mieter_id;
}

// Filter nach Zeitraum
if (isset($_GET['von']) && !empty($_GET['von'])) {
    $von = $_GET['von'];
    $where_clauses[] = "zs.datum >= ?";
    $params[] = $von;
}

if (isset($_GET['bis']) && !empty($_GET['bis'])) {
    $bis = $_GET['bis'];
    $where_clauses[] = "zs.datum <= ?";
    $params[] = $bis;
}

// SQL Query zusammenbauen
$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Bereiche für Filter laden
$bereiche = $db->fetchAll("SELECT id, name FROM bereiche ORDER BY name");

// Mieter für Filter laden
$mieter = $db->fetchAll("SELECT id, CONCAT(vorname, ' ', name) AS vollname FROM mieter ORDER BY name");

// Zählerstände aus der Datenbank abrufen mit Joins zu Zähler, Steckdose, Bereich und Mieter
$sql = "
    SELECT 
        zs.*,
        z.zaehlernummer,
        s.bezeichnung AS steckdose_bezeichnung,
        b.name AS bereich_name,
        CONCAT(m.vorname, ' ', m.name) AS mieter_name
    FROM 
        zaehlerstaende zs
    LEFT JOIN 
        zaehler z ON zs.zaehler_id = z.id
    LEFT JOIN 
        steckdosen s ON zs.steckdose_id = s.id
    LEFT JOIN 
        bereiche b ON s.bereich_id = b.id
    LEFT JOIN 
        mieter m ON s.mieter_id = m.id
    $where_sql
    ORDER BY 
        zs.datum DESC, zs.id DESC
";

$zaehlerstaende = $db->fetchAll($sql, $params);

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Zählerstände verwalten</h1>
            <a href="zaehlerstaende_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                Neuer Zählerstand
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

        <!-- Filter- und Suchoptionen -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <form method="GET" action="zaehlerstaende.php" class="flex flex-wrap gap-4">
                <div class="w-full md:w-auto">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Suche</label>
                    <input type="text" id="search" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" placeholder="Zählernummer oder Steckdose..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                </div>
                
                <div class="w-full md:w-auto">
                    <label for="bereich" class="block text-sm font-medium text-gray-700 mb-1">Bereich</label>
                    <select id="bereich" name="bereich" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle Bereiche</option>
                        <?php foreach ($bereiche as $bereich): ?>
                            <option value="<?= $bereich['id'] ?>" <?= (isset($_GET['bereich']) && $_GET['bereich'] == $bereich['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($bereich['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-full md:w-auto">
                    <label for="mieter" class="block text-sm font-medium text-gray-700 mb-1">Mieter</label>
                    <select id="mieter" name="mieter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle Mieter</option>
                        <?php foreach ($mieter as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= (isset($_GET['mieter']) && $_GET['mieter'] == $m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['vollname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="w-full md:w-auto">
                    <label for="abgerechnet" class="block text-sm font-medium text-gray-700 mb-1">Abrechnungsstatus</label>
                    <select id="abgerechnet" name="abgerechnet" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle</option>
                        <option value="1" <?= (isset($_GET['abgerechnet']) && $_GET['abgerechnet'] == '1') ? 'selected' : '' ?>>Abgerechnet</option>
                        <option value="0" <?= (isset($_GET['abgerechnet']) && $_GET['abgerechnet'] == '0') ? 'selected' : '' ?>>Nicht abgerechnet</option>
                    </select>
                </div>
                
                <div class="w-full md:w-auto">
                    <label for="von" class="block text-sm font-medium text-gray-700 mb-1">Datum von</label>
                    <input type="date" id="von" name="von" value="<?= isset($_GET['von']) ? htmlspecialchars($_GET['von']) : '' ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                </div>
                
                <div class="w-full md:w-auto">
                    <label for="bis" class="block text-sm font-medium text-gray-700 mb-1">Datum bis</label>
                    <input type="date" id="bis" name="bis" value="<?= isset($_GET['bis']) ? htmlspecialchars($_GET['bis']) : '' ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                </div>
                
                <div class="w-full md:w-auto flex items-end">
                    <button type="submit" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                        Filtern
                    </button>
                    
                    <a href="zaehlerstaende.php" class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                        Zurücksetzen
                    </a>
                </div>
            </form>
        </div>

        <!-- Zählerstände-Tabelle -->
        <div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zähler</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Steckdose</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bereich</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mieter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stand</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verbrauch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Abgerechnet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($zaehlerstaende)): ?>
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Keine Zählerstände gefunden
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($zaehlerstaende as $zs): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($zs['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars(date('d.m.Y', strtotime($zs['datum']))) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($zs['zaehlernummer']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($zs['steckdose_bezeichnung'] ?? 'Nicht zugewiesen') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($zs['bereich_name'] ?? 'Nicht zugewiesen') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($zs['mieter_name'] ?? 'Nicht zugewiesen') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        <?= htmlspecialchars(number_format($zs['stand'], 2, ',', '.')) ?> kWh
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= $zs['verbrauch'] ? htmlspecialchars(number_format($zs['verbrauch'], 2, ',', '.')) . ' kWh' : '-' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($zs['ist_abgerechnet']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Ja
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Nein
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <!-- Abrechnungs-Toggle -->
                                            <?php if ($zs['ist_abgerechnet']): ?>
                                                <a href="zaehlerstaende.php?markUnabgerechnet=<?= $zs['id'] ?>" class="text-yellow-600 hover:text-yellow-900" title="Als nicht abgerechnet markieren">
                                                    Nicht abgerechnet
                                                </a>
                                            <?php else: ?>
                                                <a href="zaehlerstaende.php?markAbgerechnet=<?= $zs['id'] ?>" class="text-green-600 hover:text-green-900" title="Als abgerechnet markieren">
                                                    Abgerechnet
                                                </a>
                                            <?php endif; ?>

                                            <a href="zaehlerstaende_form.php?id=<?= $zs['id'] ?>" class="text-marina-600 hover:text-marina-900">Bearbeiten</a>
                                            <a href="#" onclick="confirmDelete(<?= $zs['id'] ?>, '<?= date('d.m.Y', strtotime($zs['datum'])) ?>', '<?= htmlspecialchars(addslashes($zs['zaehlernummer'])) ?>')" class="text-red-600 hover:text-red-900">
                                                Löschen
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
        <h3 class="text-lg font-medium text-gray-900 mb-2">Zählerstand löschen</h3>
        <p class="text-gray-500 mb-4">Möchten Sie den Zählerstand vom <span id="zaehlerDatum"></span> für Zähler <span id="zaehlerNummer"></span> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
            <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, datum, zaehlerNummer) {
    document.getElementById('zaehlerDatum').textContent = datum;
    document.getElementById('zaehlerNummer').textContent = zaehlerNummer;
    document.getElementById('deleteLink').href = 'zaehlerstaende.php?delete=' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php
require_once 'includes/footer.php';
?>
