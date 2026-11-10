<?php

use FWHist\Modules\Mandanten\API\DB_Mandanten;
use FWHist\Auth\AccessGuard;

/**
 * VF_Upload_Unified.lib.php  (FWHist-Version, final)
 *
 * Vereinheitlichter Medien-/Dokumenten-Upload-Block für login/Services/API/UploadAPI.php.
 * Interaktionsmuster = Variante A (MassUpLibs.js): expliziter "Hochladen"-Button je Slot,
 * sequentieller Upload Datei für Datei, Ergebnis-Dateinamen landen in Hidden-Feldern, die
 * beim regulären Formular-Submit mitgeschickt werden.
 *
 * Erwartet global:
 *   $forms   - instanzierter \FWHist\Services\FormRendererFlex für dieses Formular
 *   $neu     - aktuelle Datensatz-Werte (Anzeige bestehender Dateien)
 *   $module  - Modul-Kürzel, wird zusammen mit $sub_mod als "subMod" an die API gereicht
 *              (z.B. "ARC", "PSA", "OEF|BU", "INV", "Media" - siehe UploadAPI.php Switch)
 *   $sub_mod - Untermodul-Kürzel, optional
 *   $editProtect - true zeigt nur vorhandene Dateien und blendet Upload-Steuerungen aus
 *
 * $p_a[] je Slot:
 *   'udir'  => Anzeige-Pfad bestehender Datei(en)
 *   'ko'    => Feldname Beschreibung (Textarea), '' = keine
 *   'bi'    => Zielfeld für Dateiname(n) (bei 'multi'=>true -> Array-Feld "bi[]")
 *   'aOrd'  => Vorbelegung Archivordnung (nur für Dokument-Slots relevant)
 *   'multi' => true = mehrere Dateien in diesem Slot (Mass-Upload), false = 1 Datei
 *
 * Urheber-Auswahl: AutoCompForm_Urheber() (CommFuncsLib.php) liefert je Slot ein sichtbares
 * Textfeld (id/name "urh_$j") und zwei Hidden-Felder. Deren name-Attribute (gehen beim normalen
 * Formular-Submit mit) sind "urh_id_$j" (Urheber-ID, ei_id) und "urh_name_$j" (Urheber-Name,
 * ei_org_name/ei_name - fürs Wasserzeichen). Ihre DOM-IDs heißen dagegen "urh_{j}_id" und
 * "urh_{j}_name" ({fieldId}_id / {fieldId}_name); das Upload-JS liest per getElementById die
 * IDs, nicht die name-Attribute. (Kommentar 2026-10-03 angeglichen; Verhalten unverändert.)
 *
 * OEF-Bilder (Öffentlichkeitsarbeit, 2026-10-03): Bilder in OEF-Modulen (OEF-BU/DO/PR/TE ...)
 * landen im docPathTemplate-Ordner des jeweiligen DOC_PATH_TABLE-Eintrags (nicht in der
 * Medien-Bibliothek) und werden nicht in {urh_nr}_dmedien eingetragen. Urheber und
 * Aufnahmedatum sind dort optional; Defaults setzt UploadAPI.php (Urheber 'OEF'/'Temporär',
 * Datum = Tagesdatum). Reduzieren/Wasserzeichen wie gehabt; kein Hochskalieren mehr.
 * Die Mandant-Auswahl (AutoCompForm_Mandant) war hier nur ein Behelf/Test, solange
 * AutoCompForm_Urheber noch nicht fertig war, und wurde entfernt (Stand 2026-09-13).
 *
 * eigner (Mandant/Eigentümer für den Ablagepfad bei Dokumenten, $aordRoot/$eigner/$aOrd/):
 * ist NICHT mit dem Urheber pro Foto zu verwechseln - für Dokumente gilt immer die ei_id des
 * aktuellen Arbeitskontexts, unabhängig vom Urheber-Feld. Wird EINMAL pro Seite/Datensatz aus
 * $_SESSION['BS_Prim']['Env']['mand_id'] (Fallback $_GET['mand_id']) als Hidden-Feld "eigner"
 * gerendert (nicht pro Upload-Slot). Bestätigt 2026-09-13.
 *
 * OFFEN: Bibliotheks-Auswahl (Radio "Aus Bibliothek wählen") ist im Frontend fertig verdrahtet,
 * das Backend dafür (schlichtes "alle Fotos eines Eigners auflisten") fehlt noch - siehe Chat.
 *
 * Bugfixes 2026-09-13: (1) doppeltes name-Attribut zwischen dem "Fallback"-Hidden-Feld (Altwert)
 * und dem vom JS beim Upload-Erfolg erzeugten Ergebnisfeld führte dazu, dass der Altwert den neu
 * hochgeladenen Dateinamen beim Submit überschrieb (Submit-Reihenfolge im DOM) - behoben, indem
 * Nicht-Multi-Slots nur mehr EIN Hidden-Feld besitzen, dessen .value bei Erfolg direkt gesetzt wird.
 * (2) eigner kam fälschlich aus dem Urheber-Feld (urh_id_$j) statt aus der Session - behoben.
 * (3) aOrd kam immer leer an: der Selektor `[name="AOrd-Auswahl_$j"]` traf zuerst das
 * <fw-multi-select>-Element selbst (kein .value), nicht den intern angelegten echten
 * <input type="hidden"> mit demselben name - behoben durch `input[name="..."]`.
 *
 * Bugfixes 2026-09-29: (1) Bibliotheks-Abfrage sendet jetzt auch die Sammlung (ad_sammlg/in_sammlg),
 * zeigt Fehlermeldungen der API an (früher: leeres Fenster, weil {status:'error'} nicht ausgewertet wurde)
 * und hat eine Debug-Ansicht (Seiten-URL mit ?debug=1). (2) Fehler/Hinweise des Uploads werden am Ende
 * nicht mehr von "Upload abgeschlossen." überschrieben. (3) sammlg wird beim Upload mitgeschickt.
 *
 * Ergänzung 2026-09-29 (2): Sammlungs-Feld wird modulübergreifend gefunden (*_sammlg mit Präfix
 * ad|in|fz|ac|av|az, siehe SAMMLG_PRAEFIX); mit Sammlungs-Begriff werden ALLE _dmedien-Tabellen
 * durchsucht (Urheber/Aufnahmedatum des Slots schränken dann nicht ein).
 *
 * Ergänzung 2026-09-29 (3): Thumbnails in der Bibliotheks-Auswahl und beim ausgewählten Bild
 * (SelPictThumbAPI.php: serverseitig verkleinert, gecacht in AOrd_Verz/Thumbs/).
 *
 * @author Josef Rohowsky, überarbeitet 2026-09-29
 *
 * parameter numeric $pic_cnt  Anzahl der anzuzeigenden / hochzuladender Dokuente /  Bilder 
 * parameter array  $pct_arr   Informationen Feldnamen für die Anzeige
 * parameter boolean $newRecord true: neuanlage eines Datendatzes, die Eingabe von Dokumenten / Bildern wird immer angezeigt,
 *                              false: Die Eingabe- Anzeigen sind verborgen, können aber bei Bedarf aktiviert werden (noch offen)
 *                              
 */

/**
 * Ablage-Regeln, umgesetzt nach Pfad-Tabelle.md UND der Klarstellung von Josef (2026-09-27):
 *
 * "ALLE Fotos sollen mit dem kompletten geänderten Namen ... in die Foto-Lib:
 *  AOrd_Verz/$urh_id/09/06/Datum eingespielt werden, denn genau dort werden die Fotos gesucht!
 *  In das Schema urh_id/ad_sg/ad_subsg/dokument.pdf dürfen NUR Dokumente hinein."
 *
 * Das heißt: die Medien-Bibliothek (Foto/Audio/Video) ist EIN einziger, modulübergreifend
 * fixer Pfad (MEDIENBIB_TEMPLATE, {ei_id}=Urheber-Nr. des Slots), unabhängig davon, in
 * welchem Modul/Formular hochgeladen wird - auch bei Archivalien (ARC) landet ein Foto dort,
 * NICHT im Archivalien-Ordner. Nur Dokumente (alles, was nicht Foto/Audio/Video ist) folgen
 * einem modulabhängigen Pfad, siehe DOC_PATH_TABLE - dort gilt {ei_id}=eigner
 * (Mandant/Eigentümer des Arbeitskontexts, siehe Kopf-Kommentar), nicht der Foto-Urheber.
 *
 * Bestätigt (2026-09-27): ad_subsg 05=Audio/06=Foto/10=Video; OEF-DO-Zielordner "Dokumente".
 */
