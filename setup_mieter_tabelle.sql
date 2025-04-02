
-- Mieter-Tabelle erstellen (falls nicht vorhanden)
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

-- Beispieldaten für Mieter einfügen
INSERT INTO mieter (vorname, name, email, telefon, strasse, hausnummer, mobil, hinweis, bootsname, stellplatzNr, vertragStart, vertragEnde, liegeplatz_nr)
VALUES 
('Max', 'Mustermann', 'max@example.com', '01234567890', 'Musterstraße', '123', '0987654321', 'Stammkunde', 'Seeschwalbe', 'A-42', '2023-01-01', '2023-12-31', 'A-42'),
('Julia', 'Schmidt', 'julia@example.com', '09876543210', 'Hafenstraße', '45', '01234567890', '', 'Wellentänzer', 'B-17', '2023-03-01', '2024-02-29', 'B-17');
