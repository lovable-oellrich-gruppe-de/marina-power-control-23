
<?php
// Wichtige Einbindungen für die Anwendung
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Abfrageparameter für Suchfunktion und Sortierung
$search = isset($_GET['search']) ? $_GET['search'] : '';
$orderBy = isset($_GET['order_by']) ? $_GET['order_by'] : 'installiert_am';
$orderDir = isset($_GET['order_dir']) ? $_GET['order_dir'] : 'DESC';

// Löschvorgang verarbeiten, wenn Parameter vorhanden
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    // Nur Admin darf löschen
    if ($auth->isAdmin()) {
        $zaehler_id = (int)$_GET['delete'];
        
        // Prüfen, ob Zähler in Benutzung ist
        $in_use = $db->fetchOne("SELECT COUNT(*) as count FROM zaehlerstaende WHERE zaehler_id = ?", [$zaehler_id])['count'] ?? 0;
        
        if ($in_use > 0) {
            $error_message = "Dieser Zähler hat Messdaten und kann nicht gelöscht werden. Markieren Sie ihn stattdessen als ausgebaut.";
        } else {
            // Zähler löschen
            $db->query("DELETE FROM zaehler WHERE id = ?", [$zaehler_id]);
            
            if ($db->affectedRows() > 0) {
                $success_message = "Zähler wurde erfolgreich gelöscht.";
            } else {
                $error_message = "Zähler konnte nicht gelöscht werden.";
            }
        }
    } else {
        $error_message = "Sie haben nicht die erforderlichen Rechte, um Zähler zu löschen.";
    }
}

// SQL für Zählerabfrage mit Suchfunktion
$sql = "SELECT * FROM zaehler WHERE 1=1";

// Suchparameter ergänzen
if (!empty($search)) {
    $sql .= " AND (zaehlernummer LIKE ? OR hersteller LIKE ? OR modell LIKE ? OR seriennummer LIKE ?)";
    $search_params = ["%$search%", "%$search%", "%$search%", "%$search%"];
} else {
    $search_params = [];
}

// Sortierung
$valid_order_by = ['zaehlernummer', 'hersteller', 'modell', 'installiert_am', 'letzte_wartung'];
$valid_order_dir = ['ASC', 'DESC'];

if (!in_array($orderBy, $valid_order_by)) $orderBy = 'installiert_am';
if (!in_array($orderDir, $valid_order_dir)) $orderDir = 'DESC';

$sql .= " ORDER BY $orderBy $orderDir";

// Zähler abrufen
$zaehler = $db->fetchAll($sql, $search_params);

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Zähler</h1>
            <a href="zaehler_form.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-marina-600 hover:bg-marina-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                Neuen Zähler erstellen
            </a>
        </div>
        
        <!-- Erfolgs- oder Fehlermeldungen -->
        <?php if (isset($success_message)): ?>
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($success_message) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Suchformular -->
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg p-4">
            <form method="GET" action="zaehler.php" class="flex items-center space-x-3">
                <div class="flex-1">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Suche nach Zählernummer, Hersteller, Modell..." class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md p-2">
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-marina-600 hover:bg-marina-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                    Suchen
                </button>
                <?php if (!empty($search)): ?>
                <a href="zaehler.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                    Zurücksetzen
                </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Zählerliste -->
        <div class="mt-4 overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="zaehler.php?search=<?= urlencode($search) ?>&order_by=zaehlernummer&order_dir=<?= $orderBy == 'zaehlernummer' && $orderDir == 'ASC' ? 'DESC' : 'ASC' ?>">
                                        Zählernummer
                                        <?php if ($orderBy == 'zaehlernummer'): ?>
                                            <?= $orderDir == 'ASC' ? '▲' : '▼' ?>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="zaehler.php?search=<?= urlencode($search) ?>&order_by=hersteller&order_dir=<?= $orderBy == 'hersteller' && $orderDir == 'ASC' ? 'DESC' : 'ASC' ?>">
                                        Hersteller/Modell
                                        <?php if ($orderBy == 'hersteller'): ?>
                                            <?= $orderDir == 'ASC' ? '▲' : '▼' ?>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="zaehler.php?search=<?= urlencode($search) ?>&order_by=installiert_am&order_dir=<?= $orderBy == 'installiert_am' && $orderDir == 'ASC' ? 'DESC' : 'ASC' ?>">
                                        Installiert am
                                        <?php if ($orderBy == 'installiert_am'): ?>
                                            <?= $orderDir == 'ASC' ? '▲' : '▼' ?>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="zaehler.php?search=<?= urlencode($search) ?>&order_by=letzte_wartung&order_dir=<?= $orderBy == 'letzte_wartung' && $orderDir == 'ASC' ? 'DESC' : 'ASC' ?>">
                                        Letzte Wartung
                                        <?php if ($orderBy == 'letzte_wartung'): ?>
                                            <?= $orderDir == 'ASC' ? '▲' : '▼' ?>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($zaehler)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Keine Zähler gefunden</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($zaehler as $z): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($z['zaehlernummer']) ?>
                                            <?php if (!empty($z['seriennummer'])): ?>
                                                <div class="text-xs text-gray-500">SN: <?= htmlspecialchars($z['seriennummer']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($z['hersteller'] ?? '-') ?>
                                            <?php if (!empty($z['modell'])): ?>
                                                <div class="text-xs"><?= htmlspecialchars($z['modell']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('d.m.Y', strtotime($z['installiert_am'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= !empty($z['letzte_wartung']) ? date('d.m.Y', strtotime($z['letzte_wartung'])) : '-' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($z['ist_ausgebaut']): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Ausgebaut
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Aktiv
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="zaehler_form.php?id=<?= $z['id'] ?>" class="text-marina-600 hover:text-marina-900">Bearbeiten</a>
                                            
                                            <?php if ($auth->isAdmin()): ?>
                                                <a href="#" onclick="confirmDelete(<?= $z['id'] ?>)" class="ml-2 text-red-600 hover:text-red-900">Löschen</a>
                                            <?php endif; ?>
                                            
                                            <a href="zaehlerstaende.php?zaehler_id=<?= $z['id'] ?>" class="ml-2 text-gray-600 hover:text-gray-900">Zählerstände</a>
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
</div>

<script>
function confirmDelete(id) {
    if (confirm('Sind Sie sicher, dass Sie diesen Zähler löschen möchten? Dies kann nicht rückgängig gemacht werden.')) {
        window.location.href = 'zaehler.php?delete=' + id;
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>
