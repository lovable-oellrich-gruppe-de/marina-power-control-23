<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Verbrauchsdaten für Zähler mit Steckdosen
$steckdosen_zaehler = $db->fetchAll("SELECT z.id, z.zaehlernummer, z.hinweis, s.bezeichnung AS steckdose, b.name AS bereich,
    (SELECT stand FROM zaehlerstaende WHERE zaehler_id = z.id ORDER BY datum DESC LIMIT 1) AS letzter,
    (SELECT stand FROM zaehlerstaende WHERE zaehler_id = z.id ORDER BY datum DESC LIMIT 1 OFFSET 1) AS vorheriger
    FROM zaehler z
    LEFT JOIN steckdosen s ON z.steckdose_id = s.id
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    WHERE z.steckdose_id IS NOT NULL
    ORDER BY z.zaehlernummer");

// Verbrauchsdaten für Hauptzähler (ohne Steckdose)
$haupt_zaehler = $db->fetchAll("SELECT hz.id, hz.zaehlernummer,
    (SELECT stand FROM zaehlerstaende WHERE zaehler_id = hz.id ORDER BY datum DESC LIMIT 1) AS letzter,
    (SELECT stand FROM zaehlerstaende WHERE zaehler_id = hz.id ORDER BY datum DESC LIMIT 1 OFFSET 1) AS vorheriger,
    (SELECT SUM(v.verbrauch) FROM zaehlerstaende v WHERE v.zaehler_id IN (SELECT id FROM zaehler WHERE parent_id = hz.id) AND v.id IN (
        SELECT MAX(id) FROM zaehlerstaende WHERE zaehler_id IN (SELECT id FROM zaehler WHERE parent_id = hz.id) GROUP BY zaehler_id
    )) AS unterzaehler_summe
    FROM zaehler hz
    WHERE hz.steckdose_id IS NULL
    ORDER BY hz.zaehlernummer");
?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Verbrauchsanalyse</h1>

        <!-- Abschnitt 1: Steckdosen-Zähler -->
        <h2 class="text-xl font-semibold mb-2">Verbrauch nach Steckdosen-Zählern</h2>
        <canvas id="chart_steckdosen" class="w-full h-96 mb-10"></canvas>

        <!-- Abschnitt 2: Hauptzähler-Analyse -->
        <h2 class="text-xl font-semibold mb-2">Hauptzähler vs. Summe Unterzähler</h2>
        <canvas id="chart_hauptzaehler" class="w-full h-96"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const steckdosenLabels = <?= json_encode(array_map(function($z) {
    return $z['zaehlernummer'] . "\n(" . ($z['steckdose'] ?? '-') . ", " . ($z['bereich'] ?? '-') . ")";
}, $steckdosen_zaehler)) ?>;

const steckdosenLetzter = <?= json_encode(array_map(fn($z) => (float)$z['letzter'], $steckdosen_zaehler)) ?>;
const steckdosenVorher = <?= json_encode(array_map(fn($z) => (float)$z['vorheriger'], $steckdosen_zaehler)) ?>;

new Chart(document.getElementById("chart_steckdosen"), {
    type: 'bar',
    data: {
        labels: steckdosenLabels,
        datasets: [
            {
                label: 'Letzter Stand',
                backgroundColor: '#3b82f6',
                data: steckdosenLetzter
            },
            {
                label: 'Vorheriger Stand',
                backgroundColor: '#93c5fd',
                data: steckdosenVorher
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Letzte zwei Zählerstände'
            }
        },
        scales: {
            x: {
                ticks: {
                    maxRotation: 90,
                    minRotation: 45
                }
            },
            y: {
                beginAtZero: true
            }
        }
    }
});

const hauptLabels = <?= json_encode(array_map(fn($z) => $z['zaehlernummer'], $haupt_zaehler)) ?>;
const hauptLetzter = <?= json_encode(array_map(fn($z) => (float)$z['letzter'], $haupt_zaehler)) ?>;
const hauptVorher = <?= json_encode(array_map(fn($z) => (float)$z['vorheriger'], $haupt_zaehler)) ?>;
const hauptSumme = <?= json_encode(array_map(fn($z) => (float)($z['unterzaehler_summe'] ?? 0), $haupt_zaehler)) ?>;
const hauptDifferenz = <?= json_encode(array_map(fn($z) => round(((float)$z['letzter'] - (float)$z['vorheriger']) - (float)($z['unterzaehler_summe'] ?? 0), 2), $haupt_zaehler)) ?>;

new Chart(document.getElementById("chart_hauptzaehler"), {
    type: 'bar',
    data: {
        labels: hauptLabels,
        datasets: [
            {
                label: 'Hauptzähler Verbrauch',
                backgroundColor: '#10b981',
                data: hauptLetzter.map((v, i) => v - hauptVorher[i])
            },
            {
                label: 'Summe Unterzähler',
                backgroundColor: '#6ee7b7',
                data: hauptSumme
            },
            {
                label: 'Differenz',
                backgroundColor: '#ef4444',
                data: hauptDifferenz
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Vergleich Haupt- vs. Unterzähler'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
