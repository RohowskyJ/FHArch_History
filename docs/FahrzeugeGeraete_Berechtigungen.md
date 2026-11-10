# RBAC-Umstellung der Fahrzeug- und Geräteliste

## Umfang und Status

Die Listen-Seite `FzAList.php` wurde auf den zentralen `AccessGuard`
umgestellt. Diese Änderung betrifft die Anmeldung und die Berechtigungsanzeige
für die Fahrzeug- und Gerätelisten. Fahrzeug-Detailansicht und Speicherpfad
verwenden dieselbe mandantenbezogene RBAC-Regel. Fahrzeug-Uploads werden
zusätzlich durch den zentralen Upload-Guard geschützt.

Der Umfang umfasst die Fahrzeugliste (`FAF`) und die Fahrzeugbearbeitung
(`FaFEdit.php`). Separate Eigentümer- und Dokumentbearbeitungsseiten sowie
andere Fahrzeug-/Gerätemodule sind eigenständige Zugriffspfade und werden hier
nicht umgestellt.

## Rechtekonzept

`FzAList.php` erzeugt einen `AccessGuard` mit
`AccessGuard::fromSession($DBD)`. Dadurch werden Benutzer und Rollen für den
aktuellen Request über die RBAC-Klassen aus der Datenbank geladen.

- Lesen der Fahrzeug- und Geräteliste setzt eine gültige Anmeldung voraus
  (`canReadMandantData()`).
- Das Schreibrecht wird je ausgewähltem Mandanten über
  `canWriteMandantData('FZG', $ei_id)` geprüft.
- Nach der Regel in `AccessGuard` darf `ADM-FZG` für alle Mandanten schreiben.
  `ONU-ALL` darf für Mandanten schreiben, die dem Benutzer zugeordnet sind.
  `ADM-ALLE` erfüllt die Berechtigungsprüfung ebenfalls.
- Nicht angemeldete Benutzer werden vor dem Rendern der Seite mit HTTP 403
  abgewiesen.
- Änderungen erfordern zusätzlich ein gültiges CSRF-Token.
- Ungültige Mandanten- oder Fahrzeug-IDs werden abgewiesen. Beim Bearbeiten
  wird der Fahrzeugdatensatz aus der Tabelle des ausgewählten Mandanten
  geladen; die gespeicherte Mandanten-ID wird serverseitig gesetzt.

## Verhalten der Listen

Für die Fahrzeugliste (`FAF`) wird die Aktionskonfiguration mit
`listActionConfig($canEdit)` erzeugt:

- Schreibberechtigte Benutzer erhalten die Aktion **Bearbeiten** und den
  konfigurierten Link zum Anlegen eines Fahrzeugs.
- Angemeldete Benutzer ohne Schreibrecht erhalten die Aktion **Anzeigen**.
- Für die Geräteliste (`FAG`) bleibt die Aktionsspalte ausgeblendet; auch für
  Geräte wird kein Fahrzeug-Neuanlage-Link konfiguriert.
- `edit_Protect` wird entsprechend der Aktionskonfiguration gesetzt und an die
  Listen-API übergeben. Diese Einstellung steuert die Darstellung der
  Listenspalten, ersetzt aber keine serverseitige Schreibprüfung.

`ListHostAPI.php` verlangt für nicht öffentliche Listen eine gültige Anmeldung
und berechnet `edit_Protect` für `FAF` und `FAG` selbst aus
`canWriteMandantData('FZG', $selId)`. Der vom Browser übermittelte Wert wird
damit nicht als Autorisierungsentscheidung verwendet.

`FaFEdit.php` prüft Leserecht und mandantenbezogenes Schreibrecht vor dem
Rendern beziehungsweise Speichern. Schreibende POSTs ohne passendes Recht
werden mit HTTP 403 beendet. Der Speicherpfad verlangt außerdem ein CSRF-Token
und prüft vor einem Update, ob der Datensatz in der Tabelle des gewählten
Mandanten vorhanden ist.

Für den Foto-Upload steuert `Upload_Form()` die Anzeige anhand des Guards.
`UploadAPI.php` kontrolliert beim tatsächlichen Upload unabhängig davon erneut
die Berechtigung `FZG` für die übermittelte Eigentümer-ID und verlangt ein
gültiges CSRF-Token.

## Relevante Dateien

- [`FzAList.php`](../FWHist/login/Modules/FahrzeugeGeraete/FzAList.php) –
  Anmeldung, mandantenbezogene Schreibprüfung und Listen-Konfiguration.
- [`FaFEdit.php`](../FWHist/login/Modules/FahrzeugeGeraete/FaFEdit.php) –
  RBAC-Lese-/Schreibprüfung, CSRF-Schutz und Fahrzeug-Speicherung.
- [`FaFEdit_ph0_inc.php`](../FWHist/login/Modules/FahrzeugeGeraete/FaFEdit_ph0_inc.php) –
  Formularanzeige abhängig vom Schreibschutz.
- [`AccessGuard.php`](../FWHist/login/Auth/AccessGuard.php) –
  zentrale RBAC-Regeln für Mandantendaten.
- [`Auth.php`](../FWHist/login/Auth/Auth.php) –
  Laden des angemeldeten Benutzers, seiner Rollen und Mandantenzuordnungen.
- [`ListHostAPI.php`](../FWHist/login/Services/API/ListHostAPI.php) –
  Datenbereitstellung und mandantenbezogene Schreibschutzberechnung für die
  Tabellenlisten.
- [`UploadAPI.php`](../FWHist/login/Services/API/UploadAPI.php) –
  serverseitige RBAC- und CSRF-Prüfung des Foto-Uploads.
