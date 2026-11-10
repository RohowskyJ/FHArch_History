<?php


function getRelativePath(string $from, string $to): string {
    // Absoluten Pfad normalisieren (realpath auf Verzeichnisse/Dateien)
    $from = str_replace('\\', '/', realpath($from) ?: $from);
    $to = str_replace('\\', '/', realpath($to) ?: $to);
    
    // Pfade in Teile zerlegen
    $fromParts = explode('/', rtrim($from, '/'));
    $toParts = explode('/', rtrim($to, '/'));
    
    // Gemeinsame Pfadteile entfernen
    while (count($fromParts) && count($toParts) && ($fromParts[0] === $toParts[0])) {
        array_shift($fromParts);
        array_shift($toParts);
    }
    
    // Für jeden verbleibenden Teil in $from ein "../" hinzufügen
    $relativePath = str_repeat('../', count($fromParts));
    
    // Rest von $to anhängen
    # $relativePath .= implode('/', $toParts);
    
    return $relativePath;
}
/*
// Beispiel:
// Angenommen, das Skript liegt in /login/Core/Modules/Mitglieder
$currentDir = __DIR__; // z.B. /var/www/html/login/Core/Modules/Mitglieder
// Zielpfad
$targetPath = '/var/www/html/VFH/index.php';

// Relativen Pfad berechnen
$relativePath = getRelativePath($currentDir, $targetPath);

echo $relativePath; // Ausgabe z.B.: ../../../VFH/index.php

$from sollte ein Verzeichnis sein (z.B. __DIR__).
$to kann eine Datei oder ein Verzeichnis sein.
Falls du den relativen Pfad von einer Datei aus berechnen willst, verwende dirname(__FILE__) oder dirname(__DIR__) als $from.
Die Funktion berücksichtigt automatisch gemeinsame Pfadbestandteile und erzeugt die korrekte Anzahl von ../.
So kannst du z.B. in deinem Fall den Pfad von /login/Core/Modules/Mitglieder/abc.php zu /VFH/index.php dynamisch und korrekt erzeugen.





Wie kann ich die Funktion in meinem Projekt einbinden?


Gibt es eine Version für andere Programmiersprachen?


Was mache ich, wenn die Pfade nicht funktionieren?


Kann ich die Funktion für URLs anpassen?

*/