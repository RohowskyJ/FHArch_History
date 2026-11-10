# Dokumentation: `ConfEdit.php`

## Zweck und Einordnung

`FWHist/login/AllgVerw/ConfEdit.php` ist der Controller für die administrative
Konfiguration der FWHist-Startseite. Er zeigt ein Formular mit Betreiber-,
Betriebs- und Bilddaten an und verarbeitet dessen Übermittlung.

Die Verwaltung verwendet zwei Speicherziele:

1. Eine INI-Datei unter `FWHist/login/config/`, die abhängig vom Host ausgewählt
   wird.
2. Den Datensatz mit ID `1` in der Datenbanktabelle `fv_proj_config`.

Die Formularanzeige und die Verarbeitung beim Absenden sind in zwei
Phasen-Includes aufgeteilt:

| Datei | Aufgabe |
|---|---|
| `FWHist/login/AllgVerw/ConfEdit.php` | Initialisierung, Datenbeschaffung und Steuerung der Phasen |
| `FWHist/login/AllgVerw/ConfEdit_ph0_inc.php` | Formular und vorhandene Konfigurationswerte anzeigen |
| `FWHist/login/AllgVerw/ConfEdit_ph1_inc.php` | Uploads verarbeiten und Änderungen speichern |

Die Includes werden vom Controller eingebunden und greifen deshalb auf dessen
Variablen wie `$neu`, `$conf`, `$cfg`, `$systemBasePath` und `$path2ROOT` zu.

## Ablauf im Überblick

```text
Aufruf ohne phase
    -> Phase 0
    -> Konfigurationsdatei wählen und auslesen
    -> Formular anzeigen

Formular absenden (phase=1)
    -> POST-Werte in $neu übernehmen
    -> optionale Bilder speichern und Dateinamen übernehmen
    -> Konfigurations-INI schreiben
    -> Datenbankeintrag 1 aktualisieren
    -> Weiterleitung
```

## Initialisierung und Voraussetzungen

### Session und Basispfad

Zu Beginn startet das Script die Session, sofern noch keine aktiv ist. Es
erwartet die Session-Struktur `$_SESSION['BS_Prim']`. Fehlt diese, leitet es
zum FWHist-Einstieg zurück und beendet das Script.

Der lokale Systempfad wird aus `$_SESSION['BS_Prim']['RoBase']` gelesen. Falls
dieser Session-Wert fehlt, wird ein Pfad relativ zur Scriptposition verwendet.
Über diesen Pfad lädt der Controller den Composer-Autoloader sowie die
Service-Dateien `FuncsLib.php`, `CommFuncsLib.php` und `ConstLib.php`.

### Zugriffsprüfung

Die benötigte Berechtigung wird mit `userBerechtigtOK('ADM-ALLE')` geprüft.
Auch das Formular-Include fragt die Berechtigung vor der Ausgabe des
Speichern-Buttons erneut ab.

**Hinweis zum aktuellen Stand:** Bei fehlender Berechtigung sendet der
Controller einen Redirect-Header, beendet die Ausführung an dieser Stelle aber
nicht mit `exit`. Die Berechtigungsprüfung und der Redirect sollten daher bei
Änderungen an diesem Script besonders sorgfältig geprüft werden.

### Fehlerprotokollierung

Der Controller aktiviert `E_ALL`, schaltet die direkte Anzeige von PHP-Fehlern
aus und aktiviert das PHP-Fehlerprotokoll. Zusätzlich registriert er eine
Shutdown-Funktion, die den letzten PHP-Fehler protokolliert. Die beiden
Logdateien werden mit dem Pfadpräfix
`$systemBasePath . '/FWHist/login/AOrd_Verz/'` gebildet:

- `Config_Edit_php-error.log.txt`
- `Config_Edit_fatal_error.log`

Der tatsächliche Speicherort hängt davon ab, auf welches Verzeichnis
`$systemBasePath` zeigt. Da der Sessionwert `RoBase` je nach Aufrufer bereits
auf `FWHist/` zeigen kann, sollte geprüft werden, ob das zusätzliche
`/FWHist/` im zusammengesetzten Pfad korrekt ist. Ob die Dateien geschrieben
werden können, hängt außerdem von den Verzeichnisrechten ab.

## Abhängigkeiten und Seiteneinrichtung

Der Controller verwendet unter anderem:

