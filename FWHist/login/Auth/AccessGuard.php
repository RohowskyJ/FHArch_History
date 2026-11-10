<?php
declare(strict_types=1);

namespace FWHist\Auth;

use FWHist\Database\DB_GenericLog;
use InvalidArgumentException;

/**
 * Zentrale RBAC-Prüfungen (Stand 28.10.2026).
 *
 * Rollen sind global. Fachrollen (ADM-ARC/INV/MEDIA/FZG) gelten für ihr Modul
 * und ALLE Mandanten; ONU-ALL schreibt nur für Mandanten aus fv_ben_mand.
 * Schreibrecht = Fachrolle ODER (ONU-ALL UND Mandant erlaubt). ADM-ALLE darf alles
 * (steckt bereits in Auth::hasPermission()).
 */
final class AccessGuard
{
    /** Modul-Schlüssel => Fachrolle (mandantenbezogene Module) */
    private const FACH_ROLLEN = [
        'ARC'  => 'ADM-ARC',
        'INV'  => 'ADM-INV',
        'MEDIA' => 'ADM-MEDIA',
        'FZG'  => 'ADM-FZG',
    ];

    /**
     * Module (Schlüssel = list_ID des Moduls; 'ORT' = Ort-Formular mit den Sub-Listen Wappen/Ärmelabzeichen), deren Edit-Formular auf EXTERNEN Seiten schreibgeschützt angezeigt werden darf.
     * Whitelist: alles andere bleibt extern gesperrt. Bitte an die tatsächlichen Module anpassen.
     */
    private const PUBLIC_VIEW_FORMS = ['AL', 'BU', 'DO', 'MP', 'MU', 'PR', 'TE', 'ZT', 'ZI', 'ORT'];

    private static ?self $current = null;

    /** Rolle der Freigeber (Öffentlichkeitsarbeit, derzeit Buch-Rezensionen); Rollen-Code in fv_rollen_beschr.fl_modules */
    public const REVIEW_ROLE = 'ADM-FREI';

    /** "Nicht freigegeben": Werte wie im bisherigen Formular */
    private const NO_RELEASE_ID   = 0;
    private const NO_RELEASE_DATE = '0000-00-00';

    public function __construct(private Auth $auth)
    {
    }

    /**
     * Baut den Guard für den Benutzer der aktuellen Session (be_id aus $_SESSION['BS_Prim']['BE']).
     * Rollen/Mandanten kommen frisch aus der DB; ist niemand (mehr) gültig angemeldet,
     * liefern alle Prüfungen false.
     */
    public static function fromSession(DB_GenericLog $db): self
    {
        $auth = new Auth($db);
        $beId = (int) ($_SESSION['BS_Prim']['BE']['be_id'] ?? 0);
        if ($beId > 0) {
            $auth->restore($beId);
        }

        return self::$current = new self($auth);
    }

    /**
     * Guard des laufenden Requests (z. B. in Listen-Klassen, die keinen $guard übergeben bekommen).
     * Wurde im Request schon fromSession() aufgerufen, wird dieselbe Instanz verwendet (keine zusätzlichen DB-Abfragen).
     */
    public static function current(): self
    {
        return self::$current ?? self::fromSession(new DB_GenericLog());
    }

    // ---------------------------------------------------------------
    // Mandantendaten (Archiv, Fotos, Inventar, Fahrzeuge/Geräte)
    // ---------------------------------------------------------------

    /** Schreibende Aktionen: angemeldet UND nicht auf einer externen (öffentlichen) Seite. */
    private function mayWrite(): bool
    {
        return $this->auth->isLoggedIn() && !self::isPublicView();
    }

    /** Angemeldeter Benutzer (be_id), 0 wenn niemand angemeldet ist. */
    public function userId(): int
    {
        return (int) ($this->auth->getUser()['be_id'] ?? 0);
    }

    /** Abbruch vor jeder Ausgabe: Status setzen, Meldung ausgeben, beenden. */
    public static function deny(string $message, int $status = 403): never
    {
        http_response_code($status);
        exit($message);
    }

    // CSRF-Schutz für Formulare, die speichern (Token im Formular, Prüfung beim Speichern)

