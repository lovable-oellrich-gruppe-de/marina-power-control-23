<?php
// Wichtige Includes
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Ordner für Foto-Uploads erstellen, falls noch nicht vorhanden
$upload_dir = 'uploads/zaehlerstaende';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Initialisierung der Variablen
$id = null;
$zaehler_id = '';
$steckdose_id = '';
$datum = date('Y-m-d');
$stand = '';
$hinweis = '';
$foto_url = '';
$errors = [];
$pageTitle = 'Neuen Zählerstand erfassen';
$isEdit = false;

// Zähler und Steckdosen für Dropdown-Listen laden
$zaehler = $db->fetchAll("SELECT id, zaehlernummer FROM zaehler ORDER BY zaehlernummer");
$steckdosen = $db->fetchAll("
    SELECT steckdosen.id, steckdosen.bezeichnung, bereiche.name AS bereich_name
    FROM steckdosen
    LEFT JOIN bereiche ON steckdosen.bereich_id = bereiche.id
    ORDER BY bereiche.name, steckdosen.bezeichnung
");

// Prüfen ob Bearbeiten-Modus
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $isEdit = true;
    $pageTitle = 'Zählerstand bearbeiten';

    $zs = $db->fetchOne("SELECT * FROM zaehlerstaende WHERE id = ?", [$id]);
    if ($zs) {
        $zaehler_id = $zs['zaehler_id'];
        $steckdose_id = $zs['steckdose_id'];
        $datum = $zs['datum'];
        $stand = $zs['stand'];
        $hinweis = $zs['hinweis'];
        $foto_url = $zs['foto_url'];
    } else {
        $errors[] = "Zählerstand nicht gefunden.";
    }
}

// Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zaehler_id = $_POST['zaehler_id'] ?? '';
    $steckdose_id = !empty($_POST['steckdose_id']) ? $_POST['steckdose_id'] : null;
    $datum = $_POST['datum'] ?? '';
    $stand = str_replace(',', '.', $_POST['stand'] ?? '');
    $hinweis = $_POST['hinweis'] ?? '';

    // Validierung der Eingaben
    if (empty($zaehler_id)) {
        $errors[] = "Bitte einen Zähler auswählen.";
    }
    if (empty($steckdose_id)) {
        $errors[] = "Bitte eine Steckdose auswählen.";
    }
    if (empty($datum)) {
        $errors[] = "Bitte ein Datum eingeben.";
    }
    if (!is_numeric($stand)) {
        $errors[] = "Der Zählerstand muss eine Zahl sein.";
    }

    // Foto-Upload verarbeiten
    if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024;

        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            $errors[] = "Ungültiges Dateiformat.";
        } elseif ($_FILES['foto']['size'] > $max_size) {
            $errors[] = "Die Datei ist zu groß (maximal 5MB).";
        } else {
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '_' . date('Ymd') . '.' . $file_extension;
            $upload_path = $upload_dir . '/' . $unique_filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto_url = $upload_path;
            } else {
                $errors[] = "Fehler beim Hochladen des Fotos.";
            }
        }
    }

    // Wenn keine Fehler vorliegen: speichern
    if (empty($errors)) {
        $verbrauch = null;
        $vorheriger = $db->fetchOne("
            SELECT id, stand FROM zaehlerstaende 
            WHERE zaehler_id = ? AND datum < ? 
            ORDER BY datum DESC, id DESC LIMIT 1
        ", [$zaehler_id, $datum]);
        $vorheriger_id = $vorheriger['id'] ?? null;

        if ($vorheriger) {
            $verbrauch = $stand - $vorheriger['stand'];
            if ($verbrauch < 0) {
                $errors[] = "Der neue Stand ist kleiner als der vorherige.";
            }
        }
    }

    // Speicherung oder Update
    if (empty($errors)) {
        $current_user = $auth->getCurrentUser();
        $abgelesen_von_id = $current_user['id'];

        if ($isEdit) {
            $params = [$zaehler_id, $steckdose_id, $datum, $stand, $vorheriger_id, $verbrauch, $abgelesen_von_id, $hinweis];
            $sql = "UPDATE zaehlerstaende 
                    SET zaehler_id=?, steckdose_id=?, datum=?, stand=?, vorheriger_id=?, verbrauch=?, abgelesen_von_id=?, hinweis=?";
            if (!empty($foto_url)) {
                $sql .= ", foto_url=?";
                $params[] = $foto_url;
            }
            $sql .= " WHERE id=?";
            $params[] = $id;

            $db->query($sql, $params);
            $success = "Zählerstand wurde erfolgreich aktualisiert.";
        } else {
            $params = [$zaehler_id, $steckdose_id, $datum, $stand, $vorheriger_id, $verbrauch, $abgelesen_von_id, $hinweis];
            $columns = "zaehler_id, steckdose_id, datum, stand, vorheriger_id, verbrauch, abgelesen_von_id, hinweis";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, ?";
            if (!empty($foto_url)) {
                $columns .= ", foto_url";
                $placeholders .= ", ?";
                $params[] = $foto_url;
            }
            $db->query("INSERT INTO zaehlerstaende ($columns) VALUES ($placeholders)", $params);
            $success = "Zählerstand wurde erfolgreich gespeichert.";
        }

        // Nach Speichern weiterleiten
        header("Location: zaehlerstaende.php?success=" . urlencode($success));
        exit;
    }
}

