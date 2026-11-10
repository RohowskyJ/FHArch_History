# UploadUnifiedLib.php – Beschreibung und Aufrufkette

## Zweck

[`UploadUnifiedLib.php`](../FWHist/login/Services/UploadUnifiedLib.php) stellt einen
wiederverwendbaren Upload- und Dateianzeige-Block für die Bearbeitungsformulare der
Anwendung bereit. Der Block unterstützt:

- Anzeige bereits gespeicherter Bilder und Dokumente,
- Einzel- und Mehrfachauswahl von Dateien,
- lokale Bildvorschau und Drehung vor dem Upload,
- asynchronen Upload an `UploadAPI.php`,
- optional die Übernahme eines vorhandenen Bildes aus der Medien-Bibliothek,
- das Eintragen der ausgewählten bzw. hochgeladenen Dateinamen in Formularfelder.

Die PHP-Bibliothek rendert HTML und erzeugt dazu Inline-JavaScript. Sie ist selbst kein
eigenständiger Endpunkt: Das aufrufende Formular lädt die Bibliothek und ruft
`Upload_Form()` auf. Erst durch Benutzeraktionen werden die APIs im Unterverzeichnis
`Services/API` angesprochen.

## Einbindung und Aufrufer

Die Modul-Editseiten binden die Bibliothek per `require` ein. Die zugehörigen
`*_ph0_inc.php`-Formularteile bereiten anschließend die Slots vor und rufen
`Upload_Form($pic_cnt, $pct_arr, $newRec)` auf.

Im Repository sind diese Aufrufer vorhanden:

- Archivalien: [`ArchEdit.php`](../FWHist/login/Modules/Archivalien/ArchEdit.php) und
  [`ArchEdit_ph0_inc.php`](../FWHist/login/Modules/Archivalien/ArchEdit_ph0_inc.php)
- Fahrzeuge/Geräte: [`FAFEdit.php`](../FWHist/login/Modules/FahrzeugeGeraete/FAFEdit.php) und
  [`FaFEdit_ph0_inc.php`](../FWHist/login/Modules/FahrzeugeGeraete/FaFEdit_ph0_inc.php)
- Inventar: [`InvEdit.php`](../FWHist/login/Modules/Inventar/InvEdit.php) und
  [`InvEdit_ph0_inc.php`](../FWHist/login/Modules/Inventar/InvEdit_ph0_inc.php)
- Medien: [`MedVEdit.php`](../FWHist/login/Modules/Medien/MedVEdit.php),
  [`MedDEdit.php`](../FWHist/login/Modules/Medien/MedDEdit.php) und
  [`MedDEdit_ph0_inc.php`](../FWHist/login/Modules/Medien/MedDEdit_ph0_inc.php)
- Mitglieder/Ehrungen: [`MitglEhrgEdit.php`](../FWHist/login/Modules/Mitglieder/MitglEhrgEdit.php)
  und [`MitglEhrgEdit_ph0.inc.php`](../FWHist/login/Modules/Mitglieder/MitglEhrgEdit_ph0.inc.php)
- Öffentlichkeitsarbeit: Buch, Dokumente, Marktplatz, Museen, Presse und Termine;
  die jeweiligen `*Edit.php`- und `*Edit_ph0_inc.php`-Dateien liegen unter
  [`FWHist/login/Modules/Oeffentlichkeitsarbeit/`](../FWHist/login/Modules/Oeffentlichkeitsarbeit/)

Beim Aufruf müssen die im Bibliothekskopf dokumentierten globalen Werte verfügbar sein:

| Variable | Verwendung |
| --- | --- |
| `$forms` | Instanz von `FormRendererFlex`; rendert Text-, Auswahl-, Datei- und Textarea-Felder. |
| `$neu` | Aktuelle Feldwerte, einschließlich vorhandener Dateinamen und Archivordnungsdaten. |
| `$module` | Modulkennung für Modulkonfiguration und API-Kontext. |
| `$sub_mod` | Optionale Untermodulkennung; insbesondere für OEF relevant. |
| `$editProtect` | Optional; `true` schaltet auf reine Bestandsanzeige um. |
| `$systemBasePath` | Wird von den einbindenden Editseiten zum Auflösen des Bibliothekspfads verwendet. |

`Upload_Form()` erwartet die Anzahl der Slots, ein Array mit Slot-Konfigurationen und
einen booleschen Wert für die Sichtbarkeit des Änderungs-/Uploadbereichs:

```php
Upload_Form($pic_cnt, $pct_arr, $newRec);
```

