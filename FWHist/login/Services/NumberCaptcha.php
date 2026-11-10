<?php

namespace FWHist\Services;

/**
 * Nummern-Captcha für Besucher mit leichter Sehschwäche.
 *
 * - Ziffern groß, ohne Verzerrung, schwarz auf weiß (Sieben-Segment-Schrift), als SVG-Linien gezeichnet - kein
 *   Text im HTML, den ein einfacher Bot auslesen könnte.
 * - "Neuen Code anzeigen" und "Größere Ziffern" als Schaltflächen, Hinweis auf eine Ausweich-Möglichkeit.
 * - Der Code liegt nur in der Session (Server), ist 15 Minuten gültig und erlaubt 5 Versuche.
 * - Ein richtig gelöster Code gilt 10 Minuten weiter: Wer das Formular wegen anderer Fehler noch einmal absendet,
 *   muss den Code nicht erneut eingeben. Nach erfolgreichem Absenden wird er verbraucht (consume()).
 *
 * Ein Bild-Captcha ist für blinde Besucher nicht benutzbar; dafür den Ausweich-Hinweis ($contact) anbieten.
 */
final class NumberCaptcha
{
    private const SESSION = 'AG_CAPTCHA';

    private const SEGMENTS = [
        'a' => [14, 10, 46, 10], 'b' => [50, 14, 50, 46], 'c' => [50, 54, 50, 86], 'd' => [14, 90, 46, 90],
        'e' => [10, 54, 10, 86], 'f' => [10, 14, 10, 46], 'g' => [14, 50, 46, 50],
    ];
    private const DIGITS = [
        '0' => 'abcdef', '1' => 'bc', '2' => 'abdeg', '3' => 'abcdg', '4' => 'bcfg',
        '5' => 'acdfg', '6' => 'acdefg', '7' => 'abc', '8' => 'abcdefg', '9' => 'abcdfg',
    ];

    public function __construct(
        private string $formId,
        private int $digits = 5,
        private int $ttl = 900,
        private int $maxTries = 5,
        private int $solvedTtl = 600
    ) {
    }

    /** @return array<string,mixed> */
    private function &state(): array
    {
        if (!isset($_SESSION[self::SESSION][$this->formId]) || !is_array($_SESSION[self::SESSION][$this->formId])) {
            $_SESSION[self::SESSION][$this->formId] = [];
        }

        return $_SESSION[self::SESSION][$this->formId];
    }

    /** Sorgt für einen gültigen Code (neu, wenn keiner da, abgelaufen, aufgebraucht oder $force). */
    public function issue(bool $force = false): void
    {
        $s = &$this->state();
        $valid = isset($s['code'], $s['exp']) && $s['exp'] > time() && ($s['tries'] ?? 0) < $this->maxTries;
        if (!$force && ($valid || $this->isSolved())) {
            return;
        }

        $s['code'] = str_pad((string) random_int(0, (10 ** $this->digits) - 1), $this->digits, '0', STR_PAD_LEFT);
        $s['exp'] = time() + $this->ttl;
        $s['tries'] = 0;
        $s['solved_exp'] = 0;
    }

    public function isSolved(): bool
    {
        $s = $this->state();

        return ($s['solved_exp'] ?? 0) > time();
    }

    /** Großdarstellung ein-/ausschalten (Schaltfläche "Größere Ziffern"). */
    public function toggleBig(): void
    {
        $s = &$this->state();
        $s['big'] = empty($s['big']);
    }

    /** Prüft die Eingabe. Nur Ziffern zählen (Leerzeichen und Trennzeichen werden ignoriert). */
    public function verify(mixed $input): bool
    {
        if ($this->isSolved()) {
            return true;
        }

        $s = &$this->state();
        if (!isset($s['code'], $s['exp']) || $s['exp'] <= time() || ($s['tries'] ?? 0) >= $this->maxTries) {
            return false;
        }

        $s['tries'] = ($s['tries'] ?? 0) + 1;
        $typed = is_string($input) ? (string) preg_replace('/\D+/', '', $input) : '';
        if ($typed !== '' && hash_equals((string) $s['code'], $typed)) {
            $s['solved_exp'] = time() + $this->solvedTtl;

            return true;
        }

        return false;
    }

