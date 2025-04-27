<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Ordner für Fotouploads erstellen, falls er nicht existiert
$upload_dir = 'uploads/zaehlerstaende';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Variablen initialisieren
$id = null;
$zaehler_id = '';
$steckdose_id = '';
$datum = date('Y-m-d');
$stand = '';
$hinweis = '';
$ist_abgerechnet = 0;
$foto_url = '';
$pageTitle = 'Neuen Zählerstand erfassen';
$isEdit = false;

$errors = [];

// Zähler für Dropdown laden
$zaehler = $db->fetchAll("SELECT id, zaehlernummer FROM zaehler ORDER BY zaehlernummer");

// Steckdosen für Dropdown laden
$steckdosen = $db->fetchAll("SELECT s.id, s.bezeichnung, b.name AS bereich_name
    FROM steckdosen s
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    ORDER BY b.name, s.bezeichnung");

// Bearbeiten-Modus prüfen
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
        $ist_abgerechnet = $zs['ist_abgerechnet'];
        $foto_url = $zs['foto_url'];
    } else {
        $errors[] = "Zählerstand nicht gefunden.";
    }
}

// Formular verarbeitet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zaehler_id = $_POST['zaehler_id'] ?? '';
    $steckdose_id = !empty($_POST['steckdose_id']) ? $_POST['steckdose_id'] : null;
    $datum = $_POST['datum'] ?? '';
    $stand = str_replace(',', '.', $_POST['stand'] ?? '');
    $hinweis = $_POST['hinweis'] ?? '';
    $ist_abgerechnet = isset($_POST['ist_abgerechnet']) ? 1 : 0;

    if (empty($zaehler_id)) {
        $errors[] = "Bitte einen Zähler auswählen.";
    }
    if (empty($datum)) {
        $errors[] = "Bitte ein Datum eingeben.";
    }
    if (!is_numeric($stand)) {
        $errors[] = "Der Zählerstand muss eine Zahl sein.";
    }

    // Foto hochladen
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

    // Wenn keine Fehler vorhanden
    if (empty($errors)) {
        $verbrauch = null;

        // Vorherigen Zählerstand suchen
        $vorheriger = $db->fetchOne("SELECT id, stand FROM zaehlerstaende WHERE zaehler_id = ? AND datum < ? ORDER BY datum DESC, id DESC LIMIT 1", [$zaehler_id, $datum]);
        $vorheriger_id = $vorheriger['id'] ?? null;

        if ($vorheriger) {
            $verbrauch = $stand - $vorheriger['stand'];
            if ($verbrauch < 0) {
                $errors[] = "Der neue Stand ist kleiner als der vorherige.";
            }
        }
    }

    // Speicherung nach Prüfung
    if (empty($errors)) {
        $current_user = $auth->getCurrentUser();
        $abgelesen_von_id = $current_user['id'];

        if ($isEdit) {
            $params = [$zaehler_id, $steckdose_id, $datum, $stand, $vorheriger_id, $verbrauch, $abgelesen_von_id, $hinweis, $ist_abgerechnet];
            $sql = "UPDATE zaehlerstaende SET zaehler_id=?, steckdose_id=?, datum=?, stand=?, vorheriger_id=?, verbrauch=?, abgelesen_von_id=?, hinweis=?, ist_abgerechnet=?";

            if (!empty($foto_url)) {
                $sql .= ", foto_url=?";
                $params[] = $foto_url;
            }

            $sql .= " WHERE id=?";
            $params[] = $id;

            $db->query($sql, $params);
            $success = "Zählerstand aktualisiert.";
        } else {
            $params = [$zaehler_id, $steckdose_id, $datum, $stand, $vorheriger_id, $verbrauch, $abgelesen_von_id, $hinweis, $ist_abgerechnet];
            $columns = "zaehler_id, steckdose_id, datum, stand, vorheriger_id, verbrauch, abgelesen_von_id, hinweis, ist_abgerechnet";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?";

            if (!empty($foto_url)) {
                $columns .= ", foto_url";
                $placeholders .= ", ?";
                $params[] = $foto_url;
            }

            $db->query("INSERT INTO zaehlerstaende ($columns) VALUES ($placeholders)", $params);
            $success = "Zählerstand gespeichert.";
        }

        header("Location: zaehlerstaende.php?success=" . urlencode($success));
        exit;
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
            <form method="POST" action="<?= $isEdit ? "zaehlerstaende_form.php?id=$id" : "zaehlerstaende_form.php" ?>" enctype="multipart/form-data">
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

                    <?php if (!empty($vorheriger_id)): ?>
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
                        <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">Foto vom Zählerstand</label>
                        <input type="file" id="foto" name="foto" class="w-full border border-gray-300 rounded-md px-3 py-2" accept="image/*">
                        <p class="text-sm text-gray-500 mt-1">JPG, PNG oder GIF, max. 5MB</p>
                        
                        <?php if (!empty($foto_url)): ?>
                            <div class="mt-2">
                                <p class="text-sm font-medium text-gray-700">Vorhandenes Foto:</p>
                                <a href="<?= htmlspecialchars($foto_url) ?>" target="_blank" class="mt-2 inline-block">
                                    <img src="<?= htmlspecialchars($foto_url) ?>" alt="Zählerstand Foto" class="max-h-40 rounded-md border border-gray-300">
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
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
    
    // Vorschau für Foto-Upload
    const fotoInput = document.getElementById('foto');
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Zeige Vorschau nur an, wenn es eine Bilddatei ist
                if (file.type.startsWith('image/')) {
                    // Erstelle Vorschau-Container falls noch nicht vorhanden
                    let previewContainer = document.getElementById('foto-preview');
                    if (!previewContainer) {
                        previewContainer = document.createElement('div');
                        previewContainer.id = 'foto-preview';
                        previewContainer.className = 'mt-2';
                        fotoInput.parentNode.appendChild(previewContainer);
                    }

                    // Leere den Container
                    previewContainer.innerHTML = '';
                    
                    // Füge Überschrift hinzu
                    const title = document.createElement('p');
                    title.textContent = 'Vorschau:';
                    title.className = 'text-sm font-medium text-gray-700';
                    previewContainer.appendChild(title);
                    
                    // Erstelle Bildvorschau
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = 'Zählerstand Vorschau';
                    img.className = 'max-h-40 rounded-md border border-gray-300 mt-1';
                    img.onload = function() {
                        URL.revokeObjectURL(this.src); // Speicher freigeben
                    };
                    previewContainer.appendChild(img);
                }
            }
        });
    }
});
</script>
<?php
require_once 'includes/footer.php';
?>
