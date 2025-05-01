<?php
// Wichtig: Keine Leerzeilen oder Whitespace vor dem öffnenden PHP-Tag
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Dieses Skript nur einmalig ausführen, um Admin-Passwörter zu aktualisieren
// Später sollten Sie dieses Skript aus Sicherheitsgründen löschen

// Admin-Benutzer und Benutzer-Benutzer aktualisieren
$adminEmail = 'admin@marina-power.de';
$adminPassword = 'admin123';
$benutzerEmail = 'benutzer@marina-power.de';
$benutzerPassword = 'benutzer123';

// Passwörter hashen
$adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);
$benutzerHash = password_hash($benutzerPassword, PASSWORD_DEFAULT);

// Datenbankabfragen
$updateAdmin = "UPDATE benutzer SET passwort_hash = ? WHERE email = ?";
$db->query($updateAdmin, [$adminHash, $adminEmail]);
$adminResult = $db->affectedRows();

$updateBenutzer = "UPDATE benutzer SET passwort_hash = ? WHERE email = ?";
$db->query($updateBenutzer, [$benutzerHash, $benutzerEmail]);
$benutzerResult = $db->affectedRows();

// Ergebnisausgabe
echo "<h1>Passwort-Update</h1>";

if ($adminResult > 0) {
    echo "<p>Admin-Passwort wurde erfolgreich aktualisiert.</p>";
} else {
    echo "<p>Admin-Benutzer wurde nicht gefunden oder das Passwort wurde nicht geändert.</p>";
}

if ($benutzerResult > 0) {
    echo "<p>Benutzer-Passwort wurde erfolgreich aktualisiert.</p>";
} else {
    echo "<p>Benutzer-Benutzer wurde nicht gefunden oder das Passwort wurde nicht geändert.</p>";
}

echo "<p><strong>WICHTIG: Löschen Sie diese Datei nach der Verwendung aus Sicherheitsgründen!</strong></p>";
echo "<p><a href='index.php'>Zurück zur Startseite</a></p>";
?>
