<?php
declare(strict_types=1);

namespace Fharch\Core\Auth;

use Fharch\Core\Database\DB_GenericLog;
use RuntimeException;
use InvalidArgumentException;

final class Auth
{
    private ?array $user = null; // aktuell eingeloggter Benutzer-Datensatz
    private ?array $roles = null; // Array von Rollen (fl_modules) des Benutzers
    
    public function __construct(private DB_GenericLog $pdo)
    {
    }
    
    /**
     * Versucht Benutzer mit User-ID und Passwort anzumelden.
     * Prüft Passwort-Hash (bcrypt).
     * @throws RuntimeException bei Fehler
     */
    public function login(string $userId, string $password): bool
    {
        // Benutzer laden (aktiv)
        $user = $this->pdo->fetchOne(
            'SELECT * FROM fv_benutzer WHERE be_uid = :uid AND be_act <> "i"',
            ['uid' => $userId]
            );
        
        if (!$user) {
            return false; // Benutzer nicht gefunden oder inaktiv
        }
        
        // Passwort-Hash aus fv_erlauben laden
        $authData = $this->pdo->fetchOne(
            'SELECT fe_pw FROM fv_erlauben WHERE be_id = :be_id',
            ['be_id' => $user['be_id']]
            );
        
        if (!$authData || !isset($authData['fe_pw'])) {
            return false; // Kein Passwort gesetzt
        }
        
        $hash = $authData['fe_pw'];
        
        if (!password_verify($password, $hash)) {
            return false; // Passwort falsch
        }
        
        // Optional: Passwort-Rehash prüfen und ggf. aktualisieren
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $this->db->query(
                'UPDATE fv_erlauben SET fe_pw = :pw WHERE be_id = :be_id',
                ['pw' => $newHash, 'be_id' => $user['be_id']]
                );
        }
        
        // Benutzer und Rollen laden
        $this->user = $user;
        $this->loadRoles();
        
        return true;
    }
    
    /**
     * Benutzer-Logout (Session löschen)
     */
    public function logout(): void
    {
        $this->user = null;
        $this->roles = null;
        // Session-Handling hier oder in Controller
    }
    
    /**
     * Gibt den aktuell eingeloggten Benutzer zurück oder null
     * @return array<string,mixed>|null
     */
    public function getUser(): ?array
    {
        return $this->user;
    }
    
    /**
     * Prüft, ob ein Benutzer eingeloggt ist
     */
    public function isLoggedIn(): bool
    {
        return $this->user !== null;
    }
    
    /**
     * Lädt die Rollen des aktuell eingeloggten Benutzers
     */
    private function loadRoles(): void
    {
        if (!$this->user) {
            $this->roles = [];
            return;
        }
        
        $rows = $this->pdo->fetchAll(
            'SELECT r.fl_modules FROM fv_rolle ro
             JOIN fv_rollen_beschr r ON ro.fl_id = r.fl_id
             WHERE ro.be_id = :be_id AND ro.fr_aktiv = "a"',
            ['be_id' => $this->user['be_id']]
            );
        
        $modules = [];
        foreach ($rows as $row) {
            // fl_modules ist ein Komma-getrennter String von Berechtigungen
            $parts = array_map('trim', explode(',', $row['fl_modules']));
            $modules = array_merge($modules, $parts);
        }
        
        // Duplikate entfernen
        $this->roles = array_unique($modules);
    }
    
    /**
     * Prüft, ob der eingeloggte Benutzer eine bestimmte Berechtigung hat.
     * Beispiel: $auth->hasPermission('ADM-MI')
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if ($permission === 'ADM-ALLE') {
            return true;
        } 
        
        return in_array($permission, $this->roles, true);
    }
    
    /**
     * Prüft, ob der eingeloggte Benutzer eine bestimmte Berechtigung hat.
     * Beispiel: $auth->hasPermission('ADM-MI')
     */
    public function getRoles(): array
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $this->roles;
    }
    
    /**
     * Passwort ändern (aktuelles Passwort prüfen)
     * @throws RuntimeException bei Fehler
     */
    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        if (!$this->isLoggedIn()) {
            throw new RuntimeException('Nicht eingeloggt');
        }
        
        // Aktuelles Passwort prüfen
        $authData = $this->db->fetchOne(
            'SELECT fe_pw FROM fv_erlauben WHERE be_id = :be_id',
            ['be_id' => $this->user['be_id']]
            );
        
        if (!$authData || !password_verify($currentPassword, $authData['fe_pw'])) {
            throw new RuntimeException('Aktuelles Passwort ist falsch');
        }
        
        // Neues Passwort hashen und speichern
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query(
            'UPDATE fv_erlauben SET fe_pw = :pw, fe_changed_id = :changed_id, fe_changed_at = NOW() WHERE be_id = :be_id',
            [
                'pw' => $newHash,
                'changed_id' => $this->user['be_id'],
                'be_id' => $this->user['be_id'],
            ]
            );
        
        return true;
    }
    
    /**
     * Passwort-Reset Token erzeugen und speichern (für Mailversand)
     */
    public function createPasswordResetToken(int $beId, string $token, \DateTimeInterface $expires): void
    {
        // pw_used = 0, pw_expires = $expires
        $this->db->query(
            'INSERT INTO fv_password_resets (be_id, token, pw_expires, pw_used, pw_created) VALUES (:be_id, :token, :expires, 0, NOW())',
            [
                'be_id' => $beId,
                'token' => $token,
                'expires' => $expires->format('Y-m-d H:i:s'),
            ]
            );
    }
    
    /**
     * Passwort-Reset Token validieren und Benutzer-ID zurückgeben
     */
    public function validatePasswordResetToken(string $token): ?int
    {
        $row = $this->db->fetchOne(
            'SELECT be_id FROM fv_password_resets WHERE token = :token AND pw_used = 0 AND pw_expires > NOW()',
            ['token' => $token]
            );
        
        return $row['be_id'] ?? null;
    }
    
    /**
     * Passwort-Reset Token als benutzt markieren
     */
    public function markPasswordResetTokenUsed(string $token): void
    {
        $this->db->query(
            'UPDATE fv_password_resets SET pw_used = 1 WHERE token = :token',
            ['token' => $token]
            );
    }
}
/*
2. Beispiel: Nutzung in Menü-Rendering
<?php
// Beispiel: Menü-Rendering mit Berechtigungsabfrage
*/
/** @var \Fharch\Core\Auth\Auth $auth */
/*
if ($auth->isLoggedIn()) {
    echo '<ul>';
    
    if ($auth->hasPermission('ADM-MI')) {
        echo '<li><a href="/mitgliederverwaltung">Mitgliederverwaltung</a></li>';
    }
    
    if ($auth->hasPermission('ADM-MB')) {
        echo '<li><a href="/beitragszahlung">Beitrags-Bezahleingang</a></li>';
    }
    
    echo '</ul>';
} else {
    echo '<p>Bitte einloggen, um das Menü zu sehen.</p>';
}
*/