Die Slot-Konfigurationen sind nullbasiert; im HTML und JavaScript wird die Slotnummer
einsbasiert ausgegeben (`$j = $i + 1`).

| Schlüssel in `$pct_arr[$i]` | Bedeutung |
| --- | --- |
| `bi` | Name des Zielfeldes mit dem Dateinamen. Bei `multi => true` wird das Feld als Array (`bi[]`) übertragen. |
| `udir` | Basis-Webpfad zur Anzeige bestehender Dateien. |
| `ko` | Optionaler Feldname einer Kommentar-Textarea. |
| `f1`, `f2` | Optionale zusätzliche Textarea-Felder. |
| `multi` | Optional; `true` erlaubt mehrere Dateien in einem Slot. Ohne den Wert gilt Einzeldatei-Verhalten. |

In den vorhandenen Formularteilen werden beispielsweise `ad_doc_1` bis `ad_doc_4`,
`in_foto_*` oder `pr_bild_*` als Zielfelder verwendet.

### Sichtbarkeitsmodi

- Ist `$editProtect` wahr, werden nur Slots mit vorhandenem Dateinamen angezeigt.
  Upload-Steuerungen und das Upload-JavaScript werden in diesem Zweig nicht ausgegeben.
  Gibt es keine vorhandene Datei, erscheint „Keine Bilder vorhanden.“
- Im normalen Modus wird je Slot Bestand plus Upload-/Bibliotheksbereich gerendert.
  Ist `$newRecord` wahr, ist der Änderungsbereich zunächst verborgen und kann über
  „Datei ändern / neu hochladen“ eingeblendet werden. Ist `$newRecord` falsch, wird
  dieser Bereich direkt angezeigt.

## PHP-Aufbau der Bibliothek

### Pfad- und Modulkonfiguration

`MEDIENBIB_TEMPLATE` definiert den gemeinsamen Ablagepfad von normalen Foto-, Audio-
und Video-Uploads:

```text
{ei_id}/09/{ad_subsg}/{aufn_datum}/
```

`DOC_PATH_TABLE` konfiguriert den Dokumentenpfad und die Verfügbarkeit der
Bibliotheksauswahl pro Modul. `Upload_ResolveModuleConfig($module, $subMod)` löst
Modulnamen auf:

- Nur bei `OEF` wird die Untermodulkennung zur Auswahl des Tabelleneintrags verwendet
  (`OEF-BU`, `OEF-DO` usw.).
- Bei anderen Modulen bestimmt allein `$module` den Eintrag; dort ist `$sub_mod`
  lediglich zusätzlicher Seiten-/Formularkontext.
- `FZG` wird als Alias für `FZG-GER` behandelt.
- Unbekannte Module und Einträge ohne expliziten Dokumentenpfad erhalten als
  Dokumenten-Fallback `{ei_id}/{ad_sg}/{ad_subsg}/`.
- Die Einstellung `allowLibrary` steuert getrennt davon, ob die Quelle
  „Aus Bibliothek auswählen“ angeboten wird.

| Modulschlüssel | Dokumentenpfad | Bibliotheksauswahl |
| --- | --- | --- |
| `ARC` | `{ei_id}/{ad_sg}/{ad_subsg}/` | Ja |
| `INV` | generischer Dokumenten-Fallback | Ja |
| `MEDIA` | generischer Dokumenten-Fallback | Nein |
| `FZG-GER` (auch Alias `FZG`) | generischer Dokumenten-Fallback | Ja |
| `OEF-BU` | `Buch/` | Nein |
| `OEF-DO` | `Dokumente/` | Nein |
| `OEF-MP` | `Marktplatz/` | Nein |
| `OEF-MU` | `Museen/` | Nein |
| `OEF-PR` | `Presse/` | Nein |
| `OEF-TE` | `Termine/` | Nein |

Die Medienuntergruppen ermittelt `Upload_AdSubsgFuerExt()` anhand der Konstanten aus
[`ConstLib.php`](../FWHist/login/Services/ConstLib.php): Audio = `05`, Foto = `06`,
Video = `10`. Alle übrigen unterstützten Dateitypen gelten für die Pfadentscheidung
als Dokumente.

### Rendern des Formulars

Im normalen Modus ermittelt `Upload_Form()` die für die Ablage benötigten Werte:

- **Eigentümer (`eigner`)**: zuerst aus
  `$_SESSION['BS_Prim']['Env']['mand_id']`, dann aus `$_GET['mand_id']`, schließlich
  aus `$neu['ei_id']`.
