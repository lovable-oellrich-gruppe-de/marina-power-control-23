<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$is_admin = $auth->isAdmin();

// Hauptzähler: Zähler ohne parent_id, die mindestens einen Unterzähler haben
$hauptzaehler = $db->fetchAll("SELECT z.id, z.zaehlernummer, z.hinweis, b.name AS bereich
FROM zaehler z
LEFT JOIN steckdosen s ON z.steckdose_id = s.id
LEFT JOIN bereiche b ON s.bereich_id = b.id
WHERE z.steckdose_id IS NULL
  AND z.ist_ausgebaut = 0
  AND EXISTS (
      SELECT 1 FROM zaehler u WHERE u.parent_id = z.id
  )");

$debug_messages = [];
$hauptdaten = [];

foreach ($hauptzaehler as $hz) {
    $haupt_id = $hz['id'];

    // Letzter Zählerstand des Hauptzählers
    $hauptstand = $db->fetchOne("SELECT stand FROM zaehlerstaende WHERE zaehler_id = ? ORDER BY datum DESC, id DESC LIMIT 1", [$haupt_id]);
    $hauptwert = $hauptstand ? (float)$hauptstand['stand'] : null;

    // Unterzähler
    $unterzaehler = $db->fetchAll("SELECT id, zaehlernummer FROM zaehler WHERE parent_id = ?", [$haupt_id]);
    $unter_summe = 0;
    $unter_debug = [];
    foreach ($unterzaehler as $uz) {
        $last = $db->fetchOne("SELECT stand FROM zaehlerstaende WHERE zaehler_id = ? ORDER BY datum DESC, id DESC LIMIT 1", [$uz['id']]);
        if ($last) {
            $wert = (float)$last['stand'];
            $unter_summe += $wert;
            $unter_debug[] = $uz['zaehlernummer'] . ': ' . $wert . ' kWh';
        }
    }

    $differenz = $hauptwert !== null ? round($hauptwert - $unter_summe, 2) : null;

    $hauptdaten[] = [
        'zaehlernummer' => $hz['zaehlernummer'],
        'hinweis' => $hz['hinweis'],
        'hauptwert' => $hauptwert,
        'unter_summe' => $unter_summe,
        'differenz' => $differenz,
        'unter_debug' => $unter_debug
    ];

    if ($is_admin) {
        $debug_messages[] = "Hauptzähler {$hz['zaehlernummer']}: Hauptwert = $hauptwert, Summe Unterzähler = $unter_summe, Differenz = $differenz";
        foreach ($unter_debug as $line) {
            $debug_messages[] = ' - ' . $line;
        }
    }
}
?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Hauptzähler – Verbrauchsanalyse</h1>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Zählernummer</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Hinweis</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Letzter Stand (kWh)</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Summe Unterzähler (kWh)</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Differenz (kWh)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($hauptdaten as $row): ?>
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800"><?= htmlspecialchars($row['zaehlernummer']) ?></td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800"><?= htmlspecialchars($row['hinweis']) ?></td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800"><?= $row['hauptwert'] !== null ? $row['hauptwert'] : '–' ?></td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800"><?= $row['unter_summe'] ?></td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800"><?= $row['differenz'] !== null ? $row['differenz'] : '–' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($is_admin && !empty($debug_messages)): ?>
            <div class="mt-6 bg-gray-100 border border-gray-300 text-sm text-gray-800 p-4 rounded">
                <h2 class="font-semibold mb-2">Debug-Ausgaben</h2>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($debug_messages as $msg): ?>
                        <li><?= $msg ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
