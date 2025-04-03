
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Variablen initialisieren
$id = null;
$zaehler_id = '';
$steckdose_id = '';
$datum = date('Y-m-d');
$stand = '';
$hinweis = '';
$ist_abgerechnet = 0;
$pageTitle = 'Neuen Zählerstand erfassen';
$isEdit = false;

// Zähler für Dropdown laden
$zaehler = $db->fetchAll("SELECT id, zaehlernummer FROM zaehler ORDER BY zaehlernummer");

// Steckdosen für Dropdown laden
$steckdosen = $db->fetchAll("
    SELECT s.id, s.bezeichnung, b.name AS bereich_name
    FROM steckdosen s
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    ORDER BY b.name, s.bezeichnung
");

// Prüfen, ob ein vorheriger Zählerstand existiert (für Verbrauchsberechnung)
$vorheriger_id = null;
$vorheriger_stand = null;

// Wenn ID in URL, dann Bearbeiten-Modus
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $isEdit = true;
    $pageTitle = 'Zählerstand bearbeiten';
    
    // Daten aus der Datenbank laden
    $zs = $db->fetchOne("SELECT * FROM zaehlerstaende WHERE id = ?", [$id]);
    
    if ($zs) {
        $zaehler_id = $zs['zaehler_id'];
        $steckdose_id = $zs['steckdose_id'];
        $datum = $zs['datum'];
        $stand = $zs['stand'];
        $hinweis = $zs['hinweis'];
        $ist_abgerechnet = $zs['ist_abgerechnet'] ? 1 : 0;
        $vorheriger_id = $zs['vorheriger_id'];
    } else {
        $error = "Zählerstand nicht gefunden.";
    }
}

