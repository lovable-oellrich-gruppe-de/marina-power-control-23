
<?php
// Wichtige Einbindungen für die Anwendung
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn nicht angemeldet, zur Login-Seite umleiten
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Initialisierung der Variablen
$zaehler_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errors = [];
$zaehler = [
    'zaehlernummer' => '',
    'typ' => 'Stromzähler',
    'hersteller' => '',
    'modell' => '',
    'installiert_am' => date('Y-m-d'),
    'letzte_wartung' => '',
    'seriennummer' => '',
    'max_leistung' => '',
    'ist_ausgebaut' => 0,
    'hinweis' => ''
];

// Wenn ID vorhanden, Zähler laden
if ($zaehler_id) {
    $loaded_zaehler = $db->fetchOne("SELECT * FROM zaehler WHERE id = ?", [$zaehler_id]);
    if ($loaded_zaehler) {
        $zaehler = $loaded_zaehler;
    } else {
        $errors[] = "Zähler nicht gefunden.";
    }
}

// Formular wurde abgesendet
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Daten aus dem Formular übernehmen
    $zaehler['zaehlernummer'] = trim($_POST['zaehlernummer'] ?? '');
    $zaehler['typ'] = trim($_POST['typ'] ?? 'Stromzähler');
    $zaehler['hersteller'] = trim($_POST['hersteller'] ?? '');
    $zaehler['modell'] = trim($_POST['modell'] ?? '');
    $zaehler['installiert_am'] = trim($_POST['installiert_am'] ?? date('Y-m-d'));
    $zaehler['letzte_wartung'] = trim($_POST['letzte_wartung'] ?? '');
    $zaehler['seriennummer'] = trim($_POST['seriennummer'] ?? '');
    $zaehler['max_leistung'] = trim($_POST['max_leistung'] ?? '');
    $zaehler['ist_ausgebaut'] = isset($_POST['ist_ausgebaut']) ? 1 : 0;
    $zaehler['hinweis'] = trim($_POST['hinweis'] ?? '');
    
    // Validierung
    if (empty($zaehler['zaehlernummer'])) {
        $errors[] = "Zählernummer ist erforderlich.";
    }
    
    if (empty($zaehler['installiert_am'])) {
        $errors[] = "Installationsdatum ist erforderlich.";
    }
    
    // Wenn keine Fehler, Daten speichern
    if (empty($errors)) {
        $params = [
            $zaehler['zaehlernummer'],
            $zaehler['typ'],
            $zaehler['hersteller'],
            $zaehler['modell'],
            $zaehler['installiert_am'],
            empty($zaehler['letzte_wartung']) ? null : $zaehler['letzte_wartung'],
            $zaehler['seriennummer'],
            empty($zaehler['max_leistung']) ? null : $zaehler['max_leistung'],
            $zaehler['ist_ausgebaut'],
            $zaehler['hinweis']
        ];
        
        if ($zaehler_id) {
            // Zähler aktualisieren
            $sql = "UPDATE zaehler SET 
                    zaehlernummer = ?,
                    typ = ?,
                    hersteller = ?,
                    modell = ?,
                    installiert_am = ?,
                    letzte_wartung = ?,
                    seriennummer = ?,
                    max_leistung = ?,
                    ist_ausgebaut = ?,
                    hinweis = ?
                    WHERE id = ?";
            $params[] = $zaehler_id;
            $db->query($sql, $params);
            
            $success_message = "Zähler wurde erfolgreich aktualisiert.";
        } else {
            // Neuen Zähler erstellen
            $sql = "INSERT INTO zaehler (
                    zaehlernummer, typ, hersteller, modell, installiert_am, 
                    letzte_wartung, seriennummer, max_leistung, ist_ausgebaut, hinweis
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $db->query($sql, $params);
            $zaehler_id = $db->lastInsertId();
            
            $success_message = "Zähler wurde erfolgreich erstellt.";
        }
        
        // Weiterleitung zur Zählerübersicht bei Erfolg
        if (empty($errors)) {
            header("Location: zaehler.php?success=" . urlencode($success_message));
            exit;
        }
    }
}

