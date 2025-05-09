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
$debug_messages = [];

if (!empty($selected_zaehler)) {
    foreach ($selected_zaehler as $zid) {
        $daten = $db->fetchAll("SELECT datum, stand FROM zaehlerstaende WHERE zaehler_id = ? AND datum BETWEEN ? AND ? ORDER BY datum ASC, id ASC", [$zid, $start_date, $end_date]);

        $zaehler_info = $db->fetchAll("SELECT z.zaehlernummer, z.hinweis, b.name AS bereich, m.name AS mieter FROM zaehler z LEFT JOIN steckdosen s ON z.steckdose_id = s.id LEFT JOIN bereiche b ON s.bereich_id = b.id LEFT JOIN mieter m ON s.mieter_id = m.id WHERE z.id = ?", [$zid]);
        $info = $zaehler_info[0] ?? [];
        $zaehlername = $info['zaehlernummer']
            . ($info['hinweis'] ? " – {$info['hinweis']}" : '')
            . ($info['bereich'] ? " – {$info['bereich']}" : '')
            . ($info['mieter'] ? " – {$info['mieter']}" : '');

        $debug_messages[] = "Zähler $zid: " . count($daten) . " Einträge gefunden.";
        foreach ($daten as $eintrag) {
            $debug_messages[] = " - " . $eintrag['datum'] . ": " . $eintrag['stand'] . " kWh";
        }

        if (count($daten) < 2) {
            $verbrauchsdaten[] = [
                'label' => $zaehlername,
                'verbrauch' => 0,
                'tooltip' => 'Nicht genug Daten im Zeitraum'
            ];
            continue;
        }

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
}

$nur_null_verbrauch = !empty($verbrauchsdaten) && array_reduce($verbrauchsdaten, function($carry, $v) {
    return $carry && $v['verbrauch'] === 0 && strpos($v['tooltip'], 'Nicht genug') === 0;
}, true);
?>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Verbrauchsanalyse</h1>

        <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div class="col-span-2">
                <label for="zaehlerSelect" class="block text-sm font-medium text-gray-700 mb-1">Zähler auswählen</label>
                <select id="zaehlerSelect" name="zaehler[]" multiple>
                    <?php foreach ($alle_zaehler as $z): ?>
                        <option value="<?= $z['id'] ?>" <?= in_array($z['id'], $selected_zaehler) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($z['zaehlernummer']) ?><?= $z['hinweis'] ? ' – ' . htmlspecialchars($z['hinweis']) : '' ?><?= $z['bereich'] ? ' – ' . htmlspecialchars($z['bereich']) : '' ?><?= $z['mieter'] ? ' – ' . htmlspecialchars($z['mieter']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <script>
                    new TomSelect('#zaehlerSelect', {
                        plugins: ['remove_button'],
                        maxItems: null,
                        placeholder: 'Zähler auswählen...',
                        create: false,
                        allowEmptyOption: true
                    });
                </script>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Zeitraum von</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-marina-500 focus:border-marina-500 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">bis</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-marina-500 focus:border-marina-500 p-2">
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">Anzeigen</button>
            </div>
        </form>

        <?php if (!empty($verbrauchsdaten)): ?>
            <div class="h-64">
                <canvas id="verbrauchChart" class="w-full h-full"></canvas>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('verbrauchChart').getContext('2d');
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode(array_column($verbrauchsdaten, 'label')) ?>,
                        datasets: [{
                            label: 'Verbrauch (kWh)',
                            data: <?= json_encode(array_column($verbrauchsdaten, 'verbrauch')) ?>,
                            backgroundColor: '#2563eb'
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const tooltips = <?= json_encode(array_column($verbrauchsdaten, 'tooltip')) ?>;
                                        return tooltips[context.dataIndex].split('\n');
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Verbrauch pro Zähler im gewählten Zeitraum'
                            }
                        },
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'kWh'
                                }
                            }
                        }
                    }
                });
            </script>
        <?php endif; ?>

        <?php if ($nur_null_verbrauch): ?>
            <div class="text-red-700 bg-red-100 border border-red-300 p-4 rounded mt-6">
                Hinweis: Für die ausgewählten Zähler liegen nicht genügend Zählerstände im gewählten Zeitraum vor.
            </div>
        <?php endif; ?>

        <?php if ($is_admin && !empty($debug_messages)): ?>
            <div class="mt-6 bg-gray-100 border border-gray-400 text-sm text-gray-800 p-4 rounded">
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
