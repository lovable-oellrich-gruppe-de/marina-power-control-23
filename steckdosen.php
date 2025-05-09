<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$success = isset($_GET['success']) ? $_GET['success'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
$info = isset($_GET['info']) ? $_GET['info'] : null;

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$bereich = $_GET['bereich'] ?? '';
$status = $_GET['status'] ?? '';
$mieter = $_GET['mieter'] ?? '';
$zugewiesen = $_GET['zugewiesen'] ?? '';
$orderBy = $_GET['order_by'] ?? 'bezeichnung';
$orderDir = strtoupper($_GET['order_dir'] ?? 'ASC');
$valid_order_by = ['id', 'bezeichnung', 'status', 'bereich_name', 'mieter_name'];
$valid_order_dir = ['ASC', 'DESC'];
if (!in_array($orderBy, $valid_order_by)) $orderBy = 'bezeichnung';
if (!in_array($orderDir, $valid_order_dir)) $orderDir = 'ASC';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $db->query("DELETE FROM steckdosen WHERE id = ?", [$id]);
    if ($result) {
        $success = "Steckdose wurde erfolgreich gelöscht.";
    } else {
        $error = "Fehler beim Löschen der Steckdose: " . $db->error();
    }
}

$sql = "SELECT steckdosen.id, steckdosen.bezeichnung, steckdosen.status,
        COALESCE(bereiche.name, 'Nicht zugewiesen') AS bereich_name,
        COALESCE(CONCAT(COALESCE(mieter.vorname, ''), ' ', COALESCE(mieter.name, '')), 'Nicht zugewiesen') AS mieter_name
        FROM steckdosen
        LEFT JOIN bereiche ON steckdosen.bereich_id = bereiche.id
        LEFT JOIN mieter ON steckdosen.mieter_id = mieter.id
        WHERE 1=1";
$params = [];

if (!empty($bereich)) {
    $sql .= " AND steckdosen.bereich_id = ?";
    $params[] = $bereich;
}
if (!empty($status)) {
    $sql .= " AND steckdosen.status = ?";
    $params[] = $status;
}
if (!empty($mieter)) {
    $sql .= " AND steckdosen.mieter_id = ?";
    $params[] = $mieter;
}
if ($zugewiesen !== '') {
    if ($zugewiesen === '1') {
        $sql .= " AND steckdosen.mieter_id IS NOT NULL";
    } elseif ($zugewiesen === '0') {
        $sql .= " AND steckdosen.mieter_id IS NULL";
    }
}
$sql .= " ORDER BY $orderBy $orderDir";
$steckdosen = $db->fetchAll($sql, $params);
$bereiche = $db->fetchAll("SELECT id, name FROM bereiche ORDER BY name");
$mieter = $db->fetchAll("SELECT id, CONCAT(vorname, ' ', name) AS name FROM mieter ORDER BY name");
$zaehler = $db->fetchAll("SELECT id, zaehlernummer FROM zaehler ORDER BY zaehlernummer");

require_once 'includes/header.php';
?>