// Header einbinden
require_once 'includes/header.php';
?>

<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">
                <?= $zaehler_id ? 'Zähler bearbeiten' : 'Neuen Zähler erstellen' ?>
            </h1>
            <a href="zaehler.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                Zurück zur Liste
            </a>
        </div>
        
        <!-- Fehlermeldungen -->
        <?php if (!empty($errors)): ?>
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Fehler!</strong>
                <ul class="mt-1 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Formular -->
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="p-6">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="zaehlernummer" class="block text-sm font-medium text-gray-700">Zählernummer *</label>
                        <div class="mt-1">
                            <input type="text" name="zaehlernummer" id="zaehlernummer" required
                                value="<?= htmlspecialchars($zaehler['zaehlernummer']) ?>"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-3">
                        <label for="typ" class="block text-sm font-medium text-gray-700">Typ</label>
                        <div class="mt-1">
                            <input type="text" name="typ" id="typ"
                                value="<?= htmlspecialchars($zaehler['typ']) ?>"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-3">
                        <label for="hersteller" class="block text-sm font-medium text-gray-700">Hersteller</label>
                        <div class="mt-1">
                            <input type="text" name="hersteller" id="hersteller"
                                value="<?= htmlspecialchars($zaehler['hersteller']) ?>"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-3">
                        <label for="modell" class="block text-sm font-medium text-gray-700">Modell</label>
                        <div class="mt-1">
                            <input type="text" name="modell" id="modell"
                                value="<?= htmlspecialchars($zaehler['modell']) ?>"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-3">
                        <label for="installiert_am" class="block text-sm font-medium text-gray-700">Installiert am *</label>
                        <div class="mt-1">
                            <input type="date" name="installiert_am" id="installiert_am" required
                                value="<?= htmlspecialchars($zaehler['installiert_am']) ?>"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-3">
                        <label for="letzte_wartung" class="block text-sm font-medium text-gray-700">Letzte Wartung</label>
                        <div class="mt-1">
                            <input type="date" name="letzte_wartung" id="letzte_wartung"
                                value="<?= htmlspecialchars($zaehler['letzte_wartung'] ?? '') ?>"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-3">
                        <label for="seriennummer" class="block text-sm font-medium text-gray-700">Seriennummer</label>
                        <div class="mt-1">
                            <input type="text" name="seriennummer" id="seriennummer"
                                value="<?= htmlspecialchars($zaehler['seriennummer']) ?>"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-3">
                        <label for="max_leistung" class="block text-sm font-medium text-gray-700">Max. Leistung (W)</label>
                        <div class="mt-1">
                            <input type="number" name="max_leistung" id="max_leistung"
                                value="<?= htmlspecialchars($zaehler['max_leistung'] ?? '') ?>"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="ist_ausgebaut" id="ist_ausgebaut"
                                    <?= $zaehler['ist_ausgebaut'] ? 'checked' : '' ?>
                                    class="focus:ring-marina-500 h-4 w-4 text-marina-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="ist_ausgebaut" class="font-medium text-gray-700">Zähler ist ausgebaut</label>
                                <p class="text-gray-500">Markieren Sie diese Option, wenn der Zähler nicht mehr in Betrieb ist.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sm:col-span-6">
                        <label for="hinweis" class="block text-sm font-medium text-gray-700">Hinweis</label>
                        <div class="mt-1">
                            <textarea name="hinweis" id="hinweis" rows="3"
                                class="shadow-sm focus:ring-marina-500 focus:border-marina-500 block w-full sm:text-sm border-gray-300 rounded-md"><?= htmlspecialchars($zaehler['hinweis']) ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="pt-5">
                    <div class="flex justify-end">
                        <a href="zaehler.php" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                            Abbrechen
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-marina-600 hover:bg-marina-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-marina-500">
                            Speichern
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
