# Jalousie-Anzeige im Museumsformular

## Zweck

Die Detailbereiche des Museumsformulars lassen sich bei der Bearbeitung
einzeln ein- und ausklappen. Die bisherige Accordion.js-Abhängigkeit wird
dafür nicht benötigt. Zum Einsatz kommen die nativen HTML-Elemente
`<details>` und `<summary>`.

Die Implementierung befindet sich in
[`MuseenEdit_ph0_inc.php`](../FWHist/login/Modules/Oeffentlichkeitsarbeit/MuseenEdit_ph0_inc.php).

## Verhalten nach Datensatzstatus

Der initiale Zustand richtet sich nach `mu_id`:

```php
$isNewRecord = (int)$neu['mu_id'] === 0;
$blindOpen = $isNewRecord ? ' open' : '';
```

- **Neuanlage (`mu_id == 0`):** Jeder Detailbereich erhält das HTML-Attribut
  `open` und wird damit zunächst vollständig angezeigt.
- **Bearbeitung (`mu_id != 0`):** Das Attribut `open` fehlt. Die Detailbereiche
  starten geschlossen und können jeweils unabhängig geöffnet werden.

Die Information zur Neuanlage wird zusätzlich für den vorhandenen
Foto-/Upload-Ablauf in `$hide_area` verwendet. Der sichtbare Zustand der
Detailbereiche hängt jedoch ausschließlich von `$blindOpen` ab.

## Aufklappbare Bereiche

Die Jalousie umfasst fünf Themenbereiche:

1. Sammlungs-Beschreibung
2. Kustos
3. Infrastruktur
4. Öffnungszeiten
5. Führungen und Auskunft

Ein Bereich folgt diesem HTML-Muster:

```html
<details class="museum-blind" open>
    <summary>Sammlungs-Beschreibung</summary>
    <div class="museum-blind-content">
        <!-- Formularfelder -->
    </div>
</details>
```

Das `open`-Attribut wird in der tatsächlichen Ausgabe abhängig vom
Datensatzstatus erzeugt. Das native Element stellt die Bedienung mit Maus und
Tastatur bereit; ein zusätzliches JavaScript zum Öffnen und Schließen ist
nicht erforderlich.

## Darstellung

Die CSS-Regeln werden im Formular ausgegeben:

- Geschlossene Bereiche haben einen hellgrauen (`#f2f2f2`) Kopfbereich.
- Geöffnete Bereiche erhalten einen hellen pfirsichfarbenen Kopfbereich
  (`#f9d9bd`).
- Rahmen und Abstände trennen die Themenbereiche visuell.
- `:focus-visible` hebt den Tastaturfokus des Summary-Elements hervor.

Die Inhalte der Detailbereiche werden bei geschlossenen Elementen durch das
native `<details>`-Verhalten verborgen.

## Abgrenzung

Die Jalousie ersetzt die bisherigen Accordion-Container ausschließlich für
die fünf genannten Detailbereiche. Die allgemeinen Museumsfelder vor der
Sammlung sowie Foto-, Änderungs- und Formularbereiche außerhalb dieser
`<details>`-Elemente bleiben unabhängig davon sichtbar.

Die früheren `accordionjs`-Container sowie die Einbindungen von
`accordion.min.js` und `add_accordion.js` werden in dieser Formularansicht
nicht mehr verwendet.

## Validierung

Für die Änderung wurde die PHP-Syntax von
`MuseenEdit_ph0_inc.php` geprüft; `php -l` meldete keine Syntaxfehler.
Zusätzlich war `git diff --check` erfolgreich. Ein Browser-Test wurde nicht
durchgeführt.
