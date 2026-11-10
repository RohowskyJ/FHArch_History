# Listen und geschützte Bearbeitung: Mitglieder, Öffentlichkeitsarbeit und PSA

## Ziel

Listen aus `Oeffentlichkeitsarbeit` und `Psa` sollen gegebenenfalls auch ohne
Login über die externe Website lesbar sein. Mitglieder-Ehrungen bleiben
rollenbeschränkt. Die Anzeige einer Liste ist getrennt von der Berechtigung,
Datensätze zu ändern:

| Benutzer | Liste ansehen | Neu anlegen / bearbeiten |
|---|---:|---:|
| Nicht angemeldet, öffentlicher Aufruf | Ja, sofern die Liste öffentlich sein soll | Nein |
| Angemeldet, ohne passende Modulberechtigung | Ja, sofern der Seitenzugriff erlaubt ist | Nein |
| Angemeldet, mit passender Modulberechtigung | Ja | Ja |

`$Zugr = 'Alle'` kann den öffentlichen **Lesezugriff** erlauben. Es darf nicht
als Schreibberechtigung verwendet werden. Die Schreibberechtigung muss stets
separat aus Anmeldung und Modulrolle bestimmt werden.

## Grundregel: Lesen und Schreiben getrennt bestimmen

In jedem Listen-Controller sind zwei unabhängige Entscheidungen erforderlich:

1. **Darf diese Anfrage die Liste lesen?** Für den öffentlichen Einstieg kann
   die bestehende Regel `userBerechtigtOK('Alle')` verwendet werden. Für
   geschützte Aufrufe gilt die zugehörige Modulberechtigung.
2. **Darf dieser Benutzer bearbeiten?** Das setzt immer einen angemeldeten
   Benutzer und die konkrete Modulberechtigung voraus.

Beispiel für die Schreibberechtigung:

```php
$isLoggedIn = !empty($_SESSION['BS_Prim']['BE']);
$editPermission = 'ADM-OEF'; // bei PSA z. B. ADM-PSA
$canEdit = $isLoggedIn && userHasRole($editPermission);
```

`userBerechtigtOK()` muss bei einer fehlenden Rolle `false` liefern. Ein
angemeldeter Benutzer ist nicht automatisch schreibberechtigt. Für PSA ist die
einheitliche Schreibrolle `ADM-PSA`; `PSA-ADM` ist keine gültige PSA-Rolle.
Für Öffentlichkeitsarbeit ist die einheitliche Schreibrolle `ADM-OEF`.

Die Berechtigung für das Anzeigen wird davon unabhängig anhand des vorhandenen
öffentlichen Einstiegs geprüft. Falls die Anwendung dafür etwa
`$_SESSION['BS_Prim']['Mod']['smod'] === 'index'` verwendet, muss dieses
Merkmal im jeweiligen Aufruf tatsächlich gesetzt sein. Nicht allein aus dem
Vorhandensein einer Session ableiten, ob ein Request öffentlich ist.

Die aktuellen PSA-Listen und Editoren verwenden `ADM-PSA`. Die Legacy-Scripte
unter `Psa/oris` sind von dieser Umsetzung ausgenommen.

## Listen-Controller und Listen-Konfiguration

### Zugriff auf die HTML-Seite

Der Listen-Controller soll den öffentlichen Lesezugriff gezielt zulassen und
alle nicht erlaubten Aufrufe abbrechen. Ein Redirect-Header ohne `exit` ist
kein Abbruch: der Controller rendert danach möglicherweise trotzdem die
geschützte Seite.

### Aktionen nur für Schreibberechtigte anzeigen

Die neue-Datensatz-Aktion und die Aktionsspalte müssen an `$canEdit` hängen,
nicht nur daran, ob ein Benutzer eingeloggt ist:

```php
$newRecordUrl = $canEdit ? 'DokuEdit.php?ID=0' : '';

$lists = [[
    'editScript' => $newRecordUrl,
    'actionScript' => 'DokuEdit.php',
    'showActionColumn' => $canEdit,
]];
```

