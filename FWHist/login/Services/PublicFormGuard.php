<?php

namespace FWHist\Services;

use FWHist\Auth\AccessGuard;

/**
 * Schutz für öffentliche Formulare (ohne Login): Honigtopf, Mindest-/Höchstdauer, CSRF-Token (Session),
 * Nummern-Captcha und Missbrauchsbremse (Fehlversuche je IP).
 *
 *     $pfg = new PublicFormGuard('email2vorst', $systemBasePath . 'login/AOrd_Verz/Logging/ratelimit/');
 *     if ($_POST) { $onlyShow = $pfg->handleControls($_POST); }          // "Neuer Code", "Größere Ziffern"
 *     if ($phase === 1 && !$onlyShow) { $err = $pfg->validate($_POST); ... bei Erfolg: $pfg->finish(); }
 *     $pfg->show($freshStart);                                          // vor dem Anzeigen
 *     echo $pfg->renderFields($contactMail);                            // im Formular, vor dem Senden-Knopf
 *
 * Fehlermeldungen sind für den Besucher gedacht (Deutsch, ohne technische Details).
 */
final class PublicFormGuard
{
    private const SESSION = 'AG_PUBFORM';
    private const MIN_SECONDS = 5;
    private const MAX_SECONDS = 7200;
    private const FAILS_PER_HOUR = 20;

    private NumberCaptcha $captcha;
    private RateLimiter $limiter;

    public function __construct(private string $formId, string $rateDir, ?NumberCaptcha $captcha = null)
    {
        $this->captcha = $captcha ?? new NumberCaptcha($formId);
        $this->limiter = new RateLimiter($rateDir);
    }

    /**
     * Eingabe säubern: nur Text, ohne Steuerzeichen (einzeilig: auch ohne Zeilenumbrüche; mehrzeilig: Zeilenumbrüche
     * und Tabulatoren bleiben), getrimmt. Alles andere (Arrays, Zahlen ...) ergibt ''.
     */
    public static function clean(mixed $value, bool $multiline = false): string
    {
        if (!is_string($value)) {
            return '';
        }
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = (string) preg_replace($multiline ? '/[^\P{C}\n\t]+/u' : '/\p{C}+/u', $multiline ? '' : ' ', $value);

        return trim($value);
    }

    public function captcha(): NumberCaptcha
    {
        return $this->captcha;
    }

    public function limiter(): RateLimiter
    {
        return $this->limiter;
    }

    /** Verarbeitet "Neuer Code" / "Größere Ziffern". true = nur das Formular neu anzeigen, nichts prüfen. */
    public function handleControls(array $post): bool
    {
        $handled = false;
        if (!empty($post['captcha_new'])) {
            $this->captcha->issue(true);
            $handled = true;
        }
        if (!empty($post['captcha_big'])) {
            $this->captcha->toggleBig();
            $handled = true;
        }

        return $handled;
    }

    /** Vor dem Anzeigen des Formulars: Startzeit (bei $freshStart neu) und Captcha sicherstellen. */
    public function show(bool $freshStart): void
    {
        if ($freshStart || empty($_SESSION[self::SESSION][$this->formId]['start'])) {
            $_SESSION[self::SESSION][$this->formId]['start'] = time();
        }
        $this->captcha->issue();
    }

    /** CSRF-Token, Honigtopf (unsichtbar) und Captcha. */
    public function renderFields(string $contact = ''): string
    {
        $honeypot = "<div style='position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden' aria-hidden='true'>"
            . "<label>Bitte leer lassen<input type='text' name='website' value='' tabindex='-1' autocomplete='off'></label></div>";

        return AccessGuard::csrfField() . $honeypot . $this->captcha->renderHtml($contact);
    }

    /** @return string|null Fehlermeldung für den Besucher oder null, wenn alle Prüfungen bestanden sind */
    public function validate(array $post): ?string
    {
        $client = RateLimiter::clientKey();

        if (!empty($post['website'])) {                                   // Honigtopf: Bots füllen jedes Feld
            $this->limiter->record('pub-fail', $client, 3600);

            return 'Die Anfrage konnte nicht verarbeitet werden.';
        }
        if ($this->limiter->isBlocked('pub-fail', $client, self::FAILS_PER_HOUR, 3600)) {
            return 'Zu viele fehlgeschlagene Versuche. Bitte versuchen Sie es in einer Stunde noch einmal.';
        }
        if (!AccessGuard::checkCsrf($post['csrf'] ?? null)) {
            return 'Das Formular ist nicht mehr gültig (Sitzung abgelaufen). Bitte laden Sie die Seite neu und füllen Sie es erneut aus.';
        }

        $start = (int) ($_SESSION[self::SESSION][$this->formId]['start'] ?? 0);
        $age = time() - $start;
        if ($start === 0 || $age > self::MAX_SECONDS) {
            return 'Das Formular ist abgelaufen. Bitte laden Sie die Seite neu und füllen Sie es erneut aus.';
        }
        if ($age < self::MIN_SECONDS) {
            $this->limiter->record('pub-fail', $client, 3600);

            return 'Das Formular wurde zu schnell abgeschickt. Bitte versuchen Sie es in einigen Sekunden noch einmal.';
        }

        if (!$this->captcha->verify($post['captcha'] ?? null)) {
            $this->limiter->record('pub-fail', $client, 3600);
            $this->captcha->issue(true);

            return 'Der Sicherheitscode stimmt nicht. Bitte geben Sie den neu angezeigten Code ein.';
        }

        return null;
    }

    /** Zählt einen Versand und liefert false, wenn das Limit im Zeitfenster erreicht ist. */
    public function allow(string $bucket, string $key, int $max, int $windowSeconds): bool
    {
        return $this->limiter->hit($bucket, $key, $max, $windowSeconds);
    }

    /** Nach erfolgreichem Absenden: Captcha verbrauchen und Startzeit löschen (kein zweites Absenden desselben Formulars). */
    public function finish(): void
    {
        $this->captcha->consume();
        unset($_SESSION[self::SESSION][$this->formId]['start']);
    }
}
