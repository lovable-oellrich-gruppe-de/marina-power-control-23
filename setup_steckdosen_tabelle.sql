
-- Steckdosen-Tabelle erstellen (falls nicht vorhanden)
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

-- Beispieldaten für Steckdosen einfügen
INSERT INTO steckdosen (bezeichnung, status, bereich_id, mieter_id, hinweis)
VALUES 
('Steg A - Steckdose 1', 'aktiv', 1, 1, 'Nahe am Hauptsteg'),
('Steg A - Steckdose 2', 'aktiv', 1, 2, ''),
('Steg B - Steckdose 1', 'inaktiv', 2, NULL, 'Wartung geplant'),
('Steg C - Steckdose 1', 'aktiv', 3, NULL, '');
