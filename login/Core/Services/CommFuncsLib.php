<?php 
/** 
 * Neu aufbau von Funktionen J. Rohowsky ab 2026
 *     @author  Josef Rohowsky josef@kexi.at start 24.12.2025
 * 
 * Enthält und Unterprogramme für die Auwahl von Namen und Begriffen,
 * 
 *  
 *  AutoCompForm_Benutzer    Autocomplete Form fur Benutzer- Auswahl
 *  
 *  AutoCompForm_Eigent      Autocomplete Form für Mandant/Eigentümer
 *  
 *  AutoCompForm_Mitgl      Autocomplete Form für Mitglieder
 *  
 *  AutoCompForm_Staat       Autocomplete Form fur Staaten- Auswahl
 *  
 *  AutoCompForm_Bdld       Autocomplete Form fur Bundesland- Auswahl
 *  
 *  AutocompForm_Ort        Autocomplete form für Orts-Namen Suche (Bezirk, Ortsnamen, Feuerwehr, Ortsteile
 *  
 *  AutocompForm_ArchivOrd  Autocomplete FOrmfür die Suche nach der Arcivordnung
 *  
 *  AutocompForm_Sammlung   Autocompelete Form für die Suche nach der Sammlung
 *  
 *  erlaubnisMand            Welche Berechtigung hat dieser UID für diesen Mandanten
 *  
 *  M_Foto                   Multiples hochladen von Fotos
 *  Multi_Foto               Multiples hochladen von Fotos (? aktueller)
 *  
 *  userHasRole              Ist dieser UID für dieses Script berechtigt
 *  
 *  UploadForm_M             Formular fürs Upload
 *  UploadPfad_M             Setzen des Ablage- Pfades
 *  UpLoadSave_M             Abspeichern der Hochgeladenen Dateien (Foto, Doku)
 */

/**
 * Autocomplete Abfrage für Archivordung
 * Teil der Form
 */
function AutoCompForm_ArchOrd () {
    
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="aord">Archiv- Ordnung auswählen (um zu Ändern):</label></div>
    <div class='field-control'><input type="text" id="aord" name="aord" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="aord_id" name="aord_id" />
    </div>
    <?php 
} // ende AuzoCompForm_ArchOrd

/**
 * Autocomplete Abfrage für Benutzer- Kurzzeichen
 * Teil der Form
 */
function AutoCompForm_Benutzer () {
    
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="benutzer">Benutzer auswählen (um zu Ändern):</label></div>
    <div class='field-control'><input type="text" id="benutzer" name="benutzer" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="ben_id" name="ben_id" />
    </div>
    <?php 
} // ende AuzoCompForm_Benutzer

/**
 * Autocomplete Abfrage für Bundeslandes- Kurzzeichen
 * Teil der Form
 */
function AutoCompForm_Bdld ($staatId = "") {
    /**
     * $staatId kann "" sein, kannn aber auch in id= 'staat_id' vorhanden sein, wenn vorhanden als where verwenden
     */
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="bdld">Bundesland auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="bdld" name="bdld" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="bdld_id" name="bdld_id" />
    </div>
    <?php 
} // ende AutoCompForm_Bdld

/**
 * Autocomplete Abfrage für Mandanten
 * Teil der Form
 */
function AutoCompForm_Eigent($t, $cl = false,$j="1")
{
    global $debug, $module ;
   
    # console_log('autoeigent');
    ?>
    <div class='w3-container' style='background-color: PeachPuff; padding: 10px;'>
    <?php
        if (isset($t) && $t == 'E') {
            echo "<b>Eigentümer Namen suchen:</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ";
        } else {
            echo "<b>Urheber Namen suchen:</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ";
        }
    ?>
        <input type="text" class="autocomplete" data-proc="Eigentuemer" data-target="suggestEigener_<?php echo $j; ?>" data-feed="eigentuemer_<?php echo $j; ?>" size='50'/>
    </div>
    <div id="suggestEigener_<?php echo $j; ?> class="suggestions">
       <input type="hidden" name="eigentuemer_<?php echo $j; ?>" id="eigentuemer_<?php echo $j; ?>" />
    </div>
    <?php
    if ($cl) {
        echo "<button type='submit' name='phase' value='1' class=green>Weiter</button></p>";
    }
} // Ende AutoCompForm_Eigent

/**
 * Autocomplete Abfrage für Firmen- Kurzzeichen
 * Teil der Form
 */
function AutoCompForm_Firma () {
    /**
     * $staatId kann "" sein, kannn aber auch in id= 'staat_id' vorhanden sein, wenn vorhanden als where verwenden
     */
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="firm">Bundesland auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="firm" name="firm" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="firm_id" name="firm_id" />
    </div>
    <?php 
} // ende AutoCompForm_Firma

/**
 * Autocomplete Abfrage für Mandanten- Kurzzeichen
 * Teil der Form
 */
function AutoCompForm_Mandant () {
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="staat">Mandanten auswählen (um zu Ändern):</label></div>
    <div class='field-control'><input type="text" id="mandant" name="mandant" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="mandant_id" name="mandant_id" />
    </div>
    <?php 
} // ende AutoCompForm_Mandant

/**
 * Autocomplete Abfrage für Mitglieder- Kurzzeichen
 * Teil der Form
 * 
 * @param boolean 1 : Mitglieder mit E-Mail
 */
