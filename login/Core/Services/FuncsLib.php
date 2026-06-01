<?php
/**
 * Funktionsbibliothek für allgemeine Funktionen (
 * Basis für allgmein benötigte Funktionen.
 * Benötigte PHP- Funktionen: GD, CURL, PDO
 *
 * @author  Josef Rohowsky josef@kexi.at start 24.12.2025, Neuauflage von B.R:Gaickis Lib
 * 
 * Enthält und Unterprogramme für die Auwahl von Namen und Begriffen,
 * 
 *  HTML_header      - Ausgabe des Seiten- Headers, Laden der Seitenparameter aud config_s.ini
 *  HTML_trailer     - Ausgabe Seitenende
 *  initial_debug    - Debug Anzeige mit Auswahl nach Parameter
 *  table_exists     - Feststelle, welche Tabellen mit Prefix existiert
 *  console_log      - Schreiben eines eintrags im Browser- concole.log 
 *  convertInternationalDateToSql Konvertiert Internationale Datenformate nach sql- Datums- Format Y-m-d
 *  writelog     - zum Schreiben von Eintragungen in LOG Files
 *  logandMail   - zum Schreiben von Eintragungen in LOG Files und versenden einer Mail an die Admins
 *  
 */
/*
if ($debug) {
    #echo " FuncsLib.php ist geladen. <br/>";
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('error_log', 'BS_Funcs_php-error.log.txt');
    error_reporting(E_ALL);
    
    $debug_log_file = 'BS_Funcs_debug.log.txt';
    file_put_contents($debug_log_file, "\n==== API CALL ====\n".date('Y-m-d H:i:s')."\nMETHOD: ".@$_SERVER['REQUEST_METHOD']."\n", FILE_APPEND);
    
}
*/
/**
 * Unterprogramm gibt den HTML Header aus
 *
 * input Felder für die PHP Error- Log-Datei und die Debug-Datei nach dem <Form Statement eingefügt (id='cPError und cPdebug )
 *
 *  
 * @param string $title
 *            <title> tag text
 * @param string $head
 *            zusätzliche <head> Zeilen. Auch <style>......</style> möglich
 * @param string $type
 *            Form der Seite
 *             == Form Ausgabe <body><fieldset><header</Fieldset><fieldset> aus
 *             == List gibt nur <body></fieldset aus
 *             == 1P Erste Seite: gibt das Bild aber kein Logo aus
 * @param string $width die Breite des Schirmes (div)
 *
 * @global string $path2ROOT String zur root-Angleichung für relative Adressierung
 * 
 */
