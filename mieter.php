<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
$info = $_GET['info'] ?? null;
$search = $_GET['search'] ?? '';
$orderBy = $_GET['order_by'] ?? 'name';
$orderDir = strtoupper($_GET['order_dir'] ?? 'ASC');
$valid_order_by = ['id', 'vorname', 'name', 'email', 'telefon', 'bootsname'];
$valid_order_dir = ['ASC', 'DESC'];
if (!in_array($orderBy, $valid_order_by)) $orderBy = 'name';
if (!in_array($orderDir, $valid_order_dir)) $orderDir = 'ASC';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];
        if ($db->query("DELETE FROM mieter WHERE id = ?", [$id])) {
            header("Location: mieter.php?success=" . urlencode("Mieter wurde erfolgreich gelöscht."));
            exit;
        } else {
            header("Location: mieter.php?error=" . urlencode("Mieter konnte nicht gelöscht werden oder existiert nicht."));
            exit;
        }
    } catch (Exception $e) {
        header("Location: mieter.php?error=" . urlencode("Fehler beim Löschen des Mieters: " . $e->getMessage()));
        exit;
    }
}

$params = [];
$sql = "SELECT * FROM mieter WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (vorname LIKE ? OR name LIKE ? OR email LIKE ? OR telefon LIKE ? OR bootsname LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 5, $searchTerm);
}

$sql .= " ORDER BY $orderBy $orderDir";
$mieter = $db->fetchAll($sql, $params);

require_once 'includes/header.php';
?>

<div class="py-6">
  <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Mieter verwalten</h1>
      <a href="mieter_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">Neuer Mieter</a>
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
    <?php if (!empty($info)): ?>
      <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($info) ?>
      </div>
    <?php endif; ?>

    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
      <form method="GET" action="mieter.php" class="flex flex-wrap items-end gap-4">
        <div class="w-full md:w-1/2">
          <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Suche</label>
          <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, E-Mail, Telefon..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
        </div>
        <div class="ml-auto">
          <button type="submit" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">Suchen</button>
          <a href="mieter.php" class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Zurücksetzen</a>
        </div>
      </form>
    </div>

    <div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">
      <div class="overflow-x-auto w-full">
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('ID', 'id') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('Name', 'name') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('E-Mail', 'email') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('Telefon', 'telefon') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('Bootsname', 'bootsname') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($mieter)): ?>
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Keine Mieter gefunden</td>
              </tr>
            <?php else: ?>
              <?php foreach ($mieter as $m): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $m['id'] ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($m['vorname'] . ' ' . $m['name']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['email']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['telefon'] ?? '') ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['bootsname'] ?? '') ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-4">
                      <a href="mieter_form.php?id=<?= $m['id'] ?>" class="text-marina-600 hover:text-marina-900 p-1" title="Bearbeiten">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                      </a>
                      <a href="#" onclick="confirmDelete(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['vorname'] . ' ' . $m['name'])) ?>')" class="text-red-600 hover:text-red-900 p-1" title="Löschen">
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
  </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
    <h3 class="text-lg font-medium text-gray-900 mb-2">Mieter löschen</h3>
    <p class="text-gray-500 mb-4">Möchten Sie den Mieter <span id="mieterName"></span> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
    <div class="flex justify-end space-x-3">
      <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
      <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
    </div>
  </div>
</div>

<script>
function confirmDelete(id, name) {
  document.getElementById('mieterName').textContent = name;
  document.getElementById('deleteLink').href = 'mieter.php?delete=' + id;
  document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
  document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php require_once 'includes/footer.php'; ?>
