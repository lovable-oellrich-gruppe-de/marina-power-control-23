<?php
// Konfigurationsdatei für Marina Power Control

// Datenbankverbindungsdaten
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'marina_user');
define('DB_PASSWORD', 'marina_password');
define('DB_NAME', 'marina_power');
define('DB_PORT', 3306);

// Sitzungsdauer in Sekunden (30 Minuten)
define('SESSION_DURATION', 1800);

// Basis-URL der Anwendung
define('BASE_URL', '/');

// Fehlerberichterstattung (für Produktion auf false setzen)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zeitzonen-Einstellung
date_default_timezone_set('Europe/Berlin');

// Sitzung starten - dies MUSS vor jeglicher Ausgabe erfolgen
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
