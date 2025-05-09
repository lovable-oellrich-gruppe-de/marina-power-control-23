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

$selected_zaehler = isset($_GET['zaehler']) && is_array($_GET['zaehler']) ? array_map('intval', $_GET['zaehler']) : [];
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 year'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$alle_zaehler = $db->fetchAll("SELECT z.id, z.zaehlernummer, z.hinweis, b.name AS bereich, m.name AS mieter
    FROM zaehler z
    LEFT JOIN steckdosen s ON z.steckdose_id = s.id
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    LEFT JOIN mieter m ON s.mieter_id = m.id
    ORDER BY z.zaehlernummer");

$verbrauchsdaten = [];
$zeitreihen = [];
$debug_messages = [];

if (!empty($selected_zaehler)) {
    foreach ($selected_zaehler as $zid) {
        $daten = $db->fetchAll("SELECT datum, stand FROM zaehlerstaende WHERE zaehler_id = ? AND datum BETWEEN ? AND ? ORDER BY datum ASC, id ASC", [$zid, $start_date, $end_date]);

        $info = $db->fetchOne("SELECT z.zaehlernummer, z.hinweis, b.name AS bereich, m.name AS mieter FROM zaehler z LEFT JOIN steckdosen s ON z.steckdose_id = s.id LEFT JOIN bereiche b ON s.bereich_id = b.id LEFT JOIN mieter m ON s.mieter_id = m.id WHERE z.id = ?", [$zid]);

        $zaehlername = $info['zaehlernummer']
            . ($info['hinweis'] ? " – {$info['hinweis']}" : '')
            . ($info['bereich'] ? " – {$info['bereich']}" : '')
            . ($info['mieter'] ? " – {$info['mieter']}" : '');

        $debug_messages[] = "Zähler $zid: " . count($daten) . " Einträge gefunden.";
        foreach ($daten as $eintrag) {
            $debug_messages[] = " - {$eintrag['datum']}: {$eintrag['stand']} kWh";
        }

        if (count($daten) >= 2) {
            $start = (float)$daten[0]['stand'];
            $start_datum = $daten[0]['datum'];
            $end = (float)$daten[count($daten) - 1]['stand'];
            $end_datum = $daten[count($daten) - 1]['datum'];
            $verbrauch = round($end - $start, 2);

            $verbrauchsdaten[] = [
                'label' => $zaehlername,
                'verbrauch' => $verbrauch,
                'tooltip' => "$zaehlername\n$start_datum: $start kWh\n$end_datum: $end kWh\nVerbrauch: $verbrauch kWh"
            ];
        }

        $zeitreihen[] = [
            'label' => $zaehlername,
            'data' => array_map(fn($d) => [
                'x' => $d['datum'] . 'T00:00:00',
                'y' => (float)$d['stand']
            ], $daten)
        ];
    }
}
$hasZeitreihen = !empty($zeitreihen) && count($zeitreihen[0]['data'] ?? []) > 0;
?>

<!-- Bestehender HTML-Code bleibt erhalten -->

<?php if ($is_admin && !empty($debug_messages)): ?>
    <div class="mt-6 bg-gray-100 border border-gray-400 text-sm text-gray-800 p-4 rounded">
        <h2 class="font-semibold mb-2">Debug-Ausgaben</h2>
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ($debug_messages as $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
