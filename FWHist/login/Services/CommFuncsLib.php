<?php 
/** 
 * Neu aufbau von Funktionen J. Rohowsky ab 2026
 *     @author  Josef Rohowsky josef@kexi.at start 24.12.2025
 * 
 * Enthält und Unterprogramme für die Auwahl von Namen und Begriffen,
 * 
 * AutoCompForm_*()  sind Wrapper auf FormRendererFlex_Ext (Autocomplete-Bausteine, jetzt dort)
 *
 * AutoCompForm_Abk          Autocomplete Form fur Abkürzungs- Auswahl
 * 
 * AutocompForm_ArchivOrd  Autocomplete FOrmfür die Suche nach der Arcivordnung
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
 *  AutocompForm_Sammlung   Autocompelete Form für die Suche nach der Sammlung
 *  
 *  AutoCompForm_Mandant     Autocomplete Form für Mandanten- Auswahl
 *  
 *  AutoCompForm_Urheber     Autocomplete Form für Urheber- Auswahl (inkl. Hidden-Feld für Namen, fürs Wasserzeichen)
 *  
 *  erlaubnisMand            Welche Berechtigung hat dieser UID für diesen Mandanten
 *  
 *  userHasRole              Ist dieser UID für dieses Script berechtigt
 *  
 *  filesUpload              Server Teil des (mehrfachen) File- Uploads
 *  
 *  getBaseUrlToFWHist       BasisPfad für Links  Url/FWHist/
 *  
 *  20261025
 */

/**
 * Autocomplete-Bausteine: die Funktionen AutoCompForm_*() sind nur noch dünne Wrapper (echo) auf
 * \FWHist\Services\FormRendererFlex_Ext (Datei FormRendererFlex_Ext.php, dort Dokumentation und Parameter).
 * Neue/umgestellte Formulare benutzen die Klasse direkt:
 *
 *     echo $forms->renderAutoCompStaat();                       // $forms = new FormRendererFlex_Ext(...), '' bei Schreibschutz
 *     echo \FWHist\Services\FormRendererFlex_Ext::autoCompMandant('selection', 'mand', 'mand', '', 'mand_id');   // ohne Formular
 *
 * Die Wrapper können entfallen, sobald kein Script sie mehr aufruft.
 */
function AutoCompForm_ArchOrd () {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompArchOrd();
}

function AutoCompForm_Abk (string $group = '') {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompAbk($group);
}

function AutoCompForm_Benutzer () {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompBenutzer();
}

function AutoCompForm_Bdld (string $fieldId = 'bdld', string $fieldName = 'bdld', string $value = '') {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompBdld($fieldId, $fieldName, $value);
}

function AutoCompForm_Eigent($t, $cl = false, $j = "1") {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompEigent($t, (bool) $cl, $j);
}

function AutoCompForm_Firma (string $funkt = 'F') {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompFirma($funkt);
}

function AutoCompForm_Aufbau (string $funkt = 'A') {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompAufbau($funkt);
}

function AutoCompForm_Mandant (string $mode = 'selection', string $fieldId = 'mandant', string $fieldName = 'mandant', string $value = '', string $hiddenFieldName = 'mandant_id') {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompMandant($mode, $fieldId, $fieldName, $value, $hiddenFieldName);
}

function AutoCompForm_Mitgl ($em) {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompMitgl($em);
}

function AutoCompForm_Ort () {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompOrt();
}

function AutoCompForm_Sammlg () {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompSammlg();
}

function AutoCompForm_Staat (string $fieldId = 'staat', string $fieldName = 'staat', string $value = '') {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompStaat($fieldId, $fieldName, $value);
}

function AutoCompForm_Urheber (string $mode = 'selection', string $fieldId = 'urheber', string $fieldName = 'urheber', string $value = '', string $hiddenFieldName = 'urheber_id', string $hiddenNameField = 'urheber_name') {
    echo \FWHist\Services\FormRendererFlex_Ext::autoCompUrheber($mode, $fieldId, $fieldName, $value, $hiddenFieldName, $hiddenNameField);
}

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
 * Abfrage für die Berechtigung eines eingeloggten Benutzers
 * 
 * @param string Rollen- Bezeichnung
 */
