<?php
require_once 'config.php';

class Database {
    private $connection;
    
    // Konstruktor zur Herstellung der Datenbankverbindung
    public function __construct() {
        $this->connect();
    }
    
    // Verbindung zur Datenbank herstellen
    private function connect() {
        try {
            $this->connection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
            
            if ($this->connection->connect_error) {
                throw new Exception("Verbindung fehlgeschlagen: " . $this->connection->connect_error);
            }
            
            // Zeichensatz auf UTF-8 setzen
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Datenbankfehler: " . $e->getMessage());
        }
    }
    
    // Abfrage ausführen
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt === false) {
                throw new Exception("Vorbereitungsfehler: " . $this->connection->error);
            }
            
            // Parameter binden, wenn vorhanden
            if (!empty($params)) {
                $types = '';
                $values = [];
                
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_double($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                    
                    $values[] = $param;
                }
                
                // Parameter einfügen
                $stmt->bind_param($types, ...$values);
            }
            
            // Abfrage ausführen
            $stmt->execute();
            
            // Ergebnis zurückgeben
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            die("Abfragefehler: " . $e->getMessage());
        }
    }
    
    // Alle Ergebnisse abrufen
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Ein einzelnes Ergebnis abrufen
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        return $result->fetch_assoc();
    }
    
    // Anzahl der betroffenen Zeilen abrufen
    public function affectedRows() {
        return $this->connection->affected_rows;
    }
    
    // Letzte eingefügte ID abrufen
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    // Verbindung schließen
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Globale Datenbankinstanz erstellen
$db = new Database();
?>
