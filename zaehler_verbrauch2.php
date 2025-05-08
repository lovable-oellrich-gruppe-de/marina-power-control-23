<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Zählerauswahl über GET-Parameter
$selected_zaehler = isset($_GET['zaehler']) && is_array($_GET['zaehler']) ? array_map('intval', $_GET['zaehler']) : [];
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 year'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Alle Zähler laden für Auswahl
$alle_zaehler = $db->fetchAll("SELECT zaehler.id, zaehler.zaehlernummer, steckdosen.bezeichnung AS steckdose, bereiche.name AS bereich
    FROM zaehler
    LEFT JOIN steckdosen ON zaehler.steckdose_id = steckdosen.id
    LEFT JOIN bereiche ON steckdosen.bereich_id = bereiche.id
    ORDER BY zaehler.zaehlernummer");

// Verbrauchsdaten für alle ausgewählten Zähler laden
$verbrauchsdaten = [];
$labels = [];
$werte_map = [];
$letzte_stand_map = [];
$debug_messages = [];

if (!empty($selected_zaehler)) {
    foreach ($selected_zaehler as $zid) {
        $daten = $db->fetchAll("SELECT zaehler.id, zaehler.zaehlernummer, zaehlerstaende.datum, zaehlerstaende.stand 
            FROM zaehler 
            LEFT JOIN zaehlerstaende ON zaehlerstaende.zaehler_id = zaehler.id 
            WHERE zaehler.id = ? AND zaehlerstaende.datum BETWEEN ? AND ? 
            ORDER BY zaehlerstaende.datum ASC, zaehlerstaende.id ASC", 
            [$zid, $start_date, $end_date]);

        $debug_messages[] = "Zähler $zid: " . count($daten) . " Einträge gefunden.";
        $debug_messages[] = "Daten von Zähler $zid:<pre>" . print_r($daten, true) . "</pre>";

        $zaehlernummer = null;
        $werte_map[$zid]['werte'] = [];

        if (!empty($daten)) {
            $zaehlernummer = $daten[0]['zaehlernummer'];
            $werte_map[$zid]['zaehlernummer'] = $zaehlernummer;

            $datum_map = [];
            foreach ($daten as $row) {
                $datum = date('Y-m-d', strtotime($row['datum']));
                if (!isset($datum_map[$datum])) {
                    $datum_map[$datum] = [];
                }
                $datum_map[$datum][] = (float)$row['stand'];
            }

            $previous = null;
            foreach ($datum_map as $datum => $staende) {
                $labels[$datum] = true;
                $letzte_stand_map[$zid][$datum] = end($staende);

                if ($previous === null) {
                    // erster Tag – Differenz aus erstem und letztem Stand oder gesamter Wert
                    $verbrauch = end($staende);
                } else {
                    $verbrauch = end($staende) - $previous;
                }

                $werte_map[$zid]['werte'][$datum] = max($verbrauch, 0);
                $previous = end($staende);
            }
        }
    }
    $labels = array_keys($labels);
    sort($labels);
}
$debug_messages[] = "Ausgewählte Zähler: " . implode(',', $selected_zaehler);
$debug_messages[] = "Startdatum: $start_date";
$debug_messages[] = "Enddatum: $end_date";
$debug_messages[] = "Labels: <pre>" . print_r($labels, true) . "</pre>";
$debug_messages[] = "Werte Map: <pre>" . print_r($werte_map, true) . "</pre>";
?>
