
-- Zählerstände-Tabelle erstellen (falls nicht vorhanden)
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

-- Beispieldaten für Zählerstände einfügen
INSERT INTO zaehlerstaende (zaehler_id, steckdose_id, datum, stand, vorheriger_id, verbrauch, abgelesen_von_id, ist_abgerechnet, hinweis)
VALUES 
(1, 1, '2023-12-15', 1250.50, NULL, NULL, 'user_1', FALSE, 'Erstablesung'),
(1, 1, '2024-01-15', 1320.80, 1, 70.30, 'user_1', FALSE, ''),
(2, 2, '2024-01-10', 55.20, NULL, NULL, 'user_2', FALSE, 'Neuer Zähler');

-- Index für effiziente Abfragen
CREATE INDEX idx_zaehlerstaende_zaehler_id ON zaehlerstaende(zaehler_id);
CREATE INDEX idx_zaehlerstaende_steckdose_id ON zaehlerstaende(steckdose_id);
CREATE INDEX idx_zaehlerstaende_datum ON zaehlerstaende(datum);
