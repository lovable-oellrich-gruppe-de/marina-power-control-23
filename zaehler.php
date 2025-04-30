<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$success_message = $_GET['success'] ?? '';
$info_message = $_GET['info'] ?? '';
$error_message = $_GET['error'] ?? '';

$search = $_GET['search'] ?? '';
$orderBy = $_GET['order_by'] ?? 'installiert_am';
$orderDir = strtoupper($_GET['order_dir'] ?? 'DESC');
$bereich = $_GET['bereich'] ?? '';
$status = $_GET['status'] ?? '';

$valid_order_by = ['zaehlernummer', 'hersteller', 'modell', 'installiert_am', 'letzte_wartung'];
$valid_order_dir = ['ASC', 'DESC'];
if (!in_array($orderBy, $valid_order_by)) $orderBy = 'installiert_am';
if (!in_array($orderDir, $valid_order_dir)) $orderDir = 'DESC';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if ($auth->isAdmin()) {
        $zaehler_id = (int)$_GET['delete'];
        $in_use = $db->fetchOne("SELECT COUNT(*) as count FROM zaehlerstaende WHERE zaehler_id = ?", [$zaehler_id])['count'] ?? 0;
        if ($in_use > 0) {
            header("Location: zaehler.php?error=" . urlencode("Dieser Zähler hat Messdaten und kann nicht gelöscht werden. Markieren Sie ihn stattdessen als ausgebaut."));
            exit;
        } else {
            if ($db->query("DELETE FROM zaehler WHERE id = ?", [$zaehler_id])) {
                header("Location: zaehler.php?success=" . urlencode("Zähler wurde erfolgreich gelöscht."));
                exit;
            } else {
                header("Location: zaehler.php?error=" . urlencode("Fehler beim Löschen des Zählers."));
                exit;
            }
        }
    } else {
        header("Location: zaehler.php?error=" . urlencode("Sie haben nicht die erforderlichen Rechte, um Zähler zu löschen."));
        exit;
    }
}

$sql = "SELECT z.*, s.bezeichnung AS steckdose_bezeichnung, b.name AS bereich_name
    FROM zaehler z
    LEFT JOIN steckdosen s ON z.steckdose_id = s.id
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    WHERE 1=1";

$search_params = [];
if (!empty($search)) {
    $sql .= " AND (z.zaehlernummer LIKE ? OR z.hersteller LIKE ? OR z.modell LIKE ? OR z.seriennummer LIKE ?)";
    $search_params = array_fill(0, 4, "%$search%");
}

if (!empty($bereich)) {
    $sql .= " AND s.bereich_id = ?";
    $search_params[] = $bereich;
}

if ($status !== '') {
    if ($status == 'aktiv') {
        $sql .= " AND (z.ist_ausgebaut = 0 OR z.ist_ausgebaut IS NULL)";
    } elseif ($status == 'ausgebaut') {
        $sql .= " AND z.ist_ausgebaut = 1";
    }
}

$sql .= " ORDER BY $orderBy $orderDir";
$zaehler = $db->fetchAll($sql, $search_params);
$bereiche = $db->fetchAll("SELECT id, name FROM bereiche ORDER BY name");

require_once 'includes/header.php';
?>

<div class="py-6">
  <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900">Zähler</h1>
      <a href="zaehler_form.php" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-marina-600 hover:bg-marina-700">
        Neuen Zähler erstellen
      </a>
    </div>

    <?php if (!empty($success_message)): ?>
      <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
      <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <?= htmlspecialchars($error_message) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($info_message)): ?>
      <div class="mt-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
        <?= htmlspecialchars($info_message) ?>
      </div>
    <?php endif; ?>

    <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg p-4">
      <form method="GET" action="zaehler.php" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-700 mb-1">Suche</label>
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Zählernummer, Hersteller, Modell..." class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md p-2">
        </div>
        <div class="min-w-[180px]">
          <label class="block text-sm font-medium text-gray-700 mb-1">Bereich</label>
          <select name="bereich" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
            <option value="">Alle Bereiche</option>
            <?php foreach ($bereiche as $b): ?>
              <option value="<?= $b['id'] ?>" <?= ($bereich == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="min-w-[180px]">
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
            <option value="">Alle</option>
            <option value="aktiv" <?= $status === 'aktiv' ? 'selected' : '' ?>>Aktiv</option>
            <option value="ausgebaut" <?= $status === 'ausgebaut' ? 'selected' : '' ?>>Ausgebaut</option>
          </select>
        </div>
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-marina-600 hover:bg-marina-700">
          Filtern
        </button>
        <?php if (!empty($search) || !empty($bereich) || !empty($status)): ?>
        <a href="zaehler.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
          Zurücksetzen
        </a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Tabelle wie gehabt -->
    <!-- ... (dein bestehendes HTML für die Tabelle bleibt bestehen) -->
  </div>
</div>

<script>
function confirmDelete(id) {
  document.getElementById('deleteLink').href = 'zaehler.php?delete=' + id;
  document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
  document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php require_once 'includes/footer.php'; ?>
