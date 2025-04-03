
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Benutzer abmelden
$auth->logout();

// Zur Login-Seite weiterleiten
header('Location: login.php');
exit;
?>
