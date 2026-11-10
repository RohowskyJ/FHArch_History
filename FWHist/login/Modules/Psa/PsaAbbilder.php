<?php

declare(strict_types=1);

/**
 * PSA, Bilder- Beschreibungen
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

$page = new HTMLPage('Information zu Bildern für Auszeichnungen, Ärmelabzeichen, Wappen  ',
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

 
  echo "<div class='custom-row' >"; // Beginn der Einheit Ausgab
  
  echo "<div class='Menu-Separator'><b>Bildformate zum Hochladen</b></div>";
  
  echo "<div class='custom-row' >"; // Beginn der Einheit Ausgab
  echo "Die maximale Größe von Einzelbildern für die Darstellung von Fotos darf 15 x 15 cm, Auflösung 96 DPI nicht überschreiten.
       <br>";
  echo "Die empfohlenen Größen für Auszeichnungen und Abzeichen:";
  echo "<dl compact>";
  
  echo "<dt><b>Große Bilder</b></dt>";
  echo "<dd><b><i>15 x 15 cm 96 DPI</i></b>, z.B.: Ärmelabzeichen, Auszeichnungen mit Band oder größer </dd>";
  
  echo "<dt><b>Mittlere Bilder</b></dt>";
  echo "<dd><b><i>8 x 8 cm 96 DPI</b></i>, z.B.: Leistungsabzeichen, Medaillen ohne Band, ... </dd>";
  
  echo "<dt><b>Kleine Bilder</b></dt>";
  echo "<dd><b><i>6 x 6 cm 96 DPI</i></b>, z.B.: Miniaturen, Jugenabzeichen (alt)</dd>";
 
  echo "</dl>";
  
  echo "</div>";


  $page->renderFooter();
?>