function HTML_header($title, $head = '', $type = 'Form', $width = '90em')
// --------------------------------------------------------------------------------
{
    global  $module, $sub_mod, $actor, $Anfix, $A_Off,
    $Store, $debug, $debug_log,  $path2ROOT, $ListHead, $miBeitr;

    if (!isset($debug_log)) {
        $debug_log  = false;
    }
    if (!isset($debug)) {
        $debug = false;
    }
    
    if (empty($ListHead)) {
        $ListHead = '';
    }
    
    $deb_data = '0';
    if (isset($_POST['phase']) && $_POST['phase'] == '1') {
        $deb_data = '1';
    }

    $orgName = 'BS-Tools- Organisationsname';
    $page_parm = array();
    
    $srv = $_SERVER['HTTP_HOST'];
    $caller = $_SERVER['REQUEST_URI'];
    $cal_arr = explode("/",$caller); // wird für css auswahl verwendet
    $cfg = 'config_s_l.ini';
    if (mb_strtolower($srv) === "feuerwehrhistoriker.at" || mb_strtolower($srv) === "www.feuerwehrhistoriker.at") {
        $cfg = "config_s_vfh.ini";
    }
    
    $cPerr = "";
   
    if (is_file($path2ROOT . 'login/config/'.$cfg)) {
        $page_parm = parse_ini_file($path2ROOT . 'login/config/'.$cfg, true, INI_SCANNER_NORMAL);
        # var_dump($page_parm);
        $DataPath = '';
        if ($page_parm['Config']['store'] != '' ) {
            $DataPath = $page_parm['Config']['store'];
        }
        $c_Date = date("Ymd");
        if ($page_parm['Config']['cPerr'] != "") {
            $cPerr = $path2ROOT."login/$DataPath/Logging/".$c_Date."_".$page_parm['Config']['cPerr'];
            ini_set('log_errors', 1);
            ini_set('error_log', $cPerr) ;
        }
      
        $cPath = $page_parm['Config']['cDeb'];
        
        $orgName =  $page_parm['Config']['inst'];
        $miBeitr = $page_parm['Config']['miBeitr'];
    }
    
    if (!isset($form_start)) {
        $form_start = true;
    }

    echo "<!DOCTYPE html>";
    echo "<html lang='de' style='overflow:auto;'>"; # style='overflow-x:scroll;'
    echo "<head>";
    echo "  <meta charset='UTF-8'>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
    echo "  <title>$title</title>";
    echo "  <meta  name='viewport' content='width=device-width, initial-scale=1.00'>";
    echo '<meta name="description" content="Feuerwehrhistoriker Dokumentationen - Archiv, Inventar, Beschreibungen, Kataloge, ...">';
    echo "<meta name='copyright' content='Ing. Josef Rohowsky 2020-2026'>";
    echo '<meta name="robots" content="noindex">';
    echo '<meta name="robots" content="nofollow">';
    if (is_file($path2ROOT . 'login/Basis/imgs/favicon.ico')) {
        echo "<link rel='icon' type='image/x-icon' href='" . $path2ROOT . "login/Basis/imgs/favicon.ico'>";
    }

    echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/w3-5.02.css'  type='text/css'>"; 
    echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/add.css' type='text/css'>"; 
    echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/add_N.css' type='text/css'>"; 
    echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/jquery-ui.min.css' type='text/css'>"; 
    echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/opPopOver.css' type='text/css'>";
    
    echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/FormsFlex.css' type='text/css'>";
    
    echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/accordion.css' type='text/css'>";
    echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/toggle.css' type='text/css'>";
/*
    if (strpos($caller,'Edit') >= 1 || strpos($caller,'M_Anmeld') >= 1 ) {
        echo " <link rel='stylesheet' href='" . $path2ROOT . "VFH/css/FormsFlex.css' type='text/css'>";
    }
    */
    /*
    if ( (isset($TABU) && $TABU) || (isset($TABUcss) && $TABUcss) ) {
        require $path2ROOT . "VFH/TABU_css_lib.php" ;
    }
    
    if (isset($module) && $module != "") {
        if (is_file($module."_css.lib.php")) {
            require $module."_css.lib.php";
        }
    }
 */
    ?>
    <style>
    /** Zusatz Drucker für tabulator.js? oder allgemein?*/

 .page-header {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 20px;
    font-family: Arial, sans-serif;
}

.header-container {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.logo-container {
    flex: 0 0 100px;
}

.logo {
    width: 100%;
    max-width: 100px;
    height: auto;
    border: 3px solid lightblue;
    display: block;
}

.text-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.org-name {
    font-size: 1.2rem;
    font-weight: bold;
    color: #333;
}

.page-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: #555;
    margin-top: 4px;
}

/* Responsive Anpassung */
@media (max-width: 600px) {
    .header-container {
        flex-direction: column;
        align-items: flex-start;
    }
    .logo-container {
        margin-bottom: 10px;
    }
    .org-name {
        font-size: 1.25rem;
    }
    .page-title {
        font-size: 1.1rem;
    }
}

/** Ende Drucker- Zusatz */
    
    </style>
    <?php 
    echo "<script src='" . $path2ROOT . "VFH/js/jquery-3.7.1.min.js'></script>";
    echo "<script src='" . $path2ROOT . "VFH/js//jquery-ui.min.js' ></script>";
    
    echo $head;
    echo "</head>";

    if (! isset($actor) || $actor == "") {
        $actor = $_SERVER["PHP_SELF"];
    }

    echo "<body class='w3-container' style='max-width:$width;' >"; //
    
    /**
     * Globale Variablen 
     */
    $readOnly = ''; // $readOnly = 'readonly' : alle Felder mit Edit_*() können nicht mehr geändert werden
    /*
    require "BS_Logger_CLS.php";
    $logger = new BS_Logger(
        $cPerr, true ); // Pfad Trace
    
     */   
    
    # var_dump($_GET);
    echo '<fieldset>'; ## ganze seite

    if ($type == 'Form') {
        echo "<div class='w3-container' id='header'><fieldset>";  // Seitenkopf Form start
        echo "<div class='w3-row'>";
       #  echo "<label><div style='float: left;'> <label>".$page_parm['Config']['inst']."</div></label><br>";
        echo "<div class='w3-col s9 m10 l11 '>"; // div langer Teil
        echo "<label><div style='float: left;'> <label>".$page_parm['Config']['inst']."</div></label><br>";
        echo "<span class='w3-center w3-xlarge'> $title </span>";
        echo "</div>"; // Ende langer Teil
        echo "<div class='w3-col s3 m2 l1 ' >"; // div kurzer Teil
        
        if (isset($page_parm['Config']['sign']) && $page_parm['Config']['sign'] != "" ) {
            echo "<logo><img  src= '".$path2ROOT."VFH/imgs/".$page_parm['Config']['sign']."' width='90%'></logo>";
        }

         /**
         *  debug switch beginn
         */
        
        #if ( isset($_SESSION['VF_Prim']['p_uid']) && $_SESSION['VF_Prim']['p_uid'] == '1' && $deb_data == '0') {   ##  && $deb_data == '0'
            $Hinweise = "<li>Blau unterstrichene Daten sind Klickbar <ul style='margin:0 1em 0em 1em;padding:0;'>  <li>Fahrzeug - Daten ändern: Auf die Zahl in Spalte <q>fz_id</q> Klicken.</li> ";
            $adm_cont = "
                <ul style='margin: 0 1em 0em 1em; padding: 0;'>
                $Hinweise
               </ul>
                ";
         /*
           ?>
           <!-- opPopOver -->
         
           <div class="dropup w3-right">
                <b class='dropupstrg' style='color:lightgrey; background-color:white;font-size: 10px;'>Dbg</b>
               <div class="dropup-content" style='bottom: -100px; right: -100px;'>
                   <b>Entwanzungs-Optionen</b> <br>
                   <i>Script-Module</i><br>
                   <?php
                   /*
                   if ($_SESSION['VF_Prim']['debug']['cPerr_A'] == 'A') {
                       $EinAus = "I '>PHP Error Datei nicht schreiben";
                   } else {
                       $EinAus = "A '>PHP Error Datei schreiben";
                   }
                   echo "<a class='w3-bar-item w3-button' href='" . $_SERVER['PHP_SELF'] . " ?cPerr_A=$EinAus'</a>";
                   if ($_SESSION['VF_Prim']['debug']['cDeb_A'] == 'A') {
                       $EinAus = "I '>Debug Datei nicht schreiben";
                   } else {
                       $EinAus = "A '>Debug Datei schreiben";
                   }
                   echo "<a class='w3-bar-item w3-button' href='" . $_SERVER['PHP_SELF'] . " ?cDeb_A=$EinAus'</a>";
                   * /
                   if (isset($_SESSION[$module]['Inc_Arr']) && count($_SESSION[$module]['Inc_Arr']) > 0) {
                       echo '<ul style="margin: 8px 0; padding-left: 20px; list-style: disc;">';
                       foreach ($_SESSION[$module]['Inc_Arr'] as $key) {
                           echo '<li style="margin: 4px 0; font-size: 0.9em;">' . htmlspecialchars($key) . '</li>';
                       }
                       echo '</ul>';
                   } else {
                       echo '<p style="color: #999; font-size: 0.9em; margin: 8px 0;">Keine Script Information enthalten</p>';
                   }
                   ?>
                   
                   <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">
                       SQL Befehl anzeigen <button id="toggleButt-sD" class='button-sm'>Einschalten</button><br>
                   </div>
                 
             </div>
          </div>
      
            <!-- opPopOver ende -->
            <?php 
            */
            /**
             *  debug switch ende
             */
       # }
        echo "</div>"; // ende kurzer Teil
        
        if ($page_parm['Config']['wart'] == "N") {
        } else {

            if ($page_parm['Config']['wart'] == "J") {
                echo "<p class='error' style='font-size: 1.875em;'>Wartungsarbeiten - nur Abfragen möglich - keine Änderungen</p>";
            }
            if ($page_parm['Config']['wart'] == "U") {
                echo "<p class='error' style='font-size: 1.875em;'>" . $page_parm['Config']['warg'] . " </p>";
            }
        }

        echo "</div>"; // Ende w3-row
        echo "</div><fieldset>"; ## Ende Seitenkopf Form
        
        $set_auto = "";
        if (isset($A_Off) && $A_Off) {
            $set_auto = " autocomplete='off' ";
        }
        
        echo "<form id='myform' name='myform' method='post' action='$actor' enctype='multipart/form-data' $set_auto >";
        
    } elseif ($type == '1P') {    // 1st Page mit grossem Bild
        echo "<div class='w3-container' id='header'><fieldset>";  // Seitenkopf start 1.Seite
        echo "<div class='w3-row'>";
        echo "<label><div style='float: left;'> <label>".$page_parm['Config']['inst']."</div></label><br>";
        echo "<img src='" . $path2ROOT . "VFH/imgs/2013_01_top_72_jr.png' alt='imgs/".$page_parm['Config']['fpage']."' width='98%'>";
        if ($page_parm['Config']['wart'] == "N") {
        } else {

            if ($page_parm['Config']['wart'] == "J") {
                echo "<p class='error' style='font-size: 1.875em;'>Wartungsarbeiten - nur Abfragen möglich - keine Änderungen</p>";
            }
            if ($page_parm['Config']['wart'] == "U") {
                echo "<p class='error' style='font-size: 1.875em;'>" . $page_parm['Config']['warg'] . " </p>";
            }
        }
        echo "</div>"; // Ende w3-row
        echo "</div><fieldset>"; ## Ende Seitenkopf 1.Seite
        
        $set_auto = "";
        if (isset($A_Off) && $A_Off) {
            $set_auto = " autocomplete='off' ";
        }
        
        echo "<form id='myform' name='myform' method='post' action='$actor' enctype='multipart/form-data' $set_auto >";
        
    } else { // List
        /**
         * Seiten- Kopf neu
         */
        
        if (isset($page_parm['Config']['sign']) && $page_parm['Config']['sign'] != "" ) {
            $logo = isset($page_parm['Config']['sign']) ? $page_parm['Config']['sign']: 'default-logo.png';
            $OrgName = isset($page_parm['Config']['inst']) ? $page_parm['Config']['inst'] : 'Organisation Name';  
        }
        ?>

        <header class="page-header">
            <div class="header-container">
              <div class="logo-container">
                 <img src="<?php echo $path2ROOT . 'VFH/imgs/' . $logo; ?>" alt="Logo" class="logo" />
              </div>
             <div class="text-container">
                 <div class="org-name"><?php echo htmlspecialchars($OrgName); ?></div>
                 <div class="page-title"><?php echo htmlspecialchars($ListHead); ?></div>
            </div>
          </div>
        </header>

        <?php 
    }
  
    
    if ($debug_log && $cDeb != "") {
        file_put_contents($cDeb, "\n==== API CALL ====\n".date('Y-m-d H:i:s')."\nMETHOD: ".@$_SERVER['REQUEST_METHOD']."\n", FILE_APPEND);
    }


}