function AutoCompForm_Mitgl ($em) {    
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="mitgl">Mitglied auswählen :</label></div>
    <div class='field-control'><input type="text" id="mitgl" name="mitgl" /></div>
    <input type='input' id='email' value='<?php echo $em ?>' >
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="mitgl_id" name="mitgl_id" />
    <!-- Optional: verstecktes Feld für E-Mail -->
    <input type="hidden" id="email_id" name="email_id" />
    </div>
    <?php 
} // end AutoCopmForm_Mitgl
    
/**
 * Autocomplete Abfrage für Orts- Auswahl für PSA
 * Teil der Form
 */
function AutoCompForm_Ort () {
    /**
     * $staatId kann "" sein, kannn aber auch in id= 'staat_id' vorhanden sein, wenn vorhanden als where verwenden
     */
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="ort">Ort auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="ort" name="ort" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="ort_id" name="ort_id" />
    </div>
    <?php 
} // ende AutoCompForm_ort

/**
 * Autocomplete Abfrage für Sammlung- Kurzzeichen
 * Teil der Form
 */
function AutoCompForm_Sammlg () {
   
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="sammlg">Sammlung auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="sammlg" name="sammlg" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="sammlg_id" name="sammlg_id" />
    </div>
    <?php 
} // ende AutoCompForm_Sammlg

/**
* Autocomplete Abfrage für Staats- Kurzzeichen
* Teil der Form
*/
function AutoCompForm_Staat () {
    
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="staat">Staat auswählen (um zu Ändern):</label></div>
    <div class='field-control'><input type="text" id="staat" name="staat" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="staat_id" name="staat_id" />
    </div>
    <?php 
} // ende AutoCompForm_Staat

/**
 * Autocomplete Abfrage für Urheber- Kurzzeichen
 * Teil der Form
 */
function AutoCompForm_Urheber () {
    /**
     * $staatId kann "" sein, kannn aber auch in id= 'staat_id' vorhanden sein, wenn vorhanden als where verwenden
     */
    ?>
    <div class='field-row' style='background-color: PeachPuff; padding: 10px;'>
    <div class='field-label'><label for="staat">Urheber auswählen (um zu Ändern):</label></div>
    <div class='field-control'><input type="text" id="urheber" name="urheber" /></div>
    <!-- Optional: verstecktes Feld für ID -->
    <input type="hidden" id="urheber_id" name="urheber_id" />
    </div>
    <?php 
} // ende AutoCompForm_Urheber


/**
 * Feststellen ob der Benutzer genügend Berechtigung hat
 * Berechtigung 0 -- darf nix -> abbruch
 *              1 -- darf fast alles lesen
 *              2 -- darf alles Lesen
 *              3 -- darf alles updaten
 *
 *  liest die Daten aus $_SESSION['BS_Prim'] aus.
 *
 *
 * @return number
 */
function erlaubnisMand() {
    global $module, $sub_mod ;
    $erlaub = 0;
    
    if (isset($_SESSION['BE']) ) {
        if (str_contains($_SESSION['BS_Prim']['BE']['roles'],'ALLE')) {
            $erlaub = 4 ;
        } else {
            if ($module != '' && str_contains($_SESSION['BS_Prim']['BE']['roles'], $module)) {
                $erlaub = 2;
                if (str_contains(MANDANTEN_MODS, $module)) {
                    foreach ($_SESSION['BS_Prim']['BE']['mand_perm'] as $mand_nr => $mand_erl ) {
                        if ($mand_nr == $_SESSION['mand_nr']) {
                            $erlaub = 3;
                        } else {
                            $erlaub = 2;
                        }
                    }
                }
            }
        }
    }
    
    return $erlaub;
} // Ende erlaubnisMand

/**
 * Formular- Teil zum hochladen von mehrfach-Dateien (fotos, Dokumente, ..) Modifizierte Vwersion
 *
 * @param array $Picts
 *            Daten zum Hochladen
 * @param string $sub_functs
 *            Steuerung für Sub-Funktionen
 * @return
 *
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 * @global array $db Datenbank Handle
 * @global array $neu Eingelesene Daten Felder
 * @global array $Tabellen_Spalten_COMMENT Global Array (Schlüssel: Spaltenname) mit Texten zu den Spalten
 */