function userBerechtigtOK ($berechtigung) {
    if ($berechtigung === "Alle") {
        return true;
    }
    if (
        empty($_SESSION['BS_Prim']['BE']['be_id'])
        || !userHasRole($berechtigung)
    ) {
        echo "Für diesen Programmteil besteht keine Änderungs-Berechtigung.<br>";
        return false;
    }

    return true;
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
    $rolesArray = is_array($rolesString)
        ? $rolesString
        : array_map('trim', explode(',', (string)$rolesString));
    
    // Super-User-Rolle prüfen
    if (in_array('ADM-ALLE', $rolesArray, true)) {
        return true;
    }
    
    // Gesuchte Rolle prüfen
    return in_array($requiredRole, $rolesArray, true);
} // ende userHasRole


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
} // Ende von function VF_Mail_set

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
function filesUpload($uploaddir, $fdsn, $urh_abk = "", $fo_aufn_datum = "")
{
    global $debug, $module;
    
    console_log("fileUpload $uploaddir, $fdsn");
    echo __LINE__ . " Upl upldir $uploaddir fdsn $fdsn <br>";
    var_dump($_FILES);
    
    $uploadedFiles = []; // Array für erfolgreich hochgeladene Dateien
    
    if (!empty($_FILES[$fdsn])) {
        // Anzahl der hochgeladenen Dateien
        $cnt = count($_FILES[$fdsn]['name']);
        error_log("Count $cnt");
        for ($i = 0; $i < $cnt; $i++) {
            // Ursprünglicher Dateiname
            $originalName = $_FILES[$fdsn]['name'][$i];
            if ($originalName == "") {
                // Datei ohne Namen überspringen
                continue;
            }
            
            // Dateiname ggf. umwandeln (z.B. Umlaute, Leerzeichen)
            $target = sanitize_filename($originalName);
            
            // Dateiendung ermitteln
            $fn_arr = pathinfo($target);
            $ft = strtolower($fn_arr['extension']);
            
            // Falls Grafikdatei und Zusatzinfos vorhanden, Dateiname anpassen
            if (in_array($ft, GrafFiles) && $urh_abk != "" && $fo_aufn_datum != "") {
                $newfn_arr = explode('-', $target);
                $cnt_parts = count($newfn_arr);
                if ($cnt_parts == 1) { // Originaldateiname ohne Format
                    $target = "$urh_abk-$fo_aufn_datum-" . $fn_arr['basename'];
                }
            } else {
                $target = $fn_arr['basename'];
            }
            
            echo __LINE__ . " fdsn $fdsn ; uploaddir $uploaddir; target $target <br>";
            
            // Temporärer Dateipfad der aktuellen Datei
            $tmp_name = $_FILES[$fdsn]['tmp_name'][$i];
            $error = $_FILES[$fdsn]['error'][$i];
            
            // Nur wenn kein Fehler vorliegt, Datei verschieben
            if ($error === UPLOAD_ERR_OK) {
                if (move_uploaded_file($tmp_name, $uploaddir . $target)) {
                    echo "Datei $target erfolgreich hochgeladen.<br>";
                    $uploadedFiles[] = $target;
                } else {
                    echo "Fehler beim Verschieben der Datei $target.<br>";
                }
            } else {
                echo "Upload-Fehler bei Datei $originalName: Fehlercode $error<br>";
            }
        }
    }
    
    // Rückgabe: Array der erfolgreich hochgeladenen Dateien oder false, wenn keine
    if (count($uploadedFiles) > 0) {
        return $uploadedFiles;
    }
    
    return false;
}
# end Funct filesUpload

/**
 * Korrigieren von Datenamen (Umlaute, Sonderzeichen, ..
 * 
 * strtr ersetzt Umlaute und einige häufige Sonderzeichen durch ASCII-Entsprechungen.
 * preg_replace entfernt alle Zeichen, die nicht Buchstaben, Zahlen, Punkt, Unterstrich oder Bindestrich sind, und ersetzt sie durch Unterstriche.
 * Mehrfache Unterstriche oder Bindestriche werden auf einen Unterstrich reduziert.
 * Die Ausgabe wird auf Kleinbuchstaben vereinheitlicht, was oft bei Dateinamen sinnvoll ist.
 * 
 * 
 * @param string $filename
 * @return string
 */
function sanitize_filename($filename)
{
    // Umlaute und Sonderzeichen ersetzen
    $umlaute = [
    'ä' => 'ae',
    'ö' => 'oe',
    'ü' => 'ue',
    'Ä' => 'Ae',
    'Ö' => 'Oe',
    'Ü' => 'Ue',
    'ß' => 'ss',
    'à' => 'a',
    'á' => 'a',
    'â' => 'a',
    'ã' => 'a',
    'å' => 'a',
    'æ' => 'ae',
    'ç' => 'c',
    'è' => 'e',
    'é' => 'e',
    'ê' => 'e',
    'ë' => 'e',
    'ì' => 'i',
    'í' => 'i',
    'î' => 'i',
    'ï' => 'i',
    'ñ' => 'n',
    'ò' => 'o',
    'ó' => 'o',
    'ô' => 'o',
    'õ' => 'o',
    'ø' => 'o',
    'ù' => 'u',
    'ú' => 'u',
    'û' => 'u',
    'ý' => 'y',
    'ÿ' => 'y',
    ];
    
    // Ersetze Umlaute und Sonderzeichen
    $filename = strtr($filename, $umlaute);
    
    // Alle nicht erlaubten Zeichen entfernen (nur Buchstaben, Zahlen, Punkt, Unterstrich, Bindestrich)
    $filename = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $filename);
    
    // Mehrfache Unterstriche oder Bindestriche auf einen reduzieren
    $filename = preg_replace('/[_\-]+/', '_', $filename);
    
    // Optional: Groß-/Kleinschreibung vereinheitlichen (z.B. alles klein)
    $filename = strtolower($filename);
    
    return $filename;
}

/**
 *
 * @return string Url-Pfad to App
 */
function getBaseUrlToFWHist(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_replace('#^https?://#i', '', $host); // entfernt http:// oder https:// falls vorhanden
    $host = rtrim($host, '/');
    
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
    $script  = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    
    if ($docRoot === '' || $script === '') {
        return $scheme . '://' . $host;
    }
    
    if (strpos($script, $docRoot) !== 0) {
        return $scheme . '://' . $host;
    }
    
    $relative = substr($script, strlen($docRoot));
    $relative = str_replace('\\', '/', $relative);
    
    $pos = strpos($relative, '/FWHist');
    if ($pos === false) {
        return $scheme . '://' . $host;
    }
    
    return $scheme . '://' . $host . substr($relative, 0, $pos + strlen('/FWHist'));
}
