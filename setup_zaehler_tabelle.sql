
-- Zähler-Tabelle erstellen (falls nicht vorhanden)
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

-- Beispieldaten für Zähler einfügen
INSERT INTO zaehler (zaehlernummer, typ, hersteller, modell, installiert_am, letzte_wartung, seriennummer, hinweis)
VALUES 
('Z-001', 'Stromzähler', 'ElectroCount', 'EC-2000', '2023-01-15', '2023-12-01', 'SN12345678', 'Neu installiert'),
('Z-002', 'Stromzähler', 'PowerMeter', 'PM-500', '2023-02-20', '2023-11-15', 'PM5002789', ''),
('Z-003', 'Stromzähler', 'ElectroCount', 'EC-2000', '2023-05-10', '2023-10-20', 'SN98765432', 'Gereinigt bei letzter Wartung'),
('Z-004', 'Stromzähler', 'PowerMeter', 'PM-650', '2023-04-05', NULL, 'PM6501234', 'Noch keine Wartung durchgeführt');