function M_Foto()
// --------------------------------------------------------------------------------
{
    global $debug, $db, $neu, $module, $pict_path, $Tabellen_Spalten_COMMENT ,$hide_area, $path2ROOT, $button_clicked_flag,$urheber,$verzeichn,$suff;
    
    if (!isset($urheber)) {
        $urheber = $_SESSION[$module]['Eigner']['eig_eigner'];
    }
    $verzeichnis = "";
    $suffix      = "";
    $foDsn       = "";
    $fo_org = 'H';
    
    # $_SESSION[$module]['Pct_Arr'][] = array("k1" => 'fz_b_1_komm', 'b1' => 'fz_bild_1', 'rb1' => '', 'up_err1' => '');
    
    if ($debug) {
        echo "<pre class=debug>VF_M_Foto L Beg: \$Picts ";
        var_dump($_SESSION[$module]['Pct_Arr']);
        echo "<pre>";
    }
    
    $pic_cnt = count($_SESSION[$module]['Pct_Arr']);
    
    echo "<div class='w3-container' max-width='100%' margin='5px '>";
    
    # var_dump($_SESSION[$module]['Pct_Arr']);
    
    foreach ($_SESSION[$module]['Pct_Arr'] as $key => $p_a) { # => $value) {
        # var_dump($p_a);
        # $p_a = explode("|", $value);
        
        
        #var_dump($p_a);echo "L 01025 hide_area $hide_area <br>";
        
        #echo $neu[$p_a[2]]. " ".$p_a[2] . " " . $neu[$p_a[3]]." ". $p_a[3] ."<br>";
        
        # if ($hide_area == 0 || ($hide_area == 1 && ($neu[$p_a['ko']] != '' || $neu[$p_a['bi']] != ''))) {
        if ($neu[$p_a['ko']] != '' || $neu[$p_a['bi']] != '') {
            
            # echo "Bild- Box $key wird angezeigt <br>";
            
            echo "<div class='w3-half'><fieldset>";
            echo "<div style='float:left;'>";
            
            if ($p_a['ko'] != "") {
                if (isset($Tabellen_Spalten_COMMENT[$p_a['ko']])) {
                    echo $Tabellen_Spalten_COMMENT[$p_a['ko']];
                } else {
                    echo $p_a['ko'];
                }
                echo "<textarea class='w3-input' rows='7' cols='25' name='".$p_a['ko']."' >" . $neu[$p_a['ko']] . "</textarea> ";
            }
            if ($neu[$p_a['bi']] != "") {
                $fo = $neu[$p_a['bi']];
                
                $fo_arr = explode("-", $neu[$p_a['bi']]);
                $cnt_fo = count($fo_arr);
                
                if ($cnt_fo >= 3) {   // URH-Verz- Struktur de dsn
                    $urh = $fo_arr[0]."/";
                    $verz = $fo_arr[1]."/";
                    if ($cnt_fo > 3) {
                        if (isset($fo_arr[3])) {
                            $s_verz = $fo_arr[3]."/";
                        }
                    }
                    $p = $path2ROOT ."login/AOrd_Verz/$urh/09/06/".$verz.$neu[$p_a['bi']] ;
                    
                    if (!is_file($p)) {
                        $p = $pict_path . $neu[$p_a['bi']];
                    }
                } else {
                    $p = $pict_path . $neu[$p_a['bi']];
                }
                
                echo "</div><div style='float:right;'>";
                
                $f_arr = pathinfo($neu[$p_a['bi']]);
                if ($f_arr['extension'] == "pdf") {
                    echo "<a href='$p' target='Bild $key' > Dokument</a></div>";
                } else {
                    echo "<a href='$p' target='Bild $key' > <img src='$p' alter='$p' width='200px'></a></div>";
                    echo $neu[$p_a['bi']];
                }
                
            }
            
            # $show_upload = ($hide_area == 0) || ($hide_area == 1 && $button_clicked_flag);
            
            $show_upload = true;
            'display:' . ($show_upload ? 'block' : 'none') . ';">';
            
            echo "<fieldset style='margin:10px; padding:10px; border:1px solid #ccc;'>";
            echo "<legend>Foto $key</legend>";
            
            // Datei-Input
            echo "<input type='file' id='f_Doc_$key' name='f_Doc_Name_$key' /><br/><br/>";
            
            # echo "<input type='file' id='$FeldName'  name='$FeldName' onchange='uploadImage(\"$FeldName\", $key)' accept='image/*' /><br/><br/>";
            // Verste process
            echo "<input type='hidden' id='f_Doc_$key' name='f_Doc_Name_$key' value='".$neu[$p_a['bi']]."'/>";
            echo "</fieldset>";
            
            echo '</div>';
            
        }
        
        echo "</fieldset></div>";
    }
    #echo "</div>";
    # echo "</div>";
    
    if ($debug) {
        echo "<pre class=debug>VF_Mult_ L End: <pre>";
    }
    return;
} // end M_Foto

/**
 * Formular- Teil zum hochladen von mehrfach-Dateien (fotos, Dokumente, ..)
 *
 * @param array $Picts
 *            Daten zum Hochladen
 * @param string $sub_functs
 *            Steuerung für Sub-Funktionen
 * @return
 *
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 * @global array $db Datenbank Handle
 * @global array $neu Eingelesene Daten Felder
 * @global array $Tabellen_Spalten_COMMENT Global Array (Schlüssel: Spaltenname) mit Texten zu den Spalten
 */