// Ende von function HTML_Header


/**
 * Unterprogramm gibt passend zu HTML_Header den trailer aus
 * 
 * $module_js.php enthält die notwendigen Definitonen der ja Scripts
 */
function HTML_Trailer()
// --------------------------------------------------------------------------------
{
    global $module, $sub_module, $path2ROOT;
    /*
    if (isset($TABU) && $TABU) {
        require $path2ROOT . "VFH/TABU_js_lib.php" ;
    }
   
    if (isset($module) && $module != "" ) {
        if (is_file($module."_js.lib.php")) {
            require $module."_js.lib.php";
        }
    }
 */
    ?>
  
   <script>
   /*
       function submitForm() {
           console.log('on click ausgelöst');
           document.getElementById('myform').submit();
       }
       */
    </script>

    <br>
    <footer class='footer'>
    <div class='copyrights' style='font-size: 0.7rem'>
    Copyright &copy; 2016 - <span id='year'>
    <script>document.getElementById('year').innerHTML = new Date().getFullYear();</script>
    </span>
    Josef Rohowsky - alle Rechte vorbehalten - All Rights Reserved
     
<script>
    // Funktion zum Toggeln der Sichtbarkeit
    function toggleElements(buttonId, className) {
        const button = document.getElementById(buttonId); // Button mit ID auswählen
        if (button) {
            console.log('Button gefunden:', button);
            button.addEventListener('click', function() {
                const elements = document.querySelectorAll('.' + className);
                elements.forEach(element => {
                    // Sichtbarkeit umschalten
                    element.style.display = (element.style.display === 'none' || element.style.display === '') ? 'block' : 'none';
                });
console.log('button clicked ' );
                // Text des Buttons umschalten
                button.textContent = button.textContent === 'Einschalten' ? 'Ausschalten' : 'Einschalten';
            });
        } else {
            console.error(`Button mit ID ${buttonId} nicht gefunden.`);
        }
    }

    // Aufruf der Funktion für jedes Toggle-Element
    toggleElements('toggleButt-sD', 'toggle-SqlDisp');
    // Füge hier weitere Aufrufe für andere Buttons hinzu
    /*
       toggleElements('toggleButt-dD', 'toggle-dropDown');
       toggleElements('toggleButt-cS', 'toggle-csvDisp');
       
       toggleElements('toggleButt-pE', 'toggle-pError');
       toggleElements('toggleButt-dB', 'toggle-pDebug');
    
       toggleElements('toggleButt-lL', 'toggle-langList');
       toggleElements('toggleButt-vL', 'toggle-varList');
    */
</script>
     
    </div>
    </footer>
    </form>
    
    </div></fieldset></body></html>  
    <?php
} // Ende von function HTML_Trailer


