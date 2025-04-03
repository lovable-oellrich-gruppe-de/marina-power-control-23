
<?php
require_once 'includes/config.php';

// Fehlermeldungen aktivieren für die Einrichtung
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Datenbankverbindung ohne Datenbankauswahl
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, '', DB_PORT);
    
    if ($conn->connect_error) {
        throw new Exception("Verbindung fehlgeschlagen: " . $conn->connect_error);
    }
    
    // Zeichensatz auf UTF-8 setzen
    $conn->set_charset("utf8mb4");
    
    echo "<h2>Datenbankverbindung hergestellt</h2>";
    
    // Datenbank erstellen (falls nicht vorhanden)
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Datenbank '" . DB_NAME . "' erfolgreich erstellt oder bereits vorhanden.</p>";
    } else {
        throw new Exception("Fehler beim Erstellen der Datenbank: " . $conn->error);
    }
    
    // Datenbank auswählen
    $conn->select_db(DB_NAME);
    
    // SQL-Hauptskript ausführen (enthält bereits alle Tabellendefinitionen)
    $sqlFile = 'server-config/setup_db.sql';
    
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Mehrere SQL-Anweisungen aufteilen und ausführen
        if ($sql) {
            echo "<h3>Führe SQL-Hauptdatei aus: " . htmlspecialchars($sqlFile) . "</h3>";
            
            // SQL-Kommentare entfernen und in einzelne Anweisungen aufteilen
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sqlStatements = explode(';', $sql);
            
            foreach ($sqlStatements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    if ($conn->query($statement) === TRUE) {
                        echo "<p>SQL ausgeführt: " . htmlspecialchars(substr($statement, 0, 50)) . "...</p>";
                    } else {
                        echo "<p class='error'>Fehler bei SQL-Anweisung: " . htmlspecialchars(substr($statement, 0, 50)) . "... - " . $conn->error . "</p>";
                    }
                }
            }
        } else {
            echo "<p class='error'>Konnte SQL-Datei nicht lesen: " . htmlspecialchars($sqlFile) . "</p>";
        }
    } else {
        echo "<p class='error'>SQL-Hauptdatei nicht gefunden: " . htmlspecialchars($sqlFile) . "</p>";
    }
    
    // Zusätzliche Daten für Tabellen einfügen
    $dataFiles = [
        'setup_mieter_tabelle.sql',  // Enthält nur noch die INSERT-Statements für Mieter
        'setup_steckdosen_tabelle.sql',
        'setup_zaehler_tabelle.sql',
        'setup_zaehlerstaende_tabelle.sql'
    ];
    
    foreach ($dataFiles as $dataFile) {
        if (file_exists($dataFile)) {
            $sql = file_get_contents($dataFile);
            
            if ($sql) {
                echo "<h3>Führe Daten-SQL-Datei aus: " . htmlspecialchars($dataFile) . "</h3>";
                
                // SQL-Kommentare entfernen und in einzelne Anweisungen aufteilen
                $sql = preg_replace('/--.*$/m', '', $sql);
                $sqlStatements = explode(';', $sql);
                
                foreach ($sqlStatements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        if ($conn->query($statement) === TRUE) {
                            echo "<p>SQL ausgeführt: " . htmlspecialchars(substr($statement, 0, 50)) . "...</p>";
                        } else {
                            echo "<p class='warning'>Hinweis bei SQL-Anweisung: " . htmlspecialchars(substr($statement, 0, 50)) . "... - " . $conn->error . "</p>";
                        }
                    }
                }
            }
        } else {
            echo "<p class='warning'>Daten-SQL-Datei nicht gefunden: " . htmlspecialchars($dataFile) . "</p>";
        }
    }
    
    // Admin-Benutzer prüfen und ggf. einfügen
    $result = $conn->query("SELECT * FROM benutzer WHERE email = 'admin@marina-power.de' LIMIT 1");
    if ($result && $result->num_rows == 0) {
        // Admin-Benutzer existiert noch nicht, erstellen
        $adminId = 'admin' . time();
        $adminHash = password_hash('Marina2024!', PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO benutzer (id, email, passwort_hash, name, rolle, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
        $stmt->bind_param("ssss", $adminId, $adminEmail, $adminHash, $adminName);
        
        $adminEmail = 'admin@marina-power.de';
        $adminName = 'Administrator';
        
        if ($stmt->execute()) {
            echo "<p>Admin-Benutzer wurde erstellt:<br>Email: admin@marina-power.de<br>Passwort: Marina2024!</p>";
            echo "<p><strong>Bitte ändern Sie das Passwort nach dem ersten Login!</strong></p>";
        } else {
            echo "<p class='error'>Fehler beim Erstellen des Admin-Benutzers: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>Admin-Benutzer existiert bereits.</p>";
    }
    
    echo "<h2>Datenbankeinrichtung abgeschlossen!</h2>";
    echo "<p>Alle Tabellen wurden erfolgreich erstellt oder waren bereits vorhanden.</p>";
    echo "<p><a href='index.php'>Zurück zur Startseite</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Fehler bei der Datenbankeinrichtung</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
}

// Verbindung schließen
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marina Power Control - Datenbankeinrichtung</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #1a6f8c;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        h3 {
            color: #2a809c;
            margin-top: 20px;
        }
        p {
            margin: 10px 0;
        }
        .error {
            color: #d9534f;
            background-color: #f9f2f2;
            padding: 10px;
            border-left: 4px solid #d9534f;
        }
        .warning {
            color: #f0ad4e;
            background-color: #fcf8e3;
            padding: 10px;
            border-left: 4px solid #f0ad4e;
        }
        a {
            display: inline-block;
            background-color: #2a809c;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        a:hover {
            background-color: #1a6f8c;
        }
    </style>
</head>
<body>
    <h1>Marina Power Control - Datenbankeinrichtung</h1>
</body>
</html>