- **Archivordnung (`aOrd`)**: aus `$neu['ad_sg']` und `$neu['ad_subsg']`, sofern beide
  vorhanden sind. Für eine neue Archivalie kann der JavaScript-Teil später den
  aktuellen Wert des Archivordnungs-Multiselects auslesen.
- **Modulkonfiguration**: aus `$module` und gegebenenfalls `$sub_mod`.

Diese Werte und Templates werden als Hidden-Felder in das HTML geschrieben. Wichtig:
`eigner` ist der Eigentümer des Datensatzes und damit für Dokumentenpfade relevant.
Er ist **nicht** der Urheber eines Fotos. Für normale Medien-Dateien wird der
Urheber des jeweiligen Slots verwendet.

Je Slot rendert die Bibliothek:

1. die vorhandene Datei über `Upload_Anzeige_Bestand()`,
2. optionale Textareas aus `ko`, `f1` und `f2`,
3. ein Ergebnisfeld für den Dateinamen (ein Hidden-Feld bei Einzeldatei, ein
   Container mit mehreren Hidden-Feldern bei Mehrfachdatei),
4. eine Quellenwahl und bei erlaubten Modulen die Bibliotheksauswahl,
5. Urheber-, Aufnahmedatum-, Wasserzeichen- und Dateiauswahlfelder sowie
   Vorschau, Uploadbutton und Statusanzeige.

`AutoCompForm_Urheber()` aus
[`CommFuncsLib.php`](../FWHist/login/Services/CommFuncsLib.php) erzeugt das
Urheber-Eingabefeld samt Hidden-Feldern. Das Upload-JavaScript liest die Urheber-ID
und den Urheber-Namen über die DOM-IDs `urh_{j}_id` und `urh_{j}_name`; beim
Formular-Submit werden die Hidden-Werte über ihre `name`-Attribute übertragen.

`Upload_Anzeige_Bestand()` zeigt Bilddateien als verlinkte Vorschau und Dokumente
als Dateilink. Neu benannte Medienbilder werden über den Dateinamen und die
Medien-Untergruppe zum Ablagepfad zusammengesetzt. Bei nicht entsprechend benanntem
Altbestand wird der Pfad direkt aus `udir` und Dateiname gebildet. Für Bilder ohne
Wasserzeichen wird zusätzlich versucht, Urheberinformationen über
`DB_Mandanten::getEigentuemerData()` aus
[`DB_Mandanten`](../FWHist/login/Modules/Mandanten/API/DB_Mandanten.php) anzuzeigen.

`buildPath()` ergänzt Medienpfadsegmente nur, wenn `udir` mit `AOrd_Verz` endet.
Das berücksichtigt, dass OEF-Dateien in anderen Unterverzeichnissen liegen können.

## Browser-JavaScript und Uploadablauf

`Upload_Form()` hängt am Ende `Upload_Form_JS($pic_cnt)` an. Die Funktion gibt
JavaScript aus, das für jeden Slot eine Upload-Steuerung initialisiert. Dateitypen
werden dabei mit den von PHP in JavaScript übertragenen Listen `GrafFiles`,
`AudioFiles` und `VideoFiles` erkannt.

### Dateiauswahl und Vorschau

- Beim Auswählen werden die Dateien im Browser gesammelt und Bilddateien mit
  `FileReader` als Vorschau gezeigt.
- Für Bilddateien kann die Vorschau in 90-Grad-Schritten gedreht werden. Die
  Drehung wird anschließend als Upload-Parameter übergeben.
- Der Metadatenblock wird nur eingeblendet, wenn mindestens eine Foto-, Audio- oder
  Videodatei gewählt ist.
- Mehrere ausgewählte Dateien werden **nacheinander** übertragen: pro Datei ein
  eigener Request. Im Ergebnisfeld eines Mehrfachslots wird je erfolgreicher Datei
  ein Hidden-Feld angehängt.

### Metadaten und Pfadbildung

Vor dem Upload liest JavaScript unter anderem Urheber-ID und -Name, Aufnahmedatum,
Wasserzeichenwunsch, Datensatzbeschreibung, Sammlung, Archivordnung, Eigentümer und
Modulkontext aus dem Formular. Die Archivordnung wird bevorzugt live aus dem
Archivordnungs-Multiselect gelesen; das gerenderte Hidden-Feld ist der Fallback.
`zerlegeAOrd()` akzeptiert die im Code vorgesehenen Darstellungen mit `/`, `.`
oder Leerzeichen und ermittelt daraus Sachgebiet und Untergruppe.

