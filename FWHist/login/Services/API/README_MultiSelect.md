# Multi-Select-Kaskade (Nachfolger von multidropDown.php)

Ablöse für `multidropDown.php` + `VF_MultiSel_Opt_jq_API.php`: generisch,
PDO-basiert (über `DB_GenericLog`), kein jQuery/Prototype mehr im Frontend.

## Dateien und Ziel-Platzierung (gemäß RepoStructBase.md)

```
login/Services/MultiSelect/MultiSelectProviderInterface.php
login/Services/MultiSelect/MultiSelectResolver.php
login/Services/MultiSelect/SammlungMultiSelect.php
login/Services/MultiSelect/ArchivordnungMultiSelect.php
login/Services/API/MultiSelectAPI.php
login/Services/js/fw_multi_select.js
```

Die Datei `FormRendererFlex_Ergaenzung.php` ist **kein eigenständiges Skript**,
sondern eine Methode zum Einfügen in die bestehende
`login/Services/FormsRendererFlex/...FormRendererFlex.php`.

Im HTML-Head/Footer der jeweiligen Seite einmalig einbinden:

```html
<script src="/js/fw_multi_select.js" defer></script>
<!-- Pfad an tatsächliche Ablage von js/-Assets in eurem Setup anpassen -->
```

## Verwendung

**Direkt als Web Component (ohne FormRendererFlex):**

```html
<fw-multi-select
    config-id="ARCHIVORDNUNG"
    name="ad_archivwert"
    api-url="/login/Services/API/MultiSelectAPI.php"
></fw-multi-select>
```

**Über FormRendererFlex (nach Einfügen der Ergänzungsmethode):**

```php
echo $renderer->renderMultiSelectFieldFlex(
    fieldName: 'ad_archivwert',
    configId: 'ARCHIVORDNUNG',
    apiUrl: '/login/Services/API/MultiSelectAPI.php'
);
```

Neue Konfiguration hinzufügen = neue Klasse, die
`MultiSelectProviderInterface` implementiert, + 1 Zeile in
`MultiSelectResolver::REGISTRY`. Kein Anfassen von API oder Frontend nötig.

## Wichtige Entscheidungen (aus der Abstimmung)

- Namespace **`FWHist`** (produktiver Code), nicht `FWArchiv`.
- Tabellen: `hi_sammlung`, `hi_archivord`, `hi_archord_erw` (nicht die
  `fh_`/`ar_`-Varianten aus den Kommentaren). `hi_archord_erw` ist NICHT
  mandantenspezifisch.
- Archivordnung bleibt eine **eigenständige, kaskadierende Dropdown-Variante**
  neben dem bestehenden Tree-Select-Widget (`fw_tree_select.js`) – bewusst
  für Einsatzorte, wo ein Baum nicht passt.
- DB-Zugriff und Query-Logging laufen über `DB_GenericLog` (nicht rohes PDO).
- `MultiSelectAPI.php` ist ein **eigener Endpunkt** neben `ListHostAPI.php`
  (anderer Parameter-Vertrag, kein `sel_id` nötig).
- Web Component statt Funktionen-Bibliothek: mehrere Instanzen pro Seite
  ohne ID-Kollision (Original hatte feste IDs `level1`..`level6`).

## Strukturierte Endauswertung (statt Composite-String)

Der Hidden-Input des Web Components (bzw. `ad_archivwert`) hält weiterhin
intern den Composite-String (z. B. `"07.01 01 00 00"`), weil die Kaskade
diesen zum Nachladen der jeweils nächsten Ebene braucht. Für die **Auswertung
beim Speichern** (z. B. in einzelne DB-Spalten) gibt es jetzt

```php
ArchivordnungMultiSelect::parseCompositeValue('07.01');
// -> ['07', '01']

ArchivordnungMultiSelect::parseCompositeValue('07.01 01 00 00 00');
// -> ['07', '01', '01', '00', '00', '00']
//     ad_sg  ad_sub_sg  al_lcsg  al_lcssg  al_lcssg_0  al_lcssg_1
```

