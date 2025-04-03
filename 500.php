<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="flex min-h-screen flex-col items-center justify-center bg-muted/20 p-4">
    <div class="w-full max-w-md space-y-6 rounded-lg border bg-white p-6 shadow-lg">
        <div class="space-y-2 text-center">
            <h1 class="text-3xl font-bold text-marina-800">500 - Serverfehler</h1>
            <p class="text-gray-500">Leider ist ein interner Serverfehler aufgetreten.</p>
            <div class="mt-6">
                <a href="index.php" class="text-marina-600 hover:text-marina-800 hover:underline">
                    Zurück zur Startseite
                </a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