// Wenn Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulardaten übernehmen
    $zaehler_id = $_POST['zaehler_id'];
    $steckdose_id = !empty($_POST['steckdose_id']) ? $_POST['steckdose_id'] : null;
    $datum = $_POST['datum'];
    $stand = str_replace(',', '.', $_POST['stand']); // Komma durch Punkt ersetzen für Dezimalzahlen
    $hinweis = $_POST['hinweis'];
    $ist_abgerechnet = isset($_POST['ist_abgerechnet']) ? 1 : 0;

    // Validierung
    $errors = [];

    if (empty($zaehler_id)) {
        $errors[] = "Bitte einen Zähler auswählen.";
    }

    if (empty($datum)) {
        $errors[] = "Bitte ein Datum eingeben.";
    }

    if (!is_numeric($stand)) {
        $errors[] = "Der Zählerstand muss eine Zahl sein.";
    }

    // Wenn keine Fehler aufgetreten sind
    if (empty($errors)) {
        // Vorherigen Zählerstand für diesen Zähler finden (für Verbrauchsberechnung)
        $vorheriger = $db->fetchOne("
            SELECT id, stand 
            FROM zaehlerstaende 
            WHERE zaehler_id = ? AND datum < ? 
            ORDER BY datum DESC, id DESC 
            LIMIT 1", 
            [$zaehler_id, $datum]
        );

        $vorheriger_id = $vorheriger ? $vorheriger['id'] : null;
        $verbrauch = null;

        // Verbrauch berechnen, wenn vorheriger Zählerstand existiert
        if ($vorheriger_id) {
            $verbrauch = $stand - $vorheriger['stand'];
            if ($verbrauch < 0) {
                $errors[] = "Der Zählerstand kann nicht kleiner sein als der vorherige Stand (" . $vorheriger['stand'] . ").";
            }
        }

        if (empty($errors)) {
            // Aktuelle Benutzer-ID für abgelesen_von
            $current_user = $auth->getCurrentUser();
            $abgelesen_von_id = $current_user['id'];

            if ($isEdit) {
                // Zählerstand aktualisieren
                $sql = "
                    UPDATE zaehlerstaende 
                    SET zaehler_id = ?, steckdose_id = ?, datum = ?, stand = ?, 
                        vorheriger_id = ?, verbrauch = ?, abgelesen_von_id = ?, 
                        hinweis = ?, ist_abgerechnet = ? 
                    WHERE id = ?
                ";
                $db->query($sql, [
                    $zaehler_id, $steckdose_id, $datum, $stand, 
                    $vorheriger_id, $verbrauch, $abgelesen_von_id, 
                    $hinweis, $ist_abgerechnet, $id
                ]);
                
                $success = "Zählerstand wurde erfolgreich aktualisiert.";
            } else {
                // Neuen Zählerstand erstellen
                $sql = "
                    INSERT INTO zaehlerstaende 
                    (zaehler_id, steckdose_id, datum, stand, vorheriger_id, verbrauch, abgelesen_von_id, hinweis, ist_abgerechnet) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $db->query($sql, [
                    $zaehler_id, $steckdose_id, $datum, $stand, 
                    $vorheriger_id, $verbrauch, $abgelesen_von_id, 
                    $hinweis, $ist_abgerechnet
                ]);
                
                $newId = $db->lastInsertId();
                
                // Nachfolgende Zählerstände aktualisieren
                if ($newId) {
                    // Alle nachfolgenden Zählerstände für diesen Zähler finden
                    $nachfolgende = $db->fetchAll("
                        SELECT id, stand 
                        FROM zaehlerstaende 
                        WHERE zaehler_id = ? AND datum > ? 
                        ORDER BY datum ASC, id ASC", 
                        [$zaehler_id, $datum]
                    );
                    
                    $voriger_stand = $stand;
                    $voriger_id = $newId;
                    
                    // Verbrauch für jeden nachfolgenden Stand neu berechnen
                    foreach ($nachfolgende as $nachfolger) {
                        $neuer_verbrauch = $nachfolger['stand'] - $voriger_stand;
                        $db->query("
                            UPDATE zaehlerstaende 
                            SET vorheriger_id = ?, verbrauch = ? 
                            WHERE id = ?", 
                            [$voriger_id, $neuer_verbrauch, $nachfolger['id']]
                        );
                        
                        $voriger_stand = $nachfolger['stand'];
                        $voriger_id = $nachfolger['id'];
                    }
                }
                
                $success = "Zählerstand wurde erfolgreich gespeichert.";
            }

            // Zurück zur Übersicht nach erfolgreichem Speichern
            header("Location: zaehlerstaende.php?success=" . urlencode($success));
            exit;
        }
    }
}

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Zählerstand-Formular -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form method="POST" action="<?= $isEdit ? "zaehlerstaende_form.php?id=$id" : "zaehlerstaende_form.php" ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="zaehler_id" class="block text-sm font-medium text-gray-700 mb-1">Zähler *</label>
                        <select id="zaehler_id" name="zaehler_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($zaehler as $z): ?>
                                <option value="<?= $z['id'] ?>" <?= ($zaehler_id == $z['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($z['zaehlernummer']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="steckdose_id" class="block text-sm font-medium text-gray-700 mb-1">Steckdose</label>
                        <select id="steckdose_id" name="steckdose_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($steckdosen as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($steckdose_id == $s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['bezeichnung']) ?> (<?= htmlspecialchars($s['bereich_name'] ?? 'Kein Bereich') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="datum" class="block text-sm font-medium text-gray-700 mb-1">Datum *</label>
                        <input type="date" id="datum" name="datum" value="<?= htmlspecialchars($datum) ?>" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                    </div>
                    
                    <div>
                        <label for="stand" class="block text-sm font-medium text-gray-700 mb-1">Zählerstand (kWh) *</label>
                        <input type="text" id="stand" name="stand" value="<?= htmlspecialchars($stand) ?>" required placeholder="z.B. 1234,56" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <p class="text-sm text-gray-500 mt-1">Dezimalzahl mit Komma oder Punkt, z.B. 1234,56</p>
                    </div>

                    <?php if ($vorheriger_id): ?>
                        <div class="col-span-2">
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p class="text-sm text-gray-700">
                                    <strong>Vorheriger Zählerstand:</strong> <?= htmlspecialchars(number_format($vorheriger_stand, 2, ',', '.')) ?> kWh
                                </p>
                                <?php if (isset($verbrauch)): ?>
                                    <p class="text-sm text-gray-700 mt-1">
                                        <strong>Berechneter Verbrauch:</strong> <?= htmlspecialchars(number_format($verbrauch, 2, ',', '.')) ?> kWh
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-span-2">
                        <label for="hinweis" class="block text-sm font-medium text-gray-700 mb-1">Hinweis</label>
                        <textarea id="hinweis" name="hinweis" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500"><?= htmlspecialchars($hinweis) ?></textarea>
                    </div>
                    
                    <div class="col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="ist_abgerechnet" name="ist_abgerechnet" <?= $ist_abgerechnet ? 'checked' : '' ?> class="h-4 w-4 rounded border-gray-300 text-marina-600 focus:ring-marina-500">
                            <label for="ist_abgerechnet" class="ml-2 block text-sm text-gray-700">Als abgerechnet markieren</label>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <a href="zaehlerstaende.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</a>
                    <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">
                        <?= $isEdit ? 'Aktualisieren' : 'Speichern' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript für dynamische Anzeige von Verbrauchsinformationen bei Zählerstandsänderung
document.addEventListener('DOMContentLoaded', function() {
    // Hier kann JavaScript für dynamische Berechnungen ergänzt werden
});
</script>

<?php
require_once 'includes/footer.php';
?>
