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
$zaehler_id = '';
$datum = date('Y-m-d');
$stand = '';
$hinweis = '';
$foto_url = '';
$errors = [];
$pageTitle = 'Neuen Zählerstand erfassen';
$isEdit = false;

$zaehler = $db->fetchAll("SELECT 
        z.id, 
        z.zaehlernummer, 
        z.hinweis,
        s.id AS steckdose_id,
        s.bezeichnung AS steckdose_bezeichnung,
        b.name AS bereich_name
    FROM zaehler z
    LEFT JOIN steckdosen s ON z.steckdose_id = s.id
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    ORDER BY z.zaehlernummer");

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

    $zaehler_id = !empty($_POST['zaehler_id']) ? $_POST['zaehler_id'] : null;
    $datum = $_POST['datum'] ?? '';
    $stand = str_replace(',', '.', $_POST['stand'] ?? '');
    $hinweis = $_POST['hinweis'] ?? '';

    $steckdose_id = null;
    $mieter_name = null;

    if (!empty($zaehler_id)) {
        $zaehlerInfo = $db->fetchOne("SELECT steckdose_id FROM zaehler WHERE id = ?", [$zaehler_id]);
        $steckdose_id = $zaehlerInfo['steckdose_id'] ?? null;

        if (!empty($steckdose_id)) {
            $mieterInfo = $db->fetchOne("SELECT CONCAT(m.vorname, ' ', m.name) AS mieter_name FROM steckdosen s LEFT JOIN mieter m ON s.mieter_id = m.id WHERE s.id = ?", [$steckdose_id]);
            $mieter_name = $mieterInfo['mieter_name'] ?? null;
        }
    }

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
        $vorheriger = $db->fetchOne("SELECT stand, datum FROM zaehlerstaende WHERE zaehler_id = ? AND datum < ? ORDER BY datum DESC, id DESC LIMIT 1", [$zaehler_id, $datum]);
        if ($vorheriger && $stand < $vorheriger['stand']) {
            $errors[] = "Der neue Stand ist kleiner als der bisherige Stand vom " . $vorheriger['datum'] . ".";
        }
    }

    if (empty($errors)) {
        $abgelesen_von_id = $current_user['id'];

        if ($isEdit) {
            $params = [$zaehler_id, $datum, $stand, $abgelesen_von_id, $hinweis, $mieter_name];
            $sql = "UPDATE zaehlerstaende SET zaehler_id=?, datum=?, stand=?, abgelesen_von_id=?, hinweis=?, mieter_name=?";
            if (!empty($foto_url)) {
                $sql .= ", foto_url=?";
                $params[] = $foto_url;
            }
            $sql .= " WHERE id=?";
            $params[] = $id;

            $db->query($sql, $params);
            $success = "Zählerstand wurde erfolgreich aktualisiert.";
        } else {
            $params = [$zaehler_id, $datum, $stand, $abgelesen_von_id, $hinweis, $mieter_name];
            $columns = "zaehler_id, datum, stand, abgelesen_von_id, hinweis, mieter_name";
            $placeholders = "?, ?, ?, ?, ?, ?";
            if (!empty($foto_url)) {
                $columns .= ", foto_url";
                $placeholders .= ", ?";
                $params[] = $foto_url;
            }
            $db->query("INSERT INTO zaehlerstaende ($columns) VALUES ($placeholders)", $params);
            $success = "Zählerstand wurde erfolgreich gespeichert.";
        }

        header("Location: zaehlerstaende.php?success=" . urlencode($success));
        exit;
    }
}

require_once 'includes/header.php';
?>

