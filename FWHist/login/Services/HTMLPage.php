<?php
/**
 * Klasse HTMLPage für interne Seiten der Feuerwehrhistoriker HP.
 *
 * Lädt CSS und JS aus /FWHist/css, /FWHist/js, /FWHist/login/config.
 * Parameter aus Config-Dateien werden nicht in $_SESSION gespeichert, sondern im Objekt.
 * JS-Skripte werden nicht mehr im Footer ausgegeben.
 *
 * @author Josef Rohowsky
 * @since 01.01.2025
 */

declare(strict_types=1);

namespace FWHist\Services;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message ,3, __DIR__ . "HTML_php_err_log.txt");
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/HTMLPages_fatal_error.log', $message, FILE_APPEND);
    }
});

// Fehlerberichterstattung aktivieren (alle Fehler anzeigen)
error_reporting(E_ALL);
// Fehleranzeige im Browser deaktivieren (optional, z.B. in Produktion)
ini_set('display_errors', '0');
// Fehlerprotokollierung aktivieren
ini_set('log_errors', '1');
// Pfad zur Fehler-Logdatei setzen (Pfad muss beschreibbar sein)
ini_set('error_log', __DIR__. '/HMTLPage_php_error.log');

class HTMLPage {
    /**
     * @var string Seitentitel
     */
    private $title;
    
    /**
     * @var string Zusätzliche Kopfzeilen (HTML)
     */
    private $headExtras = '';
    
    /**
     * @var string Seitentyp ('Form', 'List', '1P')
     */
    private $type = 'Form';
    
    /**
     * @var string Maximale Breite des Body-Containers (CSS-Wert)
     */
    private $width = '90em';
    
    /**
     * @var string Pfad zum Root-Verzeichnis mit abschließendem Slash (z.B. '/pfad/zum/root/')
     */
    private $systemBasePath;
    
    /**
     * @var string Pfad zum WebRoot-Verzeichnis mit abschließendem Slash (z.B. '/pfad/zum/root/')
     */
    private $basePath;
    
    /**
     * @var bool jQuery laden
     */
    private $jq = false;
    
    /**
     * @var bool jQuery UI laden
     */
    private $jqui = false;
    
    /**
     * @var bool jQuery Accordion CSS laden
     */
    private $jq_accordion = false;
    
    /**
     * @var array Zusätzliche CSS-Dateien (vollständige Pfade oder URLs)
     */
    private $extraCSS = [];
    
    /**
     * @var array Zusätzliche JS-Dateien (vollständige Pfade oder URLs)
     */
    private $extraJS = [];
    
    /**
     * @var array Geladene Config-Parameter aus INI-Datei
     */
    private $configParams = [];
    
    /**
     * @var string Debug-Daten-Indikator (z.B. aus POST)
     */
    private $deb_data = '0';
    
    /**
     * @var string Aktuelles Script für Formular-Action
     */
    private $actor = '';
    
    /**
     * @var bool Formular soll gestartet werden (form_start)
     */
    private $form_start = true;
    
    /**
     * @var bool autocomplete für Form ausschalten (A_Off)
     */
    private $A_Off = false;
    
    /**
     * Konstruktor
     *
     * @param string $title Seitentitel
     * @param string $systemBasePath Pfad zum Root-Verzeichnis (optional, mit oder ohne abschließendem '/')
     * @param array $options Optionen:
     *                       - headExtras: string, zusätzliche Kopfzeilen (HTML)
     *                       - type: string, Seitentyp ('Form', 'List', '1P')
     *                       - width: string, max. Breite des Body (z.B. '90em')
     *                       - jq: bool, jQuery laden
     *                       - jqui: bool, jQuery UI laden
     *                       - jq_accordion: bool, Accordion CSS laden
     */
    public function __construct(string $title, string $systemBasePath = '', string $basePath = '', array $options = []) {
        $this->systemBasePath = ($systemBasePath !== '' && substr($systemBasePath, -1) !== '/') ? $systemBasePath . '/' : $systemBasePath;
        $this->title = $title;
        $this->basePath = $basePath ?? '';
        $this->headExtras = $options['headExtras'] ?? '';
        $this->type = $options['type'] ?? 'Form';
        $this->width = $options['width'] ?? '90em';
        $this->jq = (bool)($options['jq'] ?? false);
        $this->jqui = (bool)($options['jqui'] ?? false);
        $this->jq_accordion = (bool)($options['jq_accordion'] ?? false);
        
        if (isset($_POST['phase']) && $_POST['phase'] === '1') {
            $this->deb_data = '1';
        }
        
        
        $this->configParams['Config'] = [
            'mode' => 'Mandanten',
            'eignr' => '',
            'ptyp' => '',
            'store' => '',
            'cPerr' => '',
            'cDeb' => '',
            'OrgNam' => 'OrgNam',
            'p_uid' => '',
            'Inc_Arr' => '',
            'benutzer' => '',
            'zu_eignr' => '',
        ];
        /*
        $message = __DIR__ . ' '.__LINE__ . " basePath $this->basePath \n";
        file_put_contents(__DIR__ .'/HTMLPages_fatal_error.log', $message, FILE_APPEND);
        */
    }
    