const MEDIENBIB_TEMPLATE = '{ei_id}/09/{ad_subsg}/{aufn_datum}/';

/**
 * Pfad-Tabelle NUR für Dokumente (alles ausser Foto/Audio/Video) je Modul, siehe Pfad-Tabelle.md.
 * INV/MEDIA/FZG-GER handeln laut Pfad-Tabelle.md ausschliesslich Audio/Foto/Video und haben
 * daher keinen eigenen Dokument-Pfad (null) - ein Dokument-Upload dort ist ein Sonderfall und
 * fällt auf den generischen Fallback in Upload_ResolveModuleConfig() zurück.
 * allowLibrary steuert (unabhängig vom Dateityp) nur, ob "Aus Bibliothek auswählen" als Quelle
 * angeboten wird.
 */
const DOC_PATH_TABLE = [
    // --- Mandanten-spezifische Daten ---------------------------------------------------
    'ARC'     => ['docPathTemplate' => '{ei_id}/{ad_sg}/{ad_subsg}/', 'allowLibrary' => true],
    'INV'     => ['docPathTemplate' => null,                          'allowLibrary' => true],
    'MEDIA'   => ['docPathTemplate' => null,                          'allowLibrary' => false],
    'FZG-GER' => ['docPathTemplate' => null,                          'allowLibrary' => true],
    // --- Mandanten-unabhängige Daten (Öffentlichkeitsarbeit) ---------------------------
    'OEF-BU' => ['docPathTemplate' => 'Buch/',       'allowLibrary' => false],
    'OEF-DO' => ['docPathTemplate' => 'Dokumente/',  'allowLibrary' => false],
    'OEF-MP' => ['docPathTemplate' => 'Marktplatz/', 'allowLibrary' => false],
    'OEF-MU' => ['docPathTemplate' => 'Museen/',     'allowLibrary' => false],
    'OEF-PR' => ['docPathTemplate' => 'Presse/',     'allowLibrary' => false],
    'OEF-TE' => ['docPathTemplate' => 'Termine/',    'allowLibrary' => false],
];

/**
 * Löst $module/$sub_mod auf einen Eintrag aus DOC_PATH_TABLE auf (gilt NUR für Dokumente,
 * siehe Kopf-Kommentar oberhalb von MEDIENBIB_TEMPLATE - Fotos/Audio/Video gehen immer in die
 * Medien-Bibliothek, unabhängig vom Modul). Liefert einen sicheren Default (kein
 * Bibliotheks-Zugriff, generisches {ei_id}/{ad_sg}/{ad_subsg}/-Pfad), falls das Modul (noch)
 * nicht in der Tabelle steht oder dort docPathTemplate=null hinterlegt ist.
 *
 * $subMod bestimmt den Tabellen-Schlüssel NUR bei $module === 'OEF' (dort trägt subMod das
 * pfadrelevante Untermodul BU/DO/PR/TE, z.B. subMod "BU" -> Schlüssel "OEF-BU"). Bei allen
 * anderen Modulen ist $sub_mod lediglich eine Formular-/Seitenkennung (z.B. "Edit") OHNE
 * Bedeutung für den Ablagepfad - dort zählt ausschliesslich $module (Bugfix 2026-09-27,
 * von Josef gefunden: die Vorgänger-Version nahm fälschlich IMMER zuerst $subMod, wodurch
 * z.B. $module="ARC"/$sub_mod="Edit" auf dem nicht existierenden Schlüssel "EDIT" landete).
 */
function Upload_ResolveModuleConfig(string $module, string $subMod = ''): array
{
    $moduleKey = strtoupper(trim($module));
    $subKey = strtoupper(trim($subMod));

    if ($moduleKey === 'OEF' && $subKey !== '') {
        $key = 'OEF-' . $subKey;
    } else {
        $key = $moduleKey;
    }

    // Bekannte Alt-/Kurzformen auf die Tabellen-Schlüssel mappen.
    $alias = [
        'FZG' => 'FZG-GER',
    ];
    $key = $alias[$key] ?? $key;

    $entry = DOC_PATH_TABLE[$key] ?? ['docPathTemplate' => null, 'allowLibrary' => false];
    if ($entry['docPathTemplate'] === null) {
        // Generischer Fallback für Dokumente in Modulen ohne eigenen Dokument-Pfad
        // (INV/MEDIA/FZG-GER laut Pfad-Tabelle.md, oder ein unbekanntes Modul).
        $entry['docPathTemplate'] = '{ei_id}/{ad_sg}/{ad_subsg}/';
    }
    return $entry;
}

/**
 * Ordnet eine Datei-Extension der Archivordnung-Untergruppe (ad_subsg) für die Medien-
 * Bibliothek zu, bestätigt nach Pfad-Tabelle.md (05=Audio, 06=Foto, 10=Video; von Josef am
 * 2026-09-27 bestätigt, nicht 02/06/10 wie in Speicher_Struktur.md). Gilt modulübergreifend,
 * siehe MEDIENBIB_TEMPLATE.
 */
function Upload_AdSubsgFuerExt(string $ext): string
{
    $ext = strtolower($ext);
    if (in_array($ext, AudioFiles, true)) {
        return '05';
    }
    if (in_array($ext, VideoFiles, true)) {
        return '10';
    }
    // Default: Foto (deckt auch GrafFiles ab)
    return '06';
}