// Header einbinden
require_once 'includes/header.php';
?>

<!-- HTML-Teil (Formular für neuen/bearbeiten Zählerstand) -->
<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
            <a href="zaehlerstaende.php" class="text-marina-600 hover:text-marina-700">
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

        <!-- Formular zur Eingabe -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <form method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Zähler auswählen -->
                    <div class="space-y-2">
                        <label for="zaehler_id" class="block text-sm font-medium text-gray-700">Zähler *</label>
                        <select id="zaehler_id" name="zaehler_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($zaehler as $z): ?>
                                <option value="<?= $z['id'] ?>" <?= ((int)$zaehler_id === (int)$z['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($z['zaehlernummer']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Steckdose auswählen -->
                    <div class="space-y-2">
                        <label for="steckdose_id" class="block text-sm font-medium text-gray-700">Steckdose *</label>
                        <select id="steckdose_id" name="steckdose_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($steckdosen as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ((int)$steckdose_id === (int)$s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['bezeichnung']) ?> (<?= htmlspecialchars($s['bereich_name'] ?? 'Kein Bereich') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Datum auswählen -->
                    <div class="space-y-2">
                        <label for="datum" class="block text-sm font-medium text-gray-700">Datum *</label>
                        <input type="date" id="datum" name="datum" value="<?= htmlspecialchars($datum) ?>" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                    </div>

                    <!-- Stand eingeben -->
                    <div class="space-y-2">
                        <label for="stand" class="block text-sm font-medium text-gray-700">Zählerstand (kWh) *</label>
                        <input type="text" id="stand" name="stand" value="<?= htmlspecialchars($stand) ?>" required placeholder="z.B. 1234,56" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                    </div>

                    <!-- Foto Upload -->
                    <div class="col-span-2 space-y-2">
                        <label for="foto" class="block text-sm font-medium text-gray-700">Foto (optional)</label>
                        <input type="file" id="foto" name="foto" accept="image/*" class="w-full rounded-md border border-gray-300 px-3 py-2">
                    </div>

                    <!-- Hinweis -->
                    <div class="col-span-2 space-y-2">
                        <label for="hinweis" class="block text-sm font-medium text-gray-700">Hinweis</label>
                        <textarea id="hinweis" name="hinweis" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500"><?= htmlspecialchars($hinweis) ?></textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="zaehlerstaende.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</a>
                    <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">
                        <?= $isEdit ? 'Aktualisieren' : 'Speichern' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Footer einbinden
require_once 'includes/footer.php';
?>