function Multi_Foto(array $Picts, $sub_funct = '')
// --------------------------------------------------------------------------------
{
    global $debug, $db, $neu, $module, $pict_path, $Tabellen_Spalten_COMMENT ;
    
    /*
     * noch verwendet in 
     * MitglEhrung
     * DokuEdit
     * 
     */
    if ($debug) {
        echo "<pre class=debug>VF_Mult_ L Beg: \$Picts ";
        var_dump($Picts);
        echo "<pre>";
    }
    # var_dump($Picts);
    $pic_cnt = count($Picts);
    
    # echo "<tr><td colspan='2'>";
    echo "<div class='w3-container' max-width='100%' margin='5px '>";
    
    foreach ($Picts as $key => $value) {
        error_log($value);
        $p_a = explode("|", $value);
        
        echo "<div class='w3-half'><fieldset>";
        echo "<div style='float:left;'>";
        if ($p_a[0] != "") {
            if (isset($Tabellen_Spalten_COMMENT[$p_a[0]])) {
                echo $Tabellen_Spalten_COMMENT[$p_a[0]];
            } else {
                echo "<b>$p_a[0]</b> ";
            }
            echo "  <input class='w3-input' type='text' name='$p_a[0]' value='" . $neu[$p_a[0]] . "' size='50'> <br/>";
        }
        if ($p_a[1] != "") {
            if (isset($Tabellen_Spalten_COMMENT[$p_a[1]])) {
                echo $Tabellen_Spalten_COMMENT[$p_a[1]];
            } else {
                echo "$p_a[1]";
            }
            echo "  <input class='w3-input' type='text' name='$p_a[1]' value='" . $neu[$p_a[1]] . "'> <br/>";
        }
        if ($p_a[2] != "") {
            if (isset($Tabellen_Spalten_COMMENT[$p_a[2]])) {
                echo $Tabellen_Spalten_COMMENT[$p_a[2]];
            } else {
                echo $p_a[2];
            }
            echo "  <textarea class='w3-input' rows='5' cols='50' name='$p_a[2]' >" . $neu[$p_a[2]] . "</textarea> ";
        }
        
        echo '<input type="hidden" name="MAX_FILE_SIZE" value="4000000" >';
        $FeldName = $p_a[3];
        
        echo "<input type='hidden' id='f_Dat_$key' name='$FeldName' value='$neu[$FeldName]' >";
        
        if (isset($Tabellen_Spalten_COMMENT[$FeldName])) {
            if ($_SESSION['VF_Prim']['p_uid'] != 999999999) {
                echo "  <span class='info'>$Tabellen_Spalten_COMMENT[$FeldName] <b>$FeldName</b> Bild hochladen </span>";
                echo "<input type='file'   id='f_Doc_$key' name='f_Name_$key' accept=VF_zuldateitypen />";
            }
        } else {
            echo "  <span class='info'><b>$FeldName</b> Bild hochladen </span>";
            echo "<input type='file'   id='f_Dat_$key' name='f_Name_$key' accept=VF_zuldateitypen />";
        }
        error_log($pict_path);
        if ($neu[$p_a[3]] != "") {
            $p = $pict_path . $neu[$p_a[3]];
            error_log($p);
            echo "</div><div style='float:right;'>";
            if (stripos($neu[$p_a[3]], ".pdf")) {
                echo "<a href='$p' target='Bild $key' > Dokument</a></div>";
            } else {
                echo "<a href='$p' target='Bild $key' > <img src='$p' alter='$p' width='200px'>  Groß  </a></div>";
            }
            
        }
        
        echo "</fieldset></div>";
    }
    echo "</div>";
    echo "</div>";
    # echo "</td></tr>";
    
    if ($debug) {
        echo "<pre class=debug>VF_Mult_ L End: <pre>";
    }
    return;
} // end Multi_Foto

/**
 * Abfrage für die Berechtigung eines eingeloggten Benutzers
 * 
 * @param string Rollen- Bezeichnung
 */
function userBerechtigtOK ($berechtigung) {
    global $path2ROOT;
    
    #$message = __FILE__ . " " . __LINE__ . " Berechtigung $berechtigung";
   #file_put_contents('userber_error.log.txt', $message, FILE_APPEND);
   
    if ($berechtigung == "Alle" ) {
        return true;
    }
    #$message = __FILE__ . " " . __LINE__ . " allesOK";
    if (isset($_SESSION['BS_Prim']['BE']['be_id']) && $_SESSION['BS_Prim']['BE']['be_id'] >= 1 && $_SESSION['BS_Prim']['BE']['roles'] != "") {   
        if (!userHasRole($berechtigung) ) {
            echo "Für diesen Programmteil besteht keine Berechtgung.<br>";
            echo "<a href='" . $path2ROOT . "login/Core/Controllers/MainMenu.php > Zurück zum Anfang </a>";
            #$message = __FILE__ . " " . __LINE__ . " Für diesen Programmteil besteht keine Berechtgung.";
            
        }
        #file_put_contents('userber_error.log.txt', $message, FILE_APPEND);
        return true;
    } else {
        echo "Für diesen Programmteil besteht keine Berechtgung.<br>";
        echo "<a href='/VFH/index.php' > Zurück zum Anfang </a>";
    }
    return false;
} // ende userBerechtigtOK

/**
 * Prüft, ob der Benutzer eine bestimmte Rolle hat.
 * Berücksichtigt dabei die Super-User-Rolle 'ADM-ALLE'.
 *
 * @param string $requiredRole Die Rolle, die geprüft werden soll.
 * @return bool True, wenn der Benutzer die Rolle oder 'ADM-ALLE' hat, sonst False.
 */
function userHasRole(string $requiredRole): bool {
    // Rollen-String aus der Session holen
    $rolesString = isset($_SESSION['BS_Prim']['BE']['roles']) ? $_SESSION['BS_Prim']['BE']['roles'] : '';
    
    // String in Array umwandeln, Leerzeichen entfernen
    $rolesArray = array_map('trim', explode(',', $rolesString));
    
    // Super-User-Rolle prüfen
    if (in_array('ADM-ALLE', $rolesArray)) {
        return true;
    }
    
    // Gesuchte Rolle prüfen
    return in_array($requiredRole, $rolesArray);
} // ende userHasRole

