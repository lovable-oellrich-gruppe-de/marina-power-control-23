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
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 year'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Alle Zähler laden für Auswahl
$alle_zaehler = $db->fetchAll("SELECT zaehler.id, zaehler.zaehlernummer, steckdosen.bezeichnung AS steckdose, bereiche.name AS bereich
    FROM zaehler
    LEFT JOIN steckdosen ON zaehler.steckdose_id = steckdosen.id
    LEFT JOIN bereiche ON steckdosen.bereich_id = bereiche.id
    ORDER BY zaehler.zaehlernummer");

// Verbrauchsdaten für alle ausgewählten Zähler laden
$verbrauchsdaten = [];
$labels = [];
$werte_map = [];
$letzte_stand_map = [];
$debug_messages = [];

if (!empty($selected_zaehler)) {
    foreach ($selected_zaehler as $zid) {
        $daten = $db->fetchAll("SELECT zaehler.id, zaehler.zaehlernummer, zaehlerstaende.datum, zaehlerstaende.stand 
            FROM zaehler 
            LEFT JOIN zaehlerstaende ON zaehlerstaende.zaehler_id = zaehler.id 
            WHERE zaehler.id = ? AND zaehlerstaende.datum BETWEEN ? AND ? 
            ORDER BY zaehlerstaende.datum ASC", 
            [$zid, $start_date, $end_date]);

        $debug_messages[] = "Zähler $zid: " . count($daten) . " Einträge gefunden.";
        $debug_messages[] = "Daten von Zähler $zid:<pre>" . print_r($daten, true) . "</pre>";

        $vorheriger_stand = null;
        if ($daten) {
            foreach ($daten as $row) {
                if (!empty($row['datum'])) {
                    $datum = date('Y-m-d', strtotime($row['datum']));
                    $labels[$datum] = true;

                    $zaehlernummer = $row['zaehlernummer'];
                    $werte_map[$row['id']]['zaehlernummer'] = $zaehlernummer;

                    // Verbrauch berechnen
                    if ($vorheriger_stand !== null) {
                        $verbrauch = (float)$row['stand'] - $vorheriger_stand;
                        $werte_map[$row['id']]['werte'][$datum] = $verbrauch > 0 ? $verbrauch : 0;
                    } else {
                        $werte_map[$row['id']]['werte'][$datum] = 0; // Keine Differenz berechenbar
                    }

                    $vorheriger_stand = (float)$row['stand'];
                    $letzte_stand_map[$row['id']][$datum] = $row['stand'];
                }
            }
        }
    }
    $labels = array_keys($labels);
    sort($labels);
}
$debug_messages[] = "Ausgewählte Zähler: " . implode(',', $selected_zaehler);
$debug_messages[] = "Startdatum: $start_date";
$debug_messages[] = "Enddatum: $end_date";
$debug_messages[] = "Labels: <pre>" . print_r($labels, true) . "</pre>";
$debug_messages[] = "Werte Map: <pre>" . print_r($werte_map, true) . "</pre>";

?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Verbrauchsanalyse</h1>

        <!-- Auswahlformular -->
        <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Zähler auswählen</label>
                <select name="zaehler[]" multiple class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-marina-500 focus:border-marina-500 p-2">
                    <?php foreach ($alle_zaehler as $z): ?>
                        <option value="<?= $z['id'] ?>" <?= in_array($z['id'], $selected_zaehler) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($z['zaehlernummer']) ?><?= $z['steckdose'] ? ' – ' . htmlspecialchars($z['steckdose']) : '' ?><?= $z['bereich'] ? ' – ' . htmlspecialchars($z['bereich']) : '' ?>
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

        <?php if (!empty($debug_messages)): ?>
            <div class="mt-6 bg-gray-100 border border-gray-400 text-sm text-gray-800 p-4 rounded">
                <h2 class="font-semibold mb-2">Debug-Ausgaben</h2>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($debug_messages as $msg): ?>
                        <li><?= $msg ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($werte_map)): ?>
            <div class="bg-white rounded-lg shadow-md p-4 mt-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Verbrauch ausgewählter Zähler</h2>
                <div id="chart-multi" style="height: 500px;"></div>
                <script>
                    const labels = <?= json_encode($labels) ?>;
                    const datasets = [
                        <?php foreach ($werte_map as $zid => $z): ?>{
                            name: "<?= addslashes($z['zaehlernummer']) ?>",
                            values: labels.map(label => <?= json_encode($z['werte']) ?>[label] ?? 0),
                            meta: labels.map(label => "Stand: <?= addslashes($letzte_stand_map[$zid][label] ?? '-') ?> kWh")
                        },<?php endforeach; ?>
                    ];

                    new Chartisan({
                        el: '#chart-multi',
                        data: {
                            chart: { type: 'bar' },
                            labels: labels,
                            datasets: datasets.map(ds => ({
                                name: ds.name,
                                values: ds.values
                            }))
                        },
                        hooks: new ChartisanHooks()
                            .datasets('bar')
                            .colors(['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'])
                            .tooltip()
                            .customTooltips({
                                enabled: true,
                                mode: 'index',
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        const dataset = datasets[tooltipItem.datasetIndex];
                                        return dataset.name + ': ' + tooltipItem.yLabel + ' kWh (' + dataset.meta[tooltipItem.index] + ')';
                                    }
                                }
                            })
                            .responsive(true)
                    });
                </script>
            </div>
        <?php elseif (!empty($selected_zaehler)): ?>
            <div class="text-red-700 bg-red-100 border border-red-300 p-4 rounded mt-6">Keine Daten für die ausgewählten Zähler gefunden.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