<div class="py-6">
  <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Steckdosen verwalten</h1>
      <a href="steckdosen_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
        Neue Steckdose
      </a>
    </div>

    <?php if (isset($success)): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <?php if (isset($info)): ?>
      <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($info) ?>
      </div>
    <?php endif; ?>

    <!-- Filterformular wiederhergestellt -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
      <form method="GET" action="steckdosen.php" class="flex flex-wrap items-end gap-4">
        <div class="w-full sm:w-auto">
          <label for="bereich" class="block text-sm font-medium text-gray-700 mb-1">Bereich</label>
          <select id="bereich" name="bereich" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
            <option value="">Alle Bereiche</option>
            <?php foreach ($bereiche as $b): ?>
              <option value="<?= $b['id'] ?>" <?= $bereich == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="w-full sm:w-auto">
          <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select id="status" name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
            <option value="">Alle Status</option>
            <option value="aktiv" <?= $status === 'aktiv' ? 'selected' : '' ?>>Aktiv</option>
            <option value="inaktiv" <?= $status === 'inaktiv' ? 'selected' : '' ?>>Inaktiv</option>
            <option value="defekt" <?= $status === 'defekt' ? 'selected' : '' ?>>Defekt</option>
          </select>
        </div>
        <div class="w-full sm:w-auto">
          <label for="mieter" class="block text-sm font-medium text-gray-700 mb-1">Mieter</label>
          <select id="mieter" name="mieter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
            <option value="">Alle Mieter</option>
            <?php foreach ($mieter as $m): ?>
              <option value="<?= $m['id'] ?>" <?= $mieter == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="w-full sm:w-auto">
          <label for="zugewiesen" class="block text-sm font-medium text-gray-700 mb-1">Zugewiesen</label>
          <select id="zugewiesen" name="zugewiesen" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
            <option value="">Alle</option>
            <option value="1" <?= $zugewiesen === '1' ? 'selected' : '' ?>>Zugewiesen</option>
            <option value="0" <?= $zugewiesen === '0' ? 'selected' : '' ?>>Nicht zugewiesen</option>
          </select>
        </div>
        <div class="ml-auto">
          <button type="submit" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
            Filtern
          </button>
          <a href="steckdosen.php" class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
            Zurücksetzen
          </a>
        </div>
      </form>
    </div>

<div class="mt-6 bg-white shadow-md rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <?php
                    function sortLink($label, $column) {
                        global $orderBy, $orderDir, $_GET;
                        $dir = ($orderBy === $column && $orderDir === 'ASC') ? 'DESC' : 'ASC';
                        $query = http_build_query(array_merge($_GET, ['order_by' => $column, 'order_dir' => $dir]));
                        $arrow = ($orderBy === $column) ? ($orderDir === 'ASC' ? '▲' : '▼') : '';
                        return "<a href='?{$query}' class='flex items-center space-x-1'>{$label} <span>{$arrow}</span></a>";
                    }
                    ?>
                    <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                        <?= sortLink('ID', 'id') ?>
                    </th>
                    <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                        <?= sortLink('Bezeichnung', 'bezeichnung') ?>
                    </th>
                    <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                        <?= sortLink('Status', 'status') ?>
                    </th>
                    <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                        <?= sortLink('Bereich', 'bereich_name') ?>
                    </th>
                    <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                        <?= sortLink('Mieter', 'mieter_name') ?>
                    </th>
                    <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Aktionen</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($steckdosen)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-1 text-center text-sm text-gray-900">
                            Keine Steckdosen gefunden
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($steckdosen as $s): ?>
                        <tr>
                            <td class="px-4 py-1 whitespace-nowrap text-sm text-gray-900 leading-tight"><?= htmlspecialchars($s['id']) ?></td>
                            <td class="px-4 py-1 whitespace-nowrap text-sm text-gray-900 leading-tight">
                                <?= htmlspecialchars($s['bezeichnung']) ?>
                            </td>
                            <td class="px-4 py-1 whitespace-nowrap">
                                <?php if ($s['status'] === 'aktiv'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Aktiv
                                    </span>
                                <?php elseif ($s['status'] === 'inaktiv'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Inaktiv
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Defekt
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-1 whitespace-nowrap text-sm text-gray-900 leading-tight">
                                <?= htmlspecialchars($s['bereich_name']) ?>
                            </td>
                            <td class="px-4 py-1 whitespace-nowrap text-sm text-gray-900 leading-tight">
                                <?= htmlspecialchars($s['mieter_name']) ?>
                            </td>
                            <td class="px-4 py-1 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-3">
                                    <a href="steckdosen_form.php?id=<?= $s['id'] ?>" class="text-marina-600 hover:text-marina-900" title="Bearbeiten">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <a href="#" onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['bezeichnung'])) ?>')" class="text-red-600 hover:text-red-900" title="Löschen">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
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

<!-- Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Steckdose löschen</h3>
        <p class="text-gray-900 mb-4">Möchten Sie die Steckdose <span id="steckdosenName"></span> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
            <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('steckdosenName').textContent = name;
    document.getElementById('deleteLink').href = 'steckdosen.php?delete=' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php require_once 'includes/footer.php'; ?>
