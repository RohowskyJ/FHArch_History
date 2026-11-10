# Anleitung: Bilddateinamen in Datensätzen ersetzen

Diese Anleitung beschreibt die Verwendung von `PictFileReplace.php`, um alte
Bilddateinamen in Datenbankfeldern manuell durch Dateinamen im aktuellen
Bildformat zu ersetzen.

## 1. Zweck und Arbeitsweise

Das Script sucht für offene Datensätze passende Bilder in der Medienbibliothek
und zeigt mögliche Treffer als anklickbare Thumbnails an. Der Benutzer prüft
die Bilder und wählt den passenden Treffer aus. Erst nach dem Speichern wird
der Dateiname in das betreffende Datenbankfeld übernommen.

Das Script verschiebt, konvertiert oder benennt keine Bilddateien um. Es ändert
nur den im Datensatz gespeicherten Bildnamen. Die ausgewählte Datei muss daher
bereits an ihrem endgültigen Ort und im aktuellen Zielformat liegen.

Die Suche erstellt keinen vollständigen Index der Bibliothek. Sie durchsucht
die für einen Datensatz ermittelten Verzeichnisse und erweitert die Suche nur,
wenn die gezielte Suche keinen Treffer findet.

## 2. Script starten

Im lokalen Entwicklungsbetrieb öffnen Sie:

```text
http://localhost/FHArch_History/rework/PictFileReplace.php
```

Die Anwendung muss mit der Datenbank und dem Bildverzeichnis verbunden sein,
die in der Konfiguration am Anfang von `PictFileReplace.php` eingetragen sind.
Wenn Sie das Script in eine andere Umgebung übertragen, prüfen Sie dort
insbesondere Datenbankverbindung und `$IMAGE_ROOT`. Zugangsdaten sollten nicht
in dieser Anleitung oder in öffentlich zugänglichen Dateien dokumentiert
werden.

Nach dem Aufruf:

1. Wählen Sie links die gewünschte Tabelle aus.
2. Die erste Seite mit offenen Datensätzen wird geladen.
3. Klicken Sie beim gewünschten nicht migrierten Bildfeld auf **„prüfen“**.
4. Wählen Sie rechts anhand des Thumbnails den richtigen Treffer aus.

Die Tabellen- und Bildfeld-Erkennung erfolgt beim Aufruf anhand der
Datenbankdefinitionen. Tabellen der Medienbibliothek (`*_dmedien`) sind von der
Bearbeitung ausgeschlossen.

## 3. Welche Datensätze angezeigt werden

Die Liste enthält Datensätze, in denen mindestens ein unterstütztes Bildfeld
einen nicht-leeren Bilddateinamen enthält, der noch nicht dem aktuellen
Namensformat entspricht.

- Leere Bildfelder werden ignoriert.
- Nicht-Bilddateien, zum Beispiel PDFs, werden ignoriert.
- Datensätze, deren nicht-leere Bilddateinamen bereits alle dem aktuellen
  Zielformat entsprechen, werden nicht angezeigt.
- Ein Datensatz mit mehreren Bildfeldern bleibt so lange offen, bis alle
  relevanten Bildfelder im Zielformat gespeichert sind.

Das erkannte Zielformat lautet:

```text
<UrhNr>-<YYYYMMDD>-<W|N>-<Originalname>.JPG
```

`W` bezeichnet eine mit Wasserzeichen versehene Datei, `N` eine Datei ohne
Wasserzeichen. Für das Erkennen des Zielformats werden das große `.JPG` und
die vollständigen Bestandteile des Namens erwartet.

## 4. Blättern und Bearbeiten

Die offene Liste ist in Seiten mit bis zu 50 Datensätzen aufgeteilt. Die
Navigation **„Vorige Seite“** und **„Nächste Seite“** wird oben und unten in der
linken Spalte angezeigt. Sie führt durch die offenen Datensätze, nicht durch
alle Tabellenzeilen. Bereits migrierte Datensätze belegen daher keinen Platz
auf einer Seite.

Der Link **„prüfen“** öffnet rechts die Suche für ein Bildfeld. Nach dem
Vormerken eines Treffers kehrt die Anwendung zur Detailansicht dieses
Datensatzes zurück. Die aktuelle Seitennummer bleibt dabei erhalten.

### Datensatz ohne aktuell passenden Treffer überspringen

Wenn die Bibliothek für einen Datensatz noch nicht vollständig aktualisiert
ist oder kein passender Treffer angezeigt wird:

1. Klicken Sie links beim Datensatz auf **„Datensatz vorläufig überspringen“**.
2. Der Datensatz wird für die aktuelle Sitzung aus der offenen Liste
   ausgeblendet. Dadurch rücken weitere offene Datensätze nach.
3. Sobald die betreffenden Bilder bereitstehen, klicken Sie auf
   **„Übersprungene Datensätze wieder anzeigen“**.