Die Ablageregel pro Datei lautet:

1. Foto/Audio/Video außerhalb von OEF-Bildslots: Medienpfad
   `urheber/09/{05|06|10}/aufnahmedatum/`.
2. Sonstige Dateien: Dokumentenpfad des Moduls aus `docPathTemplate`.
3. Bilder in OEF-Modulen: OEF-Dokumentenpfad statt Medienbibliothek. Für diese
   OEF-Bilder sind Urheber und Aufnahmedatum optional; fehlen sie, werden `OEF`,
   `Temporär` und das Tagesdatum als Metadaten-Defaults verwendet.

Vor einem normalen Medienupload bricht das Frontend ab, wenn Urheber-ID oder
Aufnahmedatum fehlen. Bei Dokumentenpfaden, die `{ei_id}` enthalten, wird ein
ermittelbarer Eigentümer verlangt. Pfade mit `{ad_sg}` benötigen Sachgebiet und
Untergruppe. Diese Prüfungen sollen verhindern, dass Dateien in unvollständige oder
falsche Verzeichnisse geschrieben werden.

### Uploadrequest und Dateinamenergebnis

Das JavaScript erstellt pro Datei ein `FormData`-Objekt mit `file`, Drehwinkel,
Metadaten und dem zentral gebildeten `storPath`. Der Request geht per `fetch()` an
[`UploadAPI.php`](../FWHist/login/Services/API/UploadAPI.php).

Bei Erfolg übernimmt das Frontend `response.files[0].filename` in das
Ergebnisfeld des Slots. Bei Einzelslots wird der Wert des vorhandenen Hidden-Feldes
überschrieben; bei Mehrfachslots wird ein Hidden-Feld pro Datei angelegt. Das
reguläre Formular übermittelt diese Namen später zusammen mit den übrigen
Datensatzfeldern. Der Datei-Upload ist also ein eigener AJAX-Schritt vor dem
regulären Formular-Submit.

Fehler und Warnungen der API werden im Statusbereich angezeigt. Ein Netzwerk- oder
JSON-Fehler wird als Uploadfehler im Statusbereich ausgegeben.

## Aufgerufene und eingebundene weitere Scripts

### `Services/API/UploadAPI.php` – Speichern und Verarbeiten

Diese API wird beim Klick auf „Datei(en) hochladen“ per `POST` aufgerufen. Sie:

- nimmt die Datei in `AOrd_Verz/VF_Upload/` entgegen,
- prüft Uploadstatus, Dateiendung und die Größenobergrenze von 40 MB,
- bereinigt den Dateinamen (unter anderem Umlaute, Leer- und Bindestriche),
- löst den vom Browser gesendeten `storPath` zu einem Verzeichnis unterhalb von
  `AOrd_Verz` auf und bereinigt dessen Verzeichnissegmente,
- verarbeitet Bilder gesondert; Audio, Video und Dokumente werden in das
  berechnete Zielverzeichnis verschoben,
- gibt JSON mit `status`, `files`, `errors` und `warnings` zurück.

Bei Bildern ruft `handleImageSpecial()` die Funktionen aus
[`FotoFuncs.php`](../FWHist/login/Services/FotoFuncs.php) auf. Das Bild wird rotiert,
proportional auf maximal 800 × 800 Pixel verkleinert (kleinere Bilder werden nicht
hochskaliert), als JPEG gespeichert und optional mit dem Urhebertext versehen.
Der Ausgabename erhält bei einem neuen Namen ein Urheber-/Datum-/Wasserzeichen-
Schema; die Bildausgabe wird mit `.jpg` gespeichert.

`UploadAPI.php` bindet außerdem ein:

- [`CommFuncsLib.php`](../FWHist/login/Services/CommFuncsLib.php) für gemeinsame
  Anwendungsfunktionen,
- [`ConstLib.php`](../FWHist/login/Services/ConstLib.php) für die erlaubten
  Erweiterungslisten,
- [`FotoFuncs.php`](../FWHist/login/Services/FotoFuncs.php) für die
  Bildverarbeitung,
- [`MedienDbLib.php`](../FWHist/login/Services/MedienDbLib.php) für die Registrierung
  eines Medienuploads.