/**
 * Formular- Teil zum hochladen von mehrfach-Dateien (fotos, Dokumente, ..) Modifizierte Vwers
 *
 *
 * in allen Programmteilen mit Foto Anzeige und hochladen verwendet, die Mandantenfähig sind.
 *
 * @return
 *
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 * @global array $db Datenbank Handle
 * @global array $neu Eingelesene Daten Felder
 * @global array $Tabellen_Spalten_COMMENT Global Array (Schlüssel: Spaltenname) mit Texten zu den Spalten
 * @global string $flow_lost True = Ausgabe der Aufruf- Trace
 * @global boolean $hide_area True - Bereich nur bei Neueingabe oder klicken auf Button Anzeigen (Ausser Foto, da nur die leeren nicht anzeigen)
 * @global string  §path2ROOT Pfad zum Root
 */
function UploadForm_M()
{
    global $debug, $db, $neu, $module, $sub_mod, $Tabellen_Spalten_COMMENT , $hide_area, $path2ROOT ,$dataSetAct ;
 
    $readOnly = "";
    /** alle <input und <textara Felder werden als readonly gesetzt */
    
    if (!isset($_SESSION['BS_Prim']['BE']['be_id']) || $_SESSION['BS_Prim']['BE']['be_id']  == "") {
        $readOnly = 'readonly';
    }
    /**
     * Parameter für die Fotos:
     *
     * $_SESSION[$module]['Pct_Arr'][] = array("k1" => 'fz_b_1_komm', 'b1' => 'fz_bild_1', 'rb1' => '', 'up_err1' => '', 'f1' => '','f2'=>'');
     * wobei k1 = blank : kein Bild- Text- Feld - kein Bildtext , keine gemeinsame Box, rb1 und up_err werden vom Uploader gesetzt,
     *                           f1 und f2 sind 2 Felder, die zusätzlich im Block eingegeben, angezeigt werden können
     */
    
    /* Schalten der Foto- Update blöcke */
    /*
     if (!isset($hide_area)) {
     $hide_area = 0;
     }
     $hide_area_group1 = $hide_area_group2 = $hide_area;
     */
    if ($debug) {
        echo "<pre class=debug>UploadForm_M ". __LINE__ ." Beg: \$Picts ";
        var_dump($_SESSION[$module]['Pct_Arr']);
        echo "<pre>";
    }
    
    $subMod = $module."|".$sub_mod;
    $pic_cnt = count($_SESSION[$module]['Pct_Arr']);
    
    /**
     * Floating Block mit Bild, Bildbeschreibung , Bildname und Upload-Block
     */
    echo "<div class='w3-container'>";                           // container für Foto und Beschreibung
    #console_log('L 056 vor class w3-row ');
    echo "<div class = 'w3-row w3-border'>";                     // Responsive Block start
    echo "<fieldset>";  #1
    
    ?>

  <div style="margin-bottom:20px; border:1px solid #ccc; padding:10px;">

    <?php
    // ?? echo "<input type='text' id='berPhase' value='init' >";
    echo "<input type='hidden' id='bildAnz' name='pic_cnt' value='$pic_cnt' >";
    for ($i = 0; $i < $pic_cnt; $i++) {
        $p_a = $_SESSION[$module]['Pct_Arr'][$i];
        // Fehlerim Bu_Edit, Anzeige der leeren Bilder wird nicht unterdrückt. AN, TE, PR sind OK
        # var_dump($neu);
        # echo $_SESSION[$module]['all_upd'] ." NULL or 1<br>";
        # var_dump($p_a);
        
        if (isset($_SESSION['BS_Prim']['BE']['be_id']) ) {
            if ($_SESSION['BS_Prim']['BE']['be_id']  != "") {
                $no_ko = 1;
                # echo "L 2324 ko  ". $p_a['ko']."  ".$neu[$p_a['ko']]." <br>";
                if ($p_a['ko'] != '' ) {
                    if ( $neu[$p_a['ko']] == "")  {
                        $no_ko = '0';
                    }
                }
                # echo "L 2330 bi  ". $p_a['bi']."  ".$neu[$p_a['bi']]." <br>";
                if ($neu[$p_a['bi']] == "" && $no_ko == '1') {
                    
                    continue;
                }
                
            }
        }
        
        $j = $i + 1; /** Für die Bild- Nr- Anzeige */
        
        if ($p_a['udir'] == '') {
            $pict_path = UploadPfad_M('', '', '', '');
        } else {
            $pict_path = $p_a['udir'] ;
        }
               
        /**
         * Responsive Container innerhalb des loops
         */
        echo "<div class = 'block-container w3-container w3-half '>";                 // start half contailer
        echo "<fieldset>";
        # echo "Bild $j <br>";
        echo "<div class='bild-data_$j' >";

        if ($p_a['ko'] != "") {
            if (isset($Tabellen_Spalten_COMMENT[$p_a['ko']])) {
                echo $Tabellen_Spalten_COMMENT[$p_a['ko']];
            } else {
                echo $p_a['ko'];
            }
            echo "<textarea class='w3-input' rows='7' cols='20' name='".$p_a['ko']."' $readOnly >" . $neu[$p_a['ko']] . "</textarea> ";
        }

        if ($p_a['f1'] != '') {
            Edit_Daten_Feld_Button($p_a['f1'], 30);
        }
        if ($p_a['f2'] != '') {
            Edit_Daten_Feld_Button($p_a['f2'], 30);
        }

        echo "<div class='bild-detail' >";

        if ($neu[$p_a['bi']] != "") {
            
            $fo = $neu[$p_a['bi']];
            
            $ds_parts = pathinfo($fo);
            $ext = strtolower($ds_parts['extension']);
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            if ($graffile )  {
                $fo_arr = explode("-", $neu[$p_a['bi']]);
                $cnt_fo = count($fo_arr);
                
                if ($cnt_fo >= 3) {   // URH-Verz- Struktur de dsn
                    $urh = $fo_arr[0];
                    $verz = $fo_arr[1]."/";
                    if ($cnt_fo > 3) {
                        if (isset($fo_arr[3])) {
                            $s_verz = $fo_arr[3]."/" ;
                        }
                    }
                    
                    $p = $path2ROOT ."login/AOrd_Verz/$urh/09/06/".$verz.$neu[$p_a['bi']] ;
                    
                    if (!is_file($p) ) {
                        $p = $pict_path . $neu[$p_a['bi']];
                    }
                } else {
                    $p = $pict_path . $neu[$p_a['bi']];
                }
                echo "<a href='$p' target='Bild $j' > <img src='$p' alter='$p' height='200px'></a><br>";
                echo $neu[$p_a['bi']];
            } else {
                #var_dump($sub_mod);
                #var_dump(SubMod_Dirs);
                $sdir = SubMod_Dirs[$sub_mod];
                $p = "<a href='".$path2ROOT ."login/AOrd_Verz/$sdir/".$neu[$p_a['bi']]."'> ".$neu[$p_a['bi']]."</a>" ;
                
                echo "$p"; 
            }

        } else {
            echo "kein Bild hochgeladen";
        }
        
        ?>  
        <!-- Bereich für die diversen Ausgaben von js  -->
        <input type="hidden" id="bild-datei-auswahl_<?php echo $j ?>" name="bild_datei_<?php echo $j ?>" value="" />
        
        <!-- Bereich, um die ausgewählten Bildinfos anzuzeigen (immer im DOM) -->
        <div id="auswahl-bild_<?php echo $j ?>" style="display:none;">  
        <h3>Neu gewähltes Bild:</h3>
        <div id="bild-vorschau-auswahl_<?php echo $j ?>"></div>
              <p>Dateiname: <span id="dateiname-auswahl_<?php echo $j ?>"></span></p>
        </div>

        <!-- Galerie-Container für die Bildauswahl -->
        <div id="bild-galerie_<?php echo $j; ?>" style="display:none; border:1px solid #ccc; padding:10px;"></div>

        <!-- Dialog für die Bilder-Auswahl (separater Dialog, eigene IDs) -->
        <div id="dialog-bilder_<?php echo $j; ?>" style="display:none;">
        <div id="bild-vorschau-dialog_<?php echo $j; ?>"></div>
        <div id="dateiname-dialog_<?php echo $j; ?>"></div>
        <input type="hidden" id="bild-datei-dialog_<?php echo $j; ?>">
        </div>
        <hr>
        <?php 
        echo "</div>"; // Bild detail end

        ?>
        
       <div class="toggle-group foto-upd-container" data-group="group1">
           <div class="toggle-block" id="block1<?php echo $j ?>"> 
           <!--  
           Block1 <?php echo $j ?>a
            -->
           <div class='foto-upd'  style='margin-bottom:20px; border:1px solid #ccc; padding:10px;'>           
           <!-- Upload Parameter Gruppe -->
                
           <!-- Radio-Buttons für Upload-Methode Auswahl -->
           <div style="margin:10px 0;">
               <?php
               if ($module != 'OEF') {
               ?>
                  <label>
                     <input type="radio" name="upload_method_<?php echo $j; ?>" value="library" data-toggle-group-a="group_lib<?php echo $j; ?>" data-toggle-group-b="group_upload<?php echo $j; ?>" data-toggle-index="<?php echo $j; ?>"> aus Bibliothek auswählen
                  </label>
               <?php 
               }
               ?>
               <label>
                   <input type="radio" name="upload_method_<?php echo $j; ?>" value="upload" data-toggle-group-a="group_upload<?php echo $j; ?>" data-toggle-group-b="group_lib<?php echo $j; ?>" data-toggle-index="<?php echo $j; ?>" checked> Datei neu hochladen
               </label>
           </div>
           
           <!-- Bibliothekssuche Gruppe -->
           <div class="toggle-group" data-group="group_lib<?php echo $j; ?>" id="sel_lib_suche<?php echo $j; ?>">
               <div class="toggle-block" id="lib<?php echo $j; ?>">
                 <!-- keine Daten, es wird nur der Eintrag zum Auslösen gebraucht -->
               </div>
           </div>

            <div id="sel_lib_upload<?php echo $j; ?>" ">  <!-- style="display:none; -->
            
                 <div class="toggle-group" data-group="group_upload<?php echo $j; ?>" id="sel_lib_upload<?php echo $j; ?>">
                 <div class="toggle-block" id="upload<?php echo $j; ?>">
                 <!-- Block 5a -->
                 <?php
                 $subMod = $module."|".$sub_mod;
                 echo "<input type='hidden' id='subMod' value='$subMod' >";
                 if ($module != 'OEF' || ($module == 'OEF' && ($sub_mod == 'MP' || $sub_mod == 'BU' || $sub_mod == 'MUS' || $sub_mod == 'PR' || $sub_mod == 'TE'))) {
                     AutoCompForm_Eigent('U', false,$j);
                     
                     echo "<div class='Menu-Separator'> Aufnahme- Datum (Ziel- Pfad der Bilder erweitern mit Anhang möglich)</div>";
                     
                     echo "<div class='w3-row'style='background-color:#eff9ff'>"; // Beginn der Einheit Ausgabe
                     echo "<div class='w3-third   ' >";
                     echo "<label for='aufnDat'>Aufnahme- Datum (Haupt- Pfad)</label>";
                     echo "  </div>";  // Ende Feldname
                     echo "  <div class='w3-twothird  ' >"; // Beginn Inhalt- Spalte
                     echo "<input type='text' id='aufnDat_$j' name='aufn_dat_$j'  />  YYYYmmDD Format oder Jahreszahl"; // 
                     echo "</div>";
                     echo "</div>"; // ende Ausgabe- Einhait
                 }
                 
                 echo "<div id='$j'></div>";
                 #echo "<button id='$j'  class='button-sm'>Hochladen</button>";

                 echo "<div class='w3-row'>"; // Beginn der Einheit Ausgabe
                 echo "<div class='w3-third   ' >";
                 echo "<label for='urhEinfg'>Urheber ins Bild einfügen</label>";
                 echo "  </div>";  // Ende Feldname
                 echo "  <div class='w3-twothird ' >"; // Beginn Inhalt- Spalte
                 echo "<input type='radio' name='urheinfueg_$j' id='urhEinfgJa_$j' value='J' checked='checked' ><label for='urheinfgJa_$j'>Ja</label><br>";     // für Fotos
                 echo "<input type='radio' name='urheinfueg_$j' id='urhEinfgNein_$j' value='N'       ><label for='urheinfgNein_$j'>Nein</label><br>";
                 # echo "<input type='hidden' name='urhName' id='urhName' value='' >";     // für Fotos
                 echo "<input type='hidden' name='reSize' id='reSize' value='800' >";         // default size max 800x 800 pixel  für Fotos 
                 echo "</div>";
                 echo "</div>"; // ende foload
                 ?>
                 <br><input type="file" id="upload_file_<?php echo $j; ?>">
                 
            </div>
                 
             </div>          

             </div>
        <?php

        echo "</fieldset>";
        echo "</div>";
    }

    echo "</fieldset>";
    echo "</div>";  // Responsive Block end
    echo "</div>";        // end container
    
    // Add hidden field for JavaScript to know how many image blocks to setup
    echo "<input type='hidden' id='bildAnz' value='".$pic_cnt."'>";

} // end UploadForm_M

