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

// Alle Zähler laden für Auswahl
$alle_zaehler = $db->fetchAll("SELECT z.id, z.zaehlernummer, s.bezeichnung AS steckdose, b.name AS bereich
    FROM zaehler z
    LEFT JOIN steckdosen s ON z.steckdose_id = s.id
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    ORDER BY z.zaehlernummer");

// Verbrauchsdaten für alle ausgewählten Zähler laden
$verbrauchsdaten = [];
$labels = [];
$werte_map = [];
if (!empty($selected_zaehler)) {
    foreach ($selected_zaehler as $zid) {
        $daten = $db->fetchAll("SELECT z.id, z.zaehlernummer, zs.datum, zs.stand
            FROM zaehler z
            LEFT JOIN zaehlerstaende zs ON zs.zaehler_id = z.id
            WHERE z.id = ? AND zs.datum IS NOT NULL
            ORDER BY zs.datum ASC", [$zid]);
        if ($daten) {
            foreach ($daten as $row) {
                $datum = date('d.m.', strtotime($row['datum']));
                $labels[$datum] = true;
                $werte_map[$row['id']]['zaehlernummer'] = $row['zaehlernummer'];
                $werte_map[$row['id']]['werte'][$datum] = (float)$row['stand'];
            }
        }
    }
    $labels = array_keys($labels);
    sort($labels);
}
?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Verbrauchsanalyse</h1>

        <!-- Auswahlformular -->
        <form method="GET" class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Zähler auswählen</label>
            <select name="zaehler[]" multiple class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-marina-500 focus:border-marina-500 p-2">
                <?php foreach ($alle_zaehler as $z): ?>
                    <option value="<?= $z['id'] ?>" <?= in_array($z['id'], $selected_zaehler) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($z['zaehlernummer']) ?><?= $z['steckdose'] ? ' – ' . htmlspecialchars($z['steckdose']) : '' ?><?= $z['bereich'] ? ' – ' . htmlspecialchars($z['bereich']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="mt-4 px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">Anzeigen</button>
        </form>

        <?php if (!empty($werte_map)): ?>
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Verbrauch ausgewählter Zähler</h2>
                <div id="chart-multi" style="height: 400px;"></div>
                <script>
                    const labels = <?= json_encode($labels) ?>;
                    const datasets = [
                        <?php foreach ($werte_map as $zid => $z): ?>{
                            name: "<?= addslashes($z['zaehlernummer']) ?>",
                            values: labels.map(label => <?= json_encode($z['werte']) ?>[label] ?? 0)
                        },<?php endforeach; ?>
                    ];

                    new Chartisan({
                        el: '#chart-multi',
                        data: {
                            chart: { type: 'bar' },
                            labels: labels,
                            datasets: datasets
                        },
                        hooks: new ChartisanHooks()
                            .datasets('bar')
                            .colors(['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'])
                            .tooltip()
                            .customTooltips(true)
                            .responsive(true)
                    });
                </script>
            </div>
        <?php elseif (!empty($selected_zaehler)): ?>
            <div class="text-red-700 bg-red-100 border border-red-300 p-4 rounded">Keine Daten für die ausgewählten Zähler gefunden.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
