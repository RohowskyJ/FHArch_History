# Analyse 

Kann die bestehende Berechtigungsstruktur der APP FHArch_History auf RBAC umgestellt werden?
Aufwandsschätzung? 

## Anforderungen

- Module mit Mandanten- Spezifischen Daten mit Tabellen Namen wie {eig_id}\_tabname (z.B. 124_dmedien, 124_inventar, 124_archivdat, 124_arch_verl, 124_inventar, 124_invent_verleih, 12f4_fza_dokumente, 124_fza_eigner, 124_fza_gerät,  124_fzm_fahrzeug, 124_fzm_geraet, wobei 124 die Eigentümer- VerwMandanten oder Urheber- Nummer ist, ist immer ident mit ma_mandanten.ei_id )
- Module ohne Mandantenbezug: alle Module im Modul Offentlichkeitsarbeit und Psa, Allgemeine Verwaltung

Alle Daten (Listen und Edit- Funktionen)  (außer der Mandanten- , Mitglieder- , Benutzer- und Zugriffs Verwaltung müssen für alle eingeloggten Benutzer lesbar sein. 
Alle Daten , die in der Externen Seite angezeigt werden sollen nur lesbar sein (Liste, eventuell bei einigen Edit- Form).

Die Schreib- Berechtigung (anlegen, update) für die nicht- Mandanten- bezogenen Daten dürfen nur eingeloggte Benutzer mit Admin- Status (ADM_ALLE,ADM-FI, ...).

Die Schreib- Berechtigungen für Mandanten- bezogene Daten muss der eingeloggte Benutzer zusätzlich die Berechtigung  für den Mandanten haben (per ei_id, alle Module).

Wenn möglich eine Teilweise Umstellung , beginnend mit /Allgverw,   /Module/Mitglieder und /Module/Mandanten

## Aufwandsschätzung

Die Umstellung ist grundsätzlich machbar und kann stufenweise erfolgen. Für
die erste Stufe mit `/AllgVerw`, `/Module/Mitglieder` und `/Module/Mandanten`
ist mit **8–14 Personentagen** zu rechnen. Für die vollständige Umsetzung der
beschriebenen Regeln in der gesamten Anwendung liegt die grobe Schätzung bei
insgesamt **30–50 Personentagen**, einschließlich der ersten Stufe.

| Arbeitspaket | Erste Stufe |
|---|---:|
| Berechtigungen und betroffene Schreib- und API-Pfade erfassen, Regeln festlegen | 1–2 PT |
| Datenmodell und Migration für Rollen sowie Mandanten-Zugriffe | 1–2 PT |
| Zentrale Autorisierungsprüfung und Einbindung in Anmeldung/Sitzung | 1–2 PT |
| Listen, Editierfunktionen und APIs der drei Startbereiche absichern | 3–5 PT |
| Tests, Migrationserprobung und Regression | 2–3 PT |
| **Summe, gerundet** | **8–14 PT** |

### Befund zum bestehenden Stand

Im Schema [docs/sqls/benutzer.md](sqls/benutzer.md) sind mit
`fv_benutzer`, `fv_rolle` und `fv_rollen_beschr` bereits Benutzer- und
Rollentabellen vorhanden. Die Berechtigungen einer Rolle sind derzeit als
kommaseparierter Text in `fl_modules` gespeichert. Eine explizite Zuordnung
von Benutzern oder Rollen zu Mandanten (`ei_id`) ist im dokumentierten Schema
nicht erkennbar. Damit besteht eine RBAC-Grundlage, die für Mandantenrechte
und eine konsistente Durchsetzung der Lese- und Schreibregeln erweitert
werden muss.

Die Rollenprüfung erfolgt unter anderem über `userBerechtigtOK()` in
`FWHist/login/Services/CommFuncsLib.php`. Die Anwendung hat daneben weitere
bestehende Sitzungs- und Prüfpfade. Deshalb müssen bei der Umstellung auch
direkte POST- und API-Aufrufe geschützt werden; das Ausblenden von
Schaltflächen allein reicht nicht aus.

### Annahmen und Unsicherheiten

- Die erste Stufe umfasst die drei genannten Bereiche, nicht die Umstellung
  aller Module.
- Mandantenrechte werden serverseitig anhand der tatsächlichen `ei_id`
  geprüft, auch bei direkten API- und POST-Aufrufen.
- Externe Ansichten bleiben lesend; Schreibzugriffe werden serverseitig
  abgewiesen.
- Die Gesamtschätzung setzt eine schrittweise Umstellung der übrigen Module
  voraus und keine grundlegende Neugestaltung der Anmeldung.
- Eine vollständige Inventur der schreibenden Endpunkte und
  mandantenspezifischen Tabellen kann die Schätzung verändern. Für die
  Projektplanung sollte eine Reserve von etwa **20–30 %** vorgesehen werden.

## Plan für die nächsten Schritte

Die Umstellung soll in kleinen, prüfbaren Schritten erfolgen. Bestehende
Berechtigungsprüfungen bleiben aktiv, bis die neue Prüfung für den jeweiligen
Bereich getestet und produktiv freigegeben ist.

| Schritt | Arbeiten und Ergebnisse | Freigabekriterium |
|---|---|---|
| 1. Regeln verbindlich festlegen | Die Rollen und Schreibrechte für `/AllgVerw`, Mitglieder- und Mandantenverwaltung sowie externe Ansichten abstimmen. Insbesondere klären, welche Ausnahmen für Mitglieder-, Benutzer- und Zugriffsverwaltung gelten und welche Admin-Rollen welche Schreibrechte erhalten. | Eine abgestimmte Berechtigungsmatrix für anonym, angemeldet, Admin und externen Zugriff liegt vor. |
| 2. Bestehende Zugriffe inventarisieren | Für die drei Startbereiche Seiten, Listen, APIs, Autocomplete-, Upload- und Speicherendpunkte sowie zugehörige Tabellen erfassen. Je Funktion dokumentieren: lesend/schreibend, verwendete Berechtigungsprüfung und mögliche direkte Aufrufwege. | Jede schreibende Operation ist einem Endpunkt, einer Ressource und einer Regel zugeordnet. |
| 3. Mandanten-Zuordnung entwerfen | Datenmodell für Benutzer- beziehungsweise Rollenrechte pro `ei_id` festlegen. Festlegen, ob Rechte nur Lesen/Schreiben oder weitere Aktionen unterscheiden, wie deaktivierte Mandanten behandelt werden und wie bestehende Berechtigungen migriert werden. Migration und Rückfallweg vorbereiten. | Schemaänderung, Migrationsskript und Zuordnung der vorhandenen Berechtigungen sind geprüft und auf einer Testkopie erprobt. |
| 4. Zentrale Prüfungen bereitstellen | Gemeinsame serverseitige Prüfungen für Anmeldung, allgemeine Berechtigung und Mandantenrecht einführen. Die Prüfung muss bei geschützten Schreibzugriffen standardmäßig ablehnen, die tatsächliche Ressource und `ei_id` prüfen und in HTML-Seiten wie auch APIs verwendbar sein. | Automatisierte Tests belegen erlaubte und verweigerte Zugriffe; fehlende oder ungültige Rechte erlauben keine Änderung. |
| 5. `/AllgVerw` umstellen | Listen für angemeldete Benutzer lesbar halten; Änderungen und Neuanlagen serverseitig auf die abgestimmten Admin-Berechtigungen begrenzen. Listen-APIs und direkte POST-Aufrufe mitprüfen. | Lesen und erlaubte Admin-Schreibvorgänge funktionieren; Benutzer ohne Admin-Recht können auch über direkte Requests nichts ändern. |
| 6. Mandanten- und Mitgliederverwaltung umstellen | Die für diese Verwaltungsbereiche festgelegten Ausnahmen umsetzen. Mandantenrechte bei jeder mandantenbezogenen Änderung aus der serverseitig geladenen Ressource ableiten, nicht ungeprüft aus Request-Parametern übernehmen. Alle zugehörigen APIs und Hilfsendpunkte einbeziehen. | Rollen- und Mandantengrenzen sind für Listen, Detailansichten und direkte API-/POST-Aufrufe getestet. |
| 7. Pilot auswerten und freigeben | Regressionstests und fachliche Abnahme durchführen, Protokollierung für verweigerte Schreibversuche prüfen und Migration auf einer produktionsnahen Kopie wiederholen. Rollout zunächst begrenzt durchführen und Rückfallverfahren bereithalten. | Keine offenen kritischen Berechtigungsfehler; Freigabe und Rückfallweg sind dokumentiert. |
| 8. Weitere Module schrittweise migrieren | Als nächste Wellen mandantenbezogene Module anhand ihrer Tabellenfamilien und danach weitere nicht mandantenbezogene Module aufnehmen. Pro Welle Inventur, Anpassung, Rechte-Matrix-Tests und Freigabe wiederholen. Externe Ansichten ausdrücklich auf lesenden Zugriff begrenzen. | Jede Welle erfüllt dieselben serverseitigen Prüf- und Abnahmekriterien, bevor die nächste startet. |
| 9. Legacy-Prüfungen bereinigen | Nach erfolgreicher Migration und Beobachtungsphase doppelte oder veraltete Berechtigungslogik nur dort entfernen, wo die neue Prüfung nachweislich alle Aufrufpfade abdeckt. Berechtigungsmodell und Betriebsdokumentation aktualisieren. | Keine produktiven Aufrufpfade hängen mehr von der abgelösten Logik ab; Dokumentation und Tests entsprechen dem aktiven Modell. |

### Mindestumfang der Tests je Umstellungswelle

- Anonyme Zugriffe auf geschützte Funktionen werden abgewiesen.
- Angemeldete Benutzer können die vorgesehenen Daten lesen.
- Schreibversuche ohne passende Admin- beziehungsweise Mandantenberechtigung
  werden serverseitig abgewiesen, auch bei direktem API- oder POST-Aufruf.
- Ein Recht für einen Mandanten gewährt keinen Schreibzugriff auf einen
  anderen Mandanten.
- Externe Ansichten bieten keine wirksame Schreibmöglichkeit; manipulierte
  Schreibrequests werden ebenfalls abgewiesen.
- Bestehende zulässige Arbeitsabläufe, insbesondere Admin-Schreibvorgänge,
  funktionieren nach der Migration weiterhin.