- `EnvSessionManager` für Umgebungswerte wie Basis- und Konfigurationspfad.
- `HTMLPage` für Header, Formularrahmen und Footer.
- `DB_GenericLog` als Datenbankverbindung.
- `DB_AllgVerw` für die Aktualisierung von `fv_proj_config`.
- `TableColumnMetadata` für Metadaten der Tabelle `fv_proj_config`.
- `FuncsLib.php`, `CommFuncsLib.php` und `ConstLib.php` für gemeinsame
  Funktionen und Konstanten.

Die Seite wird mit dem Typ `Form` und einer Breite von `60em` eingerichtet.
`HTMLPage` erzeugt das HTML-Formular mit `method="post"` und
`enctype="multipart/form-data"`. Letzteres ist notwendig, damit PHP die
ausgewählten Dateien in `$_FILES` bereitstellt.

## Auswahl der Konfigurationsdatei

Der Hostname entscheidet, welche INI-Datei bearbeitet wird:

| Host | Dateiname |
|---|---|
| `localhost` oder sonstiger nicht explizit erkannter Host | `config_s_l.ini` |
| `feuerwehrhistoriker.at` oder `www.feuerwehrhistoriker.at` | `config_s_fwhist.ini` |

Die Datei liegt unter `FWHist/login/config/`. Der Controller bestimmt `$cfg`
anhand von `$_SERVER['HTTP_HOST']` und verwendet denselben Dateinamen beim
Lesen und Schreiben.

## Phasensteuerung

Der Controller liest `phase` aus `$_POST`. Fehlt der Wert, wird Phase `0`
verwendet.

| Phase | Verhalten |
|---|---|
| `0` | Werte aus der ausgewählten INI-Datei lesen und das Formular anzeigen |
| `1` | Formulardaten übernehmen und Änderungen speichern |

Die Auswahl erfolgt am Ende über einen `switch`, der
`ConfEdit_ph0_inc.php` oder `ConfEdit_ph1_inc.php` einbindet. Anschließend
ruft der Controller `renderFooter()` auf.

## Phase 0: Werte lesen und Formular anzeigen

Bei Phase `0` wird die INI-Datei mit `parse_ini_file()` eingelesen. Erwartet
wird darin der Abschnitt `[Config]`. Die dort verwendeten INI-Schlüssel werden
auf Formularfeldnamen mit dem Präfix `c_` abgebildet.

| Formularfeld in `$neu` | INI-Schlüssel |
|---|---|
| `c_Institution` | `inst` |
| `c_Vereinsreg` | `vreg` |
| `c_Eignr` | `eignr` |
| `c_Verantwortl` | `vant` |
| `c_email` | `vema` |
| `c_Ver_Tel` | `vtel` |
| `c_mode` | `mode` |
| `c_Wartung` | `wart` |
| `c_Wart_Grund` | `warg` |
| `c_bild_1` | `sign` |
| `c_bild_2` | `fpage` |
| `c_Homepage` | `homp` |
| `c_ptyp` | ebenfalls `homp` (so im aktuellen Controller zugewiesen) |
| `c_store` | `store` |
| `c_def_pw` | `def_pw` |
| `c_Perr` | `cPerr` |
| `c_Debug` | `cDeb` |
| `c_bpath` | `bpath` |
| `c_miBeitr` | `miBeitr` |

Existiert die INI-Datei nicht, legt der Controller stattdessen einen Satz
Defaultwerte an. In diesem Zweig heißt das Institutionsfeld aktuell
`c_Incstitution` (abweichende Schreibweise zu `c_Institution`). Dieser
Defaultzweig ist daher bei Änderungen an den Feldnamen besonders zu beachten.

`ConfEdit_ph0_inc.php` baut mit `FormRendererFlex` das Formular auf. Die
Eingaben umfassen:

- Betreiberinformationen wie Institution, Vereinsregisternummer,
  Verantwortliche, E-Mail, Telefonnummer und Homepage.
- Betriebs- und Verwaltungswerte wie Modus, Wartungsstatus und Wartungsgrund.
- Konfigurationswerte wie Speicherverzeichnis, Standardpasswort und
  Protokolldateinamen.
- Zwei Bilddateinamen sowie zwei dazugehörige optionale Datei-Eingaben.

Für die Bilder sind die Eingabefelder:

