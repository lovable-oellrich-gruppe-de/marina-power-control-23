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

$sql .= " ORDER BY $orderBy $orderDir";
$bereiche = $db->fetchAll($sql, $params);

require_once 'includes/header.php';
?>