/**
 * Unterprogramm Initial debug
 *
 * Wenn $debug=true - - Inhalte von $_SERVER, $_POST, $_GET, $FILES,$_SESSION werden angezeigt
 * Parameter SRV|POST|GET|FILES|SESS|FLOW|JSD|DYN
 * 
 * 
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 * @global string $module Modul-Name für $_SESSION[$module] - Parameter
 */
function initial_debug($param, $line=0)
// --------------------------------------------------------------------------------
{
    global $debug, $module;
    
    if ($debug && $param != "") {
               
        echo "<pre class=debug>Start von $module: " . $_SERVER['SCRIPT_FILENAME']." in Zeile $line";
        echo '<br>mb_internal_encoding(): <q>' . mb_internal_encoding() . '</q>';
        echo '<br>setlocale(0,0): ' . setlocale(0, 0) . '</pre>';
        
        if (str_contains($param,"SRV")) {
            echo '<pre class=debug>$SERVER: ';
            print_r($_SERVER);
        }
        if (str_contains($param,"POST")) {
            echo '<hr>$POST: ';
            print_r($_POST);
        }
        if (str_contains($param,"GET")) {
            echo '<hr>$GET: ';
            print_r($_GET);
        }
        if (str_contains($param,"FILE")) {
            echo '<hr>$FILES: ';
            print_r($_FILES);
        }
        if (str_contains($param,"SESS")) {
            if (isset($_SESSION)) {
                echo '<hr>$_SESSION: ';
                print_r($_SESSION);
            }
        }
     
        echo '</pre>';
    }
} // Ende von function

