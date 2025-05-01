📘 README.md – Marina Power Control Setup
✅ Schritt 1: includes/config.php anpassen
Öffne die Datei includes/config.php und trage deine Datenbank-Zugangsdaten ein:

php
Kopieren
Bearbeiten
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'dein_benutzername');
define('DB_PASSWORD', 'dein_passwort');
define('DB_NAME', 'marina_power');
define('DB_PORT', 3306);
⚙️ Schritt 2: Datenbank einrichten
Führe im Browser oder lokal auf dem Server die Datei setup.php aus:

arduino
Kopieren
Bearbeiten
http://<dein-server>/setup.php
Diese:

erstellt die Datenbank marina_power (sofern nicht vorhanden),

importiert alle Tabellen und Daten aus der Datei setup_db.sql,

richtet die komplette Grundstruktur der Anwendung ein.

👤 Standard-Administrator
Nach der Einrichtung ist ein Start-Admin verfügbar:

E-Mail: admin@marina-power.de

Passwort: admin123

🔐 Bitte sofort nach dem ersten Login das Passwort ändern und ggf. weitere Admins über die Benutzerverwaltung anlegen.

🧹 Abschließende Schritte
Aus Sicherheitsgründen solltest du nach dem Setup folgende Dateien löschen:

setup.php

server-config/setup_db.sql

📍 Hinweis
Die Anwendung ist nun betriebsbereit. Du kannst dich über login.php anmelden und alle Funktionen nutzen.
