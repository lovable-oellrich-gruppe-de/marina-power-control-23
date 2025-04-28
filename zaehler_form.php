<?php
// Wichtige Einbindungen für die Anwendung
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Initialisierung der Variablen
$zaehler_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errors = [];
$zaehler = [
    'zaehlernummer' => '',
    'typ' => 'Stromzähler',
    'hersteller' => '',
    'modell' => '',
    'installiert_am' => date('Y-m-d'),
    'letzte_wartung' => '',
    'seriennummer' => '',
    'max_leistung' => '',
    'ist_ausgebaut' => 0,
    'hinweis' => ''
];

// Wenn ID vorhanden, Zähler laden
if ($zaehler_id) {
    $loaded_zaehler = $db->fetchOne("SELECT * FROM zaehler WHERE id = ?", [$zaehler_id]);
    if ($loaded_zaehler) {
        $zaehler = $loaded_zaehler;
    } else {
        $errors[] = "Zähler nicht gefunden.";
    }
}

// Formular wurde abgesendet
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Formulardaten einlesen
    $form_data = [
        'zaehlernummer' => trim($_POST['zaehlernummer'] ?? ''),
        'typ' => trim($_POST['typ'] ?? 'Stromzähler'),
        'hersteller' => trim($_POST['hersteller'] ?? ''),
        'modell' => trim($_POST['modell'] ?? ''),
        'installiert_am' => trim($_POST['installiert_am'] ?? date('Y-m-d')),
        'letzte_wartung' => trim($_POST['letzte_wartung'] ?? ''),
        'seriennummer' => trim($_POST['seriennummer'] ?? ''),
        'max_leistung' => trim($_POST['max_leistung'] ?? ''),
        'ist_ausgebaut' => isset($_POST['ist_ausgebaut']) ? 1 : 0,
        'hinweis' => trim($_POST['hinweis'] ?? '')
    ];

    // Validierung
    if (empty($form_data['zaehlernummer'])) {
        $errors[] = "Zählernummer ist erforderlich.";
    }
    if (empty($form_data['installiert_am'])) {
        $errors[] = "Installationsdatum ist erforderlich.";
    }

    // Wenn keine Fehler, speichern
    if (empty($errors)) {
        if ($zaehler_id) {
            // Änderungen prüfen
            $datenGeaendert = false;
            foreach ($form_data as $key => $value) {
                $dbValue = $zaehler[$key] ?? null;
                if ((string)$value !== (string)$dbValue) {
                    $datenGeaendert = true;
                    break;
                }
            }

            if ($datenGeaendert) {
                $sql = "UPDATE zaehler SET 
                        zaehlernummer = ?, typ = ?, hersteller = ?, modell = ?, installiert_am = ?, 
                        letzte_wartung = ?, seriennummer = ?, max_leistung = ?, ist_ausgebaut = ?, hinweis = ?
                        WHERE id = ?";
                $params = [
                    $form_data['zaehlernummer'],
                    $form_data['typ'],
                    $form_data['hersteller'],
                    $form_data['modell'],
                    $form_data['installiert_am'],
                    empty($form_data['letzte_wartung']) ? null : $form_data['letzte_wartung'],
                    $form_data['seriennummer'],
                    empty($form_data['max_leistung']) ? null : $form_data['max_leistung'],
                    $form_data['ist_ausgebaut'],
                    $form_data['hinweis'],
                    $zaehler_id
                ];
                $db->query($sql, $params);

                header("Location: zaehler.php?success=" . urlencode("Zähler wurde erfolgreich aktualisiert."));
                exit;
            } else {
                header("Location: zaehler.php?info=" . urlencode("Es wurden keine Änderungen vorgenommen."));
                exit;
            }
        } else {
            $sql = "INSERT INTO zaehler (
                        zaehlernummer, typ, hersteller, modell, installiert_am, letzte_wartung, 
                        seriennummer, max_leistung, ist_ausgebaut, hinweis
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $form_data['zaehlernummer'],
                $form_data['typ'],
                $form_data['hersteller'],
                $form_data['modell'],
                $form_data['installiert_am'],
                empty($form_data['letzte_wartung']) ? null : $form_data['letzte_wartung'],
                $form_data['seriennummer'],
                empty($form_data['max_leistung']) ? null : $form_data['max_leistung'],
                $form_data['ist_ausgebaut'],
                $form_data['hinweis']
            ];
            $db->query($sql, $params);
            $zaehler_id = $db->lastInsertId();

            header("Location: zaehler.php?success=" . urlencode("Zähler wurde erfolgreich erstellt."));
            exit;
        }
    }
}

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                <?= $zaehler_id ? 'Zähler bearbeiten' : 'Neuen Zähler erstellen' ?>
            </h1>
            <a href="zaehler.php" class="text-marina-600 hover:text-marina-700">
                Zurück zur Übersicht
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Fehler!</strong>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Zähler-Formular -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    <div class="space-y-2">
                        <label for="zaehlernummer" class="block text-sm font-medium text-gray-700">Zählernummer *</label>
                        <input type="text" id="zaehlernummer" name="zaehlernummer" value="<?= htmlspecialchars($zaehler['zaehlernummer']) ?>" required
                               class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                    </div>

                    <div class="space-y-2">
                        <label for="typ" class="block text-sm font-medium text-gray-700">Typ</label>
                        <input type="text" id="typ" name="typ" value="<?= htmlspecialchars($zaehler['typ']) ?>"
                               class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                    </div>

                    <div class="space-y-2">
                        <label for="hersteller" class="block text-sm font-medium text-gray-700">Hersteller</label>
                        <input type="text" id="hersteller" name="hersteller" value="<?= htmlspecialchars($zaehler['hersteller']) ?>"
                               class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                    </div>

                    <div class="space-y-2">
                        <label for="modell" class="block text-sm font-medium text-gray-700">Modell</label>
                        <input type="text" id="modell" name="modell" value="<?= htmlspecialchars($zaehler['modell']) ?>"
                               class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                    </div>

                    <div class="space-y-2">
                        <label for="installiert_am" class="block text-sm font-medium text-gray-700">Installiert am *</label>
                        <input type="date" id="installiert_am" name="installiert_am" value="<?= htmlspecialchars($zaehler['installiert_am']) ?>" required
                               class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                    </div>

                    <div class="space-y-2">
                        <label for="letzte_wartung" class="block text-sm font-medium text-gray-700">Letzte Wartung</label>
                        <input type="date" id="letzte_wartung" name="letzte_wartung" value="<?= htmlspecialchars($zaehler['letzte_wartung']) ?>"
                               class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                    </div>

                    <div class="space-y-2">
                        <label for="seriennummer" class="block text-sm font-medium text-gray-700">Seriennummer</label>
                        <input type="text" id="seriennummer" name="seriennummer" value="<?= htmlspecialchars($zaehler['seriennummer']) ?>"
                               class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                    </div>

                    <div class="space-y-2">
                        <label for="max_leistung" class="block text-sm font-medium text-gray-700">Max. Leistung (W)</label>
                        <input type="number" id="max_leistung" name="max_leistung" value="<?= htmlspecialchars($zaehler['max_leistung']) ?>"
                               class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                    </div>

                    <div class="sm:col-span-2 flex items-start space-x-3 mt-4">
                        <input id="ist_ausgebaut" name="ist_ausgebaut" type="checkbox" <?= $zaehler['ist_ausgebaut'] ? 'checked' : '' ?>
                               class="h-5 w-5 text-marina-600 focus:ring-marina-500 border-gray-300 rounded">
                        <label for="ist_ausgebaut" class="text-sm text-gray-700">Zähler ist ausgebaut</label>
                    </div>

                    <div class="sm:col-span-2 space-y-2">
                        <label for="hinweis" class="block text-sm font-medium text-gray-700">Hinweis</label>
                        <textarea id="hinweis" name="hinweis" rows="3"
                                  class="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"><?= htmlspecialchars($zaehler['hinweis']) ?></textarea>
                    </div>

                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="zaehler.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Abbrechen
                    </a>
                    <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">
                        Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
