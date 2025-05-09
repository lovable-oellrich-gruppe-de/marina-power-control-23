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

        $punkte = [];
        foreach ($daten as $d) {
            $punkte[] = [
                'x' => $d['datum'] . 'T00:00:00',
                'y' => (float)$d['stand']
            ];
        }

        $farbe = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $zeitreihen[] = [
            'label' => $zaehlername,
            'data' => $punkte,
            'borderColor' => $farbe,
            'backgroundColor' => $farbe,
            'showLine' => true,
            'fill' => false,
            'tension' => 0.3,
            'pointRadius' => 4,
            'pointHoverRadius' => 6
        ];
    }
}
?>

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
                <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
                <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
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

        <div class="flex space-x-4 mb-4">
            <button id="barBtn" class="px-4 py-2 bg-blue-600 text-white rounded">Balkendiagramm</button>
            <button id="lineBtn" class="px-4 py-2 bg-gray-300 text-gray-900 rounded">Liniendiagramm</button>
        </div>
        <div class="h-96">
            <canvas id="verbrauchChart" class="w-full h-full"></canvas>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
        <script>
            const ctx = document.getElementById('verbrauchChart').getContext('2d');

            const barData = {
                labels: <?= json_encode(array_column($verbrauchsdaten, 'label')) ?>,
                datasets: [{
                    label: 'Verbrauch (kWh)',
                    data: <?= json_encode(array_column($verbrauchsdaten, 'verbrauch')) ?>,
                    backgroundColor: '#2563eb'
                }]
            };

            const lineData = {
                datasets: <?= json_encode($zeitreihen, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK) ?>
            };

            console.log("Line Data", lineData);

            const defaultOptions = {
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (chartType === 'bar') {
                                    const tooltips = <?= json_encode(array_column($verbrauchsdaten, 'tooltip')) ?>;
                                    return tooltips[context.dataIndex].split('\n');
                                } else {
                                    const point = context.raw;
                                    return context.dataset.label + ': ' + point.y + ' kWh';
                                }
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Verbrauchsdaten'
                    }
                },
                responsive: true
            };

            let chartType = 'bar';
            let chart = new Chart(ctx, {
                type: 'bar',
                data: barData,
                options: {
                    ...defaultOptions,
                    scales: {
                        x: {
                            type: 'category',
                            title: { display: true, text: 'Zähler' }
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'kWh' }
                        }
                    }
                }
            });

            document.getElementById('barBtn').addEventListener('click', () => {
                chart.destroy();
                chartType = 'bar';
                chart = new Chart(ctx, {
                    type: 'line',
                    data: lineData,
                    options: {
                        ...defaultOptions,
                        spanGaps: true,
                        interaction: {
                            mode: 'nearest',
                            intersect: false
                        },
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'day',
                                    tooltipFormat: 'yyyy-MM-dd'
                                },
                                title: { display: true, text: 'Datum' }
                            },
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'kWh' }
                            }
                        }
                    }
                });
            });

            document.getElementById('lineBtn').addEventListener('click', () => {
                chart.destroy();
                chartType = 'line';
                chart = new Chart(ctx, {
                    type: 'line',
                    data: lineData,
                    options: {
                        ...defaultOptions,
                        interaction: {
                            mode: 'nearest',
                            intersect: false
                        },
                        parsing: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'day',
                                    tooltipFormat: 'yyyy-MM-dd'
                                },
                                title: { display: true, text: 'Datum' }
                            },
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'kWh' }
                            }
                        }
                    }
                });
            });
        </script>

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
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