<!-- HTML ab hier -->
<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Zählerstände verwalten</h1>
            <a href="zaehlerstaende_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                Neuer Zählerstand
            </a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Filterformular -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <form method="GET" action="zaehlerstaende.php" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Suche</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500" placeholder="Zählernummer oder Steckdose">
                </div>

                <div class="w-full md:w-auto">
                    <label for="bereich" class="block text-sm font-medium text-gray-700 mb-1">Bereich</label>
                    <select id="bereich" name="bereich" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle Bereiche</option>
                        <?php foreach ($bereiche as $bereich): ?>
                            <option value="<?= $bereich['id'] ?>" <?= (($_GET['bereich'] ?? '') == $bereich['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($bereich['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-full md:w-auto">
                    <label for="mieter" class="block text-sm font-medium text-gray-700 mb-1">Mieter</label>
                    <select id="mieter" name="mieter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle Mieter</option>
                        <?php foreach ($mieter as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= (($_GET['mieter'] ?? '') == $m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['vollname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-full md:w-auto">
                    <label for="von" class="block text-sm font-medium text-gray-700 mb-1">Datum von</label>
                    <input type="date" id="von" name="von" value="<?= htmlspecialchars($_GET['von'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                </div>

                <div class="w-full md:w-auto">
                    <label for="bis" class="block text-sm font-medium text-gray-700 mb-1">Datum bis</label>
                    <input type="date" id="bis" name="bis" value="<?= htmlspecialchars($_GET['bis'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                </div>

                <div class="w-full md:w-auto flex items-end">
                    <button type="submit" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                        Filtern
                    </button>
                    <a href="zaehlerstaende.php" class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                        Zurücksetzen
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabelle -->
        <div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('id', 'ID') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('datum', 'Datum') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('zaehlernummer', 'Zähler') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('zaehlerhinweis', 'Hinweis') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('steckdose_bezeichnung', 'Steckdose') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('bereich_name', 'Bereich') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('mieter_name', 'Mieter') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('stand', 'Stand (kWh)') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Foto</th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase"><?= sortLink('verbrauch', 'Verbrauch (kWh)') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($zaehlerstaende)): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-900">Keine Zählerstände gefunden</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($zaehlerstaende as $zs): ?>
                                <tr>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= $zs['id'] ?></td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= date('d.m.Y', strtotime($zs['datum'])) ?></td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= htmlspecialchars($zs['zaehlernummer'] ?? '-') ?></td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= htmlspecialchars($zs['zaehlerhinweis'] ?? '-') ?></td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= htmlspecialchars($zs['steckdose_bezeichnung'] ?? '-') ?></td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= htmlspecialchars($zs['bereich_name'] ?? '-') ?></td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= htmlspecialchars($zs['mieter_name'] ?? '-') ?></td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= number_format($zs['stand'], 2, ',', '.') ?> kWh</td>
                                    <td class="px-4 py-1 whitespace-nowrap text-sm text-gray-900">
                                        <?php if (!empty($zs['foto_url'])): ?>
                                            <a href="#" onclick="showImageModal('<?= htmlspecialchars($zs['foto_url']) ?>'); return false;">
                                                <img src="<?= htmlspecialchars($zs['foto_url']) ?>" alt="Foto" class="h-8 w-8 object-cover rounded shadow">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">–</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= $zs['verbrauch'] !== null ? number_format($zs['verbrauch'], 2, ',', '.') . ' kWh' : '-' ?></td>
                                    <td class="px-4 py-1 text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <a href="zaehlerstaende_form.php?id=<?= $zs['id'] ?>" class="text-marina-600 hover:text-marina-900" title="Bearbeiten">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?= $zs['id'] ?>, '<?= date('d.m.Y', strtotime($zs['datum'])) ?>', '<?= addslashes($zs['zaehlernummer']) ?>')" class="text-red-600 hover:text-red-900" title="Löschen">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal fürs Löschen -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Zählerstand löschen</h3>
        <p class="text-gray-900 mb-4">Möchten Sie den Zählerstand vom <span id="zaehlerDatum"></span> für Zähler <span id="zaehlerNummer"></span> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
            <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
        </div>
    </div>
</div>
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-60 hidden justify-center items-center z-50">
    <div class="relative bg-white rounded-lg overflow-hidden shadow-lg">
        <button onclick="closeImageModal()" class="absolute top-2 right-2 text-white bg-red-500 rounded-full px-2 py-1 text-xs hover:bg-red-600 z-10">X</button>
        <img id="modalImage" src="" alt="Vorschau" class="max-h-screen max-w-screen p-4">
    </div>
</div>

<script>
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('imageModal').classList.add('flex');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageModal').classList.remove('flex');
    document.getElementById('modalImage').src = '';
}

function confirmDelete(id, datum, zaehlerNummer) {
    document.getElementById('zaehlerDatum').textContent = datum;
    document.getElementById('zaehlerNummer').textContent = zaehlerNummer;
    document.getElementById('deleteLink').href = 'zaehlerstaende.php?delete=' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php
require_once 'includes/footer.php';
?>
