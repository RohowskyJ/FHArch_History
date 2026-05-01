<?php
declare(strict_types=1);

namespace Fharch\Core\Services;

/**
 * FormRendererFlex
 * ----------------
 * Schlanke Renderer-Klasse, die NUR Flex-basierte Render-Funktionen bereitstellt.
 * Enthält:
 * - renderHeader()
 * - renderTrenner()
 * - renderTitleDataBlockFlex()
 * - renderTextLikeFieldFlex()
 * - renderTextFieldFlex() nur mehr als Wrap für renderTextLikeFieldFlex()
 * - renderTextareaFieldFlex()  nur mehr als Wrap füe renderTextLikeFieldFlex()
 * - renderSelectFieldFlex()
 * - renderRadioFieldFlex()
 * - renderCheckboxFieldFlex()
 * - renderDatePickerFieldFlex()
 * - renderFileUploadFieldFlex()
 * - validateAll() + Validierungs-Helper
 * - getErrorMessages()
 *
 * Erwartet eine Metadatenklasse TableColumnMetadata mit:
 * - getMaxLengthsMap(): array  (fieldName => maxLen)
 * - getCommentsMap(): array    (fieldName => label/Kommentar)
 */
final class FormRendererFlex
{
    private TableColumnMetadata $meta;
    
    /** 0 = editierbar, !=0 = gesperrt (z.B. Anzeige/Bestätigung) */
    private int $phase;
    
    /** Anzahl Fehler (wird nach validateAll gesetzt) */
    private int $errors = 0;
    
    /** Eingabedaten (z.B. $_POST oder DB-Zeile) */
    private array $data;
    
    /** Fehlermeldungen pro Feldname */
    private array $errorMsgs = [];
    
    /** Wenn true: auch in phase=0 keine Bearbeitung */
    private bool $editProtect;

    /** Optionaler Modulname (z.B. für Logging) */
    private string $module;
    
    /**
     * @param TableColumnMetadata $meta
     * @param int $phase
     * @param array $data
     * @param array $errorMsgs
     * @param bool $editProtect
     * @param string $module
     */
    public function __construct(
        TableColumnMetadata $meta,
        int $phase,
        array $data,
        array $errorMsgs = [],
        bool $editProtect = false,
        string $module = ''
        ) {
            $this->meta        = $meta;
            $this->phase       = $phase;
            $this->data        = $data;
            $this->errorMsgs   = $errorMsgs;
            $this->editProtect = $editProtect;
            $this->module      = $module;
    }
    
    /* ==========================================================
     * Helpers (Flex Layout Wrapper)
     * ========================================================== */
    
