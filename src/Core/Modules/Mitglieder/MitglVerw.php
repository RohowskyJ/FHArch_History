<?php

/**
 * Menu Mitgliederverwaltung
 * 
 * @author Josef Rohowsky - neu 2023
 */
session_start();

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MiVerw_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'MVW';
$sub_mod = 'all';

$Zugr = "ADM-MI";

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

require_once __DIR__ . '/../../../../vendor/autoload.php';

use League\Plates\Engine;

# var_dump($_SESSION);
$debug = false;

# $rootPfad = "/FHArch-oop"
use Fharch\Core\EnvSessionManager;
$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
# echo __LINE__ . " basepath $basePath <bR>";
#var_dump($_SESSION);

$templates = new Engine(__DIR__ . '/../../templates'); // /src/core/templates

$data = [
    'title' => 'Mitglieder- Verwaltungs- Seite',
    'debug' => $debug,
    'path2ROOT' => $path2ROOT,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
];

echo $templates->render('pages/mitglVerw', $data);
exit;
#$ber = userBerechtigtOK($Zugr);

$debug = False; // Debug output Ein/Aus Schalter



$flow_list = False;

initial_debug('POST', 'GET');

# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================

HTML_header('Mitglieder- Verwaltung', '', 'Form', '70em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

if (userHasRole('ADM-MI')) {  // Ist benutzer berechtigt?

    echo "<div class='Menu-Separator'>Mitglieder- Verwaltung</div>";
    
    echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
    echo "<tr><td><a href='VS_M_List.php' target='M-Verwaltung'>Mitgliederverwaltung</a></td></tr>";
    echo "  </div>";  // Ende Feldname
    
    echo "<div class='Menu-Separator'>Ehrungen- Verwaltung</div>";
    
    echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
    echo "<tr><td><a href='VF_M_Ehrg_List.php?' target='M-Verwaltung'>Ehrungen</a></td></tr>";
    echo "  </div>";  // Ende Feldname

    echo "<div class='Menu-Separator'>Unterstützer- Verwaltung</div>";
    
    echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
    echo "<tr><td><a href='VS_UnterstList.php?' target='M-Verwaltung'>Unterstützer</a></td></tr>";
    echo "  </div>";  // Ende Feldname
    
}

if (userHasRole('ADM-MB')) {  // Ist Benutzer berechtigt?
    
    echo "<div class='Menu-Separator'>Mitglieder- Zahlungseingangs- Verwaltung</div>";
    echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
    echo "Hier werden die Zahlungseingänge (Mitgliedsbeitrag und ABO- Gebühr verwaltet).";
    echo "  </div>";  // Ende Feldname
    
    echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
    echo "<a href='VS_MB_List.php' target='M Bez.-Verwaltung'>Beitrags- Eingang</a>";
    echo "  </div>";  // Ende Feldname
}


echo "<div class='Menu-Separator'>Mitglieder- E-Mail an</div>";

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "Mitglieder können E-Mails an andere Mitglieder senden, ohne das Sie die E-Mail Adresse kennen.</a>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<a href='VF_M_Mail.php' target='M-Mail'>Mail an andere Mitglieder senden </a>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Separator'>Mitglieder- Auskuft laut DSVGO</div>";

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<tr><td>Jedes Mitglied kann sich die im System gespeicherten persönliche Daten entsprechend der DSVGO selbst anfordern und bekommt sie sofort per E-Mail zugeschickt.</td></tr>";
echo "  </div>";  // Ende Feldname

echo "<div class='Menu-Line'>"; // Beginn der Einheit Ausgabe
echo "<tr><td><a href='VF_M_yellow.php' target='M-Datenabfrage'>Mitglieder-Daten Auskunft laut DSGVO</a></td></tr>";
echo "  </div>";  // Ende Feldname

HTML_trailer();
?>
