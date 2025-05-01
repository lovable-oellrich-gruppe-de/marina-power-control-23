<?php
require_once 'includes/config.php';

// Fehlermeldungen aktivieren für die Einrichtung
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung ohne Datenbankauswahl
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, '', DB_PORT);
    if ($conn->connect_error) {
        throw new Exception("Verbindung fehlgeschlagen: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    echo "<h2>Verbindung erfolgreich hergestellt</h2>";

    // Datenbank anlegen, falls sie noch nicht existiert
    $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        echo "<p>Datenbank '" . DB_NAME . "' wurde erstellt oder existiert bereits.</p>";
    } else {
        throw new Exception("Fehler beim Erstellen der Datenbank: " . $conn->error);
    }

    // Datenbank aktivieren
    $conn->select_db(DB_NAME);

    // SQL-Datei laden
    $sqlFile = 'setup_db.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL-Datei nicht gefunden: $sqlFile");
    }

    $sqlContent = file_get_contents($sqlFile);
    if (!$sqlContent) {
        throw new Exception("Konnte SQL-Datei nicht lesen.");
    }

    echo "<h3>SQL-Datei wird ausgeführt: " . htmlspecialchars($sqlFile) . "</h3>";

    // SQL-Kommentare entfernen
    $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);

    // SQL-Befehle einzeln ausführen
    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            if ($conn->query($stmt)) {
                echo "<p>OK: " . htmlspecialchars(substr($stmt, 0, 60)) . "...</p>";
            } else {
                echo "<p class='warning'>Fehler: " . htmlspecialchars(substr($stmt, 0, 60)) . "... → " . $conn->error . "</p>";
            }
        }
    }

    echo "<h2>Setup abgeschlossen</h2>";
    echo "<p><a href='index.php'>Zurück zur Anwendung</a></p>";

} catch (Exception $e) {
    echo "<h2>Fehler beim Setup</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
}

// Verbindung schließen
if (isset($conn)) {
    $conn->close();
}
?>