Die konkreten Werte und übrigen Listeneinstellungen bleiben die der jeweiligen
Liste. Bei einer Liste, die für nicht berechtigte Benutzer einen
Detail-/Anzeigelink anbieten soll, kann eine eigene Anzeigeaktion bestehen
bleiben; dieser Link muss dann einen Editor im Nur-Lese-Modus öffnen. Er darf
nicht durch einen Edit-Link ersetzt werden.

`showActionColumn` und `editScript` steuern nur die Oberfläche. Ein Link kann
direkt aufgerufen oder ein Request manuell erstellt werden. Die Edit-Scripts
müssen deshalb unabhängig davon serverseitig schützen.

### `edit_Protect` zur Listen-API

Die Listenklasse kann `editProtect` verwenden, um Edit-Links oder Felder in
der Tabellenantwort wegzulassen. Für die Anzeige ist daher typischerweise
`edit_Protect = !$canEdit` zu setzen. Der Wert aus dem Browser darf aber nicht
als Berechtigungsnachweis gelten: Ein Client kann Query-Parameter wie
`edit_Protect=false` selbst setzen. Die API muss die Schreibberechtigung aus
der Session und der Modulrolle ableiten beziehungsweise das Flag serverseitig
überschreiben.

Die Daten-API muss öffentlichen Lesezugriff nur für ausdrücklich öffentliche
Listen erlauben. Nicht öffentliche List-IDs benötigen weiterhin eine
serverseitige Anmeldung beziehungsweise Rollenprüfung. Prüfen, ob die Liste
über `Services/API/ListHostAPI.php` oder über eine modul-eigene API aufgerufen
wird; beide API-Arten sind separate Zugriffspunkte.

## Edit-Controller und Formular-Include

### Schreibrequest vor jeder Änderung ablehnen

Jedes Edit-Script muss vor dem Verarbeiten eines POSTs mit Speichern-Phase
`phase=1` prüfen, ob `$canEdit` wahr ist:

```php
if (!$canEdit && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && (int)($_POST['phase'] ?? 0) === 1
) {
    http_response_code(403);
    exit('Änderungen sind nicht erlaubt.');
}
```

Diese Prüfung gehört in den Controller vor die Verzweigung, die POST-Daten
übernimmt, Dateien verarbeitet oder `insert`/`update` aufruft. Nur den
Speichern-Button auszublenden schützt nicht vor direktem POST.

### Schreibgeschützte Anzeige

Ein öffentlicher Aufruf des Edit-Scripts kann für existierende Datensätze
erlaubt sein, sofern das fachlich gewünscht ist. Dann muss das Formular-Include
`$editProtect = !$canEdit` an `FormRendererFlex` weitergeben. Ohne
Schreibberechtigung:

- Felder nur lesbar ausgeben,
- Speichern-Button und Eingabe-/Upload-Steuerungen ausblenden,
- Aktionen wie Löschen, Statuswechsel oder Autocomplete-Übernahme verhindern,
- das Formular nicht für einen neuen Datensatz (`ID=0`) öffnen.

Beim Aufruf eines neuen Datensatzes ohne `$canEdit` den Request mit HTTP 403
ablehnen oder zur öffentlichen Liste zurückführen. Kein leeres, scheinbar
editierbares Formular anzeigen.

### Sitzung und Audit-Felder

Auch öffentliche GET-Requests benötigen für optionale Benutzerwerte sichere
Fallbacks, zum Beispiel:

```php
$userId = $_SESSION['BS_Prim']['BE']['be_id'] ?? null;
```

Audit-Felder und `$_FILES`-Verarbeitung dürfen nicht voraussetzen, dass jeder
Besucher angemeldet ist. Der Schutz der POST-Verarbeitung muss verhindern,
dass ein öffentlicher Benutzer bis zu Code gelangt, der eine Benutzer-ID oder
eine Schreibberechtigung voraussetzt.

## Häufige Ursachen, warum die Berechtigung nicht funktioniert

- `$Zugr = 'Alle'` wird auch beim Editor oder beim Speichern verwendet und
  damit fälschlich als Schreibfreigabe interpretiert.
- Die Aktionsspalte wird nur mit `$isLoggedIn` gesteuert. Ein angemeldeter
  Benutzer ohne Modulrolle erhält so trotzdem einen Edit-Link.
- `$editProtect` wird in der HTML-Konfiguration gesetzt, aber mit
  `edit_Protect => false` an die API weitergegeben.