/**
 * Setzen des Speicherpfades per  Return zurückgegeben
 * UploadPfad_M
 *
 *
 * @param string $aufndat
 *            Datum oder Jahr der Aufnahme - oder Pfadname  - Darf nicht leer sein
 * @param string $basepfad
 *            Basispfad darf leer sein
 * @param string $suffix
 *            Zusatzpfad darf leer sein
 * @param string $aoPfad Archiv- Ordnungs- Teil, kann auch leer sein
 * @param string $urh_nr Urheber- Nummer
 *
 * @return string $d_path
 *
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 * @global string $module Modul-Name für $_SESSION[$module] - Parameter
 *
 */
function UploadPfad_M($aufnDatum, $suffix = '', $aoPfad = '', $urh_nr = '')
{
    global $debug, $module , $path2ROOT;
    
    console_log('uploadpfad');
    
    $basepath = $path2ROOT.'login/'.$_SESSION['VF_Prim']['store'].'/';
    
    $grp_path = $ao_path = $verzeichn = $subverz = "";
    
    $mand_mod = array('INV', 'FOT', 'F_G','F_M');
    
    if (in_array($module, $mand_mod)) { // Mandanten- Modus
        #if (isset($mand_mod[$module]))    {
        
        if ($urh_nr == "") {
            $grp_path = $_SESSION['Eigner']['eig_eigner'].'/';
        } else {
            $grp_path = $urh_nr.'/';
        }
        
        switch ($module) {
            
            case 'INV':
                $verzeichn =  'INV/';
                break;
            case 'F_G':
                if (substr($_SESSION[$module]['sammlung'], 0, 4) == 'MA_F') {
                    $verzeichn =  'MaF/';
                } else {
                    $verzeichn =  'MaG/';
                }
                break;
            case 'F_M':
                if (substr($_SESSION[$module]['fm_sammlung'], 0, 4) == 'MU_F') {
                    $verzeichn =  'MuF/';
                } else {
                    $verzeichn =  'MuG/';
                }
                break;
            case 'FOT':
                $ao_path = $aoPfad.'/';
                break;
        }
        
    } else {
        switch ($module) {
            case 'OEF':
                break;
            case 'PSA':
                if ($_SESSION[$module]['proj'] == 'AERM') {
                    $verzeichn = 'PSA/AERM/';
                } else {
                    $verzeichn = 'PSA/AUSZ/';
                }
                break;
                
        }
    }
    $dPath = $basepath.$grp_path.$ao_path.$verzeichn.$subverz;
    
    return $dPath;
    
} // end UploadPfad_M

