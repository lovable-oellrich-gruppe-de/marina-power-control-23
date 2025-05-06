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

    <div class="mt-6 overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Zählernummer</th>
            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Hersteller / Modell</th>
            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Installiert am</th>
            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Letzte Wartung</th>
            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Steckdose / Bereich</th>
            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Status</th>
            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase">Aktionen</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (empty($zaehler)): ?>
            <tr>
              <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-900">Keine Zähler gefunden</td>
            </tr>
          <?php else: ?>
            <?php foreach ($zaehler as $z): ?>
              <tr>
                <td class="px-4 py-1 text-sm font-medium text-gray-900">
                  <?= htmlspecialchars($z['zaehlernummer']) ?>
                  <?php if (!empty($z['seriennummer'])): ?>
                    <div class="text-xs text-gray-900">SN: <?= htmlspecialchars($z['seriennummer']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-1 text-sm text-gray-900">
                  <?= htmlspecialchars($z['hersteller'] ?? '-') ?>
                  <?php if (!empty($z['modell'])): ?>
                    <div class="text-xs"><?= htmlspecialchars($z['modell']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-1 text-sm text-gray-900">
                  <?= date('d.m.Y', strtotime($z['installiert_am'])) ?>
                </td>
                <td class="px-4 py-1 text-sm text-gray-900">
                  <?= !empty($z['letzte_wartung']) ? date('d.m.Y', strtotime($z['letzte_wartung'])) : '-' ?>
                </td>
                <td class="px-4 py-1 text-sm text-gray-900">
                  <?= htmlspecialchars($z['steckdose_bezeichnung'] ?? '-') ?>
                  <?php if (!empty($z['bereich_name'])): ?>
                    <div class="text-xs text-gray-900">(<?= htmlspecialchars($z['bereich_name']) ?>)</div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-1">
                  <?php if ($z['ist_ausgebaut']): ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ausgebaut</span>
                  <?php else: ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktiv</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-1 text-sm font-medium">
                  <div class="flex items-center space-x-4">
                    <a href="zaehler_form.php?id=<?= $z['id'] ?>" class="text-marina-600 hover:text-marina-900" title="Bearbeiten">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>
                    <?php if ($auth->isAdmin()): ?>
                      <a href="#" onclick="confirmDelete(<?= $z['id'] ?>)" class="text-red-600 hover:text-red-900" title="Löschen">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                          <line x1="10" y1="11" x2="10" y2="17"></line>
                          <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
      <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Zähler löschen</h3>
        <p class="text-gray-900 mb-4">Möchten Sie diesen Zähler wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
        <div class="flex justify-end space-x-3">
          <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
          <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
        </div>
      </div>
    </div>
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