Diese Methode dort aufrufen, wo der übermittelte `ad_archivwert`-Wert bisher
1:1 in die DB geschrieben wurde – Ergebnis ist ein Array mit 2 bis 6
Elementen, je nach erreichter Tiefe.

## Status

- `renderMultiSelectFieldFlex()` ist laut Rückmeldung bereits in
  `FormRendererFlex` eingebaut und funktioniert soweit.
- Bug behoben: Ebene 3+ der Archivordnung lieferte keine Treffer
  (`al_sg`-Punkt wurde fälschlich entfernt) – siehe offene Punkte, Punkt 2.
- `AppLogger`-Einbindung korrigiert (globaler Namespace, expliziter require).

## Offene Punkte / bitte prüfen

1. ~~Ebene 1 der Archivordnung~~ **geklärt**: kommt jetzt aus `hi_archivord`
   (`ar_sub_sg = '0'`), kein hartcodierter Array mehr.
2. ~~`al_sg`-Format in `hi_archord_erw`~~ **Bugfix (08.09.)**: `al_sg` wird
   bestätigt **mit** Punkt gespeichert (`$al_sg = $ar_sg.'.'.$ar_sub_sg`,
   z. B. `"07.01"`). Die frühere `normalizeSg()`-Methode entfernte den Punkt
   fälschlich, wodurch Ebene 3+ nie Treffer fand (Symptom: nur die ersten
   2 Ebenen erschienen). Ist jetzt in `ArchivordnungMultiSelect::level3()`
   korrigiert.
3. ~~`AppLogger`-Aufruf~~ **Bugfix (08.09.)**: `AppLogger.php` liegt im
   **globalen Namespace** (kein `namespace`-Header), nicht unter
   `FWHist\Services`. `MultiSelectAPI.php` bindet die Datei jetzt explizit
   per `require_once` ein (PSR-4-Autoloading greift hier nicht) und ruft
   `\AppLogger::error(...)` im globalen Namespace auf. **Pfad-Annahme**:
   `login/Services/AppLogger.php` (gleiche Ebene wie `login/Services/MultiSelect/`)
   – bitte prüfen, falls die Datei tatsächlich woanders liegt.
4. **`Composer`-Autoload**: `MultiSelectAPI.php` erwartet
   `dirname(__DIR__, 3) . '/vendor/autoload.php'`, genau wie `ListHostAPI.php`
   (also gleiche Verzeichnistiefe vorausgesetzt).
5. ~~Sammlung – Gruppenfilter~~ **geklärt**: `SammlungMultiSelect` zeigt auf
   Ebene 1 tatsächlich vorhandene `sa_grup`-Werte (DISTINCT-Query; Klartext aus
   `VF_Sel_SA_Such`, wo bekannt, sonst den Gruppencode), auf Ebene 2 die
   `hi_sammlung`-Zeilen dieser Gruppe (`sa_sammlg` / `sa_name`) und ab Ebene 3
   die Kinder anhand des `sa_grup`-Elternschlüssels.
6. **Vorbelegung (`value`-Attribut) des Web Components** setzt aktuell nur
   das Hidden-Feld, lädt aber nicht automatisch alle Zwischen-Ebenen der
   Kaskade nach (dafür bräuchte es eine "Pfad aus Endwert auflösen"-Methode
   je Provider – bewusst ausgelassen, um den Umfang nicht zu sprengen; kann
   bei Bedarf ergänzt werden, z. B. `MultiSelectProviderInterface::resolvePath()`).
7. Kein PHPUnit/Test-Code enthalten – auf Wunsch ergänze ich Tests für die
   Provider-Klassen (reine SQL-Logik, gut isoliert testbar mit einer
   In-Memory-SQLite-Variante oder Mock von `DB_GenericLog`).