- Die API vertraut dem vom Browser übergebenen `edit_Protect` oder
  Benutzerparameter.
- Das Formular-Include blendet den Speichern-Button aus, aber der Controller
  verarbeitet `phase=1` trotzdem.
- Die Berechtigungsprüfung steht nur in einem Include, das erst nach der
  Datenverarbeitung geladen wird.
- Ein Redirect bei fehlender Berechtigung wird ohne `exit` ausgeführt.
- Der Editor fragt eine andere Rollenkennung ab als die Liste oder die
  hinterlegte Benutzerrolle.
- `$_SESSION['BS_Prim']['BE']['be_id']` wird in einer öffentlichen Anfrage
  ohne Fallback gelesen.
- Die Tabellenklasse liefert eigene HTML-Aktionslinks im Datensatz. Diese
  Links müssen ebenfalls anhand des serverseitig berechneten
  Schreibschutz-Flags weggelassen oder zu einer Nur-Lese-Ansicht gemacht
  werden.

## Betroffene Dateien

Die folgenden Dateien sind die aktuell ermittelten PHP-Dateien der beiden
Modulbereiche. Bei einer vollständigen Umstellung zuerst Listen-Controller und
deren API-Zugriffe, danach alle zugehörigen Edit-Controller und Formular-
Includes prüfen. Weitere Datenbankklassen und Templates können Teil desselben
Flows sein, sind aber keine Ersatzstelle für die Zugriffskontrollen.

### Öffentlichkeitsarbeit

**Listen-Controller**

- `ArLinkList.php`
- `BuchList.php`
- `DokuList.php`
- `MarktplList.php`
- `MuseenList.php`
- `PresseList.php`
- `TerminList.php`
- `ZeitungList.php`
- `Z_InhalteList.php`

**Edit-Controller und Formular-Includes**

- `ArLinkEdit.php`, `ArLinkEdit_ph0_inc.php`
- `BuchEdit.php`, `BuchEdit_ph0_inc.php`
- `DokuEdit.php`, `DokuEdit_ph0_inc.php`
- `MarktplEdit.php`, `MarktplEdit_ph0_inc.php`
- `MuseenEdit.php`, `MuseenEdit_ph0_inc.php`
- `PresseEdit.php`, `PresseEdit_ph0_inc.php`
- `TerminEdit.php`, `TerminEdit_ph0_inc.php`
- `ZeitungEdit.php`, `ZeitungEdit_ph0_inc.php`
- `Z_InhalteEdit.php`, `Z_InhalteEdit_ph0_inc.php`

**Listen-API und Tabellenklassen**

- Modul-APIs unter `Oeffentlichkeitsarbeit/API/`, darunter
  `*_Liste_API.php`, `*_ListRepository.php` und `*_ListTableConfig.php`.
  Die API-Endpunkte liefern öffentliche Lesedaten weiter, blenden aber
  Bearbeitungsaktionen ohne Schreibrolle aus.
- Tabellenklassen unter `Oeffentlichkeitsarbeit/Lists/`.
- Gemeinsame API: `Services/API/ListHostAPI.php`, sofern in der jeweiligen
  Listen-Konfiguration verwendet. Der aktuelle OEF-ListHost wird für AL, BU,
  DO, MP, MU, PR, TE, ZT und ZI verwendet.

**Umgesetzter OEF-Stand**

- Die aktuellen List-IDs AL, BU, DO, MP, MU, PR, TE, ZT und ZI behalten ihre
  bisherigen Lesepfade. Öffentliche Einstiege, die `smod=index` verwenden,
  bleiben lesbar.
- Aktionsspalten, Neu-Links und Edit-Aktionen in den aktuellen Listen sowie den
  separaten OEF-Listen-APIs erscheinen nur mit angemeldetem Benutzer und Rolle
  `ADM-OEF`. Der gemeinsame ListHost bestimmt den Schutz serverseitig und
  vertraut nicht auf `edit_Protect` aus dem Browser.
- Alle neun Edit-Controller weisen POSTs mit `phase=1` ohne `ADM-OEF` mit
  HTTP 403 ab. Nicht berechtigte Benutzer können außerdem keine neuen
  Datensätze über `ID=0` öffnen; existierende Einträge bleiben entsprechend
  dem bisherigen Lesepfad schreibgeschützt anzeigbar.