/**
 * Hochladen von Dateien
 *
 * Bei allen Dateien:  ändern Umlaute auf alte Schreibweise Ä -> AE
 * Bei Grafischen Dateien: wenn Urheber-Abkürzung und Foto-Datum vorhanden, Umbenennen nach Foto-Vorgabe (Urh-Datum-Dateiname)
 *
 *
 * @param string $uploaddir      Zielverzeichnis
 * @param string $i              index zur uploadfile $files[uploadfile_x
 * @param string $urh_abk        Abkürzung des Urhebernamens
 * @param string $fo_aufn_datum  Aufnahmedatum
 * @return string Dsn der Datei  Name der Datei zum Eintrag in Tabelle
 */
function UploadSave_M($uploaddir, $fdsn, $urh_nr = "", $md_aufn_datum = "")
{
    global $debug, $module;

    console_log('uploadsave');
    if ($md_aufn_datum != "") {
        $md_aufn_datum_n = "$md_aufn_datum/";
    }
    #echo " L 02704 Upl upldir $uploaddir fdsn $fdsn <br>";
    # var_dump($_FILES[$fdsn]);
    $target = "";
    if ($_FILES[$fdsn]['name'] != "") {

        if ($_FILES[$fdsn]['error'] >= 1) {
            $errno = $_FILES[$fdsn]['error'];
            $err = "Upload Fehler: ";
            switch ($errno) {
                case 1:
                case 2:
                    $err .= "Err: Datei zu groß";
                    break;
                case 8:
                    $err .= "Err: Falsche Datei (Erweiterung)";
                    break;
            }
            return $err;
        }

        $f_a = pathinfo($_FILES[$fdsn]['name']);
        # var_dump($f_a);
        $target = $f_a['basename'];
        #echo "L 2755 uploaddir $uploaddir <br>";
        if (stripos($uploaddir, '09/') >= 1) {
            $ext = strtolower($f_a['extension']);
            $ao_ssg = "";
            if (in_array($ext, AudioFiles)) {
                $ao_ssg = "02/";
            }
            if (in_array($ext, GrafFiles)) {
                $ao_ssg = "06/";
            }
            if (in_array($ext, VideoFiles)) {
                $ao_ssg = "10/";
            }
            $uploaddir .= $ao_ssg.$md_aufn_datum_n;
        }
        # echo "L 02687 uploaddir $uploaddir <br>";

        if (! file_exists($uploaddir)) {
            mkdir($uploaddir, 0770, true);
        }

        if ($target != "") {
            $target = VF_trans_2_separate($target);

            $fn_arr = pathinfo($target);
            $ft = strtolower($fn_arr['extension']);
            #var_dump($fn_arr);
            if (in_array($ft, GrafFiles) && $urh_nr != "" && $md_aufn_datum != "") {
                $newfn_arr = explode('-', $target);
                $cnt = count($newfn_arr);
                if ($cnt == 1) { # original- Dateiname, nicht im Format urh-datum-Aufn_dateiname.ext,
                    $target = "$urh_nr-$md_aufn_datum-" . $fn_arr['basename'];
                }
            } else {
                $target = $fn_arr['basename'];
            }
            # echo "L 02658 fdsn $fdsn ; uploaddir $uploaddir; target $target <br>";
            # var_dump($_FILES[$fdsn]);
            if (move_uploaded_file($_FILES[$fdsn]['tmp_name'], $uploaddir . $target)) {
                # var_dump($_FILES[$fdsn]);
                # echo "L 02745 target $target <bR>";
                return $target;
            }
        }
    }
    return false;
} // end UploadSave_M