    /**
     * Lädt Parameter aus einer INI-Config-Datei in das Objekt (nicht in Session)  @return void
     */
    public function loadConfig(string $configFile): void {
        /*
        $message = __DIR__ . ' '.__LINE__ . " configfile $configFile \n";
        file_put_contents(__DIR__ .'/HTMLPages_fatal_error.log', $message, FILE_APPEND);
        */
        if (file_exists($configFile)) {
            $this->configParams = parse_ini_file($configFile, true);
            if (isset($this->configParams['Config'])) {
                $cfg = $this->configParams['Config'];
                
                if (($cfg['mode'] ?? '') === "Single" && !empty($cfg['eignr'])) {
                    $this->configParams['Config']['mode'] = $cfg['mode'];
                    $this->configParams['Config']['eignr'] = $cfg['eignr'];
                    $_SESSION['BS_Prim']['Env']['mand_perm'] =  Authz::setSingleTenant($cfg['eignr']) ;
                    // VF_Displ_Eig($cfg['eignr']); // Externe Funktion, ggf. separat aufrufen
                }
                $this->configParams['Config']['ptyp'] = $cfg['ptyp'] ?? '';
                $this->configParams['Config']['store'] = $cfg['store'] ?? '';
                
                $c_Date = date("Ymd");
                $this->configParams['Config']['cPerr'] = $this->systemBasePath . "login/logs/debug/" . $c_Date . "_" . ($cfg['cPerr'] ?? '');
                $this->configParams['Config']['cDeb'] = $this->systemBasePath . "login/logs/debug/" . $c_Date . "_" . ($cfg['cDeb'] ?? '');
                $this->configParams['Config']['OrgNam'] = $cfg['inst'] ?? '';
                
                if ($this->configParams['Config']['cPerr'] !== "") {
                    ini_set('log_errors', 1);
                    ini_set('error_log', $this->configParams['Config']['cPerr']);
                }
            }
        }
        /*
        $content = json_encode($this->configParams);
        $message = __DIR__ . ' '.__LINE__ . " configfile $content \n";
        file_put_contents(__DIR__ .'/HTMLPages_fatal_error.log', $message, FILE_APPEND);
        */
    }
    
    /**
     * Fügt zusätzliche CSS-Datei hinzu (vollständiger Pfad oder URL)
     *
     * @param string $href
     * @return void
     */
    public function addCSS(string $href): void {
        $this->extraCSS[] = $href;
    }
    
    /**
     * Fügt zusätzliche JS-Datei hinzu (vollständiger Pfad oder URL)
     *
     * @param string $src
     * @return void
     */
    public function addJS(string $src): void {
        $this->extraJS[] = $src;
    }
    
    /**
     * Setzt den Formular-Action-URL (Standard: aktuelles Script)
     *
     * @param string $actor
     * @return void
     */
    public function setActor(string $actor): void {
        $this->actor = $actor ?: $_SERVER["PHP_SELF"];
    }
    
    /**
     * Setzt, ob das Formular gestartet werden soll
     *
     * @param bool $start
     * @return void
     */
    public function setFormStart(bool $start): void {
        $this->form_start = $start;
    }
    
    /**
     * Setzt, ob autocomplete im Formular deaktiviert wird
     *
     * @param bool $aoff
     * @return void
     */
    public function setAOff(bool $aoff): void {
        $this->A_Off = $aoff;
    }
    