- Der gemeinsame OEF-Datei-Upload verlangt ebenfalls `ADM-OEF`.
- Das OEF-Menü prüft jetzt die vorhandene Rolle `ADM-OEF` statt der
  Mitgliederrolle `ADM-MI`.

### Mitglieder-Ehrungen

- Die Ehrungsliste in `MitglEdit.php` und `MitglEhrgEdit.php` wird durch
  `MitglEhrgList_inc.php` bereitgestellt. Auch die eigenständige
  `MitglEhrgList.php` ist für angemeldete Benutzer lesbar. Bearbeiten und
  Neuanlage im eingebetteten Teilbild erscheinen nur mit `ADM-MI`; die
  eigenständige Liste bietet keine Neuanlage an.
- `ListHostAPI.php` bestimmt den Schreibschutz für die List-ID `MIE`
  serverseitig; das vom Browser gelieferte `edit_Protect` ist dafür nicht
  maßgeblich.
- `MitglEdit.php` und `MitglEhrgEdit.php` lehnen Schreib-POSTs ohne `ADM-MI`
  mit HTTP 403 ab. Angemeldete Benutzer ohne diese Rolle können bestehende
  Mitglieder und Ehrungen schreibgeschützt ansehen; eine Neuanlage bleibt
  ihnen verwehrt. Die Formulare blenden Speichern aus.

### PSA

**Listen und Listen-Includes**

- `OrtsAuswList.php`, `OrtsEdit.php`, `OrtsEdit_ph0_inc.php`
- `AermAbzList_inc.php`, `AermAbzEdit.php`, `AermAbzEdit_ph0_inc.php`
- `OrtsWappenList_inc.php`, `FwWappenList_inc.php`
- `Lists/OrtsList.php`, `Lists/OWAList.php`, `Lists/AWAList.php`,
  `Lists/FWAList.php`
- `oris/VF_PS_Abz_List.php`, `oris/VF_PS_OV_AD_Z_List.php` mit
  `VF_PS_OV_AD_Z_List_CTIF.inc.php`,
  `VF_PS_OV_AD_Z_List_Standard.inc.php` und
  `VF_PS_OV_AD_Z_List_VEREINE.inc.php`, sowie `oris/VF_PS_OV_M_List.php`.
- Separate PSA-Listen-APIs unter `Psa/API/`: `EB_Liste_API.php`,
  `EL_Liste_API.php` und `EV_Liste_API.php` mit den jeweiligen
  Repository- und TableConfig-Dateien. Diese sind nicht der aktuelle
  ListHost-Pfad und müssen bei einer späteren Aktivierung separat geprüft
  werden. Die aktuellen Listen ORT, OW, FW und AWA verwenden
  `Services/API/ListHostAPI.php`.

**Weitere PSA-Edit-Scripts**

- `oris/VF_PS_OV_AB_Edit.php` und `_ph0.inc.php`
- `oris/VF_PS_OV_AD_Edit.php` und `_ph0.inc.php`
- `oris/VF_PS_OV_AE_Edit.php` und `_ph0.inc.php`
- `oris/VF_PS_OV_AZ_Edit.php` und `_ph0.inc.php`
- `oris/VF_PS_OV_AZ_CT_Edit.php` sowie `_ph0.inc.php` und `_ph1.inc.php`
- `oris/VF_PS_OV_AZ_VE_Edit.php` sowie `_ph0.inc.php` und `_ph1.inc.php`
- `oris/VF_PS_OV_M_Edit.php`
- `AermDetail.php`, `AermDetail_ph0_inc.php`
- `AermAbzEdit.php`, `AermAbzEdit_ph0_inc.php`
- `EhrgDetail.php`

**Umgesetzter PSA-Stand**

- Lesen der bisherigen öffentlichen Listen ORT, OW, FW und AWA bleibt ohne
  Anmeldung möglich.
- Aktionsspalten und Neuanlage-Links werden nur mit angemeldetem Benutzer und
  Rolle `ADM-PSA` angezeigt. Die Listen-API berechnet den Schutz selbst und
  übernimmt kein vom Browser gesetztes `edit_Protect`.