/**
 * Admin- Verständigungs- Mails je Gruppe bereitstellen
 *
 * Die -E-Mail- Adressen der dafür bestimmten Administratoren werden als Komma getrennter String bereitgestellt.
 *
 *
 * @param string $mail_grp
 *            Administrator- Gruppe
 * @return string Mail-Adresse(n)
 *
 * @global boolean $debug Anzeige von Debug- Informationen: if ($debug) { echo "Text" }
 * @global array $db Datenbank Handle
 */
function VF_Mail_Set_n($mail_grp) # Mail- Gruppe

# für Zusatzauswahlen
# Admin-Emails je Gruppeauswählen
// --------------------------------------------------------------------------------
{
    global $debug, $db, $module;
  
    if ($debug) {
        echo "<pre class=debug>F Staat Ausw L Beg: mail_grp \$mail_grp <pre>";
    }
    
    // einlesen fh_m_mail mit em_mail_grp == $mail_grp, dann E-Mail-Adresse aus fh_mitglieder mit em_mitgl_nr einlesen und in liste ausgeben "mail1, mail2, ..."
    $sql_mail = "SELECT * from fh_m_mail WHERE em_mail_grp = '$mail_grp' ";
    
    $adr_list = "";
    
    $return_mail = SQL_QUERY($db, $sql_mail);
    
    # print_r($return); echo "<br>\$sql $sql <br>";
    
    while ($row = mysqli_fetch_assoc($return_mail)) {
        $MitglNr = $row['em_mitgl_nr'];
        $sql_m = "SELECT * from fh_mitglieder WHERE mi_id = '$MitglNr' ";
        
        $return_m = SQL_QUERY($db, $sql_m);
        
        if ($return_m) {
            while ($row_m = mysqli_fetch_assoc($return_m)) {
                if ($row_m['mi_email'] != "") {
                    if ($adr_list == "") {
                        $adr_list = $row_m['mi_email'];
                    } else {
                        $adr_list .= ", " . $row_m['mi_email'];
                    }
                }
            }
        }
    }
    
    if ($adr_list == "") {
        $adr_list = " service@feuerwehrhistoriker.at ";
    }
    
    # print_r($adr_list);
    return ($adr_list);
}

// Ende von function VF_Mail_set