
<?php
require_once 'db.php';

class Auth {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Benutzer anmelden
    public function login($email, $password) {
        // E-Mail überprüfen und Benutzerdaten abrufen
        $sql = "SELECT id, email, passwort_hash, name, rolle, status FROM benutzer WHERE email = ?";
        $user = $this->db->fetchOne($sql, [$email]);
        
        // Überprüfen, ob Benutzer existiert
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Ungültige Anmeldeinformationen'
            ];
        }
        
        // Überprüfen, ob Konto aktiviert ist
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'Ihr Konto wurde noch nicht freigeschaltet. Bitte wenden Sie sich an einen Administrator.'
            ];
        }
        
        // Passwortüberprüfung für Demo-Konten
        $demoCredentials = [
            'admin@marina-power.de' => 'admin123',
            'benutzer@marina-power.de' => 'benutzer123'
        ];
        
        // Für Demo-Zwecke: Wenn es sich um ein Demo-Konto handelt, direkter Passwortvergleich
        if (array_key_exists($email, $demoCredentials) && $password === $demoCredentials[$email]) {
            // Session-Daten setzen
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['rolle'];
            $_SESSION['last_activity'] = time();
            
            // Erfolgreiche Anmeldung
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $user['rolle']
                ]
            ];
        }
        
        // Für normale Konten: Wenn nicht im Demo-Modus, Passwort mit Hash vergleichen
        // In einer Produktionsumgebung: password_verify($password, $user['passwort_hash'])
        if ($password === $user['passwort_hash']) {
            // Session-Daten setzen
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['rolle'];
            $_SESSION['last_activity'] = time();
            
            // Erfolgreiche Anmeldung
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $user['rolle']
                ]
            ];
        }
        
        // Fehlgeschlagene Anmeldung
        return [
            'success' => false,
            'message' => 'Ungültige Anmeldeinformationen'
        ];
    }
    
    // Benutzer registrieren
    public function register($email, $password, $name) {
        // Prüfen, ob E-Mail bereits existiert
        $sql = "SELECT id FROM benutzer WHERE email = ?";
        $existingUser = $this->db->fetchOne($sql, [$email]);
        
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Diese E-Mail-Adresse wird bereits verwendet'
            ];
        }
        
        // In einer Produktionsumgebung würden wir password_hash verwenden
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $hashedPassword = $password; // Nur für Demo-Zwecke!
        
        // Benutzer erstellen
        $sql = "INSERT INTO benutzer (id, email, passwort_hash, name, rolle, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $userId = uniqid('user_');
        $role = 'user';
        $status = 'pending';
        
        $this->db->query($sql, [$userId, $email, $hashedPassword, $name, $role, $status]);
        
        if ($this->db->affectedRows() > 0) {
            return [
                'success' => true,
                'message' => 'Ihr Konto wurde erstellt und wartet auf Freischaltung durch einen Administrator.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Bei der Registrierung ist ein Fehler aufgetreten.'
        ];
    }
    
    // Prüfen, ob Benutzer angemeldet ist
    public function isLoggedIn() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            // Prüfen, ob die Sitzung abgelaufen ist
            if (time() - $_SESSION['last_activity'] > SESSION_DURATION) {
                $this->logout();
                return false;
            }
            
            // Sitzungsaktivität aktualisieren
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        return false;
    }
    
    // Prüfen, ob Benutzer Admin ist
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }
    
    // Benutzer abmelden
    public function logout() {
        // Session-Variablen löschen
        $_SESSION = [];
        
        // Cookie löschen
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Session zerstören
        session_destroy();
        
        return true;
    }
    
    // Aktuellen Benutzer abrufen
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
}

// Auth-Instanz erstellen
$auth = new Auth($db);
?>