/**
 * Feststellen welche Tabelle existieren
 *
 * @param string $prefix
 *
 * Das Ergebnis ist im globalen Array $tables
 *
 * @param string $prefix , wenn == "" dann alle vorhandenen Tabellen anzeigen, sonst nur die des Prefix
 * @response array Array mit den Tabellen- Namen
 */
function table_exists($prefix) {
    global $flow_list, $path2ROOT, $DBD, $module, $sub_mod;
    
    if (!isset($prefix) ) {
        $prefix = "";
    }
    
    $like = "";
    if ($prefix != "" ) {
        $like = " LIKE '$prefix%' " ;
    }
    
    $ret_1 = $DBD->query("SHOW TABLES $like ");
    $tables = []; // Array initialisieren
    
    while ($row_1 = $ret_1->fetch_object()) {
        $vars = get_object_vars($row_1);
        $tablename = reset($vars);
        $tables[] = $tablename;
    }
    # var_dump($tables);
    return $tables;
} // table_exists ende

/**
 * Gibt einen Text auf der Browser Konsole aus.
 *
 * Die Methode verwendet die Javascript Funktion <code>console.log</code>, um den Text auf der Browser Konsole auszugeben.
 * print_r($xyz,True) soll ie Daten dann weitergeben
 * var_export($xyz,True) ebenso
 *
 *
 * @param string $output
 *            der auszugebende Text
 * @param boolean $with_script_tags
 *            wenn <code>true</code> dann werden &lt;script> tags um den Javascript code erzeugt
 */
