<?php

declare(strict_types=1);

# session_start();
/**
 * Archivalien- Liste
 *
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 *
 *
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Projekt-/Session-Umgebung initialisieren.
$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require $systemBasePath . 'vendor/autoload.php';

echo "<!Doctype html>";

use FWHist\Core\EnvSessionManager;
use FWHist\Services\FuncsClass;
use FWHist\Services\HTMLPage;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents($systemBasePath . 'login/AOrd_Verz/logging/FZMKat_List_fatal_error.log', $message, FILE_APPEND);
    }
});
    
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log',  $systemBasePath . 'login/AOrd_Verz/logging/FZMKat_List_php-error.log.txt');
    # var_dump($_SERVER);
    
    $module = 'OEF-ARC';
    $sub_mod = "LIST";
    
    $Zugr = "ADM-ARC" ;
    
    $debug = true;
    
    $tabelle = 'oe_museen';
    
    require $systemBasePath . 'login/Services/FuncsLib.php';
    require $systemBasePath . 'login/Services/CommFuncsLib.php';
    require $systemBasePath . 'login/Services/ConstLib.php';
    
    
    use FWHist\Database\DB_GenericLog;
    use FWHist\Auth\Auth;
    
    $ber = userBerechtigtOK($Zugr);
    if (!$ber) {
        header("Location $systemBasePath/");
    }
    
    $envManager = new EnvSessionManager();
    $basePath = $envManager->getEnv('basePath')  ;
    $configFileS = $envManager->getEnv('configFileS');
    
    $header =   "";
    # ===========================================================================================================
    # Haeder ausgeben
    # ===========================================================================================================
    $page = new HTMLPage('Archivalien ',
        $systemBasePath,
        $basePath,
        ['headExtras' => $header,
            'type' => 'Form',
            'width' => '90em',
            'jq' => false,
            'jqui' => false,
            'jq_accordion' => false,
        ]
        );
    
    $page->loadConfig($configFileS);
    $page->renderHeader();
    
    $proj = "Z";
    
    $moduleId = $module."-".$sub_mod;
    // Eigene Meldung mit Modulkennung loggen
    # $logger->log('Starte Verarbeitung des Moduls', $moduleId, basename(__FILE__));
    
    // XR_Database mit bestehender PDO-Instanz initialisieren
    $DBD = new DB_GenericLog();
    $pdo = $DBD->getPDO();
    
    $meta = new TableColumnMetadata($pdo,'fharch_new',false);
    
    # ===========================================================================================
    # Definition der Auswahlmöglichkeiten (mittels radio Buttons)
    # ===========================================================================================
    echo "<input type='hidden' id='srch_Id' value=''>";
    
    $list_ID = 'AR';
    
    // Prüfen, ob ID 'Abf' übergeben wurde (POST oder GET)
    $id = getRequestString('ID', ['Abf']);
    
    if ($id === 'Abf') {
        AutoCompForm_Mandant('selection', 'mand', 'mand', '', 'mand_id');
        
        echo "<p>Nach Auswahl des Mandanten klicken Sie ";
        echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";
        
        echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js'></script>";
    } else {
        // Mandant ID holen (POST bevorzugt vor GET)
        $ei_id = getRequestParam('mand_id');
        
        if ($ei_id !== null) {
            // Mandant ist gesetzt und gültig, Liste rendern
            renderList($ei_id);
        } else {
            // Kein Mandant gesetzt, evtl. Fehlermeldung oder Default-Verhalten
            echo "Kein gültiger Mandant ausgewählt.";
        }
    }
    
    $page->renderFooter();
    
    
    /** Functionen */
    
    // Funktion zur sicheren und priorisierten Abfrage von Parametern
    function getRequestParam(string $key, int $minValue = 1): ?int {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;
        if ($value !== null && filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => $minValue]]) !== false) {
            return (int)$value;
        }
        return null;
    }
    
    function getRequestString(string $key, array $allowedValues = []): ?string {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;
        if ($value !== null && (empty($allowedValues) || in_array($value, $allowedValues, true))) {
            return $value;
        }
        return null;
    }
    
    /**
     * Anzeige der Archivalien- Liste
     *
     * @param int  $ei_id Mandanten- Nummer
     */
    function renderList($ei_id)  {
        global $basePath, $proj, $module, $meta;
        
        /** multi-Select-Aufruf Archiv- Ordnung */
        
        $columnsByTables = $meta->getColumnsForTables(['hi_archivord', 'hi_archord_erw' ]);
        
        $neu = [
            "ad_sg" => "0",
            'ad_subsg' => "0",
            'ad_lcsg' => "0",
            'ad_lcssg' => "0",
            'ad_lcssg_s0' => "0",
            'ad_lcssg_s1' => "0",
        ];
        
        $forms = new FormRendererFlex($meta, 0,  $neu, [], false, $module );
        
        echo $forms->renderMultiSelectFieldFlex(fieldName: 'AOrd-Auswahl',
            configId: 'ARCHIVORDNUNG',
            apiUrl: '../../../login/Services/API/MultiSelectAPI.php'
            );
        
        
        $NeuRec = "";
        if (isset($_SESSION['BS_Prim']['BE'])) {
            # $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='OArchEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
            $NeuRec = "ArchEdit.php?ID=0&ei_id=$ei_id";
        }
        $ActionScript = 'ArchEdit.php';
        
        
        // Listen-Konfiguration serverseitig definiert
        $lists = [
            [
                'id' => 'AR',
                'container' => 'AR-table',
                'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
                'title' => '- Liste',
                'editScript' => $NeuRec,
                'actionScript' => $ActionScript,
                'actionLabel' => 'Bearbeiten',
                'newLabel' => 'Neue Archiv- Daten anlegen',
                'filterSelectFields' => [], // ad_sg/ad_subsg/ad_lc* laufen jetzt ausschließlich über 'multiSelectFilter'
                'filterTextFields' => ['ad_sammlg', 'ad_beschreibg', 'ad_keywords', ],
                'multiSelectFilter' => [
                    'selector' => 'fw-multi-select[name="AOrd-Auswahl"]',
                    'pathFields' => ['ad_sg', 'ad_subsg', 'ad_lcsg', 'ad_lcssg', 'ad_lcssg_s0', 'ad_lcssg_s1'],
                ],
                'paginationSizes' => [10, 25, 50, 100],
            ],
        ];
        
        
        // Globales Parameter-Array
        $globalParams = [
            'owner' => $ei_id,
            'user' => $_SESSION['BS_Prim']['BE']['be_id'],
            'project' => $proj,
            'sel_id' => $ei_id ?? null,
            'edit_Protect' => $editProtect ?? false,
        ];
        
        ?>

<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
console.log('Lists ', lists);
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-AR">
    <h2>Archivalien- Liste</h2>
    <div id="AR-table"></div>
</div>

<script src="<?= $basePath?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath?>/FWHist/js/list-tables.js"></script>
<script src='<?= $basePath?>/FWHist/login/Services/js/fw_multi_select.js' defer></script>

<?php 

} ## End renderList()
?>