Für Foto-/Audio-/Video-Uploads außerhalb des Medienmoduls und außerhalb der
OEF-Bildausnahme versucht die API nach erfolgreicher Ablage, einen Verzeichnis- und
Detaildatensatz in `{urh_nr}_dmedien` zu schreiben. Ein Fehler bei dieser
Registrierung macht den Datei-Upload nicht rückgängig, sondern wird als Warnung
zurückgegeben. `MedienDbLib.php` verwendet hierfür `DB_GenericLog` aus
[`Database/DB_GenericLog.php`](../FWHist/login/Database/DB_GenericLog.php) und
Prepared Statements.

### `Services/API/SelPictLibAPI.php` – Suche in der Medien-Bibliothek

Diese API wird beim Öffnen der Bildbibliothek per `POST` aufgerufen, falls die
Quellenauswahl im Modul die Bibliothek erlaubt. Das Frontend übergibt Urheber,
Aufnahmedatum, Sammlung und optional den Debug-Schalter.

Die API liest passende `*_dmedien`-Tabellen. Mit Urheber wird die Suche auf dessen
Tabelle begrenzt; ohne Urheber können Tabellen durchsucht werden. Aufnahmedatum und
Sammlung schränken die Treffer ein. Mehrere Sammlungsbegriffe, getrennt durch
Komma, Semikolon oder Zeilenumbruch, werden als alternative Suchbegriffe
interpretiert. Die Bibliothek zeigt nur Dateien mit einer Bild-Endung aus `GrafFiles`.
Je Tabelle werden höchstens 500 aktuelle Treffer betrachtet.

Die Antwort ist JSON mit `status` und `files`; bei Fehlern enthält sie `message`.
Jeder Treffer enthält unter anderem Dateiname, Urhebernummer, Aufnahmedatum,
Beschreibung und Pfadangaben. Bei `?debug=1` enthält die Antwort zusätzliche
Diagnosedaten.

Die API bindet [`ConstLib.php`](../FWHist/login/Services/ConstLib.php) und
[`MedienDbLib.php`](../FWHist/login/Services/MedienDbLib.php) ein. Letztere stellt
`medienGetPdo()` für den Datenbankzugriff bereit. Die ausgewählte Datei wird nicht
erneut hochgeladen: Ihr Dateiname wird direkt in das Ergebnisfeld des Formularslots
übernommen und danach mit dem normalen Formular gespeichert.

### `Services/API/SelPictThumbAPI.php` – Vorschaubilder

Für die Trefferliste und die gewählte Datei erzeugt das Frontend Bild-URLs auf
[`SelPictThumbAPI.php`](../FWHist/login/Services/API/SelPictThumbAPI.php). Die API
erhält Urhebernummer, Aufnahmedatum, Dateiname und gewünschte Breite als `GET`-
Parameter. Sie sucht das Original im Foto-Bibliothekspfad, erzeugt bei Bedarf mit
GD ein verkleinertes JPEG und cached es unter `AOrd_Verz/Thumbs/`. Sie liefert
direkt Bilddaten statt JSON. Fehler führen zu HTTP-Fehlercodes; das Frontend ersetzt
ein fehlendes Vorschaubild durch einen Platzhalter.

Die API nutzt `GrafFiles` aus `ConstLib.php`, prüft Dateiname und Bildformat und
stellt sicher, dass die Quelldatei innerhalb von `AOrd_Verz` liegt.

## Abgrenzung und Betriebshinweise

- `UploadUnifiedLib.php` enthält keine eigene DB-Persistenz für das Formularfeld;
  es befüllt die Dateiname-Felder. Die jeweilige Editseite verarbeitet später den
  regulären Formular-Submit.
- Der Datei-Upload über `UploadAPI.php` erfolgt bereits vor diesem Submit. Ein
  späteres Abbrechen des Formulars entfernt die hochgeladene Datei nicht automatisch.
- Der Bibliotheksauswahl-Endpunkt ist in der aktuellen Implementierung vorhanden
  und wird von der Oberfläche angesprochen. Die älteren Kommentarzeilen in der
  Bibliothek, die das Backend als „offen“ bezeichnen, sind daher nicht mehr
  zutreffend.
- Die Upload-API protokolliert Debug- und PHP-Fehler unter
  `AOrd_Verz/Logging/Upload_debug.log.txt` und
  `AOrd_Verz/Logging/UploadAPI_php-error.log.txt`. Die Bibliotheks- und Thumbnail-
  APIs verwenden eigene Logdateien unter `AOrd_Verz/Logging/`.
- Die Werte für zulässige Endungen liegen zentral in `ConstLib.php`; Änderungen
  an Dateitypen müssen mit Frontend-Erkennung, Upload-API und Bildverarbeitung
  zusammenpassen.