    /** Nach erfolgreichem Absenden: Code verbrauchen (kein zweites Absenden mit demselben Code). */
    public function consume(): void
    {
        unset($_SESSION[self::SESSION][$this->formId]['code'], $_SESSION[self::SESSION][$this->formId]['exp']);
        $_SESSION[self::SESSION][$this->formId]['solved_exp'] = 0;
    }

    /** SVG mit dem aktuellen Code (ohne Text-Elemente). */
    public function svg(): string
    {
        $s = $this->state();
        $code = (string) ($s['code'] ?? '');
        $big = !empty($s['big']);
        $n = max(1, strlen($code));
        $width = 40 + $n * 80;
        $max = $big ? 760 : 400;

        $parts = '';
        for ($i = 0; $i < $n; $i++) {
            $segs = self::DIGITS[$code[$i]] ?? '';
            $dy = random_int(-5, 5);
            $rot = random_int(-3, 3);
            $parts .= sprintf("<g transform='translate(%d %d) rotate(%d 30 50)'>", 20 + $i * 80, 15 + $dy, $rot);
            foreach (str_split($segs) as $seg) {
                [$x1, $y1, $x2, $y2] = self::SEGMENTS[$seg];
                $parts .= "<line x1='{$x1}' y1='{$y1}' x2='{$x2}' y2='{$y2}'/>";
            }
            $parts .= '</g>';
        }

        $label = "Sicherheitscode aus {$n} Ziffern. Bitte die angezeigten Ziffern in das Feld darunter eintippen.";

        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} 130' role='img' aria-label='" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . "' style='display:block;width:100%;max-width:{$max}px;height:auto;background:#fff;border:4px solid #000'>"
            . "<g stroke='#000' stroke-width='11' stroke-linecap='round' fill='none'>{$parts}</g></svg>";
    }

    /**
     * HTML-Block für das Formular: Überschrift, Ziffern, Eingabefeld (groß), Schaltflächen und Hinweis.
     * Ist der Code schon gelöst, steht nur die Bestätigung da.
     */
    public function renderHtml(string $contact = ''): string
    {
        $id = 'captcha_' . preg_replace('/[^A-Za-z0-9_-]/', '', $this->formId);
        $s = $this->state();
        $help = "Wenn Sie den Code nicht erkennen können, lassen Sie sich mit „Neuen Code anzeigen“ einen anderen zeigen "
            . "oder wählen Sie „Größere Ziffern“.";
        if ($contact !== '') {
            $help .= ' Hilft das nicht, schreiben Sie bitte an ' . htmlspecialchars($contact, ENT_QUOTES, 'UTF-8') . '.';
        }

        if ($this->isSolved()) {
            return "<div class='captcha-box' style='margin:1em 0;padding:.8em;border:3px solid #060;background:#f2fff2;font-size:1.2em'>"
                . "<b>&#10003; Sicherheitscode bestätigt.</b></div>";
        }

        $big = !empty($s['big']);
        $btn = "style='font-size:1.1em;padding:.5em 1em;margin:.3em .5em .3em 0'";

        return "<div class='captcha-box' style='margin:1em 0;padding:1em;border:3px solid #000;background:#fffbe0'>"
            . "<label for='{$id}' style='display:block;font-size:1.3em;font-weight:bold;margin-bottom:.5em'>"
            . "Sicherheitscode: Bitte geben Sie die Ziffern aus dem Bild ein</label>"
            . $this->svg()
            . "<p style='margin:.8em 0 .3em'><input type='text' id='{$id}' name='captcha' inputmode='numeric' pattern='[0-9 ]*' "
            . "autocomplete='off' maxlength='" . ($this->digits + 4) . "' size='12' required aria-describedby='{$id}_help' "
            . "style='font-size:2em;letter-spacing:.2em;padding:.2em .4em;width:8em;border:3px solid #000'></p>"
            . "<p><button type='submit' name='captcha_new' value='1' formnovalidate {$btn}>Neuen Code anzeigen</button>"
            . "<button type='submit' name='captcha_big' value='1' formnovalidate {$btn}>" . ($big ? 'Kleinere Ziffern' : 'Größere Ziffern') . "</button></p>"
            . "<p id='{$id}_help' style='margin:.3em 0 0;font-size:1.05em'>{$help}</p></div>";
    }
}