function Upload_Form($pic_cnt, array $pct_arr,  bool $newRecord)
{
    /** @var \FWHist\Services\FormRendererFlex $forms */
    global $forms, $neu, $module, $sub_mod, $systemBasePath, $editProtect;

    $editProtect = $editProtect ?? false;
    $subModValue = $module . (($sub_mod ?? '') !== '' ? '|' . $sub_mod : '');
    $editProtect = $editProtect || !AccessGuard::current()->canUpload(
        $subModValue,
        (int) ($neu['ei_id'] ?? 0)
    );

    if ($editProtect) {
        $existingSlots = [];
        for ($i = 0; $i < $pic_cnt; $i++) {
            $p_a = $pct_arr[$i] ?? [];
            $biNam = $p_a['bi'] ?? '';
            if ($biNam !== '' && trim((string) ($neu[$biNam] ?? '')) !== '') {
                $existingSlots[] = [$i, $p_a];
            }
        }

        if ($existingSlots === []) {
            echo '<p>Keine Bilder vorhanden.</p>';
            return;
        }

        echo "<div class='block-container custom-container'><div class='custom-row custom-border upload-slots-row'>";
        foreach ($existingSlots as [$i, $p_a]) {
            $j = $i + 1;
            echo "<div class='custom-container custom-half'><fieldset>";
            echo "<div class='bild-data_$j'>";
            echo "<h3>Dokument / Bild $j</h3>";
            echo "<div class='bild-detail'>";
            Upload_Anzeige_Bestand($p_a, $neu, $j);
            echo "</div>";

            if (($p_a['ko'] ?? '') !== '') {
                echo $forms->renderTextareaFieldFlex($p_a['ko']);
            }
            if (($p_a['f1'] ?? '') !== '') {
                echo $forms->renderTextareaFieldFlex($p_a['f1']);
            }
            if (($p_a['f2'] ?? '') !== '') {
                echo $forms->renderTextareaFieldFlex($p_a['f2']);
            }

            echo "</div></fieldset></div>";
        }
        echo "</div></div>";
        return;
    }

    // eigner = Mandant/Eigentümer des aktuellen Arbeitskontexts (ei_id), NICHT der Urheber
    // pro Foto - gilt einmal für den ganzen Datensatz/die ganze Seite, siehe Kopf-Kommentar.
    // Bestätigter Session-Pfad (Sept 13): $_SESSION['BS_Prim']['Env']['mand_id'].
    // Fallback auf $_GET['mand_id'], falls der Env-Key im jeweiligen Seitenaufruf (noch) nicht
    // gesetzt ist (z.B. ArchEdit.php?ad_id=7&mand_id=21 - Env-Key evtl. erst nach GET-Parsing befüllt).
    $eigner = $_SESSION['BS_Prim']['Env']['mand_id'] ?? ($_GET['mand_id'] ?? '');
    // Weiterer Fallback (Bugfix 2026-09-28, Log: "eigner=" kam bei Neu-Eingabe leer an, Dokument
    // landete unter AOrd_Verz/01/01/ statt AOrd_Verz/531/01/01/): Besitzer des Datensatzes selbst.
    // Upload_Anzeige_Bestand() verwendet für die Dokument-Anzeige ebenfalls $neu['ei_id'] als
    // Eigentümer-Verzeichnis - Ablage und Anzeige müssen dieselbe ei_id verwenden.
    if ($eigner === '' || $eigner === null) {
        $eigner = $neu['ei_id'] ?? '';
    }

    // aOrd (Archivordnung-Pfad für Dokumente) kommt NICHT aus einer eigenen Auswahl im
    // Upload-Block, sondern immer aus $neu['ad_sg']/$neu['ad_subsg'] - bei Updates aus der DB,
    // bei Neu-Eingaben bereits aus der Multi-Select-Abfrage in ArchEdit_ph0.inc.php (Zeile 84),
    // die zu diesem Zeitpunkt schon in $neu gemergt ist. Bestätigt/korrigiert 2026-09-13.
    $adSg = trim((string) ($neu['ad_sg'] ?? ''));
    $adSubsg = trim((string) ($neu['ad_subsg'] ?? ''));
    $aOrdWert = ($adSg !== '' && $adSubsg !== '') ? "$adSg/$adSubsg" : '';

    // Modul-Konfiguration NUR für Dokumente laut Pfad-Tabelle.md (siehe DOC_PATH_TABLE oben);
    // gilt einheitlich für alle Slots dieses Formulars, da $module/$sub_mod pro Aufruf fix sind.
    // Fotos/Audio/Video ignorieren das und gehen immer in die Medien-Bibliothek (MEDIENBIB_TEMPLATE).
    $moduleConfig = Upload_ResolveModuleConfig((string) $module, (string) ($sub_mod ?? ''));

    echo "<div class='block-container custom-container'><div class='custom-row custom-border upload-slots-row'>";   // Inhalt Dokument Anzeige und Edit je $i
    echo "<input type='hidden' id='bildAnz' name='pic_cnt' value='$pic_cnt'>";
    echo "<input type='hidden' id='subMod' value='" . htmlspecialchars($subModValue, ENT_QUOTES) . "'>";
    echo "<input type='hidden' class='upload-csrf-token' value='" . htmlspecialchars(AccessGuard::csrfToken(), ENT_QUOTES, 'UTF-8') . "'>";
    echo "<input type='hidden' id='eigner' value='" . htmlspecialchars((string) $eigner, ENT_QUOTES) . "'>";
    echo "<input type='hidden' id='aOrd' value='" . htmlspecialchars($aOrdWert, ENT_QUOTES) . "'>";
    // Zentrale Pfad-Vorgaben fürs Frontend (Wunsch aus Medien_Verwaltung.md: Pfadaufbau nach dem
    // Sammeln der Parameter im Frontend, als eine Variable ($storPath) statt Einzelteilen im
    // Backend). medienBibTemplate gilt IMMER für Foto/Audio/Video (modulübergreifend, siehe
    // Kopf-Kommentar bei MEDIENBIB_TEMPLATE); docPathTemplate NUR für alles andere (Dokumente).
    echo "<input type='hidden' id='medienBibTemplate' value='" . htmlspecialchars(MEDIENBIB_TEMPLATE, ENT_QUOTES) . "'>";
    echo "<input type='hidden' id='docPathTemplate' value='" . htmlspecialchars($moduleConfig['docPathTemplate'], ENT_QUOTES) . "'>";
    echo "<input type='hidden' id='allowLibrary' value='" . ($moduleConfig['allowLibrary'] ? '1' : '0') . "'>";
    // OEF-Modul mit Eintrag in DOC_PATH_TABLE: Bilder gehen in den OEF-Ordner (docPathTemplate)
    // statt in die Medien-Bibliothek; Urheber/Aufnahmedatum sind dafür optional.
    // $module kann "OEF" (dann bestimmt $sub_mod den Schlüssel) oder bereits "OEF-MP" o.ä. sein
    // (Live-Wert subMod = "OEF-MP|MP", 2026-10-03) - beides ergibt denselben Tabellen-Schlüssel.
    $oefKey = strtoupper(trim((string) $module));
    if ($oefKey === 'OEF') {
        $oefKey = 'OEF-' . strtoupper(trim((string) ($sub_mod ?? '')));
    }
    $oefBild = strncmp($oefKey, 'OEF-', 4) === 0 && isset(DOC_PATH_TABLE[$oefKey]);
    echo "<input type='hidden' id='oefBild' value='" . ($oefBild ? '1' : '0') . "'>";

    for ($i = 0; $i < $pic_cnt; $i++) {
        $p_a = $pct_arr[$i];
        $multi = !empty($p_a['multi']);
        $j = $i + 1;

        echo "<div class=' custom-container custom-half'><fieldset>";  // 1 block-container
        
        echo "<div class='bild-data_$j'>";

        echo "<h3>Dokument / Bild $j</h3>";
        echo "<div class='bild-detail'>";
        Upload_Anzeige_Bestand($p_a, $neu, $j);
        echo "</div>";
        
        if (($p_a['ko'] ?? '') != '') {
            echo $forms->renderTextareaFieldFlex($p_a['ko']);
        }
        if (($p_a['f1'] ?? '') != '') {
            echo $forms->renderTextareaFieldFlex($p_a['f1']);
        }
        if (($p_a['f2'] ?? '') != '') {
            echo $forms->renderTextareaFieldFlex($p_a['f2']);
        }

       
        // $newRecord ist laut ArchEdit_ph0_inc.php TRUE bei einem BESTEHENDEN Datensatz
        // (ad_id !== 0) und FALSE bei echter Neuanlage (ad_id == 0) - invers zum Namen,
        // siehe dortiger Code. Neuanlage: Block immer sichtbar, kein Toggle nötig.
        // Bestehender Datensatz: Block standardmäßig versteckt, über Button einblendbar.
        $uploadBlockId = "upload-block_$j";
        if ($newRecord) {
            echo "<button type='button' class='upload-toggle-btn' aria-expanded='false' aria-controls='$uploadBlockId' onclick=\""
                . "var b=document.getElementById('$uploadBlockId');"
                . "var zu=(b.style.display==='none');"
                . "b.style.display=zu?'':'none';"
                . "this.setAttribute('aria-expanded', zu?'true':'false');"
                . "this.textContent=zu?'Datei ändern ausblenden':'Datei ändern / neu hochladen';"
                . "\">Datei ändern / neu hochladen</button>";
        }
        echo "<div id='$uploadBlockId'" . ($newRecord ? " style='display:none;'" : "") . ">";

        $zielFeld = $multi ? $p_a['bi'] . '[]' : $p_a['bi'];
        if ($multi) {
            // Mehrere Dateien: Container, JS hängt pro erfolgreichem Upload/Bibliotheks-Treffer
            // ein eigenes Hidden-Feld "bi[]" an.
            echo "<div id='upload-ergebnis_$j' data-field='" . htmlspecialchars($zielFeld, ENT_QUOTES) . "' data-multi='1'></div>";
        } else {
            // Genau 1 Datei: EIN Hidden-Feld, Vorbelegung = Altwert; JS setzt bei Erfolg direkt
            // .value neu, statt ein zweites Feld mit demselben name anzuhängen (sonst gewinnt
            // beim Submit je nach DOM-Reihenfolge der Altwert und überschreibt den neuen Namen).
                echo "<input type='hidden' id='upload-ergebnis_$j' data-field='" . htmlspecialchars($zielFeld, ENT_QUOTES) . "' data-multi='0' name='" . htmlspecialchars($p_a['bi'], ENT_QUOTES) . "' value='" . htmlspecialchars($neu[$p_a['bi']] ?? '', ENT_QUOTES) . "'>";
        }

        echo "<div class='foto-upd' style='margin-bottom:20px;border:1px solid #ccc;padding:10px;'>";
        echo "<h4>" . ($multi ? "Dateien hochladen (Mehrfachauswahl)" : "Datei hochladen") . " $j</h4>";

        // Bibliotheks-Auswahl nur anbieten, wenn für dieses Modul laut Pfad-Tabelle.md vorgesehen
        // (aktuell: ARC, INV, FZG-GER - NICHT für Medien selbst und NICHT für die
        // mandantenunabhängigen OEF-Module, siehe DOC_PATH_TABLE).
        $quelleOptionen = ['upload' => 'Datei neu hochladen'];
        if ($moduleConfig['allowLibrary']) {
            $quelleOptionen['library'] = 'Aus Bibliothek auswählen';
        }
        echo $forms->renderSelectFieldFlex(
            "upload_method_$j",
            $quelleOptionen,
            '', '', 'Quelle'
        );

        // --- Bibliotheks-Auswahl (Backend noch offen, siehe Kopf-Kommentar) --------------
        echo "<div id='sel_lib_suche$j' class='lib-such-block' style='display:none; margin-top:12px; padding:12px; border:1px solid #d0d7de; background:#fafafa; border-radius:6px;'>";
        echo "<div style='display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px;'>";
        echo "<div style='font-weight:600;'>Bild aus Bibliothek auswählen</div>";
        echo "<button type='button' class='lib-open-popup' data-slot='$j' style='padding:6px 12px; background:#2a5d8f; color:white; border:none; border-radius:4px; cursor:pointer;'>Auswahl öffnen</button>";
        echo "</div>";
        echo "<div id='auswahl-bild_$j' style='display:none; margin-top:12px; padding:10px; border:1px solid #d0d7de; background:#fff; border-radius:4px;'>";
        echo "<h5 style='margin:0 0 8px;'>Ausgewähltes Bild:</h5><div id='bild-vorschau-auswahl_$j'></div>";
        echo "<p style='margin:8px 0 0;'>Dateiname: <span id='dateiname-auswahl_$j'></span></p>";
        echo "</div>";
        echo "<div id='dialog-bilder_$j' class='lib-popup' style='display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:99999; align-items:center; justify-content:center;'>";
        echo "<div style='background:#fff; width:min(980px, 92vw); max-height:85vh; overflow:auto; border-radius:10px; padding:16px; box-shadow:0 20px 60px rgba(0,0,0,.3);'>";
        echo "<div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;'>";
        echo "<strong>Bildbibliothek</strong>";
        echo "<button type='button' class='lib-close-popup' data-slot='$j' style='border:1px solid #bbb; background:#fff; padding:5px 10px; cursor:pointer;'>Schließen</button>";
        echo "</div>";
        echo "<div id='bild-vorschau-dialog_$j' style='display:flex; flex-wrap:wrap; gap:12px; min-height:120px;'></div>";
        echo "<div id='dateiname-dialog_$j' style='margin-top:12px; font-size:12px; color:#444;'></div>";
        echo "<input type='hidden' id='bild-datei-dialog_$j'>";
        echo "</div>";
        echo "</div>";
        echo "</div>"; // sel_lib_suche

        // --- Neu hochladen ---------------------------------------------------------------
        echo "<div id='sel_lib_upload$j' class='upload-block'>";

        echo "<div id='meta-block_$j' class='meta-medien-block'>";
        if ($oefBild) {
            echo "<p class='hinweis-oef' style='margin:4px 0;font-size:90%;'>Für Bilder optional: ohne Urheber wird 'OEF' / 'Temporär' verwendet, ohne Aufnahmedatum das heutige Datum.</p>";
        }
        echo "<div id='urheber-auswahl_$j'>";
        AutoCompForm_Urheber('selection', "urh_$j", "urh_$j", '', "urh_id_$j", "urh_name_$j");
        echo "</div>";
        // Aufnahmedatum wird für JEDES Modul benötigt, sobald im Slot ein Foto/Audio/Video
        // hochgeladen wird (Medien-Bibliothek ist modulübergreifend fix, siehe
        // MEDIENBIB_TEMPLATE) - daher immer rendern; sichtbar wird der ganze meta-block
        // ohnehin nur, wenn tatsächlich eine Medien-Datei ausgewählt ist (siehe JS metaBlock).
        echo $forms->renderTextFieldFlex("aufn_dat_$j", 20, 'YYYYmmdd oder YYYYmmdd_xyz', "placeholder='YYYYmmdd_xyz' pattern='[A-Za-z0-9_]+' title='Nur Ziffern, Buchstaben und Unterstrich' oninput=\"this.value=this.value.replace(/[^A-Za-z0-9_]/g,'')\"");
        echo $forms->renderSelectFieldFlex("urheinfueg_$j", ['J' => 'Ja', 'N' => 'Nein'], '', '', 'Urheber ins Bild einfügen');
        echo "<input type='hidden' id='reSize_$j' value='800'>";
        echo "</div>"; // meta-block

        echo $forms->renderFileUploadFieldFlex("upload_file_$j", '', $multi ? 'multiple' : '');
        echo "<div id='preview_$j' style='margin:10px 0;'></div>";
        echo "<button type='button' id='upload_btn_$j' style='color:#cc0000'>Datei(en) hochladen</button>";
        echo "<span id='status_$j' style='margin-left:10px;'></span>";

        echo "</div>"; // sel_lib_upload
        echo "</div>"; // foto-upd
        echo "</div>"; // $uploadBlockId
        echo "</div>"; // bild-data
        echo "</fieldset></div>"; // block-container
    }

    echo "</div></div>";

    Upload_Form_JS($pic_cnt);
    
} // end Upload_Form

