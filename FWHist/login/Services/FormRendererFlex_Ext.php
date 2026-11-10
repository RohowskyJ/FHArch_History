<?php

namespace FWHist\Services;

use BadMethodCallException;
use LogicException;

/**
 * FormRendererFlex_Ext
 * --------------------
 * Erweiterung von FormRendererFlex um die Autocomplete-Bausteine der Anwendung (früher die Funktionen
 * AutoCompForm_*() in CommFuncsLib.php). Diese Teile gehören zur APP, nicht zur Tool-Basis FormRendererFlex.
 *
 * FormRendererFlex ist "final" (und seine Felder sind private): die Klasse wird daher nicht abgeleitet, sondern
 * umhüllt (Delegation). Ein Objekt dieser Klasse ersetzt in den Formularen direkt ein FormRendererFlex:
 *
 *     $forms = new FormRendererFlex_Ext($meta, $phase, $neu, [], $editProtect, $module);   // gleiche Parameter
 *     echo $forms->renderTextLikeFieldFlex('ei_name', 60);          // wird an FormRendererFlex weitergereicht
 *     echo $forms->renderAutoCompStaat();                           // Autocomplete-Baustein, '' bei Schreibschutz
 *
 * Wird FormRendererFlex einmal nicht mehr "final" deklariert, kann diese Klasse ohne Änderung der Aufrufer auf
 * "extends FormRendererFlex" umgestellt werden (Konstruktor und __call entfallen dann).
 *
 * Bausteine ohne Formular (z. B. Mandantenauswahl auf einer Listenseite) sind statische Methoden ohne Schreibschutz:
 *
 *     echo FormRendererFlex_Ext::autoCompMandant('selection', 'mand', 'mand', '', 'mand_id');
 *
 * Instanzmethoden: renderAutoCompXxx() <-> statisch autoCompXxx() (gleiche Parameter). Die Instanzvariante liefert
 * bei Schreibschutz (editProtect oder phase != 0) einen leeren String, die Aufrufer brauchen kein "if (!$editProtect)".
 *
 * Die JavaScript-Dateien (AutoComp_Staat.js, AutoComp_Bdld.js, AutoComp_Mandant.js, AutoComp_Urheber.js ...) müssen
 * weiterhin vom Formular eingebunden werden.
 *
 * @method string renderAutoCompArchOrd()
 * @method string renderAutoCompAbk(string $group = '')
 * @method string renderAutoCompBenutzer()
 * @method string renderAutoCompBdld(string $fieldId = 'bdld', string $fieldName = 'bdld', string $value = '')
 * @method string renderAutoCompEigent($t, bool $cl = false, $j = '1')
 * @method string renderAutoCompFirma(string $funkt = 'F')
 * @method string renderAutoCompAufbau(string $funkt = 'A')
 * @method string renderAutoCompMandant(string $mode = 'selection', string $fieldId = 'mandant', string $fieldName = 'mandant', string $value = '', string $hiddenFieldName = 'mandant_id')
 * @method string renderAutoCompMitgl($em)
 * @method string renderAutoCompOrt()
 * @method string renderAutoCompSammlg()
 * @method string renderAutoCompStaat(string $fieldId = 'staat', string $fieldName = 'staat', string $value = '')
 * @method string renderAutoCompUrheber(string $mode = 'selection', string $fieldId = 'urheber', string $fieldName = 'urheber', string $value = '', string $hiddenFieldName = 'urheber_id', string $hiddenNameField = 'urheber_name')
 */
final class FormRendererFlex_Ext
{
    private const BOX = "style='background-color: PeachPuff; padding: 10px;'";

    private ?FormRendererFlex $base = null;

    /** true = keine Bearbeitung (editProtect oder phase != 0): Autocomplete-Bausteine entfallen */
    private bool $protected;

    /**
     * Gleiche Parameter wie FormRendererFlex. Ohne $meta (null) sind nur die Autocomplete-Bausteine verfügbar,
     * z. B. auf Listenseiten ohne Formular.
     */
    public function __construct(
        ?TableColumnMetadata $meta = null,
        int $phase = 0,
        array $data = [],
        array $errorMsgs = [],
        bool $editProtect = false,
        string $module = ''
    ) {
        if ($meta !== null) {
            $this->base = new FormRendererFlex($meta, $phase, $data, $errorMsgs, $editProtect, $module);
        }
        $this->protected = $editProtect || $phase !== 0;
    }

