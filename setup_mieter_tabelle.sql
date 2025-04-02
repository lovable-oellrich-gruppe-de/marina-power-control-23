
-- Mieter-Tabelle erstellen (falls nicht vorhanden)
CREATE TABLE IF NOT EXISTS mieter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vorname VARCHAR(100) NOT NULL,
  nachname VARCHAR(100) NOT NULL,
  strasse VARCHAR(100),
  hausnummer VARCHAR(20),
  email VARCHAR(255) NOT NULL,
  telefon VARCHAR(50),
  mobil VARCHAR(50),
  hinweis TEXT,
  bootsname VARCHAR(100),
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Beispieldaten für Mieter einfügen
INSERT INTO mieter (vorname, nachname, strasse, hausnummer, email, telefon, mobil, hinweis, bootsname)
VALUES 
('Max', 'Mustermann', 'Musterstraße', '123', 'max@example.com', '01234567890', '0987654321', 'Stammkunde', 'Seeschwalbe'),
('Julia', 'Schmidt', 'Hafenstraße', '45', 'julia@example.com', '09876543210', '01234567890', '', 'Wellentänzer');
