<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Ordner für Fotouploads erstellen, falls er nicht existiert
$upload_dir = 'uploads/zaehlerstaende';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Variablen initialisieren
$id = null;
$zaehler_id = '';
$steckdose_id = '';
$datum = date('Y-m-d');
$stand = '';
$hinweis = '';
$ist_abgerechnet = 0;
$foto_url = '';
$pageTitle = 'Neuen Zählerstand erfassen';
$isEdit = false;

$errors = [];

// Zähler für Dropdown laden
$zaehler = $db->fetchAll("SELECT id, zaehlernummer FROM zaehler ORDER BY zaehlernummer");

// Steckdosen für Dropdown laden
$steckdosen = $db->fetchAll("
    SELECT s.id, s.bezeichnung, b.name AS bereich_name
    FROM steckdosen s
    LEFT JOIN bereiche b ON s.bereich_id = b.id
    ORDER BY b.name, s.bezeichnung
");

// Bearbeiten-Modus prüfen
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $isEdit = true;
    $pageTitle = 'Zählerstand bearbeiten';

    $zs = $db->fetchOne("SELECT * FROM zaehlerstaende WHERE id = ?", [$id]);

    if ($zs) {
        $zaehler_id = $zs['zaehler_id'];
        $steckdose_id = $zs['steckdose_id'];
        $datum = $zs['datum'];
        $stand = $zs['stand'];
        $hinweis = $zs['hinweis'];
        $ist_abgerechnet = $zs['ist_abgerechnet'];
        $foto_url = $zs['foto_url'];
    } else {
        $errors[] = "Zählerstand nicht gefunden.";
    }
}

// Formular verarbeitet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zaehler_id = $_POST['zaehler_id'] ?? '';
    $steckdose_id = !empty($_POST['steckdose_id']) ? $_POST['steckdose_id'] : null;
    $datum = $_POST['datum'] ?? '';
    $stand = str_replace(',', '.', $_POST['stand'] ?? '');
    $hinweis = $_POST['hinweis'] ?? '';
    $ist_abgerechnet = isset($_POST['ist_abgerechnet']) ? 1 : 0;

    if (empty($zaehler_id)) {
        $errors[] = "Bitte einen Zähler auswählen.";
    }
    if (empty($datum)) {
        $errors[] = "Bitte ein Datum eingeben.";
    }
    if (!is_numeric($stand)) {
        $errors[] = "Der Zählerstand muss eine Zahl sein.";
    }

    // Foto hochladen
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

    // Wenn keine Fehler vorhanden
    if (empty($errors)) {
        $verbrauch = null;

        // Vorherigen Zählerstand suchen
        $vorheriger = $db->fetchOne("SELECT id, stand FROM zaehlerstaende WHERE zaehler_id = ? AND datum < ? ORDER BY datum DESC, id DESC LIMIT 1", [$zaehler_id, $datum]);
        $vorheriger_id = $vorheriger['id'] ?? null;

        if ($vorheriger) {
            $verbrauch = $stand - $vorheriger['stand'];
            if ($verbrauch < 0) {
                $errors[] = "Der neue Stand ist kleiner als der vorherige.";
            }
        }
    }

    // Speicherung nach Prüfung
    if (empty($errors)) {
        $current_user = $auth->getCurrentUser();
        $abgelesen_von_id = $current_user['id'];

        if ($isEdit) {
            $params = [$zaehler_id, $steckdose_id, $datum, $stand, $vorheriger_id, $verbrauch, $abgelesen_von_id, $hinweis, $ist_abgerechnet];
            $sql = "UPDATE zaehlerstaende SET zaehler_id=?, steckdose_id=?, datum=?, stand=?, vorheriger_id=?, verbrauch=?, abgelesen_von_id=?, hinweis=?, ist_abgerechnet=?";

            if (!empty($foto_url)) {
                $sql .= ", foto_url=?";
                $params[] = $foto_url;
            }

            $sql .= " WHERE id=?";
            $params[] = $id;

            $db->query($sql, $params);
            $success = "Zählerstand aktualisiert.";
        } else {
            $params = [$zaehler_id, $steckdose_id, $datum, $stand, $vorheriger_id, $verbrauch, $abgelesen_von_id, $hinweis, $ist_abgerechnet];
            $columns = "zaehler_id, steckdose_id, datum, stand, vorheriger_id, verbrauch, abgelesen_von_id, hinweis, ist_abgerechnet";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?";

            if (!empty($foto_url)) {
                $columns .= ", foto_url";
                $placeholders .= ", ?";
                $params[] = $foto_url;
            }

            $db->query("INSERT INTO zaehlerstaende ($columns) VALUES ($placeholders)", $params);
            $success = "Zählerstand gespeichert.";
        }

        header("Location: zaehlerstaende.php?success=" . urlencode($success));
        exit;
    }
}

// Header einbinden
require_once 'includes/header.php';
?>

<!-- Hier würde dein HTML-Formular weitergehen -->
<!-- (Formularcode bleibt wie bei dir, da er super strukturiert ist!) -->

<?php
require_once 'includes/footer.php';
?>
