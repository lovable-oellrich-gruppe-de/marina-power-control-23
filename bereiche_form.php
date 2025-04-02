
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

// Standardwerte für neuen Bereich
$bereich = [
    'id' => '',
    'name' => '',
    'beschreibung' => '',
    'aktiv' => true
];

// Prüfen, ob ein Bereich zur Bearbeitung übergeben wurde
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $result = $db->fetchOne("SELECT * FROM bereiche WHERE id = ?", [$id]);
    
    if ($result) {
        $bereich = $result;
    } else {
        $error = "Bereich nicht gefunden.";
    }
}

// Formular wurde abgesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulardaten einlesen
    $bereich = [
        'id' => $_POST['id'] ?? '',
        'name' => $_POST['name'] ?? '',
        'beschreibung' => $_POST['beschreibung'] ?? '',
        'aktiv' => isset($_POST['aktiv']) ? 1 : 0
    ];
    
    // Validierung
    if (empty($bereich['name'])) {
        $error = "Bitte geben Sie einen Namen ein.";
    } else {
        // Entweder aktualisieren oder neu anlegen
        if (!empty($bereich['id'])) {
            // Aktualisieren
            $query = "UPDATE bereiche SET 
                name = ?, 
                beschreibung = ?, 
                aktiv = ? 
                WHERE id = ?";
                
            $params = [
                $bereich['name'],
                $bereich['beschreibung'],
                $bereich['aktiv'],
                $bereich['id']
            ];
            
            $db->query($query, $params);
            
            if ($db->affectedRows() >= 0) {
                $success = "Bereich wurde erfolgreich aktualisiert.";
            } else {
                $error = "Fehler beim Aktualisieren des Bereichs.";
            }
        } else {
            // Neu anlegen
            $query = "INSERT INTO bereiche (
                name, beschreibung, aktiv
                ) VALUES (?, ?, ?)";
                
            $params = [
                $bereich['name'],
                $bereich['beschreibung'],
                $bereich['aktiv']
            ];
            
            $db->query($query, $params);
            
            if ($db->affectedRows() > 0) {
                $success = "Bereich wurde erfolgreich erstellt.";
                // Formular zurücksetzen
                $bereich = [
                    'id' => '',
                    'name' => '',
                    'beschreibung' => '',
                    'aktiv' => true
                ];
            } else {
                $error = "Fehler beim Erstellen des Bereichs.";
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
                <?= empty($bereich['id']) ? 'Neuen Bereich anlegen' : 'Bereich bearbeiten' ?>
            </h1>
            <a href="bereiche.php" class="text-marina-600 hover:text-marina-700">
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

        <!-- Bereich-Formular -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <form action="bereiche_form.php" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($bereich['id']) ?>">
                
                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?= htmlspecialchars($bereich['name']) ?>" 
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label for="beschreibung" class="block text-sm font-medium text-gray-700">Beschreibung</label>
                        <textarea 
                            id="beschreibung" 
                            name="beschreibung" 
                            rows="3"
                            class="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        ><?= htmlspecialchars($bereich['beschreibung']) ?></textarea>
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="aktiv" 
                            name="aktiv" 
                            <?= $bereich['aktiv'] ? 'checked' : '' ?>
                            class="h-4 w-4 text-marina-600 focus:ring-marina-500 border-gray-300 rounded"
                        >
                        <label for="aktiv" class="ml-2 block text-sm text-gray-700">
                            Aktiv
                        </label>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="bereiche.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
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