/**
 * Anzeige einer bereits hochgeladenen/gespeicherten Datei.
 */
function Upload_Anzeige_Bestand($p_a, $neu, $j)
{
    # var_dump($neu);
    # var_dump($p_a);
    # $dataBasePath = $p_a['udir']; 
    # var_dump($dataBasePath);
    /*
    if ($dataBasePath == '') {
        echo __FILE__. " " . __LINE__ . " Kein Speicherpfad angegeben.  Abbbruch. Bitte diese Zeile an den Admin Weitergeben. <br>";
    }
    */
    $biNam  = $p_a['bi'] ?? '';
 
    $wert = $neu[$biNam];
    $urhId = $neu['ei_id'];
    
    if ($wert == "") {
        echo "Kein Dokument/Bild vorhanden.<br>";
        return;
    }
    $ds_parts = pathinfo($wert);
    $ext = strtolower($ds_parts['extension'] ?? '');

    if (in_array($ext, GrafFiles)) {
        $fo_arr = explode('-', $wert);
        if (count($fo_arr) >= 3) {
            $urh = $fo_arr[0];
            $verz = $fo_arr[1];

            $adSubsgAnzeige = $urh . "/09/" . Upload_AdSubsgFuerExt($ext) . "/" . $verz ;
            # $p = ($p_a['udir'] ?? '') . $urh . "/09/" . $adSubsgAnzeige . "/" . $verz . $wert;
            # var_dump($adSubsgAnzeige);
            // echo buildPath('/FWHist/login/AOrd_Verz/', 'datei.txt', ['zusatz']) . "\n";
            $bPfad = buildPath($p_a['udir'], $wert, [$adSubsgAnzeige]) . "\n";
            // Ausgabe: /FWHist/login/AOrd_Verz/zusatz/datei.txt
            
            $p = $bPfad ;
            # var_dump($p);
            
            echo "<a href='$p' target='Bild $j' > <img src='$p' alt='$p' width='200'>  Groß  </a>";
            
            if ($fo_arr[2] == 'W') { // Wasserzeichen Urheber vorhanden - Pfad zum Urheber
                
            } else { // kein Wassserzeichen - Urheber- Info anictzeigen
                # $urh als mandantenInfo anfragen und anzeigen
                $mand = new DB_Mandanten();
                $eigent_arr = $mand->getEigentuemerData($urh);
                echo " Urheber: " . $eigent_arr['eig_name'] . ', ' .  $eigent_arr['eig_verant'] . " <br>" ;
            }

            if ($p_a['ko'] != '' ) {
                echo  $neu[$p_a['ko']] . "<br>";
            }
            if ($p_a['f1'] != '' ) {
                echo  $neu[$p_a['f1']] . "<br>";
            }
            if ($p_a['f2'] != '' ) {
                echo  $neu[$p_a['f2']] . "<br>";
            }
        } else {
            $p = ($p_a['udir'] ?? '') . $wert; // Altbestand - anderes Namensschema
            echo "<a href='$p' target='Bild $j' > <img src='$p' alt='$p' width='200'>  Groß  </a>";
        }
    
    } else {
    
        $p = ($p_a['udir'] ?? '') . $neu['ei_id'] . "/" . $neu['ad_sg'] . "/" .  $neu['ad_subsg'] . "/" . htmlspecialchars($wert, ENT_QUOTES); 
        echo "<a href='$p' target='Bild $j'>" . htmlspecialchars($wert, ENT_QUOTES) . "</a>";
    }
}