4. Suchen Sie den Datensatz erneut und öffnen Sie das Bildfeld wieder über
   **„prüfen“**.

Das Überspringen ändert weder den Datenbankwert noch die Bilddatei. Es ist
temporär und gilt nur für die aktuelle PHP-Sitzung und die ausgewählte Tabelle.
Vorgemerkte Änderungen verhindern das Überspringen; speichern oder verwerfen
Sie diese zuerst.

## 5. Wie die Bildsuche arbeitet

Das Script leitet aus dem alten Dateinamen nach Möglichkeit UrhNr, Datum und
Originalnamen ab. Es sucht in der Bibliothek unter:

```text
<Bildverzeichnis>/<UrhNr>/09/06/<Aufnahmedatum>/
```

Ist eine UrhNr erkennbar, wird deren Verzeichnis verwendet. Ist keine UrhNr
erkennbar, werden numerische UrhNr-Verzeichnisse geprüft. Ein erkennbares
achtstelliges Datum grenzt die erste Suche auf den entsprechenden Datumsordner
ein.

Wenn die Suche in den gezielten Ordnern **keinen Treffer** liefert, wird die
Datumseinschränkung aufgehoben und es werden die passenden Bildordner der
ermittelten UrhNr-Verzeichnisse geprüft. Sobald die gezielte Suche Treffer
findet, wird diese erweiterte Suche nicht zusätzlich ausgeführt.

Ein Dateiname wie:

```text
2_10_20170807_IMG_2009_c.jpg
```

wird zum Beispiel so interpretiert:

- `20170807` als Datum für die erste, gezielte Suche,
- `IMG_2009_c` als Originalname,
- `_c` am Ende als Kennzeichnung eines beschnittenen Bildes, die für den
  Suchbegriff entfernt wird,
- `img_2009` als Suchbegriff.

Damit können auch aktuelle Bilddateien mit `IMG_2009` gefunden werden, selbst
wenn sie unter einem anderen Datum abgelegt wurden und die Suche auf das Datum
des alten Datensatznamens keinen Treffer ergibt. Die Suche akzeptiert
Kandidatennamen, deren normalisierter Bildname den Suchbegriff enthält.

### Treffer sorgfältig prüfen

Ein gleicher Kameradateiname bedeutet nicht zwingend, dass es dasselbe Foto
ist. Bei mehreren Treffern können unterschiedliche Daten, Versionen oder
Wasserzeichenvarianten vorliegen. Vergleichen Sie daher:

- das Thumbnail mit dem erwarteten Bild,
- den angezeigten Dateinamen,
- Datum und gegebenenfalls weitere angezeigte Informationen.

Wählen Sie nicht allein aufgrund eines ähnlichen Dateinamens. Das Script kann
die inhaltliche Identität eines Fotos nicht automatisch beweisen.

## 6. Auswahl vormerken und speichern

Das Anklicken eines Thumbnail-Buttons **speichert den Dateinamen noch nicht
in der Datenbank**. Es merkt die Auswahl zunächst in der aktuellen Sitzung
vor.

Es gibt zwei Speicherwege:

### Einzelnen Datensatz speichern

1. Wählen Sie den passenden Thumbnail-Treffer.
2. Prüfen Sie in der Detailansicht den vorgemerkten neuen Dateinamen.
3. Klicken Sie auf **„Vorgemerkte Felder speichern“**.

### Mehrere Änderungen der aktuellen Seite speichern

1. Merken Sie die passenden Treffer für die gewünschten Datensätze der Seite
   vor.
2. Prüfen Sie die vorgemerkten Dateinamen in der linken Liste.
3. Klicken Sie unten links auf **„Änderungen dieser Seite speichern“**.

Nicht geänderte Datensätze werden beim Sammelspeichern übersprungen. Das
Sammelspeichern betrifft nur vorgemerkte Datensätze der aktuellen Seite.

Vor dem Speichern validiert das Script, dass das Bildfeld noch ersetzbar ist
und der ausgewählte Kandidat weiterhin in der Bibliothek gefunden wird. Bei
einer erfolgreichen Änderung wird der Datenbankwert aktualisiert und ein
Protokolleintrag geschrieben.

## 7. Neue Bilder nachträglich aufnehmen

Wenn nach einem ersten Suchlauf weitere Bilder in die Bibliothek hochgeladen
oder in das neue Format gebracht werden:

1. Lassen Sie Datensätze, deren Bilder noch fehlen, zunächst
   **vorläufig übersprungen**.
2. Stellen Sie die neuen Dateien im endgültigen Bibliotheksverzeichnis und im
   gültigen Zielformat bereit.
3. Klicken Sie in der Anwendung auf **„Übersprungene Datensätze wieder
   anzeigen“**.
