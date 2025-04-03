
<?php
// Wichtig: Keine Leerzeilen oder Whitespace vor dem öffnenden PHP-Tag
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Dashboard-Daten abrufen - Korrigierte Abfragen
$totalMieter = $db->fetchOne("SELECT COUNT(*) as count FROM mieter WHERE aktiv = 1")['count'] ?? 0;
$totalSteckdosen = $db->fetchOne("SELECT COUNT(*) as count FROM steckdosen WHERE status = 'aktiv'")['count'] ?? 0;
// Korrigierte Abfrage für Zähler - 'status' geändert zu 'ist_ausgebaut = 0'
$totalZaehler = $db->fetchOne("SELECT COUNT(*) as count FROM zaehler WHERE ist_ausgebaut = 0")['count'] ?? 0;

// Letzte Zählerstände abrufen - Korrigierte JOIN-Bedingung
$letzteZaehlerstaende = $db->fetchAll("
    SELECT z.id, z.stand, z.datum, m.name as mieter_name, zr.seriennummer 
    FROM zaehlerstaende z
    JOIN zaehler zr ON z.zaehler_id = zr.id
    LEFT JOIN steckdosen s ON zr.id = s.id
    LEFT JOIN mieter m ON s.mieter_id = m.id
    ORDER BY z.datum DESC
    LIMIT 5
");

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Mieter Karte -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Aktive Mieter</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900"><?= $totalMieter ?></div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="mieter.php" class="font-medium text-marina-600 hover:text-marina-900">Alle anzeigen</a>
                    </div>
                </div>
            </div>

            <!-- Steckdosen Karte -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Aktive Steckdosen</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900"><?= $totalSteckdosen ?></div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="steckdosen.php" class="font-medium text-marina-600 hover:text-marina-900">Alle anzeigen</a>
                    </div>
                </div>
            </div>

            <!-- Zähler Karte -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Aktive Zähler</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900"><?= $totalZaehler ?></div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="zaehler.php" class="font-medium text-marina-600 hover:text-marina-900">Alle anzeigen</a>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="mt-8 text-xl font-bold text-gray-900">Neueste Zählerstände</h2>
        <div class="mt-2 overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zähler</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mieter</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stand</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($letzteZaehlerstaende)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Keine Zählerstände vorhanden</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($letzteZaehlerstaende as $stand): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($stand['seriennummer']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($stand['mieter_name'] ?? 'Nicht zugewiesen') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($stand['stand']) ?> kWh</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d.m.Y H:i', strtotime($stand['datum'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-right">
            <a href="zaehlerstaende.php" class="text-marina-600 hover:text-marina-900 text-sm font-medium">Alle Zählerstände anzeigen →</a>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
