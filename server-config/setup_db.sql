
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

-- Beispieldaten für Bereiche einfügen
INSERT INTO bereiche (name, beschreibung) VALUES
('Steg A', 'Hauptsteg mit 20 Liegeplätzen'),
('Steg B', 'Nebensteg mit 15 Liegeplätzen'),
('Steg C', 'Besuchersteg mit 10 Liegeplätzen');

-- Beispieldaten für Mieter einfügen
INSERT INTO mieter (vorname, name, email, telefon, strasse, hausnummer, mobil, hinweis, bootsname, stellplatzNr, vertragStart, vertragEnde, liegeplatz_nr)
VALUES 
('Max', 'Mustermann', 'max@example.com', '01234567890', 'Musterstraße', '123', '0987654321', 'Stammkunde', 'Seeschwalbe', 'A-42', '2023-01-01', '2023-12-31', 'A-42'),
('Julia', 'Schmidt', 'julia@example.com', '09876543210', 'Hafenstraße', '45', '01234567890', '', 'Wellentänzer', 'B-17', '2023-03-01', '2024-02-29', 'B-17');

-- Beispieldaten für Steckdosen einfügen
INSERT INTO steckdosen (bezeichnung, status, bereich_id, mieter_id, hinweis)
SELECT 'Steckdose A1', 'aktiv', b.id, m.id, 'Testdaten'
FROM bereiche b, mieter m
WHERE b.name = 'Steg A' AND m.email = 'max@example.com'
LIMIT 1;

INSERT INTO steckdosen (bezeichnung, status, bereich_id, hinweis)
SELECT 'Steckdose B1', 'aktiv', b.id, 'Frei verfügbar'
FROM bereiche b
WHERE b.name = 'Steg B'
LIMIT 1;

-- Beispieldaten für Zähler einfügen
INSERT INTO zaehler (zaehlernummer, typ, hersteller, modell, installiert_am, letzte_wartung, seriennummer, max_leistung, hinweis)
VALUES
('Z-A1-001', 'Stromzähler', 'ElektroTech', 'ET-2000', '2023-01-15', '2023-06-15', 'SN12345678', 3600, 'Testzähler für Steg A'),
('Z-B1-001', 'Stromzähler', 'PowerMeter', 'PM-Pro', '2023-02-20', '2023-07-20', 'PM987654321', 7200, 'Hochleistungszähler für Steg B');

-- Beispieldaten für Zählerstände einfügen
INSERT INTO zaehlerstaende (zaehler_id, steckdose_id, datum, stand, abgelesen_von_id, hinweis)
SELECT 
    z.id,
    s.id,
    '2023-05-01',
    1250.75,
    'admin1',
    'Initialer Zählerstand'
FROM 
    zaehler z, 
    steckdosen s 
WHERE 
    z.zaehlernummer = 'Z-A1-001' AND 
    s.bezeichnung = 'Steckdose A1'
LIMIT 1;

INSERT INTO zaehlerstaende (zaehler_id, steckdose_id, datum, stand, abgelesen_von_id, hinweis)
SELECT 
    z.id,
    s.id,
    '2023-06-01',
    1325.50,
    'admin1',
    'Monatliche Ablesung'
FROM 
    zaehler z, 
    steckdosen s 
WHERE 
    z.zaehlernummer = 'Z-A1-001' AND 
    s.bezeichnung = 'Steckdose A1'
LIMIT 1;

-- Admin-Benutzer einfügen (das Passwort-Hash wird in setup.php erstellt)
INSERT INTO benutzer (id, email, passwort_hash, name, rolle, status)
VALUES ('admin1', 'admin@marina-power.de', 'DEMO_HASH_WERT', 'Administrator', 'admin', 'active');
