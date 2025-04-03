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

// Standardwerte für neue Steckdose
$steckdose = [
    'id' => '',
    'bezeichnung' => '',
    'status' => 'aktiv',
    'bereich_id' => '',
    'mieter_id' => '',
    'hinweis' => ''
];

// Bereiche und Mieter für Auswahlfelder laden
$bereiche = $db->fetchAll("SELECT id, name FROM bereiche ORDER BY name");
$mieter = $db->fetchAll("SELECT id, CONCAT(vorname, ' ', nachname) AS name FROM mieter ORDER BY nachname, vorname");

// Prüfen, ob eine Steckdose zur Bearbeitung übergeben wurde
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $result = $db->fetchOne("SELECT * FROM steckdosen WHERE id = ?", [$id]);
    
    if ($result) {
        $steckdose = $result;
    } else {
        $error = "Steckdose nicht gefunden.";
    }
}

// Formular wurde abgesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulardaten einlesen
    $steckdose = [
        'id' => $_POST['id'] ?? '',
        'bezeichnung' => $_POST['bezeichnung'] ?? '',
        'status' => $_POST['status'] ?? 'aktiv',
        'bereich_id' => $_POST['bereich_id'] ?? '',
        'mieter_id' => $_POST['mieter_id'] ?? '',
        'hinweis' => $_POST['hinweis'] ?? ''
    ];
    
    // Leere Werte in NULL umwandeln
    if (empty($steckdose['bereich_id'])) $steckdose['bereich_id'] = null;
    if (empty($steckdose['mieter_id'])) $steckdose['mieter_id'] = null;
    
    // Validierung
    if (empty($steckdose['bezeichnung'])) {
        $error = "Bitte geben Sie eine Bezeichnung ein.";
    } else {
        // Entweder aktualisieren oder neu anlegen
        if (!empty($steckdose['id'])) {
            // Aktualisieren
            $query = "UPDATE steckdosen SET 
                bezeichnung = ?, 
                status = ?, 
                bereich_id = ?, 
                mieter_id = ?, 
                hinweis = ? 
                WHERE id = ?";
                
            $params = [
                $steckdose['bezeichnung'],
                $steckdose['status'],
                $steckdose['bereich_id'],
                $steckdose['mieter_id'],
                $steckdose['hinweis'],
                $steckdose['id']
            ];
            
            $db->query($query, $params);
            
            if ($db->affectedRows() >= 0) {
                $success = "Steckdose wurde erfolgreich aktualisiert.";
            } else {
                $error = "Fehler beim Aktualisieren der Steckdose.";
            }
        } else {
            // Neu anlegen
            $query = "INSERT INTO steckdosen (
                bezeichnung, status, bereich_id, mieter_id, hinweis
                ) VALUES (?, ?, ?, ?, ?)";
                
            $params = [
                $steckdose['bezeichnung'],
                $steckdose['status'],
                $steckdose['bereich_id'],
                $steckdose['mieter_id'],
                $steckdose['hinweis']
            ];
            
            $db->query($query, $params);
            
            if ($db->affectedRows() > 0) {
                $success = "Steckdose wurde erfolgreich erstellt.";
                // Formular zurücksetzen
                $steckdose = [
                    'id' => '',
                    'bezeichnung' => '',
                    'status' => 'aktiv',
                    'bereich_id' => '',
                    'mieter_id' => '',
                    'hinweis' => ''
                ];
            } else {
                $error = "Fehler beim Erstellen der Steckdose.";
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
                <?= empty($steckdose['id']) ? 'Neue Steckdose anlegen' : 'Steckdose bearbeiten' ?>
            </h1>
            <a href="steckdosen.php" class="text-marina-600 hover:text-marina-700">
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

        <!-- Steckdosen-Formular -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <form action="steckdosen_form.php" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($steckdose['id']) ?>">
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2 space-y-2">
                        <label for="bezeichnung" class="block text-sm font-medium text-gray-700">Bezeichnung *</label>
                        <input 
                            type="text" 
                            id="bezeichnung" 
                            name="bezeichnung" 
                            value="<?= htmlspecialchars($steckdose['bezeichnung']) ?>" 
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                    </div>
                    
                    <div class="space-y-2">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                            <option value="aktiv" <?= $steckdose['status'] === 'aktiv' ? 'selected' : '' ?>>Aktiv</option>
                            <option value="inaktiv" <?= $steckdose['status'] === 'inaktiv' ? 'selected' : '' ?>>Inaktiv</option>
                            <option value="defekt" <?= $steckdose['status'] === 'defekt' ? 'selected' : '' ?>>Defekt</option>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="bereich_id" class="block text-sm font-medium text-gray-700">Bereich</label>
                        <select 
                            id="bereich_id" 
                            name="bereich_id" 
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                            <option value="">-- Keiner --</option>
                            <?php foreach ($bereiche as $bereich): ?>
                                <option value="<?= htmlspecialchars($bereich['id']) ?>" <?= $steckdose['bereich_id'] == $bereich['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($bereich['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="mieter_id" class="block text-sm font-medium text-gray-700">Mieter</label>
                        <select 
                            id="mieter_id" 
                            name="mieter_id" 
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        >
                            <option value="">-- Keiner --</option>
                            <?php foreach ($mieter as $m): ?>
                                <option value="<?= htmlspecialchars($m['id']) ?>" <?= $steckdose['mieter_id'] == $m['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="sm:col-span-2 space-y-2">
                        <label for="hinweis" class="block text-sm font-medium text-gray-700">Hinweis</label>
                        <textarea 
                            id="hinweis" 
                            name="hinweis" 
                            rows="3"
                            class="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-marina-500 focus:border-marina-500"
                        ><?= htmlspecialchars($steckdose['hinweis']) ?></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="steckdosen.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
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
