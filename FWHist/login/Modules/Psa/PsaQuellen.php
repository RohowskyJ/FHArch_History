<?php

declare(strict_types=1);

/**
 * PSA Beschreibug Quellen
 *
 * @author Josef Rohowsky - neu 2018
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
new EnvSessionManager();

const Module_Name = 'PSA';
$module = Module_Name;

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
#require $systemBasePath . 'login/Services/CommFuncsLib.php';
#require $systemBasePath . 'login/Services/ConstLib.php';

use FWHist\Database\DB_GenericLog;
#use FWHist\Modules\Psa\API\DB_Psa;
#use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
#use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
#use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Information zu Auszeichnungen, Ärmelabzeichen, Wappen ',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
        'jq' => true,
        'jqui' => true,
        'jq_accordion' => true,
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

echo "<div class='Menu-Separator'><b>Abkürzungen</b></div>"; // Beginn der Einheit Ausgab


echo "<div class='custm-row' >"; // Beginn der Einheit Ausgab
echo "ÖBFV Österreichischer Bundes-Feuerwehrverband, NÖLFV Niederösterreichischer Landesfeuerwehrverband, FF Freiwillige Feuerwehr, BtF Betriebsfeuerwehr, FM Feuerwehrmuseum.
       ";
echo "</div>";

echo "<div class='Menu-Separator'>Quellen</div>";

echo "<div class='custom-row' >"; // Beginn der Einheit Ausgab
echo "<b>Unterlagen und Ausarbeitungen von </b> Foist Johann, Giczi Leopold, Schneider Hans (ÖBFV,NÖ LFV), Hollauf Siegfried (NÖ LFV), Schmidt Günter Erik (Orden und Ehrenzeichen Österreichs, 1918 - 1938, 1945 - 1999)";
echo "</div>";

echo "<div class='Menu-Separator'>Objekte zum Fotografieren oder Bilder von:</div>";
    
echo "<div class='custom-row' >"; // Beginn der Einheit Ausgab
echo "<b>Alle namentlich erwähnten Institutionen und Personen (außer es steht in Klammer der Staat) sind aus Österreich.</b><br/>";
echo "ÖBFV, FM Baden Stadt, FM Gumpoldskirchen, FM Fischamend, FM Frohsdorf, FF/Gde Kaltenleutgeben, FM Laxenburg (mit Sammlung Foist), FF Maria Lanzendorf, FM Möllersdorf, FF Purkersdorf, 
        Braunstein Franz, Binder Hubert, Dachauer Johann, Florian Hell, 
        Fastl Christian, Gutmann Heinrich, Hollauf Siegfried (NÖ LFV), Iszovitz Stefan, Iszovitz Werner, Klaedtke Bernd (D), Krenn Heinrich, Laager Brigitte, Lef&egrave;vre Horst (D), 
        Maresch Friedrich, Mischinger Manfred, Mislivececk Manfred, Poloma Peter,  
        Pink Wolfgang, Rath Roman, Reinholz Heiko (D), Rohowsky Josef, Schanda Herbert (ELDBSTV), Terscinar Reinhard, FM Vorarlberg, Wandl Rudi, FM Wien, Weiß Alois, Wörner Frank (D), Zehetner Karl.
       ";
echo "</div>";

echo "<div class='Menu-Separator'><b>Literaturhinweise</b></div>";

echo "<div class='custom-row >"; // Beginn der Einheit Ausgab
echo "finden sich im Buch 'Niederösterreichische Feuerwehrstudien 8', 
        Autor Christian Fastl</u>, 'Bibliographie zur Niederösterreichischen Feuerwehrgeschichte, Tulln 2011'.
        Seite 16 - 18, Auszeichnungen, (Leistungs-) Abzeichen, Abzeichen, Ehrungen.";
echo "</div>";

$page->renderFooter();
?>
