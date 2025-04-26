<?php
require_once 'config.php';

class Database {
    private $connection;
    private $last_stmt;
    
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
            $success = $stmt->execute();
            
            // Wenn execute() fehlschlägt, werfe eine Exception
            if (!$success) {
                throw new Exception("Ausführungsfehler: " . $stmt->error);
            }

            $this->last_stmt = $stmt;
            
            // Ergebnis zurückgeben
            if (stripos($sql, 'SELECT') === 0) {
                $result = $stmt->get_result();
                return $result;
            } else {
                return true;
            }
            
        } catch (Exception $e) {
            // Hier könnten Sie auch Logging hinzufügen
            throw $e; // Re-throw, damit der Aufrufer den Fehler behandeln kann
        }
    }
    
    // Alle Ergebnisse abrufen
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $data = [];
    
        // Wenn kein gültiges Ergebnisobjekt vorhanden ist, leeres Array zurückgeben
        if (!($result instanceof mysqli_result)) {
            return $data;
        }
    
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    
        return $data;
    }
    
    // Ein einzelnes Ergebnis abrufen
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        // Wenn $result keine Ressource oder null ist, null zurückgeben
        if (!$result) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    // Anzahl der betroffenen Zeilen abrufen
    public function affectedRows() {
    //    return $this->connection->affected_rows;
        if (!isset($this->last_stmt) || !($this->last_stmt instanceof mysqli_stmt)) {
            return -1;
        }
        return $this->last_stmt->affected_rows;
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
