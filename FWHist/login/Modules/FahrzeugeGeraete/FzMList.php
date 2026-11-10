<?php

declare(strict_types=1);

# session_start();
/**
 * Maschinen- betrieben Fahrzeuge und Geräte Liste
 *
 * @author Josef Rohowsky - neu 2020 - Umstellung Klassen/PDO, Module 2026
 *
 *
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfen, ob Session gültig ist
if (empty($_SESSION['BS_Prim'])) {
    // Session ungültig → Weiterleitung auf externe URL
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        header('Location: /localhost/Feuerwehr_Archiv/FWHist/');
        exit;
    }
    header('Location: https://www.feuerwehrhistoriker.at/FWHist/');
    exit;
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
        file_put_contents($systemBasePath . 'login/AOrd_Verz/logging/FZA_List_fatal_error.log', $message, FILE_APPEND);
    }
});
    
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log',$systemBasePath . 'login/AOrd_Verz/logging/FZMList_php-error.log.txt');
    # var_dump($_SERVER);
    
    $module = 'FZG-Mot';
    $sub_mod = "LIST";
    
    $Zugr = "ADM-FZG" ;
    
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
    $page = new HTMLPage('Muskel getriebene Fahrzeuge und Geräte ',
        $systemBasePath,
        $basePath,
        ['headExtras' => $header,
            'type' => 'Form',
            'width' => '90em',
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
    
    $phase = $_POST['phase'] ?? 0 ;
    
    $neu['sammlg'] = 'MU_F';
    
    $forms = new FormRendererFlex($meta, 0,  $neu, [], false, $module );
    
    # ===========================================================================================
    # Definition der Auswahlmöglichkeiten (mittels radio Buttons)
    # ===========================================================================================
    echo "<input type='hidden' id='srch_Id' value=''>";
    
    #var_dump($_POST);
    #var_dump($_GET);

    // Prüfen, ob ID 'Abf' übergeben wurde (POST oder GET)
    $id = getRequestString('ID', ['Abf']);
    
    if ($id === 'Abf') {
        AutoCompForm_Mandant('selection', 'mand', 'mand', '', 'mand_id');
        
        echo $forms->renderSelectFieldFlex('sammlg', ['MU_F' => 'Muskelgetriebene Farzeuge', 'MU_G' => 'Muskelgetriebene Geräte']);
        
        echo "<p>Nach Auswahl des Mandanten und der Sammlung klicken Sie ";
        echo "<button type='submit' name='phase' value='1' class=green>Daten abspeichern</button></p>";

        echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js'></script>";
        
    } else {
        #var_dump($_POST);
        // Mandant ID holen (POST bevorzugt vor GET)
        $ei_id = getRequestParam('mand_id');
        # $sammlg = $_POST['sammlg'] :: null;
        $sammlg = '';
        if (isset($_POST['sammlg']) && $_POST['sammlg'] != '') {
            $sammlg = $_POST['sammlg'];
        }
        #var_dump($ei_id);
        #var_dump($sammlg);
        if ($ei_id !== null && $sammlg !== null) {
            
            // Mandant ist gesetzt und gültig, Liste rendern
            renderList($ei_id, $sammlg);
        } else {
            // Kein Mandant gesetzt, evtl. Fehlermeldung oder Default-Verhalten
            echo "Kein gültiger Mandant / gültige Sammlung ausgewählt.";
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
    function renderList($ei_id, $sammlg)  {
        global $basePath, $proj, $module, $meta;
        
        /** multi-Select-Aufruf Archiv- Ordnung */
        
        $columnsByTables = $meta->getColumnsForTables(['hi_sammlung' ]);
        
        $neu = [
            "sa_grup" => "",
            'sa_sammlg' => "",
        ];
        
        $forms = new FormRendererFlex($meta, 0,  $neu, [], false, $module );
        
        if ($sammlg == 'MU_F') {
            $edit = 'FmFEdit.php';
            $listId = 'FMF';
            $proj = $sammlg;
            $txtFlds = ['fm_sammlg', 'fm_baujahr', 'fm_bezeich',  ];
            $listTit = 'Fahrzeuge';
        } else {
            $edit = 'FmGEdit.php';
            $listId = 'FMG';
            $proj = $sammlg;
            $txtFlds = ['mg_sammlg', 'mg_baujahr', 'mg_bezeich',  ];
            $listTit = 'Geräte';
        }
        
        echo $forms->renderMultiSelectFieldFlex(fieldName: 'Sammlung',
            configId: 'SAMMLUNG',
            apiUrl: '../../../login/Services/API/MultiSelectAPI.php'
            );
        
        
        $NeuRec = "";
        if (isset($_SESSION['BS_Prim']['BE'])) {
            # $NeuRec = " &nbsp; &nbsp; &nbsp; <a href='OArchEdit.php?ID=0' > Neuen Datensatz anlegen </a>";
            $NeuRec = "$edit?ei_id=$ei_id";
        }
        $ActionScript = $edit;
        
        
        // Listen-Konfiguration serverseitig definiert
        $lists = [
            [
                'id' => $listId,
                'container' => $listId . '-table',
                'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
                'title' => '- Liste',
                'editScript' => $NeuRec,
                'actionScript' => $ActionScript,
                'actionLabel' => 'Bearbeiten',
                'newLabel' => 'Neue Daten anlegen',
                'filterSelectFields' => [], // ad_sg/ad_subsg/ad_lc* laufen jetzt ausschließlich über 'multiSelectFilter'
                'filterTextFields' => $txtFlds,
                'multiSelectFilter' => [
                    'selector' => 'fw-multi-select[name="Sammlung"]',
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
        
        #var_dump( $lists);        
        #var_dump($globalParams); 
        ?>
        
<script>
const lists = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
console.log('Lists ', lists);
const globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
globalThis.globalParams = globalParams;
</script>

<div id="root-<?php echo $listId ?>">
     <h2>Muskel betriebene <?php echo $listTit ?> Liste</h2>
     <div id= "<?php echo $listId ?>-table"></div>
</div>

<script src="<?= $basePath?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath?>/FWHist/js/list-tables.js"></script>
<script src='<?= $basePath?>/FWHist/login/Services/js/fw_multi_select.js' defer></script>

<?php 

} ## End renderList()
?>