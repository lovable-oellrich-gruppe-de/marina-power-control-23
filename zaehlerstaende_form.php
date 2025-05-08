<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$upload_dir = 'uploads/zaehlerstaende';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$id = null;
$datum = date('Y-m-d');
$stand = '';
$hinweis = '';
$foto_url = '';
$errors = [];
$pageTitle = 'Neuen Zählerstand erfassen';
$isEdit = false;
$zaehler_id = '';

$zaehler = $db->fetchAll("SELECT z.id, z.zaehlernummer, z.hinweis, s.bezeichnung AS steckdose, b.name AS bereich FROM zaehler z LEFT JOIN steckdosen s ON z.steckdose_id = s.id LEFT JOIN bereiche b ON s.bereich_id = b.id ORDER BY z.zaehlernummer");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $isEdit = true;
    $pageTitle = 'Zählerstand bearbeiten';

    $zs = $db->fetchOne("SELECT * FROM zaehlerstaende WHERE id = ?", [$id]);
    if ($zs) {
        $zaehler_id = $zs['zaehler_id'];
        $datum = $zs['datum'];
        $stand = $zs['stand'];
        $hinweis = $zs['hinweis'];
        $foto_url = $zs['foto_url'];
    } else {
        $errors[] = "Zählerstand nicht gefunden.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user = $auth->getCurrentUser();

    if (isset($_POST['delete_foto']) && $isEdit && isset($current_user) && $current_user['role'] === 'admin') {
        $fotoInfo = $db->fetchOne("SELECT foto_url FROM zaehlerstaende WHERE id = ?", [$id]);
        if (!empty($fotoInfo['foto_url']) && file_exists($fotoInfo['foto_url'])) {
            unlink($fotoInfo['foto_url']);
        }
        $db->query("UPDATE zaehlerstaende SET foto_url = NULL WHERE id = ?", [$id]);
        header("Location: zaehlerstaende_form.php?id=$id&success=" . urlencode("Foto wurde erfolgreich gelöscht."));
        exit;
    }

    $zaehler_id = !empty($_POST['zaehler_id']) ? (int)$_POST['zaehler_id'] : null;
    $datum = $_POST['datum'] ?? '';
    $stand = str_replace(',', '.', $_POST['stand'] ?? '');
    $hinweis = $_POST['hinweis'] ?? '';

    $zaehlerInfo = $db->fetchOne("SELECT s.id, CONCAT(m.vorname, ' ', m.name) AS mieter_name FROM zaehler z LEFT JOIN steckdosen s ON z.steckdose_id = s.id LEFT JOIN mieter m ON s.mieter_id = m.id WHERE z.id = ?", [$zaehler_id]);
    $steckdose_id = $zaehlerInfo['id'] ?? null;
    $mieter_name = $zaehlerInfo['mieter_name'] ?? null;

    if (empty($zaehler_id)) {
        $errors[] = "Bitte einen Zähler auswählen.";
    }
    if (empty($datum)) {
        $errors[] = "Bitte ein Datum eingeben.";
    }
    if (!is_numeric($stand)) {
        $errors[] = "Der Zählerstand muss eine Zahl sein.";
    }

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

    if (empty($errors)) {
        $verbrauch = null;
        $vorheriger = $db->fetchOne("SELECT id, stand FROM zaehlerstaende WHERE zaehler_id = ? AND datum < ? ORDER BY datum DESC, id DESC LIMIT 1", [$zaehler_id, $datum]);
        $vorheriger_id = $vorheriger['id'] ?? null;

        if ($vorheriger) {
            $verbrauch = $stand - $vorheriger['stand'];
            if ($verbrauch < 0) {
                $errors[] = "Der neue Stand ist kleiner als der vorherige.";
            }
        } else {
            $verbrauch = null; // Kein Vorwert = kein Vergleich = keine Fehlermeldung
            $vorheriger_id = null;
        }
    }

    if (empty($errors)) {
        $abgelesen_von_id = $current_user['id'];

        if ($isEdit) {
            $params = [$zaehler_id, $datum, $stand, $vorheriger_id, $verbrauch, $abgelesen_von_id, $hinweis, $mieter_name];
            $sql = "UPDATE zaehlerstaende SET zaehler_id=?, datum=?, stand=?, vorheriger_id=?, verbrauch=?, abgelesen_von_id=?, hinweis=?, mieter_name=?";
            if (!empty($foto_url)) {
                $sql .= ", foto_url=?";
                $params[] = $foto_url;
            }
            $sql .= " WHERE id=?";
            $params[] = $id;

            $db->query($sql, $params);
        } else {
            $params = [$zaehler_id, $datum, $stand, $vorheriger_id, $verbrauch, $abgelesen_von_id, $hinweis, $mieter_name];
            $columns = "zaehler_id, datum, stand, vorheriger_id, verbrauch, abgelesen_von_id, hinweis, mieter_name";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, ?";
            if (!empty($foto_url)) {
                $columns .= ", foto_url";
                $placeholders .= ", ?";
                $params[] = $foto_url;
            }
            $db->query("INSERT INTO zaehlerstaende ($columns) VALUES ($placeholders)", $params);
        }

        header("Location: zaehlerstaende.php?success=" . urlencode("Zählerstand gespeichert."));
        exit;
    }
}

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
                        <label for="steckdose_id" class="block text-sm font-medium text-gray-700">Steckdose (optional)</label>
                        <select id="steckdose_id" name="steckdose_id"
                                class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                            <option value="">Keine Steckdose zugewiesen</option>
                            <?php foreach ($steckdosen as $steckdose): ?>
                                <option value="<?= $steckdose['id'] ?>" <?= ((int)($zaehler['steckdose_id'] ?? 0) === (int)$steckdose['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($steckdose['bezeichnung']) ?> (<?= htmlspecialchars($steckdose['bereich_name'] ?? 'kein Bereich') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
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

                    <div class="space-y-2">
                        <label for="parent_id" class="block text-sm font-medium text-gray-700">Übergeordneter Zähler (optional)</label>
                        <select id="parent_id" name="parent_id"
                                class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
                            <option value="">Kein übergeordneter Zähler</option>
                            <?php foreach ($alle_zaehler as $z): ?>
                                <option value="<?= $z['id'] ?>" <?= ((int)($zaehler['parent_id'] ?? 0) === (int)$z['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($z['zaehlernummer']) ?><?= $z['bereich_name'] ? ' – ' . htmlspecialchars($z['bereich_name']) : '' ?><?= $z['hinweis'] ? ' – ' . htmlspecialchars($z['hinweis']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
