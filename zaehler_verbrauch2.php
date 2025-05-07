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

// Nur ausgewählte Zähler laden
$verbrauchsdaten = [];
if (!empty($selected_zaehler)) {
    foreach ($selected_zaehler as $zid) {
        $daten = $db->fetchAll("SELECT z.id, z.zaehlernummer, zs.datum, zs.stand
            FROM zaehler z
            LEFT JOIN zaehlerstaende zs ON zs.zaehler_id = z.id
            WHERE z.id = ?
            ORDER BY zs.datum ASC", [$zid]);
        if ($daten) {
            $verbrauchsdaten[$zid] = $daten;
        }
    }
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

        <?php if (!empty($verbrauchsdaten)): ?>
            <div class="grid grid-cols-1 gap-8">
                <?php foreach ($verbrauchsdaten as $zid => $werte): ?>
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            <?= htmlspecialchars($werte[0]['zaehlernummer']) ?>
                        </h2>
                        <div id="chart-<?= $zid ?>" style="height: 300px;"></div>
                        <script>
                            const data<?= $zid ?> = <?= json_encode(array_map(function($w) {
                                return [
                                    'datum' => date('d.m.', strtotime($w['datum'])),
                                    'verbrauch' => (float) $w['stand'],
                                ];
                            }, $werte)) ?>;

                            new Chartisan({
                                el: '#chart-<?= $zid ?>',
                                data: {
                                    chart: { type: 'bar' },
                                    labels: data<?= $zid ?>.map(row => row.datum),
                                    datasets: [{
                                        name: 'Verbrauch',
                                        values: data<?= $zid ?>.map(row => row.verbrauch)
                                    }]
                                },
                                hooks: new ChartisanHooks()
                                    .datasets('bar')
                                    .colors(['#2563eb'])
                                    .legend(false)
                                    .tooltip()
                                    .customTooltips(true)
                                    .responsive(true)
                            });
                        </script>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($selected_zaehler)): ?>
            <div class="text-red-700 bg-red-100 border border-red-300 p-4 rounded">Keine Daten für die ausgewählten Zähler gefunden.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
