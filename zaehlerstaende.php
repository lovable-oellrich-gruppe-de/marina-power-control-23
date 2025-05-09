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

$orderBy = $_GET['order_by'] ?? 'datum';
$orderDir = strtoupper($_GET['order_dir'] ?? 'DESC');
$validColumns = ['id', 'datum', 'zaehlernummer', 'zaehlerhinweis', 'steckdose_bezeichnung', 'bereich_name', 'mieter_name', 'stand'];
if (!in_array($orderBy, $validColumns)) $orderBy = 'datum';
if (!in_array($orderDir, ['ASC', 'DESC'])) $orderDir = 'DESC';

function sortLink($column, $label) {
    global $orderBy, $orderDir, $_GET;
    $newDir = ($orderBy === $column && $orderDir === 'ASC') ? 'DESC' : 'ASC';
    $query = $_GET;
    $query['order_by'] = $column;
    $query['order_dir'] = $newDir;
    $url = '?' . http_build_query($query);
    $arrow = '';
    if ($orderBy === $column) {
        $arrow = $orderDir === 'ASC' ? '↑' : '↓';
    }
    return "<a href=\"$url\" class=\"flex items-center space-x-1\">$label<span>$arrow</span></a>";
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($db->query("DELETE FROM zaehlerstaende WHERE id = ?", [$id])) {
        header("Location: zaehlerstaende.php?success=" . urlencode("Zählerstand wurde erfolgreich gelöscht."));
        exit;
    } else {
        header("Location: zaehlerstaende.php?error=" . urlencode("Fehler beim Löschen des Zählerstands."));
        exit;
    }
}

$where_clauses = [];
$params = [];

if (!empty($_GET['search'])) {
    $search = $_GET['search'];
    $where_clauses[] = "(z.zaehlernummer LIKE ? OR z.hinweis LIKE ? OR s.bezeichnung LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($_GET['bereich'])) {
    $where_clauses[] = "s.bereich_id = ?";
    $params[] = (int)$_GET['bereich'];
}

if (!empty($_GET['mieter'])) {
    $where_clauses[] = "s.mieter_id = ?";
    $params[] = (int)$_GET['mieter'];
}

if (!empty($_GET['von'])) {
    $where_clauses[] = "zs.datum >= ?";
    $params[] = $_GET['von'];
}

if (!empty($_GET['bis'])) {
    $where_clauses[] = "zs.datum <= ?";
    $params[] = $_GET['bis'];
}

$where_sql = '';
if ($where_clauses) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

$bereiche = $db->fetchAll("SELECT id, name FROM bereiche ORDER BY name");
$mieter = $db->fetchAll("SELECT id, CONCAT(vorname, ' ', name) AS vollname FROM mieter ORDER BY name");

$sql = "SELECT 
            zs.*, 
            z.zaehlernummer, 
            z.hinweis AS zaehlerhinweis,
            s.bezeichnung AS steckdose_bezeichnung, 
            b.name AS bereich_name, 
            CONCAT(m.vorname, ' ', m.name) AS mieter_name
        FROM zaehlerstaende zs
        LEFT JOIN zaehler z ON zs.zaehler_id = z.id
        LEFT JOIN steckdosen s ON zs.steckdose_id = s.id
        LEFT JOIN bereiche b ON s.bereich_id = b.id
        LEFT JOIN mieter m ON s.mieter_id = m.id
        $where_sql
        ORDER BY $orderBy $orderDir, zs.id DESC";

$zaehlerstaende = $db->fetchAll($sql, $params);

// Verbrauch berechnen
$verbrauch_liste = [];
foreach ($zaehlerstaende as $index => $row) {
    if (!isset($verbrauch_liste[$row['zaehler_id']])) {
        $verbrauch_liste[$row['zaehler_id']] = [];
    }
    $verbrauch_liste[$row['zaehler_id']][] = $row;
}

foreach ($verbrauch_liste as $zaehler_id => &$liste) {
    usort($liste, fn($a, $b) => strtotime($a['datum']) <=> strtotime($b['datum']));
    for ($i = 1; $i < count($liste); $i++) {
        $liste[$i]['verbrauch'] = $liste[$i]['stand'] - $liste[$i - 1]['stand'];
    }
    $liste[0]['verbrauch'] = null;
}

$zaehlerstaende = array_merge(...array_values($verbrauch_liste));

require_once 'includes/header.php';
?>
