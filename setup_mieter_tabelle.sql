
-- Beispieldaten für Mieter einfügen (die Tabellendefinition ist bereits in setup_db.sql)
INSERT INTO mieter (vorname, name, email, telefon, strasse, hausnummer, mobil, hinweis, bootsname, stellplatzNr, vertragStart, vertragEnde, liegeplatz_nr)
VALUES 
('Max', 'Mustermann', 'max@example.com', '01234567890', 'Musterstraße', '123', '0987654321', 'Stammkunde', 'Seeschwalbe', 'A-42', '2023-01-01', '2023-12-31', 'A-42'),
('Julia', 'Schmidt', 'julia@example.com', '09876543210', 'Hafenstraße', '45', '01234567890', '', 'Wellentänzer', 'B-17', '2023-03-01', '2024-02-29', 'B-17');
