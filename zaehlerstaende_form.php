<?php
// Deine bestehenden Includes
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
$steckdosen = $db->fetchAll("SELECT s.id, s.bezeichnung, b.name AS bereich_name FROM steckdosen s LEFT JOIN bereiche b ON s.bereich_id = b.id ORDER BY b.name, s.bezeichnung");

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

        // Prüfen, ob die zugehörige Steckdose in der Liste ist
        if ($steckdose_id) {
            $steckdose_exists = false;
            foreach ($steckdosen as $s) {
                if ((int)$s['id'] === (int)$steckdose_id) {
                    $steckdose_exists = true;
                    break;
                }
            }

            if (!$steckdose_exists) {
                // Fehlende Steckdose nachladen und hinzufügen
                $single_steckdose = $db->fetchOne("SELECT s.id, s.bezeichnung, b.name AS bereich_name FROM steckdosen s LEFT JOIN bereiche b ON s.bereich_id = b.id WHERE s.id = ?", [$steckdose_id]);
                if ($single_steckdose) {
                    $steckdosen[] = $single_steckdose;
                }
            }
        }
    } else {
        $errors[] = "Zählerstand nicht gefunden.";
    }
}

// Formularverarbeitung und weitere Logik bleibt wie gehabt...

// Header einbinden
require_once 'includes/header.php';
?>

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

        <!-- Formular -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <form method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="zaehler_id" class="block text-sm font-medium text-gray-700">Zähler *</label>
                        <select id="zaehler_id" name="zaehler_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($zaehler as $z): ?>
                                <option value="<?= $z['id'] ?>" <?= $zaehler_id == $z['id'] ? 'selected' : '' ?>><?= htmlspecialchars($z['zaehlernummer']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="steckdose_id" class="block text-sm font-medium text-gray-700">Steckdose</label>
                        <select id="steckdose_id" name="steckdose_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($steckdosen as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ((int)$steckdose_id === (int)$s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['bezeichnung']) ?> (<?= htmlspecialchars($s['bereich_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="datum" class="block text-sm font-medium text-gray-700">Datum *</label>
                        <input type="date" id="datum" name="datum" value="<?= htmlspecialchars($datum) ?>" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                    </div>

                    <div class="space-y-2">
                        <label for="stand" class="block text-sm font-medium text-gray-700">Zählerstand (kWh) *</label>
                        <input type="text" id="stand" name="stand" value="<?= htmlspecialchars($stand) ?>" required placeholder="z.B. 1234,56" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                    </div>

                    <div class="col-span-2 space-y-2">
                        <label for="foto" class="block text-sm font-medium text-gray-700">Foto (optional)</label>
                        <input type="file" id="foto" name="foto" accept="image/*" class="w-full rounded-md border border-gray-300 px-3 py-2">
                    </div>

                    <div class="col-span-2 space-y-2">
                        <label for="hinweis" class="block text-sm font-medium text-gray-700">Hinweis</label>
                        <textarea id="hinweis" name="hinweis" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500"><?= htmlspecialchars($hinweis) ?></textarea>
                    </div>
                </div>

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
require_once 'includes/footer.php';
?>