    public static function csrfToken(): string
    {
        if (empty($_SESSION['AG_CSRF']) || !is_string($_SESSION['AG_CSRF'])) {
            $_SESSION['AG_CSRF'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['AG_CSRF'];
    }

    public static function checkCsrf(mixed $token): bool
    {
        $known = $_SESSION['AG_CSRF'] ?? '';

        return is_string($token) && $token !== '' && is_string($known) && $known !== '' && hash_equals($known, $token);
    }

    public const CSRF_MESSAGE = 'Ungültige Anfrage (Sicherheits-Token fehlt oder ist abgelaufen). Bitte Seite neu laden.';

    /** Hidden-Feld mit dem Token für das Formular (nur ausgeben, wenn gespeichert werden darf). */
    public static function csrfField(): string
    {
        return "<input type='hidden' name='csrf' value='" . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . "'>";
    }

    /** Beim Speichern (phase 1) nach der Rechteprüfung aufrufen: beendet mit 403, wenn das Token fehlt oder falsch ist. */
    public static function requireCsrf(): void
    {
        if (!self::checkCsrf($_POST['csrf'] ?? null)) {
            self::deny(self::CSRF_MESSAGE);
        }
    }

    /** Lesen: jeder eingeloggte Benutzer (historische Recherche-Daten). */
    public function canReadMandantData(): bool
    {
        return $this->auth->isLoggedIn();
    }

    /**
     * Schreiben: Fachrolle des Moduls (alle Mandanten) ODER ONU-ALL mit Mandanten-Erlaubnis.
     * @throws InvalidArgumentException bei unbekanntem Modul
     */
    public function canWriteMandantData(string $module, int $eiId): bool
    {
        $module = strtoupper($module);
        if (!isset(self::FACH_ROLLEN[$module])) {
            throw new InvalidArgumentException("Unbekanntes Modul für Mandantendaten: {$module}");
        }

        return $this->mayWrite() && (
            $this->auth->hasPermission(self::FACH_ROLLEN[$module])
            || ($this->auth->hasRole('ONU-ALL') && $this->auth->hasMandant($eiId))
        );
    }

    // ---------------------------------------------------------------
    // Mandanten-Stammdatei (ma_mandanten)
    // ---------------------------------------------------------------

    /** ADM-MA ändert alle Daten in ma_mandanten, aber keine Modul-Daten. */
    public function canEditMandantStamm(): bool
    {
        return $this->mayWrite() && $this->auth->hasPermission('ADM-MA');
    }

    // ---------------------------------------------------------------
    // Mitgliederdaten (mi_*)
    // ---------------------------------------------------------------

    public function canReadMemberData(): bool
    {
        return !self::isPublicView() && $this->auth->hasPermission('ADM-MI');
    }

    public function canWriteMemberData(): bool
    {
        return $this->mayWrite() && $this->auth->hasPermission('ADM-MI');
    }

    public function canReadMemberPaymentData(): bool
    {
        return !self::isPublicView() && $this->auth->hasPermission('ADM-MB');
    }

    public function canWriteMemberPaymentData(): bool
    {
        return $this->mayWrite() && $this->auth->hasPermission('ADM-MB');
    }

    /**
     * ADM-MB darf zusätzlich mi_bezahlt und mi_m_*_bez* in mi_mitglieder ändern.
     */
    public function canWriteMemberField(string $field): bool
    {
        if ($this->canWriteMemberData()) {
            return true;
        }

        $isPayField = $field === 'mi_bezahlt' || preg_match('/^mi_m_.+_bez/', $field) === 1;

        return $isPayField && $this->mayWrite() && $this->auth->hasPermission('ADM-MB');
    }

    // ---------------------------------------------------------------
    // Sonderaktionen
    // ---------------------------------------------------------------

    /** Export (DB-Sicherung), Löschen, Freigeben. */
    public function canAdminAction(): bool
    {
        return $this->mayWrite() && ($this->auth->hasPermission('ADM-MA') || $this->auth->hasPermission('ADM-MI'));
    }

    /** Freigabe der Buch-Rezensionen (Rolle REVIEW_ROLE). */
    public function canApproveReviews(): bool
    {
        return $this->mayWrite() && $this->auth->hasPermission(self::REVIEW_ROLE);
    }

    /** Benutzer-Berechtigungen (Rollen, Mandanten-Zuordnung) verwalten. */
    public function canManageAccess(): bool
    {
        return $this->mayWrite() && $this->auth->hasPermission('ADM-ZUGR');
    }

    /**
     * Darf der Benutzer diese Rolle an andere vergeben?
     * ADM-ALLE: alle Rollen; ADM-ZUGR: alle außer ADM-ALLE.
     */
    public function canGrantRole(string $roleCode): bool
    {
        if (!$this->mayWrite()) {
            return false;
        }

        if ($this->auth->hasRole('ADM-ALLE')) {
            return true;
        }

        return $this->auth->hasRole('ADM-ZUGR') && $roleCode !== 'ADM-ALLE';
    }
    // ---------------------------------------------------------------
    // Module ohne Mandantenbezug / generische Hilfen
    // ---------------------------------------------------------------

    public function isLoggedIn(): bool
    {
        return $this->auth->isLoggedIn();
    }

    /** Für tabellengesteuerte Prüfungen (z. B. Listen-Rollen in ListHostAPI). ADM-ALLE erfüllt jede. */
    public function hasPermission(string $permission): bool
    {
        return $this->auth->hasPermission($permission);
    }

    /**
     * Darf der Benutzer eine Rolle mit diesen Rollen-Codes (fv_rollen_beschr.fl_modules, kommasepariert) vergeben,
     * ändern oder entziehen? Nur wenn er JEDEN enthaltenen Code vergeben darf (ADM-ZUGR: alle außer ADM-ALLE).
     */
    public function canGrantRoleModules(?string $flModules): bool
    {
        $codes = array_values(array_filter(
            array_map('trim', explode(',', (string) $flModules)),
            static fn(string $c): bool => $c !== ''
        ));
        if ($codes === []) {
            return $this->canManageAccess();
        }

        foreach ($codes as $code) {
            if (!$this->canGrantRole($code)) {
                return false;
            }
        }

        return true;
    }

    // ---------------------------------------------------------------
    // Menüs: Einträge ohne Berechtigung werden nicht angezeigt
    // ---------------------------------------------------------------

    /** Rollen, die in der Zentralen Verwaltung ("Referat 1 - Organisation") etwas sehen; ADM-ALLE immer */
    private const ADMIN_MENU_ROLES = ['ADM-MA', 'ADM-MI', 'ADM-MB', 'ADM-ZUGR', 'ADM-FI', 'ADM-AB'];

    /** Referat 1 / Zentrale Verwaltung sichtbar? */
    public function canSeeAdminMenu(): bool
    {
        foreach (self::ADMIN_MENU_ROLES as $role) {
            if ($this->auth->hasPermission($role)) {
                return true;
            }
        }

        return false;
    }

    /** Menü der Mitgliederverwaltung sichtbar? (ADM-MI oder ADM-MB, ADM-ALLE immer) */
    public function canSeeMemberMenu(): bool
    {
        return !self::isPublicView()
            && ($this->auth->hasPermission('ADM-MI') || $this->auth->hasPermission('ADM-MB'));
    }

    /**
     * Sichtbarkeits-Flags für die Menü-Templates (als $can übergeben). Die Menüs blenden nur aus; die Zielseiten
     * prüfen die Rechte selbst. Die Menüs der Module (Archiv, Inventar, Medien ...) bleiben für alle sichtbar.
     *
     * @return array<string,bool>
     */
    public function menuFlags(): array
    {
        return [
            'referat1'      => $this->canSeeAdminMenu(),                 // Hauptmenü: Referat 1 - Organisation
            'mitglieder'    => $this->canSeeMemberMenu(),                // Zentrale Verwaltung: Mitgliederverwaltung
            'mitgl_verw'    => $this->auth->hasPermission('ADM-MI'),     // Mitglieder, Aktivierung, Ehrungen, Unterstützer
            'mitgl_zahlung' => $this->auth->hasPermission('ADM-MB'),     // Zahlungseingang
            'mandanten'     => $this->auth->hasPermission('ADM-MA'),     // Eigentümer / Mandanten
            'zugriff'       => $this->auth->hasPermission('ADM-ZUGR'),   // Benutzer- und Zugriffsverwaltung
            'firmen'        => $this->auth->hasPermission('ADM-FI'),     // Firmen (Hersteller/Aufbauer)
            'abkuerzungen'  => $this->auth->hasPermission('ADM-AB'),     // Abkürzungen
            'admin'         => $this->auth->hasRole('ADM-ALLE'),         // Protokolle, Konfiguration, Prozesse, Import, Sicherung
        ];
    }

    // ---------------------------------------------------------------
    // Listen: Action-Spalte und Detail-/Edit-Formulare (Anzeige vs. Bearbeiten)
    // ---------------------------------------------------------------

    /** Externe (öffentliche) Seite? Erkennung wie bisher über smod = 'index'. */
    public static function isPublicView(): bool
    {
        return ($_SESSION['BS_Prim']['Mod']['smod'] ?? '') === 'index';
    }

    /**
     * Steuerung der Action-Spalte einer Liste. Anzeigen (Detail-/Edit-Formular öffnen) und Bearbeiten
     * sind getrennt: wer die Liste sieht und ein Detail-Formular existiert, bekommt die Spalte ("Anzeigen");
     * bearbeiten darf nur, wer $canEdit hat (Label "Bearbeiten"), das Formular selbst ist sonst schreibgeschützt.
     *
     * @param bool $canEdit        Schreibrecht (z. B. canWriteMandantData('INV', $ei_id))
     * @param bool $hasDetailForm  Leser können ein schreibgeschütztes Detail-/Edit-Formular öffnen
     *                             (false = Spalte nur für Bearbeiter, z. B. Sub-Listen ohne Detailansicht)
     * @param bool $detailPublic   Detail-Formular darf auf externen Seiten (schreibgeschützt) geöffnet werden
     * @param bool $isPublicView   Aufruf von einer externen Seite
     * @return array{showActionColumn: bool, actionLabel: string, editProtect: bool}
     */
    public function listActionConfig(
        bool $canEdit,
        bool $hasDetailForm = true,
        bool $detailPublic = false,
        bool $isPublicView = false
    ): array {
        $canEdit = $canEdit && !$isPublicView;
        $mayOpen = $isPublicView ? $detailPublic : $this->auth->isLoggedIn();

        return [
            'showActionColumn' => $canEdit || ($hasDetailForm && $mayOpen),
            'actionLabel'      => $canEdit ? 'Bearbeiten' : 'Anzeigen',
            'editProtect'      => !$canEdit,
        ];
    }

    /**
     * Darf das Edit-Formular eines Moduls (schreibgeschützt) angezeigt werden?
     * Intern: jeder Angemeldete. Extern: nur Module aus PUBLIC_VIEW_FORMS.
     * Die Schreibrechte (Speichern, Upload) werden davon getrennt geprüft.
     */
    public function canViewEditForm(string $modul, bool $isPublicView = false): bool
    {
        if ($isPublicView) {
            return in_array(strtoupper($modul), self::PUBLIC_VIEW_FORMS, true);
        }

        return $this->auth->isLoggedIn();
    }

    // ---------------------------------------------------------------
    // Buch-Rezensionen (oe_buecher): Freigabe-Workflow
    // ---------------------------------------------------------------

    /**
     * Sieht der Benutzer NICHT freigegebene Rezensionen? Bearbeiter (ADM-OEF) und Freigeber (ADM-FREI),
     * nie auf externen Seiten. Alle anderen (extern, ONU-ALL, ONU-GAST) sehen nur bu_frei_stat = 'F'.
     */
    public function canSeeUnreleasedReviews(): bool
    {
        return $this->canEditOeffentlichkeit() || $this->canApproveReviews();   // beide sind extern immer false
    }

    /**
     * Rezension bearbeiten: Freigeber (ADM-FREI) immer; Bearbeiter (ADM-OEF) nur, solange sie nicht freigegeben ist.
     * (Annahme: freigegebene Einträge ändert nur der Freigeber. Soll ADM-OEF sie auch ändern dürfen: hier ändern.)
     */
    public function canEditReview(bool $released): bool
    {
        return $this->canApproveReviews() || (!$released && $this->canEditOeffentlichkeit());
    }

    /**
     * Freigabe-Felder beim Speichern (bu_frei_stat SET('U','F'): U = unfrei, F = frei; bu_frei_id; bu_frei_dat).
     * Werte aus dem Request werden nie übernommen. Neuanlage: immer unfrei. Nur ein Freigeber (REVIEW_ROLE/ADM-ALLE)
     * kann freigeben ('F') oder zurücknehmen ('U'); beim Freigeben setzt der Server Freigeber-ID und Datum, bei der
     * Rücknahme werden beide zurückgesetzt (0 / '0000-00-00', wie im bisherigen Formular).
     *
     * @param array      $neu   Eingabedaten; bu_frei_stat darf den Wunsch des Freigebers enthalten ('U' oder 'F')
     * @param array|null $old   bestehender Datensatz (null bei Neuanlage)
     * @param int        $beId  angemeldeter Benutzer
     * @return array     $neu mit korrekten Freigabe-Feldern (bestehender Datensatz ohne Freigeber-Recht: Felder fehlen = unverändert)
     */
    public function applyReviewRelease(array $neu, ?array $old, int $beId): array
    {
        $wish = array_key_exists('bu_frei_stat', $neu) ? $neu['bu_frei_stat'] : null;
        $wish = (is_string($wish) && in_array($wish, ['U', 'F'], true)) ? $wish : null;
        unset($neu['bu_frei_stat'], $neu['bu_frei_id'], $neu['bu_frei_dat']);

        $unreleased = [
            'bu_frei_stat' => 'U',
            'bu_frei_id'   => self::NO_RELEASE_ID,
            'bu_frei_dat'  => self::NO_RELEASE_DATE,
        ];

        if ($old === null) {
            $neu = $unreleased + $neu;                       // Neuanlage: unfrei
        }

        if ($wish === null || !$this->canApproveReviews()) {
            return $neu;                                      // sonst: Freigabe-Felder bleiben unverändert
        }

        if ($wish === 'F') {
            $neu['bu_frei_stat'] = 'F';
            if (($old['bu_frei_stat'] ?? '') !== 'F') {
                $neu['bu_frei_id']  = $beId;
                $neu['bu_frei_dat'] = date('Y-m-d');
            }
        } else {
            $neu = $unreleased + $neu;                        // Rücknahme
        }

        return $neu;
    }

    /** Öffentlichkeitsarbeit (OEF). */
    public function canEditOeffentlichkeit(): bool
    {
        return $this->mayWrite() && $this->auth->hasPermission('ADM-OEF');
    }

    /** Zeitungsinhalte sind öffentlich lesbar sowie für ADM-OEF, ONU-ALL und ONU-GAST. */
    public function canViewZeitungsContent(): bool
    {
        return self::isPublicView()
            || ($this->auth->isLoggedIn()
                && ($this->canEditOeffentlichkeit()
                    || $this->auth->hasPermission('ONU-ALL')
                    || $this->auth->hasPermission('ONU-GAST')));
    }

    /** Wappen/Ärmelabzeichen etc. (PSA). */
    public function canEditPsa(): bool
    {
        return $this->mayWrite() && $this->auth->hasPermission('ADM-PSA');
    }

    /**
     * Upload-Berechtigung nach subMod ("MODUL|UNTERMODUL"). Mandantenbezogene Module
     * (ARC, INV, FZG) und Medien-Uploads werden gegen eine positive Eigentümer-ID geprüft.
     * Unbekannte Module: nur ADM-ALLE.
     */
    public function canUpload(string $subMod, int $eiId): bool
    {
        if (!$this->mayWrite()) {
            return false;
        }

        $key = strtoupper(trim(explode('|', $subMod)[0]));

        return match (true) {
            str_starts_with($key, 'OEF') => $this->canEditOeffentlichkeit(),
            str_starts_with($key, 'PSA') => $this->canEditPsa(),
            $key === 'ADM-ARC', str_starts_with($key, 'ARC') => $eiId > 0 && $this->canWriteMandantData('ARC', $eiId),
            str_starts_with($key, 'INV') => $eiId > 0 && $this->canWriteMandantData('INV', $eiId),
            str_starts_with($key, 'FZG') => $eiId > 0 && $this->canWriteMandantData('FZG', $eiId),
            $key === 'MEDIA', $key === 'ADM-MED' => $eiId > 0 && $this->canWriteMandantData('MEDIA', $eiId),
            $key === 'MVW' => $this->canWriteMemberData(),
            default => $this->auth->hasRole('ADM-ALLE'),   // unbekanntes Modul: nur Super-Admin
        };
    }
}