- `OrtsEdit.php` und `AermAbzEdit.php` prüfen die Rolle nochmals serverseitig
  vor dem Speichern. Nicht berechtigte Schreib-POSTs enden mit HTTP 403.
- `AermDetail.php` bleibt eine reine Anzeige. `/Psa/oris` bleibt unverändert.
- Die Ärmelabzeichen-Bearbeitung speichert Text- und Sortierfelder; ein
  Bild-Upload wird derzeit nicht angeboten. `OWAEdit.php` und `FWAEdit.php`
  sind im aktuellen PHP-Verzeichnis nicht vorhanden, daher sind diese
  Edit-Endpunkte noch nicht implementiert.
- `userBerechtigtOK()` liefert bei fehlender Rolle nun `false`; damit greifen
  Rollenprüfungen auch in den anderen Modulen tatsächlich.

## Vorgehen in kleinen, überprüfbaren Schritten

1. **Öffentlichkeit festlegen:** Für jede Liste dokumentieren, ob sie extern
   sichtbar sein darf und welche Datensätze/Felder öffentlich sind.
2. **Rolle bestätigen:** Pro Modul die gültige Schreibrolle aus der
   Benutzerverwaltung feststellen; nicht zwischen `ADM-OEF`, `ADM-PSA` und
   `PSA-ADM` raten.
3. **Listen-HTML absichern:** öffentlichen Lesezugriff nur für öffentliche
   Listen zulassen; `editScript` und `showActionColumn` anhand `$canEdit`
   setzen.
4. **API absichern:** direkte anonyme Lesezugriffe nur für öffentliche
   List-IDs zulassen; Rollen und Bearbeitungsschutz serverseitig bestimmen,
   nicht aus Query-Parametern übernehmen.
5. **Edit-Controller absichern:** `$canEdit` vor jeder Schreibphase prüfen;
   POST ohne Berechtigung mit 403 abbrechen.
6. **Formular-Includes absichern:** `$editProtect = !$canEdit`; Uploads,
   Sonderaktionen und Speichern-Buttons ebenfalls sperren.
7. **Je Listentyp testen:** nicht angemeldet, angemeldet ohne Modulrolle und
   angemeldet mit Modulrolle.

## Testmatrix

Für jede öffentliche Liste und jedes zugehörige Edit-Script testen:

| Test | Erwartetes Ergebnis |
|---|---|
| Externe Liste ohne Login öffnen | Öffentliche Datensätze werden angezeigt. |
| API-URL für die öffentliche Liste ohne Login direkt aufrufen | Nur die vorgesehenen öffentlichen Listendaten werden geliefert. |
| Liste ohne Login | Keine Neu-, Bearbeiten-, Löschen- oder Upload-Aktion. |
| Edit-URL eines existierenden Datensatzes ohne Login | Nur-Lese-Anzeige oder 403, gemäß gewünschtem öffentlichen Detailzugriff. |
| Edit-URL für neuen Datensatz ohne Login | 403 oder Rückleitung; kein beschreibbares Formular. |
| POST mit `phase=1` ohne passende Rolle | HTTP 403; kein Datenbank- oder Dateisystem-Schreibvorgang. |
| Angemeldet ohne Modulrolle | Lesen erlaubt, sofern vorgesehen; keine Schreibaktionen. |
| Angemeldet mit Modulrolle | Erlaubte Editier- und Anlagefunktionen funktionieren. |
| API-Aufruf mit manipuliertem `edit_Protect=false` | Gewährt keine zusätzliche Berechtigung und erzeugt keine Edit-Aktionen. |

Während dieser Tests immer auch die direkten URLs zu Edit-Controller und API
aufrufen. Das Ausblenden eines Buttons allein ist kein Berechtigungstest.

## Bestehende Referenzen

- [`PSA_Tabulator_Listen.md`](./PSA_Tabulator_Listen.md) beschreibt die
  Listen-Konfiguration, Aktionsspalten und `edit_Protect` im PSA-Tabulator-
  Muster.
- [`Tabulator_OrtsList_n.md`](./Tabulator_OrtsList_n.md) dokumentiert die
  neuere Ortslisten-Variante und weist auf getrennte API-Prüfungen hin.
