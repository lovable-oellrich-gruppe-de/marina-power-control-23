
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

-- Beispieldaten für Mieter einfügen - Angepasst an die vorhandene Tabellenstruktur
INSERT INTO mieter (vorname, name, email, telefon, liegeplatz_nr)
VALUES 
('Max', 'Mustermann', 'max@example.com', '01234567890', 'A-42'),
('Julia', 'Schmidt', 'julia@example.com', '09876543210', 'B-17');