| Zweck | Dateiname im Formular | Zu speicherndes Konfigurationsfeld |
|---|---|---|
| Logo | `userfile_1[]` | `c_bild_1` |
| Bild der ersten Seite | `userfile_2[]` | `c_bild_2` |

Die Textfelder `c_bild_1` und `c_bild_2` zeigen die derzeit gespeicherten
Dateinamen an. Vorhandene Bilddateien werden darunter als Thumbnails mit
maximal 160 × 120 Pixeln angezeigt. Die Vorschau wird nur ausgegeben, wenn
eine Datei mit dem gespeicherten Namen im `imgs/`-Verzeichnis existiert. Eine
neue Datei ist optional. Im Konfigurationsformular ist kein verstecktes
`MAX_FILE_SIZE`-Limit gesetzt. Die tatsächliche Uploadgröße wird daher von den
PHP- und Servereinstellungen (zum Beispiel `upload_max_filesize` und
`post_max_size`) begrenzt.

Der Speichern-Button wird nur ausgegeben, wenn `userBerechtigtOK($Zugr)`
positiv ist. Beim Absenden setzt der Button `phase=1`.

## Phase 1: Eingaben übernehmen und speichern

Der Controller kopiert bei Phase `1` die POST-Werte nach `$neu` und führt
`trim()` auf den Werten aus. Datei-Uploads werden separat über `$_FILES`
behandelt.

### Bild-Uploads

`ConfEdit_ph1_inc.php` bildet die Upload-Felder den Konfigurationswerten zu:

1. `userfile_1` wird dem Logo-Feld `c_bild_1` zugeordnet.
2. `userfile_2` wird dem Startseitenbild-Feld `c_bild_2` zugeordnet.
3. Wenn für das jeweilige Feld eine Datei gewählt wurde, ruft das Include
   `filesUpload()` aus `CommFuncsLib.php` auf.
4. Das Uploadziel ist der `imgs`-Ordner neben dem Verzeichnis `login`,
   also normalerweise `FWHist/imgs/`.
5. Gibt der Helper einen Dateinamen zurück, wird der erste erfolgreiche
   Dateiname in `$neu` als Wert für das passende `c_bild_*`-Feld eingetragen.
6. Ohne neue Datei bleibt der zuvor übermittelte Dateiname aus dem Formular
   unverändert.

`filesUpload()` bereinigt Dateinamen mit `sanitize_filename()`, verschiebt die
temporäre Upload-Datei mit `move_uploaded_file()` ins Zielverzeichnis und
liefert bei Erfolg eine Liste von Dateinamen zurück. Wenn keine Datei
erfolgreich übertragen wurde, liefert die Funktion `false`. PHP-Fehlercode `2`
(`UPLOAD_ERR_FORM_SIZE`) bedeutet, dass die Datei das im HTML-Formular
angegebene `MAX_FILE_SIZE` überschritten hat; der hier verwendete Grenzwert
wurde entfernt. Andere PHP-Uploadlimits gelten weiterhin.

### INI-Datei

Das Phase-1-Include baut den Inhalt der INI-Datei mit dem Abschnitt `[Config]`
neu auf. `$neu` wird durchlaufen; Einträge ohne `c_`-Präfix werden dabei
entfernt und nicht in die INI-Datei übernommen. Bekannte Felder werden auf
ihre INI-Schlüssel abgebildet:

| `$neu`-Feld | INI-Schlüssel |
|---|---|
| `c_Institution` | `inst` |
| `c_Vereinsreg` | `vreg` |
| `c_Verantwortl` | `vant` |
| `c_email` | `vema` |
| `c_Ver_Tel` | `vtel` |
| `c_mode` | `mode` |
| `c_Wartung` | `wart` |
| `c_Wart_Grund` | `warg` |
| `c_Eignr` oder `c_Eigner` | `eignr` |
| `c_bild_1` | `sign` |
| `c_bild_2` | `fpage` |
| `c_Homepage` | `homp` |
| `c_ptyp` | `ptyp` |
| `c_store` | `store` |
| `c_def_pw` | `def_pw` |
| `c_Perr` | `cPerr` |
| `c_Debug` | `cDeb` |
| `c_bpath` | `bpath` |
| `c_miBeitr` | `miBeitr` |

