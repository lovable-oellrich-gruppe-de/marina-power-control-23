
-- Bereiche-Tabelle erstellen (falls nicht vorhanden)
CREATE TABLE IF NOT EXISTS bereiche (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  beschreibung TEXT,
  aktiv BOOLEAN DEFAULT TRUE,
  erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Beispieldaten für Bereiche einfügen
INSERT INTO bereiche (name, beschreibung, aktiv)
VALUES 
('Steg A', 'Hauptsteg im Osten der Marina', TRUE),
('Steg B', 'Mittlerer Steg', TRUE),
('Steg C', 'Westlicher Steg', TRUE),
('Servicebereich', 'Bereich für Reparatur und Wartung', TRUE);
