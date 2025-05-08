<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Zählerauswahl über GET-Parameter
$selected_zaehler = isset($_GET['zaehler']) && is_array($_GET['zaehler']) ? array_map('intval', $_GET['zaehler']) : [];
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 month'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Alle Zähler für Dropdown-Auswahl
$alle_zaehler = $db->fetchAll("SELECT zaehler.id, zaehler.zaehlernummer, zaehler.hinweis FROM zaehler ORDER BY zaehler.zaehlernummer");

$verbrauchsdaten = [];
$debug_messages = [];

if (!empty($selected_zaehler)) {
    foreach ($selected_zaehler as $zid) {
        $daten = $db->fetchAll("SELECT datum, stand FROM zaehlerstaende WHERE zaehler_id = ? AND datum BETWEEN ? AND ? ORDER BY datum ASC, id ASC", [$zid, $start_date, $end_date]);

        $zaehler_info = $db->fetch("SELECT zaehlernummer, hinweis FROM zaehler WHERE id = ?", [$zid]);
        $zaehlername = $zaehler_info['zaehlernummer'] . ($zaehler_info['hinweis'] ? " (" . $zaehler_info['hinweis'] . ")" : '');

        $debug_messages[] = "Zähler $zid: " . count($daten) . " Einträge gefunden.";

        if (count($daten) < 2) {
            $verbrauchsdaten[] = [
                'label' => $zaehlername,
                'verbrauch' => 0,
                'tooltip' => 'Nicht genug Daten im Zeitraum'
            ];
            continue;
        }

        $start = (float)$daten[0]['stand'];
        $end = (float)$daten[count($daten) - 1]['stand'];
        $verbrauch = round($end - $start, 2);

        $verbrauchsdaten[] = [
            'label' => $zaehlername,
            'verbrauch' => $verbrauch,
            'tooltip' => "von $start bis $end kWh"
        ];
    }
}

?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Verbrauchsanalyse</h1>

        <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Zähler auswählen</label>
                <select name="zaehler[]" multiple class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-marina-500 focus:border-marina-500 p-2">
                    <?php foreach ($alle_zaehler as $z): ?>
                        <option value="<?= $z['id'] ?>" <?= in_array($z['id'], $selected_zaehler) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($z['zaehlernummer']) ?><?= $z['hinweis'] ? ' – ' . htmlspecialchars($z['hinweis']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Zeitraum von</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-marina-500 focus:border-marina-500 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">bis</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-marina-500 focus:border-marina-500 p-2">
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="mt-2 px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">Anzeigen</button>
            </div>
        </form>

        <?php if (!empty($verbrauchsdaten)): ?>
            <canvas id="verbrauchChart" class="w-full h-96"></canvas>
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
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const tooltips = <?= json_encode(array_column($verbrauchsdaten, 'tooltip')) ?>;
                                        return tooltips[context.dataIndex];
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
        <?php elseif (!empty($selected_zaehler)): ?>
            <div class="text-red-700 bg-red-100 border border-red-300 p-4 rounded mt-6">Keine gültigen Verbrauchsdaten im gewählten Zeitraum gefunden.</div>
        <?php endif; ?>

        <div class="mt-6 bg-gray-100 border border-gray-400 text-sm text-gray-800 p-4 rounded">
            <h2 class="font-semibold mb-2">Debug-Ausgaben</h2>
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($debug_messages as $msg): ?>
                    <li><?= $msg ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
