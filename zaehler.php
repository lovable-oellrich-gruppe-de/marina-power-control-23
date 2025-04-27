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

// Erfolgs- oder Fehlermeldungen übernehmen
$success_message = isset($_GET['success']) ? $_GET['success'] : null;
$error_message = null;

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
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Zähler</h1>
            <a href="zaehler_form.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-marina-600 hover:bg-marina-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
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
                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Suchen
                </button>
                <?php if (!empty($search)): ?>
                <a href="zaehler.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Zurücksetzen
                </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Zählerliste mit erhöhter Breite -->
        <div class="mt-4 overflow-x-auto w-full">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="zaehler.php?search=<?= urlencode($search) ?>&order_by=zaehlernummer&order_dir=<?= $orderBy == 'zaehlernummer' && $orderDir == 'ASC' ? 'DESC' : 'ASC' ?>" class="flex items-center">
                                        Zählernummer
                                        <?php if ($orderBy == 'zaehlernummer'): ?>
                                            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <?php if ($orderDir == 'ASC'): ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                <?php else: ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                <?php endif; ?>
                                            </svg>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="zaehler.php?search=<?= urlencode($search) ?>&order_by=hersteller&order_dir=<?= $orderBy == 'hersteller' && $orderDir == 'ASC' ? 'DESC' : 'ASC' ?>" class="flex items-center">
                                        Hersteller/Modell
                                        <?php if ($orderBy == 'hersteller'): ?>
                                            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <?php if ($orderDir == 'ASC'): ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                <?php else: ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                <?php endif; ?>
                                            </svg>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="zaehler.php?search=<?= urlencode($search) ?>&order_by=installiert_am&order_dir=<?= $orderBy == 'installiert_am' && $orderDir == 'ASC' ? 'DESC' : 'ASC' ?>" class="flex items-center">
                                        Installiert am
                                        <?php if ($orderBy == 'installiert_am'): ?>
                                            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <?php if ($orderDir == 'ASC'): ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                <?php else: ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                <?php endif; ?>
                                            </svg>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="zaehler.php?search=<?= urlencode($search) ?>&order_by=letzte_wartung&order_dir=<?= $orderBy == 'letzte_wartung' && $orderDir == 'ASC' ? 'DESC' : 'ASC' ?>" class="flex items-center">
                                        Letzte Wartung
                                        <?php if ($orderBy == 'letzte_wartung'): ?>
                                            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <?php if ($orderDir == 'ASC'): ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                <?php else: ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                <?php endif; ?>
                                            </svg>
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
                                            <div class="flex items-center space-x-4">
                                                <a href="zaehler_form.php?id=<?= $z['id'] ?>" class="text-marina-600 hover:text-marina-900 p-1" title="Bearbeiten">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                
                                                <?php if ($auth->isAdmin()): ?>
                                                    <a href="#" onclick="confirmDelete(<?= $z['id'] ?>)" class="text-red-600 hover:text-red-900 p-1" title="Löschen">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="zaehlerstaende.php?zaehler_id=<?= $z['id'] ?>" class="text-gray-600 hover:text-gray-900 p-1" title="Zählerstände">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