    /** Das umhüllte FormRendererFlex (z. B. um es an eine Funktion zu übergeben, die den Basistyp verlangt). */
    public function base(): FormRendererFlex
    {
        if ($this->base === null) {
            throw new LogicException('FormRendererFlex_Ext wurde ohne TableColumnMetadata erzeugt.');
        }

        return $this->base;
    }

    /**
     * renderAutoCompXxx() -> statisches autoCompXxx() (bei Schreibschutz ''), alles andere -> FormRendererFlex.
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (str_starts_with($name, 'renderAutoComp')) {
            $builder = 'autoComp' . substr($name, strlen('renderAutoComp'));
            if (!method_exists(self::class, $builder)) {
                throw new BadMethodCallException('Unbekannter Autocomplete-Baustein: ' . $name);
            }

            return $this->protected ? '' : self::$builder(...$arguments);
        }

        $base = $this->base();
        if (!method_exists($base, $name)) {
            throw new BadMethodCallException('Unbekannte Methode: ' . $name);
        }

        return $base->$name(...$arguments);
    }

    private static function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    // ==========================================================================================
    // Autocomplete-Bausteine (Teil der Form). Rückgabe: HTML, kein echo.
    // ==========================================================================================

    /** Archivordnung (Feld aord / aord_id) */
    public static function autoCompArchOrd(): string
    {
        $box = self::BOX;

        return <<<HTML
<div class='field-row' {$box}>
<div class='field-label'><label for="aord">Archiv- Ordnung auswählen (um zu Ändern):</label></div>
<div class='field-control'><input type="text" id="aord" name="aord" /></div>
<!-- Optional: verstecktes Feld für ID -->
<input type="hidden" id="aord_id" name="aord_id" />
</div>

HTML;
    }

    /** Abkürzungen (Feld abk / abk_id), $group schränkt die Treffer ein */
    public static function autoCompAbk(string $group = ''): string
    {
        $box = self::BOX;
        $group = self::e($group);

        return <<<HTML
<div class='abk-autocomplete'>
<button type='button' class='abk-autocomplete-toggle' aria-expanded='false' aria-controls='abk-autocomplete-panel'>Abkürzung ändern</button>
<div id='abk-autocomplete-panel' class='abk-autocomplete-panel' hidden>
    <div class='field-row' {$box}>
    <div class='field-label'><label for="abk">Abkürzung auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="abk" name="abk" data-group="{$group}" /></div>
    <input type="hidden" id="abk_id" name="abk_id" />
    </div>
</div>
</div>

HTML;
    }

    /** Benutzer-Kurzzeichen (Feld benutzer / ben_id) */
    public static function autoCompBenutzer(): string
    {
        $box = self::BOX;

        return <<<HTML
<div class='field-row' {$box}>
<div class='field-label'><label for="benutzer">Benutzer auswählen (um zu Ändern):</label></div>
<div class='field-control'><input type="text" id="benutzer" name="benutzer" /></div>
<!-- Optional: verstecktes Feld für ID -->
<input type="hidden" id="ben_id" name="ben_id" />
</div>

HTML;
    }

    /** Bundesland-Kurzzeichen (Feld bdld / bdld_id, hängt am Staat-Feld staat_id) */
    public static function autoCompBdld(string $fieldId = 'bdld', string $fieldName = 'bdld', string $value = ''): string
    {
        $box = self::BOX;
        $fieldId = self::e($fieldId);
        $fieldName = self::e($fieldName);
        $value = self::e($value);

        return <<<HTML
<div class='bdld-autocomplete'>
<button type='button' class='bdld-autocomplete-toggle' aria-expanded='false' aria-controls='bdld-autocomplete-panel'>Bundesland ändern</button>
<div id='bdld-autocomplete-panel' class='bdld-autocomplete-panel' hidden>
    <div class='field-row' {$box}>
    <div class='field-label'><label for="{$fieldId}">Bundesland auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="{$fieldId}" name="{$fieldName}" value="{$value}" data-state-id="staat_id" data-hidden-id="bdld_id" /></div>
    <input type="hidden" id="bdld_id" name="bdld_id" />
    </div>
</div>
</div>

HTML;
    }

    /**
     * Eigentümer/Urheber (älterer Baustein, w3-container).
     * Korrigiert gegenüber der Funktion: das id-Attribut von suggestEigener_<n> war nicht geschlossen.
     *
     * @param mixed $t  'E' = Eigentümer, sonst Urheber
     * @param bool  $cl true = Weiter-Button anfügen
     * @param mixed $j  Zähler für mehrere Felder
     */
    public static function autoCompEigent($t, bool $cl = false, $j = '1'): string
    {
        $j = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $j);
        $label = ($t === 'E')
            ? '<b>Eigentümer Namen suchen:</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; '
            : '<b>Urheber Namen suchen:</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ';
        $button = $cl ? "<button type='submit' name='phase' value='1' class=green>Weiter</button></p>" : '';

