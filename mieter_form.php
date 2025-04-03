<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$mieter = [
    'id' => '',
    'vorname' => '',
    'nachname' => '',
    'strasse' => '',
    'hausnummer' => '',
    'email' => '',
    'telefon' => '',
    'mobil' => '',
    'hinweis' => '',
    'bootsname' => ''
];

// Prüfen, ob ein Mieter zur Bearbeitung übergeben wurde
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $result = $db->fetchOne("SELECT * FROM mieter WHERE id = ?", [$id]);
    
    if ($result) {
        $mieter = $result;
    } else {
        $error = "Mieter nicht gefunden.";
    }
}

// Formular wurde abgesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulardaten einlesen
    $mieter = [
        'id' => $_POST['id'] ?? '',
        'vorname' => $_POST['vorname'] ?? '',
        'nachname' => $_POST['nachname'] ?? '',
        'strasse' => $_POST['strasse'] ?? '',
        'hausnummer' => $_POST['hausnummer'] ?? '',
        'email' => $_POST['email'] ?? '',
        'telefon' => $_POST['telefon'] ?? '',
        'mobil' => $_POST['mobil'] ?? '',
        'hinweis' => $_POST['hinweis'] ?? '',
        'bootsname' => $_POST['bootsname'] ?? ''
    ];
    
    // Validierung
    if (empty($mieter['vorname']) || empty($mieter['nachname']) || empty($mieter['email'])) {
        $error = "Bitte füllen Sie alle Pflichtfelder aus.";
    } else {
        // Entweder aktualisieren oder neu anlegen
        if (!empty($mieter['id'])) {
            // Aktualisieren
            $query = "UPDATE mieter SET 
                vorname = ?, 
                nachname = ?, 
                strasse = ?, 
                hausnummer = ?, 
                email = ?, 
                telefon = ?, 
                mobil = ?, 
                hinweis = ?, 
                bootsname = ? 
                WHERE id = ?";
                
            $params = [
                $mieter['vorname'],
                $mieter['nachname'],
                $mieter['strasse'],
                $mieter['hausnummer'],
                $mieter['email'],
                $mieter['telefon'],
                $mieter['mobil'],
                $mieter['hinweis'],
                $mieter['bootsname'],
                $mieter['id']
            ];
            
            $db->query($query, $params);
            
            if ($db->affectedRows() >= 0) {
                $success = "Mieter wurde erfolgreich aktualisiert.";
            } else {
                $error = "Fehler beim Aktualisieren des Mieters.";
            }
        } else {
            // Neu anlegen
            $query = "INSERT INTO mieter (
                vorname, nachname, strasse, hausnummer, email, telefon, mobil, hinweis, bootsname
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $params = [
                $mieter['vorname'],
                $mieter['nachname'],
                $mieter['strasse'],
                $mieter['hausnummer'],
                $mieter['email'],
                $mieter['telefon'],
                $mieter['mobil'],
                $mieter['hinweis'],
                $mieter['bootsname']
            ];
            
            $db->query($query, $params);
            
            if ($db->affectedRows() > 0) {
                $success = "Mieter wurde erfolgreich erstellt.";
                // Formular zurücksetzen
                $mieter = [
                    'id' => '',
                    'vorname' => '',
                    'nachname' => '',
                    'strasse' => '',
                    'hausnummer' => '',
                    'email' => '',
                    'telefon' => '',
                    'mobil' => '',
                    'hinweis' => '',
                    'bootsname' => ''
                ];
            } else {
                $error = "Fehler beim Erstellen des Mieters.";
            }
        }
    }
}

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                <?= empty($mieter['id']) ? 'Neuen Mieter anlegen' : 'Mieter bearbeiten' ?>
            </h1>
            <a href="mieter.php" class="text-marina-600 hover:text-marina-700">
                Zurück zur Übersicht
            </a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Mieter-Formular -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <form action="mieter_form.php" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($mieter['id']) ?>">
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="vorname" class="block text-sm font-medium text-gray-700">Vorname *</label>
                        <input 
                            type="text" 
                            id="vorname" 
                            name="vorname" 
                            value="<?= htmlspecialchars($mieter['vorname']) ?>" 
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label for="nachname" class="block text-sm font-medium text-gray-700">Nachname *</label>
                        <input 
                            type="text" 
                            id="nachname" 
                            name="nachname" 
                            value="<?= htmlspecialchars($mieter['name']) ?>" 
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label for="strasse" class="block text-sm font-medium text-gray-700">Straße</label>
                        <input 
                            type="text" 
                            id="strasse" 
                            name="strasse" 
                            value="<?= htmlspecialchars($mieter['strasse']) ?>"
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label for="hausnummer" class="block text-sm font-medium text-gray-700">Hausnummer</label>
                        <input 
                            type="text" 
                            id="hausnummer" 
                            name="hausnummer" 
                            value="<?= htmlspecialchars($mieter['hausnummer']) ?>"
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2 sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700">E-Mail *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($mieter['email']) ?>" 
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label for="telefon" class="block text-sm font-medium text-gray-700">Telefon</label>
                        <input 
                            type="tel" 
                            id="telefon" 
                            name="telefon" 
                            value="<?= htmlspecialchars($mieter['telefon']) ?>"
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label for="mobil" class="block text-sm font-medium text-gray-700">Mobil</label>
                        <input 
                            type="tel" 
                            id="mobil" 
                            name="mobil" 
                            value="<?= htmlspecialchars($mieter['mobil']) ?>"
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2 sm:col-span-2">
                        <label for="bootsname" class="block text-sm font-medium text-gray-700">Bootsname</label>
                        <input 
                            type="text" 
                            id="bootsname" 
                            name="bootsname" 
                            value="<?= htmlspecialchars($mieter['bootsname']) ?>"
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2 sm:col-span-2">
                        <label for="hinweis" class="block text-sm font-medium text-gray-700">Hinweis</label>
                        <textarea 
                            id="hinweis" 
                            name="hinweis" 
                            rows="3"
                            class="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        ><?= htmlspecialchars($mieter['hinweis']) ?></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="mieter.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Abbrechen
                    </a>
                    <button type="submit" class="px-4 py-2 bg-marina-600 text-white rounded hover:bg-marina-700">
                        Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