/**
 * Aufbau des Pfades, mit Unterscheidug Normalfall ( AOrd_Verz ist das letzte Element ) oder Sonderfall OEFFI, AOrd_Verz ist nicht das letzte Pfad- Element 
 * und das Bild steht nicht in der Foto- Bibliothek sonden im genannten Verzeichnis
 */
function buildPath(string $basePath, string $fileName, array $extensionParts = []): string {
    // Pfad normalisieren (Slashes vereinheitlichen) und in Segmente zerlegen
    $segments = array_filter(explode('/', str_replace('\\', '/', trim($basePath, '/'))));
    
    // Prüfen, ob das letzte Segment 'AOrd_Verz' ist
    if (end($segments) === 'AOrd_Verz') {
        // Normalfall: Pfad erweitern
        $segments = array_merge($segments, $extensionParts);
    }
    
    // Pfad zusammenbauen und Datei anhängen
    return '/' . implode('/', $segments) . '/' . ltrim($fileName, '/');
} // Ende Function buildPath

/**
 * Generisches JS: Vorschau/Rotation, dynamische Meta-Block-Anzeige nach Dateityp,
 * sequentieller Einzel-Datei-Upload (Variante A), Bibliotheks-Umschalter (Frontend).
 */
function Upload_Form_JS($pic_cnt)
{
    $grafFilesJs  = json_encode(GrafFiles);
    $audioFilesJs = json_encode(AudioFiles);
    $videoFilesJs = json_encode(VideoFiles);
    ?>
    <script>
    (function () {
        const GRAF = <?php echo $grafFilesJs; ?>;
        const AUDIO = <?php echo $audioFilesJs; ?>;
        const VIDEO = <?php echo $videoFilesJs; ?>;
        const bilder = {};

        // Liest den Wert eines Formularfelds unabhängig davon, ob es als Radio-Gruppe oder als
        // <select>/Hidden-Input gerendert wurde (renderSelectFieldFlex liefert ein Select bzw. ein
        // Custom-Element mit Hidden-Input, KEINE Radio-Buttons - der frühere Selektor
        // input[name=...]:checked lieferte dort immer null und damit fest 'N', d.h. nie ein
        // Wasserzeichen; Bugfix 2026-09-27).
        function leseFeldWert(name, fallback) {
            const radio = document.querySelector(`input[type="radio"][name="${name}"]:checked`);
            if (radio) return radio.value;
            const el = document.querySelector(`select[name="${name}"], input[name="${name}"]:not([type="radio"]), #${name}`);
            return (el && el.value !== undefined && el.value !== '') ? el.value : fallback;
        }
        // Archivordnung für Dokument-Pfade zum UPLOAD-ZEITPUNKT ermitteln. Bei einer Neu-Eingabe
        // ist $neu['ad_sg']/['ad_subsg'] beim Rendern der Seite noch leer (das Hidden-Feld #aOrd
        // bleibt dann leer), die Auswahl erfolgt erst danach im <fw-multi-select> der Seite
        // (config-id ARCHIVORDNUNG, siehe fw_multi_select.js: Hidden-Input im Light DOM mit dem
        // Wert der tiefsten gewählten Ebene). Daher hat der LIVE-Wert der Auswahl Vorrang, das
        // Hidden-Feld #aOrd (Vorbelegung aus der DB bei Updates) ist nur Fallback.
        function ermittleAOrd() {
            const ms = document.querySelector('fw-multi-select[config-id="ARCHIVORDNUNG" i]')
                    || document.querySelector('fw-multi-select[name="ad_archivwert"]')
                    || document.querySelector('fw-multi-select[config-id*="ARCHIV" i]');
            const live = ms ? (ms.querySelector('input[type="hidden"]')?.value || '') : '';
            return live !== '' ? live : (document.getElementById('aOrd')?.value || '');
        }
        // Zerlegt den Archivordnung-Wert in [ad_sg, ad_subsg]. Bekannte Formen: "01/02" (aus
        // $neu bei Updates), "01.02 04" (Composite, 1. Teil = Pfad) und "07 03 00 00"
        // (leerzeichengetrennt, wie im Kopfkommentar von fw_multi_select.js).
        function zerlegeAOrd(aOrd) {
            const roh = String(aOrd || '').trim();
            if (roh === '') return ['', ''];
            if (roh.includes('/')) { const t = roh.split('/').filter(Boolean); return [t[0] || '', t[1] || '']; }
            if (roh.includes('.')) { const t = roh.split(/\s+/)[0].split('.').filter(Boolean); return [t[0] || '', t[1] || '']; }
            const t = roh.split(/\s+/); return [t[0] || '', t[1] || ''];
        }
        // Beschreibung des Datensatzes (Archivalien: ad_beschreibg, Inventar: in_beschreibg) - wird
        // als md_beschreibg in die Medien-Verwaltung übernommen. Live aus dem Formular gelesen.
        function leseBeschreibung() {
            const el = document.querySelector('[name="ad_beschreibg"], [name="in_beschreibg"]');
            return el ? (el.value || '') : '';
        }
        // Sammlung des Datensatzes: das Feld heißt je Modul <präfix>_sammlg - ad (Archivalien),
        // in (Inventar), fz (Fahrzeuge/Geräte), später ac|av|az (PSA). Gesucht wird generisch nach
        // jedem Feld name="*_sammlg" mit einem der bekannten Präfixe; das erste NICHT leere gewinnt.
        // Neues Modul-Präfix: nur SAMMLG_PRAEFIX ergänzen. (md_sammlg der Medien-Verwaltung selbst
        // ist bewusst ausgenommen.) Gelesen wird zum Klick-Zeitpunkt, also der aktuelle Formularwert.
        const SAMMLG_PRAEFIX = ['ad', 'in', 'fz', 'ac', 'av', 'az'];
        let letztesSammlgFeld = '';
        // Feld gilt als Sammlungs-Feld, wenn NAME oder ID <präfix>_sammlg lautet (auch mit Endung
        // wie _1, [] oder [0]); nur input/textarea/select (bei select zählt der option-value = Kürzel).
        function sammlgFelder() {
            return Array.from(document.querySelectorAll('input[name*="sammlg" i], textarea[name*="sammlg" i], select[name*="sammlg" i], input[id*="sammlg" i], textarea[id*="sammlg" i], select[id*="sammlg" i]'));
        }
        function leseSammlung() {
            letztesSammlgFeld = '';
            const re = new RegExp('^(' + SAMMLG_PRAEFIX.join('|') + ')_sammlg(?:$|[_\\[\\d])', 'i');
            for (const el of sammlgFelder()) {
                const bez = [el.getAttribute('name') || '', el.id || ''].find(x => re.test(x));
                if (!bez) continue;
                const wert = String(el.value ?? '').trim();
                if (wert !== '') { letztesSammlgFeld = bez; return wert; }
            }
            return '';
        }
        // nur für ?debug=1: alle Felder, deren Name/ID "sammlg" enthält, samt aktuellem Wert
        function sammlgKandidaten() {
            return sammlgFelder().map(el => ({
                tag: el.tagName.toLowerCase(), name: el.getAttribute('name') || '', id: el.id || '',
                wert: String(el.value ?? ''), sichtbar: !!(el.offsetWidth || el.offsetHeight)
            }));
        }
        function extOf(f) { return f.split('.').pop().toLowerCase(); }
        function istMedienDatei(f) { const e = extOf(f); return GRAF.includes(e) || AUDIO.includes(e) || VIDEO.includes(e); }
        function istBild(f) { return GRAF.includes(extOf(f)); }
        // OEF-Bild: Ablage im OEF-Ordner (docPathTemplate), Urheber/Datum optional, kein _dmedien-Eintrag
        const OEF_BILD = (document.getElementById('oefBild')?.value === '1');
        function istOefBild(f) { return OEF_BILD && istBild(f); }

        // ad_subsg je Datei-Extension, nach Pfad-Tabelle.md (05=Audio, 06=Foto, 10=Video).
        // Muss mit der PHP-Funktion Upload_AdSubsgFuerExt() synchron bleiben - siehe dortiger
        // Kommentar zum offenen Widerspruch mit Speicher_Struktur.md (02/06/10).
        function adSubsgFuerExt(fname) {
            const e = extOf(fname);
            if (AUDIO.includes(e)) return '05';
            if (VIDEO.includes(e)) return '10';
            return '06';
        }

        // Zentraler Pfad-Aufbau im Frontend (Wunsch Medien_Verwaltung.md): $storPath wird hier
        // EINMAL aus dem passenden Template (medienBibTemplate für Foto/Audio/Video, sonst
        // docPathTemplate - siehe MEDIENBIB_TEMPLATE/DOC_PATH_TABLE in UploadUnifiedLib.php)
        // und den gesammelten Slot-Werten gebaut, statt die Einzelteile (ei_id, ad_sg, ad_subsg,
        // aufn_datum) dem Backend zu überlassen.
        function baueStorPath(template, werte) {
            return template.replace(/\{(\w+)\}/g, (m, key) => (werte[key] ?? ''));
        }

        // Reagiert auf Änderungen der Quelle (Upload/Bibliothek), egal ob das Feld als Radio,
        // <select> oder Custom-Element mit Hidden-Input gerendert wird: (1) delegierte
        // change/input-Events (auch durch Shadow DOM via composedPath), (2) zusätzlich ein leichter
        // Wert-Vergleich, falls ein Custom-Element seinen Hidden-Input nur per Script setzt und
        // dabei kein Event am Input auslöst.
        function registriereQuelleListener(j) {
            const name = 'upload_method_' + j;
            const pruefe = () => toggleGruppen(j);
            ['change', 'input'].forEach(ev => document.addEventListener(ev, e => {
                const pfad = e.composedPath ? e.composedPath() : [e.target];
                if (pfad.some(el => el && (el.name === name || el.id === name || (el.getAttribute && el.getAttribute('name') === name)))) {
                    pruefe();
                }
            }));
            let letzterWert = leseFeldWert(name, 'upload');
            setInterval(() => {
                const w = leseFeldWert(name, 'upload');
                if (w !== letzterWert) { letzterWert = w; pruefe(); }
            }, 400);
        }

        // --- Umschalten Bibliothek <-> Upload -------------------------------------------
        function toggleGruppen(j) {
            // Bugfix 2026-09-27: renderSelectFieldFlex liefert ein Select/Custom-Element, keine
            // Radio-Buttons - der frühere Selektor input[...][value="library"]:checked fand dort nie etwas.
            const isLib = leseFeldWert('upload_method_' + j, 'upload') === 'library';
            document.getElementById('sel_lib_suche' + j).style.display = isLib ? '' : 'none';
            document.getElementById('sel_lib_upload' + j).style.display = isLib ? 'none' : '';
            if (isLib) startBibliothekAjax(j);
        }

        // --- Thumbnails (SelPictThumbAPI.php: serverseitig verkleinert und gecacht) ---------------
        const THUMB_API = '../../../login/Services/API/SelPictThumbAPI.php';
        function esc(t) {
            return String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }
        function thumbUrl(b, breite) {
            return THUMB_API + '?urhNr=' + encodeURIComponent(b.urhNr) + '&aufnDat=' + encodeURIComponent(b.aufnDat || '')
                 + '&dsn=' + encodeURIComponent(b.dateiname) + '&w=' + breite;
        }
        // Vorschau-Kasten w x h; angefordert wird die doppelte Breite (scharf auf hochauflösenden Displays)
        function thumbHtml(b, w, h) {
            const anfrage = Math.min(800, w * 2);
            return `<div style="width:${w}px;height:${h}px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;">`
                 + `<img src="${thumbUrl(b, anfrage)}" alt="${esc(b.dateiname)}" loading="lazy" decoding="async" `
                 + `style="max-width:100%;max-height:100%;object-fit:contain;display:block;" `
                 + `onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'Kein Vorschaubild',style:'font-size:11px;color:#888;padding:4px;text-align:center'}))">`
                 + `</div>`;
        }

        function startBibliothekAjax(j) {
            // Fotos aus der Medien-Bibliothek ({urh_nr}_dmedien), gefiltert nach Urheber, Aufnahmedatum
            // und/oder Sammlung des Datensatz-Formulars (siehe SelPictLibAPI.php). Debug-Ausgabe im
            // Popup und in der Konsole: Seiten-URL mit ?debug=1 aufrufen.
            // Mit Sammlungs-Begriff (ad_sammlg/in_sammlg/fz_sammlg/...) werden ALLE {urh_nr}_dmedien-
            // Tabellen durchsucht - Urheber und Aufnahmedatum des Upload-Slots schränken dann NICHT ein.
            // Ohne Sammlung gilt Urheber und/oder Aufnahmedatum des Slots (Fallback).
            const sammlg  = leseSammlung();
            const urhNr   = sammlg === '' ? (document.getElementById('urh_' + j + '_id')?.value || '') : '';
            const aufnDat = sammlg === '' ? (document.getElementById('aufn_dat_' + j)?.value || '') : '';
            const debug   = new URLSearchParams(location.search).get('debug') === '1' ? '1' : '0';
            if (debug === '1') {
                console.log('SelPictLib JS v2026-09-29b, Slot ' + j, {
                    sammlg, urhNr, aufnDat, sammlgFeld: letztesSammlgFeld, sammlungsFelderImFormular: sammlgKandidaten()
                });
            }
            const dialog  = document.getElementById('dialog-bilder_' + j);
            const preview = document.getElementById('bild-vorschau-dialog_' + j);
            const auswahl = document.getElementById('auswahl-bild_' + j);
            if (preview) preview.innerHTML = '<div style="padding:12px; color:#666;">Bilder werden geladen...</div>';
            if (auswahl) auswahl.style.display = 'none';
            if (dialog) dialog.style.display = 'flex';

            if (urhNr === '' && aufnDat === '' && sammlg === '') {
                if (preview) preview.innerHTML = '<div style="padding:12px;border:1px dashed #ccc;background:#fff;color:#666;">'
                    + 'Kein Suchbegriff: bitte im Formular die Sammlung (Feld ' + SAMMLG_PRAEFIX.map(p => p + '_sammlg').join(' / ')
                    + ') eintragen oder im Upload-Block Urheber bzw. Aufnahmedatum angeben.</div>'
                    + (debug === '1' ? '<pre style="font-size:11px;background:#f6f6f6;padding:8px;overflow:auto;max-height:200px;">Gefundene Felder mit "sammlg":\n'
                        + JSON.stringify(sammlgKandidaten(), null, 2).replace(/</g, '&lt;') + '</pre>' : '');
                return;
            }
            const body = new URLSearchParams({ urhNr, aufnDat, sammlg, debug });
            fetch('../../../login/Services/API/SelPictLibAPI.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            })
            .then(r => r.text())
            .then(txt => {
                let daten;
                try { daten = JSON.parse(txt); }
                catch (e) { throw new Error('Keine gültige JSON-Antwort: ' + txt.slice(0, 300)); }
                if (debug === '1') console.log('SelPictLib DEBUG', { gesendet: Object.fromEntries(body), sammlgFeld: letztesSammlgFeld, antwort: daten });
                if (daten.status !== 'ok') { throw new Error(daten.message || 'Unbekannter Fehler'); }
                const files = Array.isArray(daten.files) ? daten.files : [];
                bilder[j] = files;
                let html = '<div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-start;">';
                if (!files.length) {
                    html += '<div style="padding:12px;border:1px dashed #ccc;background:#fff;color:#666;">Keine Bilder gefunden.</div>';
                } else {
                    files.forEach((b, idx) => {
                        html += `<div class="bild-item" data-index="${idx}" data-slot="${j}" title="${esc(b.beschreibung || b.dateiname)}" style="cursor:pointer;border:1px solid #cfcfcf;padding:8px;border-radius:4px;background:#fff;width:170px;">
                                    ${thumbHtml(b, 150, 112)}
                                    <div style="margin-top:6px;font-size:12px;word-break:break-word;">${esc(b.dateiname)}</div>
                                 </div>`;
                    });
                }
                html += '</div>';
                if (debug === '1' && daten.debug) {
                    html += '<pre style="margin-top:12px;font-size:11px;background:#f6f6f6;padding:8px;overflow:auto;max-height:240px;width:100%;">'
                          + JSON.stringify(daten.debug, null, 2).replace(/</g, '&lt;') + '</pre>';
                }
                if (preview) preview.innerHTML = html;
            })
            .catch(err => {
                if (preview) preview.innerHTML = '<div style="padding:12px;border:1px solid #d44;color:#a00;background:#fff3f3;">Bibliotheks-Abfrage fehlgeschlagen: '
                    + String(err.message || err).replace(/</g, '&lt;') + '</div>';
                console.error('Bibliotheks-Abfrage fehlgeschlagen:', err);
            });
        }

        document.addEventListener('click', function (e) {
            const popupOpen = e.target.closest('.lib-open-popup');
            if (popupOpen) {
                const j = popupOpen.dataset.slot;
                startBibliothekAjax(Number(j));
                return;
            }

            const popupClose = e.target.closest('.lib-close-popup');
            if (popupClose) {
                const j = popupClose.dataset.slot;
                const dialog = document.getElementById('dialog-bilder_' + j);
                if (dialog) dialog.style.display = 'none';
                return;
            }

            const item = e.target.closest('.bild-item');
            if (!item) return;
            const j = item.dataset.slot;
            const bild = bilder[j][item.dataset.index];
            const auswahl = document.getElementById('auswahl-bild_' + j);
            document.getElementById('dateiname-dialog_' + j).textContent = bild.dateiname;
            document.getElementById('bild-datei-dialog_' + j).value = bild.dateiname;
            document.getElementById('bild-vorschau-auswahl_' + j).innerHTML = thumbHtml(bild, 200, 150);
            document.getElementById('dateiname-auswahl_' + j).textContent = bild.dateiname;
            document.getElementById('dialog-bilder_' + j).style.display = 'none';
            if (auswahl) auswahl.style.display = 'block';

            // Ausgewählte Bibliotheks-Datei direkt als Ergebnis übernehmen (wie ein Upload-Treffer)
            const ergebnisEl = document.getElementById('upload-ergebnis_' + j);
            if (ergebnisEl.dataset.multi === '1') {
                ergebnisEl.innerHTML = '';
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = ergebnisEl.dataset.field;
                hidden.value = bild.dateiname;
                ergebnisEl.appendChild(hidden);
            } else {
                ergebnisEl.value = bild.dateiname;
            }
        });

        // --- Neu-Hochladen je Slot (Variante A: 1 Datei = 1 Request, sequentiell) --------
        function setupUploadBlock(j) {
            const fileInput = document.getElementById('upload_file_' + j);
            const previewDiv = document.getElementById('preview_' + j);
            const uploadBtn = document.getElementById('upload_btn_' + j);
            const statusSpan = document.getElementById('status_' + j);
            const metaBlock = document.getElementById('meta-block_' + j);
            if (!fileInput) return;

            let selectedFiles = [];
            let rotations = [];

            fileInput.addEventListener('change', function (e) {
                selectedFiles = Array.from(e.target.files);
                rotations = selectedFiles.map(() => 0);
                renderPreviews();
                if (metaBlock) {
                    metaBlock.style.display = selectedFiles.some(f => istMedienDatei(f.name)) ? '' : 'none';
                }
            });

            function renderPreviews() {
                previewDiv.innerHTML = '';
                selectedFiles.forEach((file, idx) => {
                    const row = document.createElement('div');
                    row.style.marginBottom = '8px';
                    row.appendChild(document.createTextNode(file.name + ' '));
                    if (istBild(file.name)) {
                        const l = document.createElement('button'); l.type = 'button'; l.textContent = '⟲';
                        const r = document.createElement('button'); r.type = 'button'; r.textContent = '⟳';
                        const holder = document.createElement('span');
                        l.onclick = () => { rotations[idx] = (rotations[idx] - 90) % 360; renderImage(file, rotations[idx], holder); };
                        r.onclick = () => { rotations[idx] = (rotations[idx] + 90) % 360; renderImage(file, rotations[idx], holder); };
                        row.append(l, r, holder);
                        renderImage(file, rotations[idx], holder);
                    } else {
                        const tag = document.createElement('em');
                        tag.textContent = '[' + extOf(file.name).toUpperCase() + ']';
                        row.appendChild(tag);
                    }
                    previewDiv.appendChild(row);
                });
            }

            function renderImage(file, rotation, holder) {
                const reader = new FileReader();
                reader.onload = e => {
                    holder.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '150px';
                    img.style.transform = 'rotate(' + rotation + 'deg)';
                    holder.appendChild(img);
                };
                reader.readAsDataURL(file);
            }

            uploadBtn.addEventListener('click', function () {
                if (!selectedFiles.length) { alert('Bitte mindestens eine Datei auswählen.'); return; }

                const medienBibTemplate = document.getElementById('medienBibTemplate')?.value || '{ei_id}/09/{ad_subsg}/{aufn_datum}/';
                const docPathTemplate = document.getElementById('docPathTemplate')?.value || '{ei_id}/{ad_sg}/{ad_subsg}/';

                const meta = {
                    urhNr: document.getElementById('urh_' + j + '_id')?.value || '',
                    // eigner = Mandant/Eigentümer für Dokumente, NICHT der Urheber pro Foto -
                    // kommt aus dem einmaligen, seitenweiten Session-Feld (siehe Kopf-Kommentar).
                    eigner: document.getElementById('eigner')?.value || '',
                    urhName: document.getElementById('urh_' + j + '_name')?.value || '',
                    aufnDat: document.getElementById('aufn_dat_' + j)?.value || '',
                    urhEinfgJa: ['J', 'JA', '1', 'TRUE'].includes(String(leseFeldWert('urheinfueg_' + j, 'N')).toUpperCase()) ? 'J' : 'N',
                    reSize: document.getElementById('reSize_' + j)?.value || '800',
                    aOrd: ermittleAOrd(),
                    beschreibg: leseBeschreibung(),
                    sammlg: leseSammlung(),
                    subMod: document.getElementById('subMod')?.value || ''
                };

                // Urheber-Nr. und Aufnahmedatum sind IMMER Pflicht, sobald ein Foto/Audio/Video
                // dabei ist - die Medien-Bibliothek (urh_id/09/ad_subsg/Datum) ist modulübergreifend
                // fix, siehe MEDIENBIB_TEMPLATE (Klarstellung Josef 2026-09-27: "ALLE Fotos ... in
                // die Foto-Lib ... denn genau dort werden die Fotos gesucht").
                // Ausnahme: Bilder in OEF-Modulen (Urheber/Datum optional, Defaults setzt die API).
                // OEF-Bilder: Defaults VOR der Pflichtprüfung setzen (Urheber-Nr. 'OEF', Name 'Temporär',
                // Aufnahmedatum = Tagesdatum YYYYmmdd), damit die Meldung nicht kommt. Nur wenn ALLE
                // Medien-Dateien der Auswahl OEF-Bilder sind; sonst bleibt die Pflichtprüfung aktiv.
                const medienDateien = selectedFiles.filter(f => istMedienDatei(f.name));
                if (OEF_BILD && medienDateien.length && medienDateien.every(f => istOefBild(f.name))) {
                    if (meta.urhNr === '')   { meta.urhNr = 'OEF'; }
                    if (meta.urhName === '') { meta.urhName = 'Temporär'; }
                    if (meta.aufnDat === '') {
                        const d = new Date();
                        meta.aufnDat = d.getFullYear() + String(d.getMonth() + 1).padStart(2, '0') + String(d.getDate()).padStart(2, '0');
                    }
                }
                const brauchtMeta = selectedFiles.some(f => istMedienDatei(f.name) && !istOefBild(f.name));
                if (brauchtMeta && (meta.urhNr === '' || meta.aufnDat === '')) {
                    alert('Urheber-Nr. und Aufnahmedatum sind für Foto/Audio/Video Pflichtfelder.');
                    return;
                }

                // aOrd ("01.02 04" o.ä.) liefert im ersten, Punkt-getrennten Teil die Pfad-Ebenen
                // ad_sg/ad_subsg für Dokumente (siehe Kopf-Kommentar, Sept-13-Notiz).
                const [adSgAusAOrd, adSubsgAusAOrd] = zerlegeAOrd(meta.aOrd);

                // Dokumente in Modulen mit Archivordnung-Pfad (docPathTemplate enthält {ad_sg}, z.B. ARC)
                // brauchen eine gewählte Archivordnung (mind. Sachgebiet + Untergruppe), sonst
                // landen sie im falschen/abgeschnittenen Verzeichnis.
                // Dokumente brauchen die Eigentümer-Nr. (ei_id) im Pfad - fehlt sie, würde der Pfad
                // stillschweigend um dieses Segment verkürzt (AOrd_Verz/01/01/ statt /531/01/01/).
                const brauchtEigner = docPathTemplate.includes('{ei_id}') && selectedFiles.some(f => !istMedienDatei(f.name));
                if (brauchtEigner && meta.eigner === '') {
                    alert('Die Eigentümer-Nr. (ei_id) für die Ablage von Dokumenten konnte nicht ermittelt werden - Upload abgebrochen, damit die Datei nicht im falschen Verzeichnis landet.');
                    return;
                }

                const brauchtAOrd = docPathTemplate.includes('{ad_sg}') && selectedFiles.some(f => !istMedienDatei(f.name));
                if (brauchtAOrd && (adSgAusAOrd === '' || adSubsgAusAOrd === '')) {
                    alert('Bitte zuerst die Archivordnung (mindestens Sachgebiet und Untergruppe) auswählen - Dokumente werden danach abgelegt.');
                    return;
                }

                statusSpan.textContent = 'wird hochgeladen ...';
                const ergebnisEl = document.getElementById('upload-ergebnis_' + j);
                const istMulti = ergebnisEl.dataset.multi === '1';
                if (istMulti) ergebnisEl.innerHTML = '';

                const warnungen = [];
                const fehler = [];
                uploadNext(0);

                function uploadNext(idx) {
                    if (idx >= selectedFiles.length) {
                        // Fehler/Hinweise NICHT überschreiben (früher wurden sie durch diesen Text sofort ersetzt)
                        statusSpan.textContent = 'Upload abgeschlossen.'
                            + (fehler.length ? ' Fehler: ' + fehler.join('; ') : '')
                            + (warnungen.length ? ' Hinweis: ' + warnungen.join('; ') : '');
                        return;
                    }
                    const file = selectedFiles[idx];
                    const rotation = rotations[idx] ? (rotations[idx] * -1) : 0;

                    // Foto/Audio/Video: IMMER Medien-Bibliothek (urh_id/09/ad_subsg/Datum),
                    // unabhängig vom Modul. Alles andere (Dokumente): modulabhängiger docPathTemplate
                    // (eigner/ad_sg/ad_subsg bei ARC, fixer Ordner bei OEF-*) - siehe DOC_PATH_TABLE.
                    const storPath = (istMedienDatei(file.name) && !istOefBild(file.name))
                        ? baueStorPath(medienBibTemplate, {
                            ei_id: meta.urhNr,
                            ad_subsg: adSubsgFuerExt(file.name),
                            aufn_datum: meta.aufnDat
                        })
                        : baueStorPath(docPathTemplate, {
                            ei_id: meta.eigner,
                            ad_sg: adSgAusAOrd || '',
                            ad_subsg: adSubsgAusAOrd || ''
                        });

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('rotation', rotation);
                    Object.entries(meta).forEach(([k, v]) => formData.append(k, v));
                    // Zentral im Frontend gebauter Ablagepfad (Wunsch Medien_Verwaltung.md); die
                    // Einzelteile oben bleiben zusätzlich erhalten, falls ein Aufrufer noch nicht
                    // auf storPath umgestellt ist.
                    formData.append('storPath', storPath);
                    const csrfToken = document.querySelector('.upload-csrf-token')?.value;
                    if (!csrfToken) {
                        fehler.push('Sicherheits-Token fehlt. Bitte Seite neu laden.');
                        statusSpan.textContent = 'Fehler: ' + fehler.join('; ');
                        return;
                    }
                    formData.append('csrf', csrfToken);

                    fetch('../../../login/Services/API/UploadAPI.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(response => {
                            if (response.errors && response.errors.length) {
                                fehler.push(...response.errors);
                                statusSpan.textContent = 'Fehler: ' + response.errors.join('; ');
                            } else if (response.files && response.files.length) {
                                if (istMulti) {
                                    const hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.name = ergebnisEl.dataset.field;
                                    hidden.value = response.files[0].filename;
                                    ergebnisEl.appendChild(hidden);
                                } else {
                                    ergebnisEl.value = response.files[0].filename;
                                }
                                statusSpan.textContent = `Datei ${idx + 1}/${selectedFiles.length} hochgeladen.`;
                                if (response.warnings && response.warnings.length) {
                                    warnungen.push(...response.warnings);
                                }
                            }
                            uploadNext(idx + 1);
                        })
                        .catch(err => {
                            statusSpan.textContent = 'Fehler beim Upload: ' + err;
                        });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            <?php for ($j = 1; $j <= $pic_cnt; $j++): ?>
            toggleGruppen(<?php echo $j; ?>);
            registriereQuelleListener(<?php echo $j; ?>);
            setupUploadBlock(<?php echo $j; ?>);
            <?php endfor; ?>
        });
    })();
    </script>
    <?php
}
