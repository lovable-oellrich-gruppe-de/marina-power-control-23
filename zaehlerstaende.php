<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Erfolg- oder Fehlermeldungen aus der URL holen
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Löschen eines Zählerstands, wenn ID übergeben wurde
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM zaehlerstaende WHERE id = ?", [$id]);

    if ($db->affectedRows() > 0) {
        header("Location: zaehlerstaende.php?success=" . urlencode("Zählerstand wurde erfolgreich gelöscht."));
        exit;
    } else {
        header("Location: zaehlerstaende.php?error=" . urlencode("Fehler beim Löschen des Zählerstands."));
        exit;
    }
}

// Vorbereitung der Filter-Parameter
$where_clauses = [];
$params = [];

// Filter-Optionen (wie Suche, Bereich, Mieter, Zeit)
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $where_clauses[] = "(z.zaehlernummer LIKE ? OR s.bezeichnung LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (isset($_GET['bereich']) && !empty($_GET['bereich'])) {
    $where_clauses[] = "s.bereich_id = ?";
    $params[] = $_GET['bereich'];
}

if (isset($_GET['mieter']) && !empty($_GET['mieter'])) {
    $where_clauses[] = "s.mieter_id = ?";
    $params[] = $_GET['mieter'];
}

if (isset($_GET['von']) && !empty($_GET['von'])) {
    $where_clauses[] = "zs.datum >= ?";
    $params[] = $_GET['von'];
}

if (isset($_GET['bis']) && !empty($_GET['bis'])) {
    $where_clauses[] = "zs.datum <= ?";
    $params[] = $_GET['bis'];
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Bereiche und Mieter für Filter laden
$bereiche = $db->fetchAll("SELECT id, name FROM bereiche ORDER BY name");
$mieter = $db->fetchAll("SELECT id, CONCAT(vorname, ' ', name) AS vollname FROM mieter ORDER BY name");

// Zählerstände abfragen
$sql = "SELECT 
        zs.*,
        z.zaehlernummer,
        s.bezeichnung AS steckdose_bezeichnung,
        b.name AS bereich_name,
        CONCAT(m.vorname, ' ', m.name) AS mieter_name
    FROM 
        zaehlerstaende zs
    LEFT JOIN zaehler z ON zs.zaehler_id = z.id
    LEFT JOIN steckdosen s ON zs.steckdose_id = s.id
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    LEFT JOIN mieter m ON s.mieter_id = m.id
    $where_sql
    ORDER BY zs.datum DESC, zs.id DESC";

$zaehlerstaende = $db->fetchAll($sql, $params);

// Header einbinden
require_once 'includes/header.php';
?>

<!-- Der Rest bleibt gleich wie dein bisheriger HTML-Code -->
<!-- Meldungen anzeigen, Filter, Tabelle, Delete Modal -->
<!-- Nur die Aktionen für "Abgerechnet/Unabgerechnet" wurden entfernt! -->

<?php
require_once 'includes/footer.php';
?>
