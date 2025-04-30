<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!$auth->isAdmin()) {
    header('Location: index.php');
    exit;
}

$search = $_GET['search'] ?? '';
$rolle = $_GET['rolle'] ?? '';
$status = $_GET['status'] ?? '';
$orderBy = $_GET['order_by'] ?? 'erstellt_am';
$orderDir = strtoupper($_GET['order_dir'] ?? 'DESC');
$valid_order_by = ['name', 'email', 'rolle', 'status', 'erstellt_am'];
$valid_order_dir = ['ASC', 'DESC'];
if (!in_array($orderBy, $valid_order_by)) $orderBy = 'erstellt_am';
if (!in_array($orderDir, $valid_order_dir)) $orderDir = 'DESC';

if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    $user = $db->fetchOne("SELECT status FROM benutzer WHERE id = ?", [$userId]);
    if ($user) {
        $newStatus = ($user['status'] === 'active') ? 'pending' : 'active';
        $db->query("UPDATE benutzer SET status = ? WHERE id = ?", [$newStatus, $userId]);
        header('Location: users.php?status_changed=1');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'toggle_role' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    $user = $db->fetchOne("SELECT rolle FROM benutzer WHERE id = ?", [$userId]);
    if ($user) {
        $newRole = ($user['rolle'] === 'admin') ? 'user' : 'admin';
        $db->query("UPDATE benutzer SET rolle = ? WHERE id = ?", [$newRole, $userId]);
        header('Location: users.php?role_changed=1');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    if ($userId !== $_SESSION['user_id']) {
        $db->query("DELETE FROM benutzer WHERE id = ?", [$userId]);
        header('Location: users.php?deleted=1');
        exit;
    } else {
        header('Location: users.php?error=self_delete');
        exit;
    }
}

$params = [];
$sql = "SELECT id, email, name, rolle, status, erstellt_am FROM benutzer WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($rolle)) {
    $sql .= " AND rolle = ?";
    $params[] = $rolle;
}
if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY $orderBy $orderDir";
$users = $db->fetchAll($sql, $params);

require_once 'includes/header.php';
function sortLink($label, $column) {
    global $orderBy, $orderDir, $_GET;
    $dir = ($orderBy === $column && $orderDir === 'ASC') ? 'DESC' : 'ASC';
    $query = http_build_query(array_merge($_GET, ['order_by' => $column, 'order_dir' => $dir]));
    $arrow = ($orderBy === $column) ? ($orderDir === 'ASC' ? '▲' : '▼') : '';
    return "<a href='?{$query}' class='flex items-center space-x-1'>{$label} <span>{$arrow}</span></a>";
}
?>

<div class="py-6">
  <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Benutzerverwaltung</h1>
      <a href="user_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">Neuer Benutzer</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-4 items-end bg-white p-4 rounded-lg shadow mb-6">
      <div class="flex-1 min-w-[200px]">
        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Suche</label>
        <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name oder E-Mail" class="rounded-md border-gray-300 shadow-sm focus:ring-marina-500 focus:border-marina-500">
      </div>
      <div class="w-full sm:w-auto">
        <label for="rolle" class="block text-sm font-medium text-gray-700 mb-1">Rolle</label>
        <select name="rolle" id="rolle" class="rounded-md border-gray-300 shadow-sm focus:ring-marina-500 focus:border-marina-500">
          <option value="">Alle</option>
          <option value="admin" <?= $rolle === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="user" <?= $rolle === 'user' ? 'selected' : '' ?>>Benutzer</option>
        </select>
      </div>
      <div class="w-full sm:w-auto">
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status" id="status" class="rounded-md border-gray-300 shadow-sm focus:ring-marina-500 focus:border-marina-500">
          <option value="">Alle</option>
          <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktiv</option>
          <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Ausstehend</option>
        </select>
      </div>
      <div class="ml-auto">
        <button type="submit" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">Filtern</button>
        <a href="users.php" class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Zurücksetzen</a>
      </div>
    </form>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('Name', 'name') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('E-Mail', 'email') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('Rolle', 'rolle') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('Status', 'status') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= sortLink('Erstellt am', 'erstellt_am') ?></th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($users)): ?>
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Keine Benutzer gefunden</td>
              </tr>
            <?php else: ?>
              <?php foreach ($users as $user): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= $user['rolle'] === 'admin' ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>' : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Benutzer</span>' ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= $user['status'] === 'active' ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktiv</span>' : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Ausstehend</span>' ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d.m.Y H:i', strtotime($user['erstellt_am'])) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                                            <a href="users.php?action=toggle_status&id=<?= $user['id'] ?>" class="text-marina-600 hover:text-marina-900 p-1" title="Status ändern">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                                </svg>
                                            </a>
                                            <a href="users.php?action=toggle_role&id=<?= $user['id'] ?>" class="text-marina-600 hover:text-marina-900 p-1" title="Rolle ändern">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="8.5" cy="7" r="4"></circle>
                                                    <polyline points="17 11 19 13 23 9"></polyline>
                                                </svg>
                                            </a>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <a href="#" onclick="confirmDeleteUser('<?= $user['id'] ?>', '<?= htmlspecialchars(addslashes($user['name'])) ?>')" class="text-red-600 hover:text-red-900 p-1" title="Löschen">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
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
    </div>
  </div>
</div>

<div id="deleteUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
    <h3 class="text-lg font-medium text-gray-900 mb-2">Benutzer löschen</h3>
    <p class="text-gray-500 mb-4">Möchten Sie den Benutzer <span id="userName"></span> wirklich löschen?</p>
    <div class="flex justify-end space-x-3">
      <button onclick="closeDeleteUserModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
      <a id="deleteUserLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
    </div>
  </div>
</div>

<script>
function confirmDeleteUser(id, name) {
  document.getElementById('userName').textContent = name;
  document.getElementById('deleteUserLink').href = 'users.php?action=delete&id=' + encodeURIComponent(id);
  document.getElementById('deleteUserModal').classList.remove('hidden');
}
function closeDeleteUserModal() {
  document.getElementById('deleteUserModal').classList.add('hidden');
}
</script>

<?php require_once 'includes/footer.php'; ?>
