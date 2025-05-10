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
$repeat = isset($_POST['repeat']) && $_POST['repeat'] === '1';

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

        if ($repeat) {
            header("Location: zaehlerstaende_form.php?success=" . urlencode($success) . "&datum=" . urlencode($datum));
        } else {
            header("Location: zaehlerstaende.php?success=" . urlencode($success));
        }
        exit;
    }
}

if (isset($_GET['datum'])) {
    $datum = $_GET['datum'];
}

require_once 'includes/header.php';
?>



<!-- Styles und Scripts für Tom Select -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<!-- HTML-Formular für Zählerstand-Erfassung -->
<div class="py-6">
  <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
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

    <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
      <form method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

          <!-- Zähler Dropdown mit Suche -->
          <div class="space-y-2 sm:col-span-2">
            <label for="zaehler_id" class="block text-sm font-medium text-gray-700">Zähler *</label>
            <select id="zaehler_id" name="zaehler_id" required>
              <option value="">Bitte wählen...</option>
              <?php foreach ($zaehler as $z): ?>
                <option value="<?= $z['id'] ?>" <?= ((int)$zaehler_id === (int)$z['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($z['zaehlernummer']) ?>
                  <?= !empty($z['hinweis']) ? ' – ' . htmlspecialchars($z['hinweis']) : '' ?>
                  <?= !empty($z['bereich_name']) ? ' – ' . htmlspecialchars($z['bereich_name']) : '' ?>
                  <?= !empty($z['steckdose_bezeichnung']) ? ' – ' . htmlspecialchars($z['steckdose_bezeichnung']) : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <script>
              new TomSelect('#zaehler_id', {
                create: false,
                allowEmptyOption: true,
                placeholder: 'Zähler auswählen...'
              });
            </script>
          </div>

          <!-- Datum Eingabe -->
          <div class="space-y-2">
            <label for="datum" class="block text-sm font-medium text-gray-700">Datum *</label>
            <input type="date" id="datum" name="datum" value="<?= htmlspecialchars($datum) ?>" required class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
          </div>

          <!-- Zählerstand Eingabe -->
          <div class="space-y-2">
            <label for="stand" class="block text-sm font-medium text-gray-700">Zählerstand (kWh) *</label>
            <input type="text" id="stand" name="stand" value="<?= htmlspecialchars($stand) ?>" required placeholder="z.B. 1234,56" class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
          </div>

          <!-- Foto Upload -->
          <div class="sm:col-span-2 space-y-2">
            <label for="foto" class="block text-sm font-medium text-gray-700">Foto (optional)</label>
            <input type="file" id="foto" name="foto" accept="image/*" class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500">
            <div id="foto_preview" class="mt-2"></div>
          </div>

          <?php if (!empty($foto_url)): ?>
            <div class="sm:col-span-2 mt-4">
              <p class="text-sm font-medium text-gray-700 mb-2">Aktuelles Foto:</p>
              <div class="flex items-start space-x-4">
                <a href="<?= htmlspecialchars($foto_url) ?>" target="_blank" class="inline-block">
                  <img src="<?= htmlspecialchars($foto_url) ?>" alt="Zählerstand Foto" class="max-h-40 rounded-md border border-gray-300">
                </a>

                <?php if (isset($current_user) && $current_user['role'] === 'admin'): ?>
                  <form method="POST" action="zaehlerstaende_form.php?id=<?= (int)$id ?>" onsubmit="return confirm('Möchten Sie das Foto wirklich löschen?');">
                    <input type="hidden" name="delete_foto" value="1">
                    <button type="submit" class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                      Foto löschen
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Hinweis Textarea -->
          <div class="sm:col-span-2 space-y-2">
            <label for="hinweis" class="block text-sm font-medium text-gray-700">Hinweis</label>
            <textarea id="hinweis" name="hinweis" rows="3" class="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"><?= htmlspecialchars($hinweis) ?></textarea>
          </div>
        </div>

        <!-- Formular-Buttons -->
        <div class="mt-6 flex justify-end space-x-3">
          <a href="zaehlerstaende.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
            Abbrechen
          </a>
          <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">
            <?= $isEdit ? 'Aktualisieren' : 'Speichern' ?>
          </button>
          <?php if (!$isEdit): ?>
            <button type="submit" name="repeat" value="1" class="px-4 py-2 bg-marina-500 text-white rounded hover:bg-marina-600">
              Speichern & neuen Stand erfassen
            </button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Vorschau und Komprimierung für Foto-Upload -->
<script>
document.getElementById('foto').addEventListener('change', function(event) {
  const file = event.target.files[0];
  if (!file) return;

  const maxWidth = 1200;
  const reader = new FileReader();

  reader.onload = function(e) {
    const img = new Image();
    img.onload = function() {
      const previewContainer = document.getElementById('foto_preview');
      previewContainer.innerHTML = '';
      const previewImage = document.createElement('img');
      previewImage.src = e.target.result;
      previewImage.classList.add('max-h-40', 'rounded-md', 'border', 'border-gray-300');
      previewContainer.appendChild(previewImage);

      if (img.width > maxWidth) {
        const canvas = document.createElement('canvas');
        const scale = maxWidth / img.width;
        canvas.width = maxWidth;
        canvas.height = img.height * scale;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(function(blob) {
          const resizedFile = new File([blob], file.name, { type: file.type });
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(resizedFile);
          document.getElementById('foto').files = dataTransfer.files;
        }, file.type, 0.85);
      }
    };
    img.src = e.target.result;
  };
</script>

<?php
// Footer einbinden
require_once 'includes/footer.php';
?>