    private function isDisabled(): bool
    {
        return ($this->phase !== 0) || $this->editProtect;
    }
    
    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES);
    }
    
    private function renderLabel(string $fieldName, bool $strong = false, string $fallback = ''): string
    {
        $comments = $this->meta->getCommentsMap();
        
        // Debug-Ausgabe (temporär)
        // Debug-Ausgabe in eigene Logdatei
        error_log("renderLabel called with fieldName='$fieldName', fallback='$fallback'\n", 3, __DIR__ . '/formflex_log.log');
        error_log("Comments map: " . print_r($comments, true) . "\n", 3, __DIR__ . '/formflex_log.log');
        
        if ($fallback !== '') {
            $label = $fallback;
        } elseif (isset($comments[$fieldName]) && $comments[$fieldName] !== '') {
            $label = (string)$comments[$fieldName];
        } else {
            $label = ucfirst($fieldName);
        }
        
        $labelEsc = $this->esc($label);
        $fieldNameEsc = $this->esc($fieldName);
        
        if ($strong) {
            return "<label for='{$fieldNameEsc}'><strong>{$labelEsc}</strong></label>";
        }
        
        return "<label for='{$fieldNameEsc}'>{$labelEsc}</label>";
    }
    
    /**
     * Flex-Wrapper: zwei Spalten (Label | Control)
     * Erwartet, dass Sie im CSS (global) z.B. folgende Klassen definieren:
     * - .field-row, .field-label, .field-control, .input, .readonly-block, .error, .hint, .danger-border
     */
    private function wrapFlexRow(string $fieldName, string $labelHtml, string $controlHtml): string
    {
        return "
          <div class='field-row' data-field='{$this->esc($fieldName)}'>
            <div class='field-label'>{$labelHtml}</div>
            <div class='field-control'>{$controlHtml}</div>
          </div>
        ";
    }
    
    /* ==========================================================
     * UI Blöcke
     * ========================================================== */
    
    // Seitentitel (neutral, kann per CSS überschrieben werden)
    public function renderHeader(string $text): string
    {
        $style = "background-color:#b0b0b0;color:#333;font-size:0.95em;text-align:center;
                  padding:0.5em 1em;margin:1em 0;font-weight:700;border-radius:4px;";
        return "<div style=\"$style\">".$this->esc($text)."</div>";
    }
    
    // Trennzeile / Abschnittsbalken
    public function renderTrenner(string $text): string
    {
        $style = "background-color:#e0e0e0;height:1.2em;width:100%;margin:1em 0;";
        return "<div style=\"$style\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$this->esc($text)."</div>";
    }
    
    /* ==========================================================
     * Flex Render Funktionen (Felder)
     * ========================================================== */
    
    /**
     * Titel + Datablock (read-only freundlich): Label fett, Wert als Block wenn readonly/disabled.
     */
    public function renderTitleDataBlockFlex(
        string $fieldName,
        int $fieldLength = 0,
        string $infoText = '',
        string $fieldAttr = '',
        string $readOnly = ''
        ): string {
            $value      = $this->esc((string)($this->data[$fieldName] ?? ''));
            $maxLengths = $this->meta->getMaxLengthsMap();
            $maxLength  = (int)($maxLengths[$fieldName] ?? 0);
            $error      = (string)($this->errorMsgs[$fieldName] ?? '');
            
            $readOnlyMode = ($readOnly === 'readonly');
            $disabled     = $this->isDisabled();
            
            $maxlengthAttr = ($maxLength > 0) ? "maxlength='{$maxLength}'" : '';
            $sizeAttr      = ($fieldLength > 0) ? "size='{$fieldLength}'" : '';
            
            $labelHtml = $this->renderLabel($fieldName, true, '');
            
            if ($readOnlyMode || $disabled) {
                $control = "<div class='readonly-block'>{$value}</div>";
            } else {
                $class   = $error ? 'input danger-border' : 'input';
                $control = "<input class='{$class}' type='text' id='{$this->esc($fieldName)}' name='{$this->esc($fieldName)}'
                        value='{$value}' {$maxlengthAttr} {$sizeAttr} {$fieldAttr}>";
            }
            
            if ($error !== '') {
                $control .= "<div class='error'>".$this->esc($error)."</div>";
            }
            if ($infoText !== '') {
                $control .= "<div class='hint'>".$this->esc($infoText)."</div>";
            }
            
            $labelHtml = $readOnlyMode
            ? "<strong>{$this->esc($label)}</strong>"
            : "<label for='{$this->esc($fieldName)}'><strong>{$this->esc($label)}</strong></label>";
            
            return $this->wrapFlexRow($fieldName, $labelHtml, $control);
    }
     
    /**
     * NEU: Eine Funktion für "Text-like" Felder.
     * - Wählt input vs textarea anhand maxLen (Meta) und optionaler Überschreibung.
     *
     * @param string $fieldName
     * @param int    $fieldLength   (wie bisher: size="" für input; bei textarea ignoriert)
     * @param string $infoText
     * @param string $fieldAttr     Zusätzliche Attribute (pattern, placeholder, etc.)
     * @param string $readOnly      Input feld kann nur gelesen werden wenn = 'readonly'
     * @param int    $switchAt      Grenze: bis inkl. switchAt => input, darüber => textarea
     * @param int    $maxRows       Max Zeilen für textarea
     * @param int    $minRows       Min Zeilen für textarea
     * @param int    $colsEstimate  Zeichen pro Zeile (Schätzwert für Rows-Berechnung)
     * @param float  $visibleRatio  Ziel: wieviel vom maxLen soll ohne Scrollen sichtbar sein (0..1)
     */
    public function renderTextLikeFieldFlex(
        string $fieldName,
        int $fieldLength = 0,
        string $infoText = '',
        string $fieldAttr = '',
        string $readOnly = '',
        int $switchAt = 70,
        int $maxRows = 6,
        int $minRows = 3,
        int $colsEstimate = 46,
        float $visibleRatio = 0.67
        
        ): string {
            $value      = $this->esc((string)($this->data[$fieldName] ?? ''));
            $maxLengths = $this->meta->getMaxLengthsMap();
            $maxLen     = (int)($maxLengths[$fieldName] ?? 0);
            $error      = (string)($this->errorMsgs[$fieldName] ?? '');
            
            // $readonly = $this->readOnly;               // 'readonly' oder ''
            $disabled = $this->isDisabled() ? 'disabled' : '';
            
            // maxlength-Attribut nur wenn sinnvoll gesetzt
            $maxlengthAttr = ($maxLen > 0) ? "maxlength='{$maxLen}'" : '';
            
            $class = $error ? 'input danger-border' : 'input';
            
            // Label
            $labelHtml = $this->renderLabel($fieldName, true,'');
            
            // Disabled/Readonly Darstellung (wie bisher: bei disabled => readonly-block)
            if ($disabled !== '') {
                $control = "<div class='readonly-block' style='white-space:pre-wrap'>{$value}</div>";
            } else {
                // Entscheid: input vs textarea
                $useTextarea = ($maxLen > $switchAt);
                
                // Wenn maxLen unbekannt (0), konservativ: input (kannst du auch anders wählen)
                if ($maxLen === 0) {
                    $useTextarea = false;
                }
                
                if (!$useTextarea) {
                    $sizeAttr = ($fieldLength > 0) ? "size='{$fieldLength}'" : '';
                    $control = "<input class='{$class}' type='text' id='{$this->esc($fieldName)}' name='{$this->esc($fieldName)}'
                          value='{$value}' {$maxlengthAttr} {$sizeAttr} {$fieldAttr} {$readOnly} {$disabled}>";
                } else {
                    // Rows-Berechnung: so, dass ca. visibleRatio * maxLen sichtbar ist.
                    // Annahme: colsEstimate Zeichen pro Zeile (typisch 42–52 bei Formularbreite).
                    // rows = ceil((maxLen * visibleRatio) / colsEstimate)
                    $targetVisibleChars = (int)ceil(max(1, $maxLen) * max(0.1, min(1.0, $visibleRatio)));
                    $rows = (int)ceil($targetVisibleChars / max(10, $colsEstimate));
                    
                    // Begrenzen
                    $rows = max($minRows, min($maxRows, $rows));
                    
                    // Optional: rows dynamisch leicht am IST-Inhalt orientieren (macht UX angenehmer)
                    // aber weiterhin max 6:
                    $plainLen = mb_strlen(htmlspecialchars_decode($value, ENT_QUOTES));
                    if ($plainLen > 0) {
                        $rowsByValue = (int)ceil(($plainLen / max(10, $colsEstimate)));
                        $rows = max($minRows, min($maxRows, max($rows, $rowsByValue)));
                    }
                    
                    $control = "<textarea class='{$class}' id='{$this->esc($fieldName)}' name='{$this->esc($fieldName)}'
                          rows='{$rows}' {$maxlengthAttr} {$fieldAttr} {$readOnly} {$disabled}>{$value}</textarea>";
                }
            }
            
            // Fehler/Hint wie gehabt
            if ($error !== '') {
                $control .= "<div class='error'>".$this->esc($error)."</div>";
            }
            if ($infoText !== '') {
                $control .= "<div class='hint'>".$this->esc($infoText)."</div>";
            }
            
            return $this->wrapFlexRow($fieldName, $labelHtml, $control);
    }
    
    /* ==========================================================
     Optional: Alte Funktionsnamen als Wrapper behalten,
     damit du nicht überall Call-Sites ändern musst.
     ========================================================== */
    
    public function renderTextFieldFlex(
        string $fieldName,
        int $fieldLength = 0,
        string $infoText = '',
        string $fieldAttr = ''
        ): string {
            // Erzwinge input (auch wenn maxLen > 70) – oder einfach Standard nutzen:
            return $this->renderTextLikeFieldFlex(
                fieldName: $fieldName,
                fieldLength: $fieldLength,
                infoText: $infoText,
                fieldAttr: $fieldAttr,
                switchAt: 70
                );
    }
    
    public function renderTextareaFieldFlex(
        string $fieldName,
        string $infoText = '',
        string $fieldAttr = ''
        ): string {
            // Erzwinge textarea indem switchAt sehr klein ist:
            return $this->renderTextLikeFieldFlex(
                fieldName: $fieldName,
                fieldLength: 0,
                infoText: $infoText,
                fieldAttr: $fieldAttr,
                switchAt: 0
                );
    }
    /**
     * Select (Flex)
     * @param array $options [value => label]
     */
    public function renderSelectFieldFlex(
        string $fieldName,
        array $options,
        string $infoText = '',
        string $fieldAttr = '',
        string $readOnly = '',
        string $labelOverride = ''
        ): string {
            $valueCurrent = (string)($this->data[$fieldName] ?? '');
            $error        = (string)($this->errorMsgs[$fieldName] ?? '');
            
           //  $readonly = $this->readOnly;
            $disabled = $this->isDisabled() ? 'disabled' : '';
            
            $class = $error ? 'input danger-border' : 'input';
            
            if ($disabled !== '') {
                $shown = $options[$valueCurrent] ?? $valueCurrent;
                $control = "<div class='readonly-block'>".$this->esc((string)$shown)."</div>";
            } else {
                $control = "<select class='{$class}' id='{$this->esc($fieldName)}' name='{$this->esc($fieldName)}'
                        {$fieldAttr} {$readOnly} {$disabled}>";
                        foreach ($options as $val => $text) {
                            $selected = ((string)$val === $valueCurrent) ? 'selected' : '';
                            $control .= "<option value='".$this->esc((string)$val)."' {$selected}>".$this->esc((string)$text)."</option>";
                        }
                        $control .= "</select>";
            }
            
            if ($error !== '') {
                $control .= "<div class='error'>".$this->esc($error)."</div>";
            }
            if ($infoText !== '') {
                $control .= "<div class='hint'>".$this->esc($infoText)."</div>";
            }
            
            $labelHtml = $this->renderLabel($fieldName, true, $labelOverride);
         
            return $this->wrapFlexRow($fieldName, $labelHtml, $control);
    }
    
    /**
     * Radio (Flex)
     * @param array $buttons [value => text]
     */
    public function renderRadioFieldFlex(
        string $fieldName,
        array $buttons,
        string $infoText = '',
        string $readOnly = '',
        string $labelOverride = ''
        ): string {
            $valueCurrent = (string)($this->data[$fieldName] ?? '');
            $error        = (string)($this->errorMsgs[$fieldName] ?? '');
            
            //$readonly = $this->readOnly;
            $disabled = $this->isDisabled();
            
            if ($disabled || $readOnly === 'readonly') {
                $shown = $buttons[$valueCurrent] ?? $valueCurrent;
                $control = "<div class='readonly-block'>".$this->esc((string)$shown)."</div>";
            } else {
                $control = "<div class='radio-row'>";
                foreach ($buttons as $value => $text) {
                    $checked = ((string)$value === $valueCurrent) ? 'checked' : '';
                    $control .= "<label class='radio-pill'>
                    <input type='radio' name='{$this->esc($fieldName)}' value='".$this->esc((string)$value)."' {$checked}>
                    <span>".$this->esc((string)$text)."</span>
                </label>";
                }
                $control .= "</div>";
            }
            
            if ($error !== '') {
                $control .= "<div class='error'>".$this->esc($error)."</div>";
            }
            if ($infoText !== '') {
                $control .= "<div class='hint'>".$this->esc($infoText)."</div>";
            }
            
            $labelHtml = $this->renderLabel($fieldName, true, $labelOverride);
            
            return $this->wrapFlexRow($fieldName, $labelHtml, $control);
    }
    
    /**
     * Checkbox (Flex)
     */
    public function renderCheckboxFieldFlex(
        string $fieldName,
        string $text,
        string $infoText = '',
        string $readOnly = '',
        string $labelOverride = ''
        ): string {
            $valueCurrent = $this->data[$fieldName] ?? '';
            $error        = (string)($this->errorMsgs[$fieldName] ?? '');
            
            // $readonly = $this->readOnly;
            $disabled = $this->isDisabled();
            
            $checked = ($valueCurrent === 'Y' || $valueCurrent === true || $valueCurrent === 1 || $valueCurrent === '1') ? 'checked' : '';
            
            if ($disabled || $readOnly === 'readonly') {
                $control = "<div class='readonly-block'>".($checked ? 'Ja' : 'Nein')." — ".$this->esc($text)."</div>";
            } else {
                $control = "<label class='check-row'>
                <input type='checkbox' name='{$this->esc($fieldName)}' value='Y' {$checked}>
                <span>".$this->esc($text)."</span>
            </label>";
            }
            
            if ($error !== '') {
                $control .= "<div class='error'>".$this->esc($error)."</div>";
            }
            if ($infoText !== '') {
                $control .= "<div class='hint'>".$this->esc($infoText)."</div>";
            }
            
            $labelHtml = $this->renderLabel($fieldName, true, $labelOverride);
            
            return $this->wrapFlexRow($fieldName, $labelHtml, $control);
    }
    
    /**
     * Date Picker (Flex)
     */
    public function renderDatePickerFieldFlex(
        string $fieldName,
        string $infoText = '',
        string $fieldAttr = '',
        string $readOnly = ''
        ): string {
            $value = $this->esc((string)($this->data[$fieldName] ?? ''));
            $error = (string)($this->errorMsgs[$fieldName] ?? '');
            
            // $readonly = $this->readOnly;
            $disabled = $this->isDisabled() ? 'disabled' : '';
            
            $class = $error ? 'input danger-border' : 'input';
            
            if ($disabled !== '') {
                $control = "<div class='readonly-block'>{$value}</div>";
            } else {
                $control = "<input class='{$class}' type='date' id='{$this->esc($fieldName)}' name='{$this->esc($fieldName)}'
                        value='{$value}' {$fieldAttr} {$readonly} {$disabled}>";
            }
            
            if ($error !== '') {
                $control .= "<div class='error'>".$this->esc($error)."</div>";
            }
            if ($infoText !== '') {
                $control .= "<div class='hint'>".$this->esc($infoText)."</div>";
            }
            
            $labelHtml = $this->renderLabel($fieldName, true, $labelOverride);
            
            return $this->wrapFlexRow($fieldName, $labelHtml, $control);
    }
    
    /**
     * File Upload (Flex)
     */
    public function renderFileUploadFieldFlex(
        string $fieldName,
        string $infoText = '',
        string $fieldAttr = '',
        string $readOnly = ''
        ): string {
            $error = (string)($this->errorMsgs[$fieldName] ?? '');
            
            // $readonly = $this->readOnly;
            $disabled = $this->isDisabled() ? 'disabled' : '';
            
            $class = $error ? 'input danger-border' : 'input';
            
            if ($disabled !== '') {
                $control = "<div class='readonly-block'>Dateiupload deaktiviert</div>";
            } else {
                $control = "<input class='{$class}' type='file' id='{$this->esc($fieldName)}' name='{$this->esc($fieldName)}'
                        {$fieldAttr} {$readonly} {$disabled}>";
            }
            
            if ($error !== '') {
                $control .= "<div class='error'>".$this->esc($error)."</div>";
            }
            if ($infoText !== '') {
                $control .= "<div class='hint'>".$this->esc($infoText)."</div>";
            }
            
            $labelHtml = $this->renderLabel($fieldName, true);
            
            return $this->wrapFlexRow($fieldName, $labelHtml, $control);
    }
    
    /* ==========================================================
     * Validierung (optional, aber praktisch)
     * ========================================================== */
    
    public function validateTextField(string $fieldName, bool $required = false, ?string $pattern = null): bool
    {
        $value      = trim((string)($this->data[$fieldName] ?? ''));
        $maxLengths = $this->meta->getMaxLengthsMap();
        $maxLength  = isset($maxLengths[$fieldName]) ? (int)$maxLengths[$fieldName] : null;
        
        if ($required && $value === '') {
            $this->errorMsgs[$fieldName] = 'Dieses Feld ist erforderlich.';
            return false;
        }
        if ($maxLength !== null && $maxLength > 0 && mb_strlen($value) > $maxLength) {
            $this->errorMsgs[$fieldName] = "Maximale Länge von {$maxLength} Zeichen überschritten.";
            return false;
        }
        if ($pattern !== null && $value !== '' && !preg_match($pattern, $value)) {
            $this->errorMsgs[$fieldName] = 'Ungültiges Format.';
            return false;
        }
        return true;
    }
    
    public function validateNumberField(string $fieldName, bool $required = false, $min = null, $max = null): bool
    {
        $value = trim((string)($this->data[$fieldName] ?? ''));
        
        if ($required && $value === '') {
            $this->errorMsgs[$fieldName] = 'Dieses Feld ist erforderlich.';
            return false;
        }
        if ($value === '') {
            return true;
        }
        if (!is_numeric($value)) {
            $this->errorMsgs[$fieldName] = 'Bitte geben Sie eine gültige Zahl ein.';
            return false;
        }
        $num = $value + 0;
        if ($min !== null && $num < $min) {
            $this->errorMsgs[$fieldName] = "Der Wert muss mindestens {$min} sein.";
            return false;
        }
        if ($max !== null && $num > $max) {
            $this->errorMsgs[$fieldName] = "Der Wert darf maximal {$max} sein.";
            return false;
        }
        return true;
    }
    
    public function validateSelectField(string $fieldName, array $allowedValues, bool $required = false): bool
    {
        $value = (string)($this->data[$fieldName] ?? '');
        
        if ($required && $value === '') {
            $this->errorMsgs[$fieldName] = 'Bitte wählen Sie eine Option aus.';
            return false;
        }
        if ($value !== '' && !in_array($value, $allowedValues, true)) {
            $this->errorMsgs[$fieldName] = 'Ungültige Auswahl.';
            return false;
        }
        return true;
    }
    
    public function validateCheckboxField(string $fieldName): bool
    {
        $value = $this->data[$fieldName] ?? '';
        if ($value !== '' && $value !== 'Y' && $value !== 1 && $value !== true && $value !== '1') {
            $this->errorMsgs[$fieldName] = 'Ungültiger Wert für Checkbox.';
            return false;
        }
        return true;
    }
    
    public function validateDateField(string $fieldName, bool $required = false, ?string $format = 'Y-m-d'): bool
    {
        $value = trim((string)($this->data[$fieldName] ?? ''));
        
        if ($required && $value === '') {
            $this->errorMsgs[$fieldName] = 'Dieses Feld ist erforderlich.';
            return false;
        }
        if ($value === '') {
            return true;
        }
        
        $d = \DateTime::createFromFormat((string)$format, $value);
        if (!($d && $d->format((string)$format) === $value)) {
            $this->errorMsgs[$fieldName] = 'Ungültiges Datumsformat.';
            return false;
        }
        return true;
    }
    
    public function validateFileUploadField(string $fieldName, ?array $allowedMimeTypes = null, ?int $maxFileSize = null, bool $required = false): bool
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                $this->errorMsgs[$fieldName] = 'Bitte wählen Sie eine Datei aus.';
                return false;
            }
            return true;
        }
        
        $file = $_FILES[$fieldName];
        
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $this->errorMsgs[$fieldName] = 'Fehler beim Datei-Upload.';
            return false;
        }
        
        if ($maxFileSize !== null && (int)$file['size'] > $maxFileSize) {
            $this->errorMsgs[$fieldName] = 'Die Datei ist zu groß.';
            return false;
        }
        
        if ($allowedMimeTypes !== null) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                $this->errorMsgs[$fieldName] = 'Dateityp nicht erlaubt.';
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validiert alle Felder anhand eines Regelsets.
     * Beispiel:
     * [
     *   'name' => ['type'=>'text','required'=>true,'pattern'=>'/^[\p{L}\s-]+$/u'],
     *   'age'  => ['type'=>'number','min'=>0,'max'=>120],
     *   'typ'  => ['type'=>'select','allowedValues'=>['A','B']],
     * ]
     */
    public function validateAll(array $validationRules): bool
    {
        $this->errorMsgs = [];
        $valid = true;
        
        foreach ($validationRules as $field => $rules) {
            $type = $rules['type'] ?? 'text';
            
            switch ($type) {
                case 'text':
                    $valid = $this->validateTextField(
                    (string)$field,
                    (bool)($rules['required'] ?? false),
                    $rules['pattern'] ?? null
                    ) && $valid;
                    break;
                    
                case 'number':
                    $valid = $this->validateNumberField(
                    (string)$field,
                    (bool)($rules['required'] ?? false),
                    $rules['min'] ?? null,
                    $rules['max'] ?? null
                    ) && $valid;
                    break;
                    
                case 'select':
                    $valid = $this->validateSelectField(
                    (string)$field,
                    (array)($rules['allowedValues'] ?? []),
                    (bool)($rules['required'] ?? false)
                    ) && $valid;
                    break;
                    
                case 'checkbox':
                    $valid = $this->validateCheckboxField((string)$field) && $valid;
                    break;
                    
                case 'date':
                    $valid = $this->validateDateField(
                    (string)$field,
                    (bool)($rules['required'] ?? false),
                    $rules['format'] ?? 'Y-m-d'
                        ) && $valid;
                        break;
                        
                case 'file':
                    $valid = $this->validateFileUploadField(
                    (string)$field,
                    $rules['allowedMimeTypes'] ?? null,
                    $rules['maxFileSize'] ?? null,
                    (bool)($rules['required'] ?? false)
                    ) && $valid;
                    break;
                    
                default:
                    // unbekannter Typ -> ignorieren
                    break;
            }
        }
        
        $this->errors = count($this->errorMsgs);
        return $valid;
    }
    
    /* ==========================================================
     * Getter
     * ========================================================== */
    
    public function getErrorMessages(): array
    {
        return $this->errorMsgs;
    }
    
    public function getErrorCount(): int
    {
        return $this->errors;
    }
}