Die Zuweisung `$neu['c_ptyp']` aus dem Formular wird in der INI als `ptyp`
gespeichert. Beim Lesen wird `c_ptyp` im aktuellen Controller jedoch aus
`homp` befüllt. Dieses asymmetrische Mapping ist beim Ändern der
Konfigurationsschlüssel zu berücksichtigen.

Anschließend wird die ausgewählte Datei mit Schreibmodus (`fopen(..., 'w')`)
geöffnet und vollständig neu geschrieben. Ein vorhandener Inhalt außerhalb
der hier generierten Werte bleibt dabei nicht erhalten.

### Datenbank

Nach dem Schreiben der INI-Datei ruft das Include
`$conf->updateConfig(1, $neu)` auf. `DB_AllgVerw::updateConfig()` aktualisiert
damit den Datensatz in `fv_proj_config`, dessen Primär-/Suchfeld
`c_flnr` den Wert `1` hat.

Die übergebenen `$neu`-Daten enthalten Formularfelder mit `c_`-Präfix.
`DB_AllgVerw` ist für die Datenbankoperation zuständig; der Controller erzeugt
den INI-Inhalt unabhängig davon selbst.

### Weiterleitung nach dem Speichern

Wenn `$_SESSION[$module]['inst']` gesetzt ist, wird dorthin weitergeleitet.
Diese Session-Variable wird gesetzt, wenn der Aufruf mit `?inst=J` erfolgt.
Andernfalls lautet das Weiterleitungsziel `$path2ROOT . "/VFH/index.php"`.

## Session- und Installationsparameter

Bei `?inst=J` setzt der Controller:

```php
$_SESSION['ADM-Conf']['inst'] = $systemBasePath . 'install/install_cleanup.php';
```

Nach erfolgreichem Speichern leitet der Phase-1-Code daher zum
Installations-Cleanup weiter. Ohne diesen Parameter wird die normale
Weiterleitung ausgeführt.

## Fehler- und Betriebsaspekte

- Der Controller gibt bei Phase `0` den berechneten Dateipfad mit `var_dump()`
  aus. Das ist sichtbare Debug-Ausgabe und kann Layout oder Header-Verhalten
  beeinflussen.
- Das Upload-Utility `filesUpload()` enthält derzeit eigene Diagnoseausgaben
  wie `var_dump($_FILES)` und Statusmeldungen. Das kann beim Speichern
  sichtbare Ausgabe erzeugen.
- Das INI-Schreiben prüft den Rückgabewert von `fopen()` nicht. Fehler beim
  Öffnen oder Schreiben der Datei werden daher nicht durch diesen Controller
  explizit behandelt.
- Die Datenbank-Rückgabe aus `updateConfig()` wird in `$result` gespeichert,
  aber im aktuellen Ablauf nicht ausgewertet.
- Die Weiterleitung wird nach dem Rendern des Seitenheaders und der Verarbeitung
  angefordert. Da bereits HTML ausgegeben worden sein kann, ist sie von
  aktivem Output-Buffering abhängig; bei Problemen mit `headers already sent`
  sind die Ausgabestellen vor `header()` zu prüfen.
- Die Meldungen aus dem Upload-Helper werden nicht in einen Formular-Fehler
  übersetzt. Ein fehlgeschlagener Upload kann deshalb trotz anschließendem
  Speichern der übrigen Felder stattfinden.

## Änderungshinweise

Bei Erweiterungen oder Fehlerkorrekturen an der Konfigurationsverwaltung
sollten die folgenden Stellen gemeinsam geprüft werden:

1. **Formularfeld** in `ConfEdit_ph0_inc.php`.
2. **Einlesen** aus der INI-Datei in `ConfEdit.php`.
3. **Mapping und Schreiben** in `ConfEdit_ph1_inc.php`.
4. **Spalten und Updateverhalten** in `DB_AllgVerw.php` sowie
   `fv_proj_config`.
5. **Dateiupload** in `ConfEdit_ph0_inc.php`, `ConfEdit_ph1_inc.php` und
   `CommFuncsLib.php`.
6. **INI-Varianten** `config_s_l.ini` und `config_s_fwhist.ini`.

Insbesondere Feldnamen müssen in Formular, Lese-Mapping, Schreib-Mapping und
Datenbank konsistent sein. Ein Datei-Upload setzt voraus, dass das Formular
`multipart/form-data` verwendet und der Webserver Schreibrechte auf
`FWHist/imgs/` hat.