function console_log(string $output, $with_script_tags = true)
{
    global $module;
    
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
} # end of console_log


/**
 * Wandelt ein Datum aus verschiedenen internationalen Formaten in das SQL-Format YYYY-mm-dd um.
 * Unterstützt u.a. Formate wie:
 * - dd-mm-yyyy, dd.mm.yyyy, dd/mm/yyyy
 * - yyyy-mm-dd, yyyy/mm/dd, yyyy.mm.dd
 * - mm/dd/yyyy, mm-dd-yyyy, mm.dd.yyyy (optional, US-Format)
 *
 * Gibt bei ungültigem Datum null zurück.
 *
 * @param string $inputDate Datum als String in einem der unterstützten Formate
 * @param bool $assumeUSFormat Wenn true, wird mm/dd/yyyy als US-Format interpretiert (optional, default false)
 * @return string|null Datum im Format 'YYYY-mm-dd' oder null bei Fehler
 */
function convertInternationalDateToSql(string $inputDate, bool $assumeUSFormat = false): ?string
{
    
    $inputDate = trim($inputDate);
    if ($inputDate === '') {
        return null;
    }
    
    // Ersetze verschiedene Trennzeichen durch Bindestrich für einheitliche Verarbeitung
    $normalized = str_replace(['.', '/', '\\', ' '], '-', $inputDate);
    
    // Mögliche Formate in Reihenfolge der Priorität
    $formats = [];
    
    // Wenn US-Format angenommen wird, prüfen wir mm-dd-yyyy zuerst
    if ($assumeUSFormat) {
        $formats[] = 'm-d-Y';
        $formats[] = 'm-d-y';
    }
    
    // Europäische und ISO Formate
    $formats[] = 'd-m-Y';
    $formats[] = 'd-m-y';
    $formats[] = 'Y-m-d';
    $formats[] = 'y-m-d';
    
    // Versuche alle Formate nacheinander
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $normalized);
        if ($dt !== false) {
            // Validierung: Formatierte Ausgabe muss mit Eingabe übereinstimmen (nach Normalisierung)
            if ($dt->format($format) === $normalized) {
                return $dt->format('Y-m-d');
            }
        }
    }
    
    // Fallback: Versuche mit strtotime (unsicher, aber oft hilfreich)
    $ts = strtotime($inputDate);
    if ($ts !== false) {
        return date('Y-m-d', $ts);
    }
    
    // Ungültiges Datum
    return null;
}