    /**
     * Gibt den HTML-Header aus (inkl. Body-Start, Seitenkopf, Wartungsanzeige, Formularstart)
     *
     * @return void
     */
    public function renderHeader(): void {
        // HTML Grundgerüst (DOCTYPE, html, head) und CSS/JS Links aus Teil 1 sollten hier vorangestellt werden,
        // da JS nicht mehr im Footer ausgegeben wird, müssen sie hier eingebunden werden.
        echo "<!DOCTYPE html>\n";
        echo "<html lang='de' style='overflow-x:scroll;'>\n";
        echo "<head>\n";
        echo "  <meta charset='UTF-8'>\n";
        echo "  <title>" . htmlspecialchars($this->title) . "</title>\n";
        echo "  <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
        echo "  <meta name='description' content='Feuerwehrhistoriker Dokumentationen'>\n";
        echo "  <meta name='robots' content='noindex, nofollow'>\n";
        echo "  <link rel='icon' type='image/x-icon' href='" . $this->systemBasePath . "imgs/favicon.ico'>\n";
        
        // CSS aus /FWHist/css
        $cssFiles = ["tabulator-custom.css", "PageHead.css",  "toggle.css", "tabulator-6.5.2/tabulator.min.css", "FormsFlex.css", ]; #  "forms.css"  "add.css", "Druck.css", , "dropup.css", 
        foreach ($cssFiles as $css) {
            echo "  <link rel='stylesheet' href='" . $this->basePath . "/FWHist/css/" . $css . "' type='text/css'>\n";
        }

        // Zusätzliche CSS-Dateien
        foreach ($this->extraCSS as $css) {
            echo "  <link rel='stylesheet' href='" . htmlspecialchars($css) . "' type='text/css'>\n";
        }
        
        // Zusätzliche JS-Dateien
        foreach ($this->extraJS as $js) {
            echo "  <script src='" . htmlspecialchars($js) . "'></script>\n";
        }
        
        // Zusätzliche Kopfzeilen
        if ($this->headExtras) {
            echo $this->headExtras . "\n";
        }
        
        echo "</head>\n";
        
        // Body Start
        echo "<body class='custom-container' style='max-width:" . htmlspecialchars($this->width) . ";'>\n";
        
        // Start Fieldset
        echo "<fieldset>\n";
        
        // Seitenkopf je nach Typ
        if ($this->type === 'Form') {
            echo "<div class='custom-container' id='header'><fieldset>\n";
            echo "<div class='custom-row'>\n";
            
            // Linker großer Bereich mit Instanzname und Titel
            echo "<div class='custom-col s9 m10 l11'>\n";
            echo "<label><div style='float: left;'>" . htmlspecialchars($this->configParams['Config']['OrgNam'] ?? '') . "</div></label><br>\n";
            echo "<span class='custom-center custom-xlarge'>" . htmlspecialchars($this->title) . "</span>\n";
            echo "</div>\n";
            
            // Rechter kleiner Bereich mit Logo
            echo "<div class='custom-col s3 m2 l1'>\n";
            $sign = $this->configParams['Config']['sign'] ?? 'Lehner_Dauber_Signet_s_72.jpg';
            
            echo "<logo><img src='" . $this->basePath . "/FWHist/imgs/" . htmlspecialchars($sign) . "' width='50px'></logo>\n";
            
            // Debug-Switch (nur wenn p_uid == 1 und deb_data == 0)
            if (($this->configParams['Config']['p_uid'] ?? '') === '1' && $this->deb_data === '0') {
                echo "<div class='dropup custom-right'>\n";
                echo "<b class='dropupstrg' style='color:lightgrey; background-color:white;font-size: 10px;'>Dbg</b>\n";
                echo "<div class='dropup-content' style='bottom: -100px; right: -100px;'>\n";
                echo "<b>Entwanzungs-Optionen</b><br>\n";
                echo "<i>Script-Module</i><br>\n";
                
                if (!empty($this->configParams['Config']['Inc_Arr']) && is_array($this->configParams['Config']['Inc_Arr']) && count($this->configParams['Config']['Inc_Arr']) > 0) {
                    echo '<ul style="margin: 8px 0; padding-left: 20px; list-style: disc;">';
                    foreach ($this->configParams['Config']['Inc_Arr'] as $key) {
                        echo '<li style="margin: 4px 0; font-size: 0.9em;">' . htmlspecialchars($key) . '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p style="color: #999; font-size: 0.9em; margin: 8px 0;">Keine Script Information enthalten</p>';
                }
                
                echo '<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">';
                echo 'SQL Befehl anzeigen <button id="toggleButt-sD" class="button-sm">Einschalten</button><br>';
                echo '</div>';
                
                echo "</div>\n</div>\n";
            }
            
            echo "</div>\n"; // Ende custom-row
            echo "</fieldset></div>\n"; // Ende header fieldset und container
        } elseif ($this->type === '1P') {
            // 1st Page mit großem Bild
            echo "<div class='custom-container' id='header'><fieldset>\n";
            echo "<div class='custom-row'>\n";
            echo "<label><div style='float: left;'>" . htmlspecialchars($this->configParams['Config']['inst'] ?? '') . "</div></label><br>\n";
            echo "<img src='" . $this->basePath . "/FWHist/login/imgs/2013_01_top_72_jr.png' alt='Top Image' width='98%'>\n";
            
            $wart = $this->configParams['Config']['wart'] ?? 'N';
            $warg = $this->configParams['Config']['warg'] ?? '';
            
            if ($wart !== 'N') {
                if ($wart === 'J') {
                    echo "<p class='error' style='font-size: 1.875em;'>Wartungsarbeiten - nur Abfragen möglich - keine Änderungen</p>\n";
                } elseif ($wart === 'U') {
                    echo "<p class='error' style='font-size: 1.875em;'>" . htmlspecialchars($warg) . "</p>\n";
                }
            }
            
            echo "</div>\n";
            echo "</fieldset></div>\n";
        } else {
            // Typ 'List' oder andere - kein spezieller Header hier
            echo "<div class='custom-container' id='header'><fieldset>\n";
            echo "<div class='custom-row'>\n";
            
            // Linker kleiner Bereich mit Logo
            echo "<div class='custom-col s3 m2 l1'>\n";
            $sign = $this->configParams['Config']['sign'] ?? 'Lehner_Dauber_Signet_s_72.jpg';
            
            echo "<logo><img src='" . $this->basePath . "/FWHist/imgs/" . htmlspecialchars($sign) . "' width='50px'></logo>\n";
            echo "</div>\n";
            
            // Rechter großer Beriech mit Titel
            echo "<div class='custom-col s9 m10 l11'>\n";
            echo "<label><div style='float: left;'>" . htmlspecialchars($this->configParams['Config']['OrgNam'] ?? '') . "</div></label><br>\n";
            echo "<span class='custom-center custom-xlarge'>" . htmlspecialchars($this->title) . "</span>\n";
        }
        
        // Wartungsanzeige (für 'Form' Typ schon oben, hier ggf. nochmal für andere Typen)
        if ($this->type !== 'Form' && $this->type !== '1P') {
            $wart = $this->configParams['Config']['wart'] ?? 'N';
            $warg = $this->configParams['Config']['warg'] ?? '';
            if ($wart !== 'N') {
                if ($wart === 'J') {
                    echo "<p class='error' style='font-size: 1.875em;'>Wartungsarbeiten - nur Abfragen möglich - keine Änderungen</p>\n";
                } elseif ($wart === 'U') {
                    echo "<p class='error' style='font-size: 1.875em;'>" . htmlspecialchars($warg) . "</p>\n";
                }
            }
        }
        
        // Formularstart
        $set_auto = $this->A_Off ? " autocomplete='off' " : "";
        if ($this->form_start) {
            $actor = $this->actor ?: $_SERVER["PHP_SELF"];
            echo "<form id='myform' name='myform' method='post' action='" . htmlspecialchars($actor) . "' enctype='multipart/form-data' $set_auto >\n";
        }
    }
    
    /**
     * Gibt den HTML-Footer aus (ohne JS-Skripte)
     *
     * @return void
     */
    public function renderFooter(): void {
        echo "</form>\n";
        echo "</fieldset>\n";
        echo "<br>\n";
        ?>
        <footer class='footer'>
            <div class='copyrights' style='font-size: 0.7rem'>
                Copyright &copy; 2016 - <span id='year'>
                <script>document.getElementById('year').innerHTML = new Date().getFullYear();</script>
                </span>
                Josef Rohowsky - alle Rechte vorbehalten - All Rights Reserved

                <script>
                // Funktion zum Toggeln der Sichtbarkeit
                function toggleElements(buttonId, className) {
                    const button = document.getElementById(buttonId);
                    if (button) {
                        button.addEventListener('click', function() {
                            const elements = document.querySelectorAll('.' + className);
                            elements.forEach(element => {
                                element.style.display = (element.style.display === 'none' || element.style.display === '') ? 'block' : 'none';
                            });
                            button.textContent = button.textContent === 'Einschalten' ? 'Ausschalten' : 'Einschalten';
                        });
                    } else {
                        console.error(`Button mit ID ${buttonId} nicht gefunden.`);
                    }
                }
                // Beispiel-Aufruf
                toggleElements('toggleButt-sD', 'toggle-SqlDisp');
                </script>
            </div>
        </footer>
        <?php
        echo "</body>\n";
        echo "</html>\n";
    }

    /**
     * Gibt die geladenen Config-Parameter zurück
     *
     * @return array
     */
    public function getConfigParams(): array {
        return $this->configParams;
    }

}

/*
Diese Klasse:

Gibt CSS- und JS-Dateien im <head> aus (JS nicht mehr im Footer).
Verwendet interne Objekt-Eigenschaften statt $_SESSION für Config-Parameter.
Zeigt Wartungsanzeigen und Debug-Infos im Header.
Öffnet und schließt das Formular je nach Einstellung.
Gibt im Footer nur das HTML-Footer-Element und Inline-JavaScript für UI-Toggle aus, keine externen JS-Skripte.
Ist flexibel erweiterbar durch addCSS() und addJS().
Falls Sie weitere Anpassungen wünschen, stehe ich gern zur Verfügung.





Wie binde ich externe JS-Dateien korrekt ein?


Kann ich die Wartungsanzeige im Header anpassen?


Wie füge ich dem HTML-Head weitere Meta-Tags hinzu?


Ist die Klasse auch für andere Seitentypen geeignet?

*/