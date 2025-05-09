<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$success_message = $_GET['success'] ?? null;
$error_message = $_GET['error'] ?? null;
$search = $_GET['search'] ?? '';
$aktiv_filter = $_GET['aktiv'] ?? '';
$orderBy = $_GET['order_by'] ?? 'name';
$orderDir = strtoupper($_GET['order_dir'] ?? 'ASC');
$valid_order_by = ['id', 'name', 'beschreibung', 'aktiv', 'steckdosen_count'];
$valid_order_dir = ['ASC', 'DESC'];
if (!in_array($orderBy, $valid_order_by)) $orderBy = 'name';
if (!in_array($orderDir, $valid_order_dir)) $orderDir = 'ASC';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $linkedResources = $db->fetchOne("SELECT COUNT(*) as count FROM steckdosen WHERE bereich_id = ?", [$id]);
    if ($linkedResources['count'] > 0) {
        header("Location: bereiche.php?error=" . urlencode("Bereich kann nicht gelöscht werden, da er noch mit Steckdosen verknüpft ist."));
        exit;
    } else {
        if ($db->query("DELETE FROM bereiche WHERE id = ?", [$id])) {
            header("Location: bereiche.php?success=" . urlencode("Bereich wurde erfolgreich gelöscht."));
            exit;
        } else {
            header("Location: bereiche.php?error=" . urlencode("Fehler beim Löschen des Bereichs."));
            exit;
        }
    }
}

$params = [];
$sql = "SELECT bereiche.*, (SELECT COUNT(*) FROM steckdosen WHERE bereich_id = bereiche.id) AS steckdosen_count FROM bereiche WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (bereiche.name LIKE ? OR bereiche.beschreibung LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($aktiv_filter !== '') {
    $sql .= " AND aktiv = ?";
    $params[] = (int)$aktiv_filter;
}

$sql .= " ORDER BY $orderBy $orderDir";
$bereiche = $db->fetchAll($sql, $params);

require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Bereiche verwalten</h1>
            <a href="bereiche_form.php" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">
                Neuer Bereich
            </a>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <form method="GET" action="bereiche.php" class="flex flex-wrap gap-4 items-end">
                <div class="w-full sm:w-auto">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Suche</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name oder Beschreibung" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                </div>
                <div class="w-full sm:w-auto">
                    <label for="aktiv" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="aktiv" name="aktiv" class="w-full rounded-md border-gray-300 shadow-sm focus:border-marina-500 focus:ring focus:ring-marina-500">
                        <option value="">Alle</option>
                        <option value="1" <?= $aktiv_filter === '1' ? 'selected' : '' ?>>Aktiv</option>
                        <option value="0" <?= $aktiv_filter === '0' ? 'selected' : '' ?>>Inaktiv</option>
                    </select>
                </div>
                <div class="ml-auto">
                    <button type="submit" class="bg-marina-600 text-white px-4 py-2 rounded hover:bg-marina-700">Filtern</button>
                    <a href="bereiche.php" class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Zurücksetzen</a>
                </div>
            </form>
        </div>

        <div class="mt-4 bg-white shadow-md rounded-lg overflow-hidden">
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
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider"><?= sortLink('ID', 'id') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider"><?= sortLink('Name', 'name') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider"><?= sortLink('Beschreibung', 'beschreibung') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider"><?= sortLink('Status', 'aktiv') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider"><?= sortLink('Steckdosen', 'steckdosen_count') ?></th>
                            <th class="px-4 py-1 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($bereiche)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-1 text-center text-sm text-gray-900">Keine Bereiche gefunden</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bereiche as $b): ?>
                                <tr>
                                    <td class="px-4 py-1 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($b['id']) ?></td>
                                    <td class="px-4 py-1 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($b['name']) ?></td>
                                    <td class="px-4 py-1 text-sm text-gray-900"><?= htmlspecialchars($b['beschreibung'] ?: '-') ?></td>
                                    <td class="px-4 py-1 whitespace-nowrap">
                                        <?php if ($b['aktiv']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktiv</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inaktiv</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-1 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($b['steckdosen_count']) ?></td>
                                    <td class="px-4 py-1 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <a href="bereiche_form.php?id=<?= $b['id'] ?>" class="text-marina-600 hover:text-marina-900" title="Bearbeiten">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['name'])) ?>', <?= $b['steckdosen_count'] ?>)" class="text-red-600 hover:text-red-900" title="Löschen">
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

<!-- Bestätigungsdialog für das Löschen -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Bereich löschen</h3>
        <p class="text-gray-900 mb-4">Möchten Sie den Bereich <span id="bereichName"></span> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
        <p id="warningText" class="text-red-500 mb-4 hidden">Dieser Bereich kann nicht gelöscht werden, da er noch mit Steckdosen verknüpft ist.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Abbrechen</button>
            <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Löschen</a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name, steckdosenCount) {
    document.getElementById('bereichName').textContent = name;
    const warningText = document.getElementById('warningText');
    const deleteLink = document.getElementById('deleteLink');

    if (steckdosenCount > 0) {
        warningText.classList.remove('hidden');
        deleteLink.classList.add('opacity-50', 'cursor-not-allowed');
        deleteLink.href = '#';
        deleteLink.onclick = function(e) { e.preventDefault(); };
    } else {
        warningText.classList.add('hidden');
        deleteLink.classList.remove('opacity-50', 'cursor-not-allowed');
        deleteLink.href = 'bereiche.php?delete=' + id;
        deleteLink.onclick = null;
    }

    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php require_once 'includes/footer.php'; ?>