/**
 * Unterprogramm zum Schreiben von Eintragungen in LOG Files.
 *
 *
 *
 * @param string $log_DSN
 *            Dateiname (wird mit Jahr und .log ergänzt)
 * @param string $logtext
 *            Log- Text welcher Einzutragen ist (wird mit Systemdaten ergänzt)
 * @return string Ergänzter DSN des MonatsLogFiles (mit YYYY und .log)
 *
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 */
function writelog($log_DSN, $logtext)
// --------------------------------------------------------------------------------
{
    global $debug, $module, $s_mod, $path2ROOT;
    
    $log_pref = $path2ROOT . "login/AOrd_Verz/Logging/";
    
    # Dem FileNamen YYYY- voranstellen
    $exploded = explode('/', $log_DSN); // zerlegen in ein Array (der FileName ist das letzte Array Element)
    $exploded[] = date('Y-') . array_pop($exploded) . '.log'; // Datum und Typ zum FileNamen hinzufügen
    $dsn = implode('/', $exploded); // DSN wieder zusammenfügen
    if ($debug) {
        echo "<pre class=debug>writelog log_DSN:$log_DSN dsn: $dsn</pre>";
    }
    
    $eintragen = "\n\n" . date('Y-m-d H:i:s ') . $_SERVER['REQUEST_URI'] . "\n$logtext" . "\nSystemdaten ***************************************************************";
    if (isset($_SERVER['REMOTE_USER'])) {
        $eintragen .= "\n         REMOTE_USER: " . $_SERVER['REMOTE_USER'];
    }
    $eintragen .= "\n                Host: " . gethostbyaddr($_SERVER["REMOTE_ADDR"]) .
    # . "\n IP: " . $_SERVER["REMOTE_ADDR"]
    # . "\n Server name: " . $_SERVER['SERVER_NAME']
    "\n                Page: " . $_SERVER["REQUEST_URI"];
    if (isset($_SERVER["HTTP_REFERER"])) {
        $eintragen .= "\n             Referer: " . $_SERVER["HTTP_REFERER"];
    }
    $eintragen .= "\n             Browser: " . $_SERVER["HTTP_USER_AGENT"] . "\nHTTP         _ACCEPT: " . $_SERVER["HTTP_ACCEPT"] . "\nHTTP_ACCEPT_LANGUAGE: " . $_SERVER["HTTP_ACCEPT_LANGUAGE"] . "\nHTTP     _CONNECTION: " . $_SERVER["HTTP_CONNECTION"] . "\n         REMOTE_PORT: " . $_SERVER["REMOTE_PORT"] . "\n**** LOG ENDE *************************************************************";
    
    $datei = fopen($dsn, 'at');
    fputs($datei, mb_convert_encoding($eintragen, "ISO-8859-1"));
    fclose($datei);
    
    if ($debug) {
        echo "<pre class=debug>Log Record in $dsn<hr>" . nl2br("$eintragen") . "</pre>";
    }
    
    return $dsn;
} // Ende von function writelog

/**
 * Unterprogramm zum Schreiben von Eintragungen in LOG Files und versenden einer Mail an die Admins
 *
 * @param string $log_DSN
 *            Name der Log Datei (wird mit Jahr und .log ergänzt)
 * @param string $logtext
 *            Text welcher in den LOG Einzutragen ist (wird mit Datum , Uhrzeit und Systemdaten ergänzt)
 * @param string $MailTo
 *            Mail Empänger(Liste)
 * @param string $MailSubject
 *            Subject Text der EMail
 * @param string $Mailtext
 *            Inhalt der Email in HTML format (wird mit Zusatzinformation ergän
 * @return boolean Rückgabewert: immer True
 *
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 * @global $module Modul-Name für $_SESSION[$module] - Parameter
 */
function logandMail($log_DSN, $logtext, $MailTo, $MailSubject, $Mailtext)
// --------------------------------------------------------------------------------
{
    global $debug, $module;
    
    $logtext = htmlspecialchars_decode($logtext, ENT_QUOTES);
    $logDateiname = writelog($log_DSN, "$MailSubject\n$logtext");
    $URL = absoluteURL($logDateiname);
    $dsn = realpath($logDateiname);
    
    $Mailbody = $Mailtext . "<br><pre>$logtext</pre>";
    if ($_SERVER['SERVER_NAME'] != 'localhost') {
        $Mailbody .= "<p>Die Information wurde im Log Jahresfile <a href='$URL'>$URL</a> gespeichert.</p>";
    } else {
        $Mailbody .= "<p>Die Information wurde im Log Jahresfile <q>$dsn</q> gespeichert.</p>";
    }
    
    sendEmail($MailTo, "VFH $module: $MailSubject", $Mailbody);
    
    return true;
} // Ende von function logandMail