4. Gehen Sie zur Tabelle und Seite des offenen Datensatzes und klicken Sie
   erneut auf **„prüfen“**.
5. Vergleichen Sie die jetzt angebotenen Thumbnails und speichern Sie den
   richtigen Treffer.

Eine erneute Suche wird beim Öffnen von **„prüfen“** ausgeführt. Ein
Browser-Neuladen allein aktualisiert keine bereits offene Detailansicht; öffnen
Sie deshalb den Datensatz beziehungsweise das Bildfeld erneut über den Link in
der linken Liste.

Gespeicherte Datensätze erscheinen nicht wieder in der offenen Liste, sofern
alle relevanten Bildfelder nun im Zielformat stehen. Die Protokolldatei ist
kein Suchindex und muss für eine erneute Suche nicht gelöscht werden.

## 8. Protokoll und Neustart

Änderungen werden zeilenweise in folgender Datei protokolliert:

```text
rework/PictFileReplace.log
```

Jede Zeile enthält unter anderem Zeitpunkt, Tabelle, Datensatz-ID sowie alte
und neue Bildwerte. Die Datei ist ein Änderungsnachweis. Sie steuert weder die
offene Datensatzliste noch die Bildsuche.

**Für einen Neustart der Auswahl müssen die Protokolle nicht gelöscht werden.**
Um übersprungene Datensätze wieder in die Auswahl aufzunehmen, verwenden Sie
den Button **„Übersprungene Datensätze wieder anzeigen“**. Gespeicherte
Datensätze bleiben ausgeblendet, weil ihre Bildfelder in der Datenbank bereits
korrigiert sind. Sie erneut in die offene Auswahl aufzunehmen, würde eine
Änderung der Bildwerte in der Datenbank erfordern; das Löschen des Protokolls
bewirkt dies ausdrücklich nicht.

Die Oberfläche bietet keine Funktion zum Löschen des Änderungsprotokolls.
Löschen oder bearbeiten Sie die Datei nicht als Methode, um einen Suchlauf
zurückzusetzen. Falls ein Protokoll aus einem konkreten betrieblichen Grund
archiviert werden soll, sichern Sie es zuerst und beachten Sie, dass dadurch
der dokumentierte Änderungsverlauf verloren gehen kann.

## 9. Häufige Fragen und Fehlersuche

### „Nach dem Speichern wird der Datensatz nicht mehr angezeigt.“

Das ist das erwartete Verhalten, wenn alle nicht-leeren Bildfelder nun das
Zielformat haben. Der Datensatz ist dann nicht mehr offen.

### „Ich finde den Datensatz, den ich übersprungen habe, nicht mehr.“

Klicken Sie oberhalb der Liste auf **„Übersprungene Datensätze wieder
anzeigen“**. Das setzt die temporäre Überspringliste der ausgewählten Tabelle
für die aktuelle Sitzung zurück.

### „Ein neu hochgeladenes Bild erscheint nicht.“

- Prüfen Sie, ob die Datei im konfigurierten Bildverzeichnis liegt.
- Prüfen Sie die Ordnerstruktur `<UrhNr>/09/06/<Datum>/`.
- Prüfen Sie das Zielformat, insbesondere `W` oder `N`, Datum und `.JPG`.
- Öffnen Sie den Datensatz über **„prüfen“** erneut, damit die Suche neu
  ausgeführt wird.
- Die erweiterte Suche nach anderen Datumsordnern wird nur ausgeführt, wenn
  die gezielte Datumssuche keinen Kandidaten findet.

### „Es gibt mehrere Treffer für denselben Bildnamen.“

Das kann bei wiederverwendeten Kameradateinamen vorkommen. Prüfen Sie alle
Thumbnails und die Dateinamen. Das Script priorisiert Namensübereinstimmungen,
entscheidet aber nicht automatisch, welche Aufnahme inhaltlich richtig ist.

### „Ein vorgemerkter Wert wird nicht gespeichert.“

Prüfen Sie die angezeigte Fehlermeldung. Der Kandidat muss beim Speichern noch
vorhanden sein, das Bildfeld muss noch einen alten ersetzbaren Wert enthalten
und das Formular muss aus derselben gültigen Sitzung stammen. Bei einer
zwischenzeitlichen Änderung öffnen Sie den Datensatz erneut und wählen den
Treffer noch einmal aus.

## 10. Sicherheit und Datenpflege

- Speichern Sie nur nach Sichtprüfung des ausgewählten Thumbnails.
- Bewahren Sie die Protokolldatei als Änderungsnachweis auf.
- Sichern Sie die Datenbank vor umfangreichen Sammeländerungen.
- Verwenden Sie das Tool nur mit vertrauenswürdigen Tabellen und
  Bildverzeichnissen.
- Halten Sie Datenbankzugangsdaten aus Dokumentation und Versionsverwaltung
  heraus.