        return <<<HTML
<div class='w3-container' style='background-color: PeachPuff; padding: 10px;'>
{$label}
    <input type="text" class="autocomplete" data-proc="Eigentuemer" data-target="suggestEigener_{$j}" data-feed="eigentuemer_{$j}" size='50'/>
</div>
<div id="suggestEigener_{$j}" class="suggestions">
   <input type="hidden" name="eigentuemer_{$j}" id="eigentuemer_{$j}" />
</div>
{$button}
HTML;
    }

    /** Firmen (Feld firm / firm_id), $funkt 'A' oder 'F' */
    public static function autoCompFirma(string $funkt = 'F'): string
    {
        $box = self::BOX;
        $funkt = self::e(in_array($funkt, ['A', 'F'], true) ? $funkt : '');

        return <<<HTML
<div class='firma-autocomplete'>
<button type='button' class='firma-autocomplete-toggle' aria-expanded='false' aria-controls='firma-autocomplete-panel'>Firma ändern</button>
<div id='firma-autocomplete-panel' class='firma-autocomplete-panel' hidden>
    <div class='field-row' {$box}>
    <div class='field-label'><label for="firm">Firma auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="firm" name="firm" data-funkt="{$funkt}" data-hidden-id="firm_id" /></div>
    <input type="hidden" id="firm_id" name="firm_id" />
    </div>
</div>
</div>

HTML;
    }

    /** Aufbauhersteller (Feld aufb / aufb_id), $funkt 'A' oder 'F' */
    public static function autoCompAufbau(string $funkt = 'A'): string
    {
        $box = self::BOX;
        $funkt = self::e(in_array($funkt, ['A', 'F'], true) ? $funkt : '');

        return <<<HTML
<div class='aufbau-autocomplete'>
<button type='button' class='firma-autocomplete-toggle' aria-expanded='false' aria-controls='aufbau-autocomplete-panel'>Aufbauhersteller ändern</button>
<div id='aufbau-autocomplete-panel' class='firma-autocomplete-panel' hidden>
    <div class='field-row' {$box}>
    <div class='field-label'><label for="aufb">Aufbauhersteller auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="aufb" name="aufb" data-funkt="{$funkt}" data-hidden-id="aufb_id" /></div>
    <input type="hidden" id="aufb_id" name="aufb_id" />
    </div>
</div>
</div>

HTML;
    }

    /**
     * Mandanten (Feld $fieldId, Hidden-Feld $hiddenFieldName; ID des Hidden-Felds: {$fieldId}_id)
     *
     * @param string $mode 'selection' (immer sichtbar) oder 'toggle' (per Button einblendbar)
     */
    public static function autoCompMandant(
        string $mode = 'selection',
        string $fieldId = 'mandant',
        string $fieldName = 'mandant',
        string $value = '',
        string $hiddenFieldName = 'mandant_id'
    ): string {
        $box = self::BOX;
        $fieldId = self::e($fieldId);
        $fieldName = self::e($fieldName);
        $value = self::e($value);
        $hiddenFieldName = self::e($hiddenFieldName);
        $open = $close = '';
        if ($mode === 'toggle') {
            $open = "<button type='button' class='mandant-autocomplete-toggle' aria-expanded='false' aria-controls='mandant-autocomplete-panel'>Mandant ändern</button>\n<div id='mandant-autocomplete-panel' class='mandant-autocomplete-panel' hidden>";
            $close = '</div>';
        }

        return <<<HTML
<div class='mandant-autocomplete' data-mandant-autocomplete>
{$open}
    <div class='field-row' {$box}>
    <div class='field-label'><label for="{$fieldId}">Mandant auswählen:</label></div>
    <div class='field-control'><input type="text" id="{$fieldId}" name="{$fieldName}" value="{$value}" /></div>
    <input type="hidden" id="{$fieldId}_id" name="{$hiddenFieldName}" />
    </div>
{$close}
</div>

HTML;
    }

    /**
     * Mitglieder (Feld mitgl / mitgl_id / email_id)
     *
     * @param mixed $em 1 = Mitglieder mit E-Mail
     */
    public static function autoCompMitgl($em): string
    {
        $box = self::BOX;
        $em = self::e((string) $em);

        return <<<HTML
<div class='field-row' {$box}>
<div class='field-label'><label for="mitgl">Mitglied auswählen :</label></div>
<div class='field-control'><input type="text" id="mitgl" name="mitgl" /></div>
<input type='input' id='email' value='{$em}' >
<!-- Optional: verstecktes Feld für ID -->
<input type="hidden" id="mitgl_id" name="mitgl_id" />
<!-- Optional: verstecktes Feld für E-Mail -->
<input type="hidden" id="email_id" name="email_id" />
</div>

HTML;
    }

    /** Orts-Auswahl für PSA (Feld ort / ort_id) */
    public static function autoCompOrt(): string
    {
        $box = self::BOX;

        return <<<HTML
<div class='field-row' {$box}>
<div class='field-label'><label for="ort">Ort auswählen (um zu ändern):</label></div>
<div class='field-control'><input type="text" id="ort" name="ort" /></div>
<!-- Optional: verstecktes Feld für ID -->
<input type="hidden" id="ort_id" name="ort_id" />
</div>

HTML;
    }

    /** Sammlung (Feld sammlg / sammlg_id) */
    public static function autoCompSammlg(): string
    {
        $box = self::BOX;

        return <<<HTML
<div class='field-row' {$box}>
<div class='field-label'><label for="sammlg">Sammlung auswählen (um zu ändern):</label></div>
<div class='field-control'><input type="text" id="sammlg" name="sammlg" /></div>
<!-- Optional: verstecktes Feld für ID -->
<input type="hidden" id="sammlg_id" name="sammlg_id" />
</div>

HTML;
    }

    /** Staats-Kurzzeichen (Feld staat / staat_id) */
    public static function autoCompStaat(string $fieldId = 'staat', string $fieldName = 'staat', string $value = ''): string
    {
        $box = self::BOX;
        $fieldId = self::e($fieldId);
        $fieldName = self::e($fieldName);
        $value = self::e($value);

        return <<<HTML
<div class='staat-autocomplete'>
<button type='button' class='staat-autocomplete-toggle' aria-expanded='false' aria-controls='staat-autocomplete-panel'>Staat ändern</button>
<div id='staat-autocomplete-panel' class='staat-autocomplete-panel' hidden>
    <div class='field-row' {$box}>
    <div class='field-label'><label for="{$fieldId}">Staat auswählen (um zu ändern):</label></div>
    <div class='field-control'><input type="text" id="{$fieldId}" name="{$fieldName}" value="{$value}" /></div>
    <input type="hidden" id="staat_id" name="staat_id" />
    </div>
</div>
</div>

HTML;
    }

    /**
     * Urheber (Feld $fieldId, Hidden-Felder {$fieldId}_id und {$fieldId}_name)
     *
     * @param string $mode            'selection' (immer sichtbar) oder 'toggle' (per Button einblendbar)
     * @param string $hiddenFieldName name des Hidden-Felds für die Urheber-ID (ei_id)
     * @param string $hiddenNameField name des Hidden-Felds für den Urheber-Namen (wird von AutoComp_Urheber.js
     *                                befüllt, z. B. fürs Wasserzeichen beim Foto-Upload)
     */
    public static function autoCompUrheber(
        string $mode = 'selection',
        string $fieldId = 'urheber',
        string $fieldName = 'urheber',
        string $value = '',
        string $hiddenFieldName = 'urheber_id',
        string $hiddenNameField = 'urheber_name'
    ): string {
        $box = self::BOX;
        $fieldId = self::e($fieldId);
        $fieldName = self::e($fieldName);
        $value = self::e($value);
        $hiddenFieldName = self::e($hiddenFieldName);
        $hiddenNameField = self::e($hiddenNameField);
        $open = $close = '';
        if ($mode === 'toggle') {
            $open = "<button type='button' class='urheber-autocomplete-toggle' aria-expanded='false' aria-controls='urheber-autocomplete-panel'>Urheber ändern</button>\n<div id='urheber-autocomplete-panel' class='urheber-autocomplete-panel' hidden>";
            $close = '</div>';
        }

        return <<<HTML
<div class='urheber-autocomplete' data-urheber-autocomplete>
{$open}
    <div class='field-row' {$box}>
    <div class='field-label'><label for="{$fieldId}">Urheber auswählen:</label></div>
    <div class='field-control'><input type="text" id="{$fieldId}" name="{$fieldName}" value="{$value}" /></div>
    <input type="hidden" id="{$fieldId}_id" name="{$hiddenFieldName}" />
    <input type="hidden" id="{$fieldId}_name" name="{$hiddenNameField}" />
    </div>
{$close}
</div>

HTML;
    }
}
