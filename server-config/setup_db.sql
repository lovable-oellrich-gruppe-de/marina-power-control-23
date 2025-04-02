
-- MariaDB Einrichtungsskript für Marina Power Control

-- Datenbank erstellen (falls nicht vorhanden)
CREATE DATABASE IF NOT EXISTS marina_power DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE marina_power;

-- Bereiche (Stegbereiche)
CREATE TABLE IF NOT EXISTS bereiche (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  beschreibung TEXT,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktiv BOOLEAN DEFAULT TRUE
);

-- Mieter
CREATE TABLE IF NOT EXISTS mieter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  vorname VARCHAR(100) NOT NULL,
  email VARCHAR(255),
  telefon VARCHAR(50),
  liegeplatz_nr VARCHAR(20),
  aktiv BOOLEAN DEFAULT TRUE,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Steckdosen
CREATE TABLE IF NOT EXISTS steckdosen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bezeichnung VARCHAR(100) NOT NULL,
  bereich_id INT,
  typ VARCHAR(50),
  status VARCHAR(20) DEFAULT 'aktiv',
  position VARCHAR(100),
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (bereich_id) REFERENCES bereiche(id) ON DELETE SET NULL
);

-- Zähler
CREATE TABLE IF NOT EXISTS zaehler (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seriennummer VARCHAR(50) NOT NULL,
  steckdose_id INT,
  mieter_id INT,
  einbaudatum DATE,
  typ VARCHAR(50),
  status VARCHAR(20) DEFAULT 'aktiv',
  notizen TEXT,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (steckdose_id) REFERENCES steckdosen(id) ON DELETE SET NULL,
  FOREIGN KEY (mieter_id) REFERENCES mieter(id) ON DELETE SET NULL
);

-- Zählerstände
CREATE TABLE IF NOT EXISTS zaehlerstaende (
  id INT AUTO_INCREMENT PRIMARY KEY,
  zaehler_id INT NOT NULL,
  stand DECIMAL(10, 2) NOT NULL,
  datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  einheit VARCHAR(10) DEFAULT 'kWh',
  photo_url VARCHAR(255),
  erstellt_von VARCHAR(100),
  notizen TEXT,
  FOREIGN KEY (zaehler_id) REFERENCES zaehler(id) ON DELETE CASCADE
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
