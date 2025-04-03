
-- MariaDB Einrichtungsskript für Marina Power Control

-- Datenbank erstellen (falls nicht vorhanden)
CREATE DATABASE IF NOT EXISTS marina_power DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE marina_power;

-- Bereiche (Stegbereiche)
CREATE TABLE IF NOT EXISTS bereiche (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  beschreibung TEXT,
  aktiv BOOLEAN DEFAULT TRUE,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Mieter
CREATE TABLE IF NOT EXISTS mieter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vorname VARCHAR(100) NOT NULL,
  name VARCHAR(100) NOT NULL,
  strasse VARCHAR(100),
  hausnummer VARCHAR(20),
  email VARCHAR(255) NOT NULL,
  telefon VARCHAR(50),
  mobil VARCHAR(50),
  hinweis TEXT,
  bootsname VARCHAR(100),
  stellplatzNr VARCHAR(20),
  vertragStart DATE,
  vertragEnde DATE,
  liegeplatz_nr VARCHAR(20),
  aktiv BOOLEAN DEFAULT TRUE,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Steckdosen
CREATE TABLE IF NOT EXISTS steckdosen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bezeichnung VARCHAR(100) NOT NULL,
  status ENUM('aktiv', 'inaktiv', 'defekt') NOT NULL DEFAULT 'aktiv',
  bereich_id INT,
  mieter_id INT,
  letzte_ablesung DATETIME,
  hinweis TEXT,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (bereich_id) REFERENCES bereiche(id) ON DELETE SET NULL,
  FOREIGN KEY (mieter_id) REFERENCES mieter(id) ON DELETE SET NULL
);

-- Zähler
CREATE TABLE IF NOT EXISTS zaehler (
  id INT AUTO_INCREMENT PRIMARY KEY,
  zaehlernummer VARCHAR(50) NOT NULL,
  typ VARCHAR(50) DEFAULT 'Stromzähler',
  hersteller VARCHAR(100),
  modell VARCHAR(100),
  installiert_am DATE NOT NULL,
  letzte_wartung DATE,
  seriennummer VARCHAR(100),
  max_leistung INT,
  ist_ausgebaut BOOLEAN DEFAULT FALSE,
  hinweis TEXT,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Zählerstände
CREATE TABLE IF NOT EXISTS zaehlerstaende (
  id INT AUTO_INCREMENT PRIMARY KEY,
  zaehler_id INT NOT NULL,
  steckdose_id INT,
  datum DATE NOT NULL,
  stand DECIMAL(10, 2) NOT NULL,
  vorheriger_id INT,
  verbrauch DECIMAL(10, 2),
  abgelesen_von_id VARCHAR(36),
  foto_url TEXT,
  ist_abgerechnet BOOLEAN DEFAULT FALSE,
  hinweis TEXT,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (zaehler_id) REFERENCES zaehler(id) ON DELETE CASCADE,
  FOREIGN KEY (steckdose_id) REFERENCES steckdosen(id) ON DELETE SET NULL,
  FOREIGN KEY (vorheriger_id) REFERENCES zaehlerstaende(id) ON DELETE SET NULL
);

-- Benutzer
CREATE TABLE IF NOT EXISTS benutzer (
  id VARCHAR(50) PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  passwort_hash VARCHAR(255) NOT NULL,
  name VARCHAR(100),
  rolle ENUM('admin', 'user') DEFAULT 'user',
  status ENUM('active', 'pending') DEFAULT 'pending',
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Beispieldaten einfügen
INSERT INTO bereiche (name, beschreibung) VALUES
('Steg A', 'Hauptsteg mit 20 Liegeplätzen'),
('Steg B', 'Nebensteg mit 15 Liegeplätzen'),
('Steg C', 'Besuchersteg mit 10 Liegeplätzen');

-- Admin-Benutzer einfügen (Passwort sollte in einer echten Umgebung gehasht sein)
INSERT INTO benutzer (id, email, passwort_hash, name, rolle, status)
VALUES ('admin1', 'admin@marina-power.de', 'DEMO_HASH_WERT', 'Administrator', 'admin', 'active');
