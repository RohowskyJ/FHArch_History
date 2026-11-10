<?php
declare(strict_types=1);

namespace FWHist\Database;

final class DB_Benutzer
{
    /** Interne Whitelist der erlaubten Tabellen */
    private array $allowedTables = [
        'fv_benutzer',
        'fv_ben_dat',
        'fv_erlauben',
        'fv_password_resets',
        'fv_rolle',
        'fv_rollen_beschr',
        'fv_benutzer_mandant',
        // weitere Tabellen können hier ergänzt werden
    ];
    
    private DB_GenericLog $dbGenericLog;
    
    private ?int $be_id = null;
    private ?string $be_uid = null;
    private ?int $role_id = null;
    private ?string $role_description = null;
    private array $permissions = []; // mandant_id => erlaubnis (read, update, nix)
    
    public function __construct(DB_GenericLog $dbGenericLog)
    {
        $this->dbGenericLog = $dbGenericLog;
    }
    
    /**
     * Prüft, ob Tabelle erlaubt ist
     */
    private function checkTableAllowed(string $table): void
    {
        if (!in_array($table, $this->allowedTables, true)) {
            throw new \InvalidArgumentException("Tabelle nicht erlaubt: {$table}");
        }
    }
    
    /**
     * Allgemeine SELECT-Methode für erlaubte Tabellen
     */
    public function select(
        string $table,
        array $columns = ['*'],
        array $where = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
        ): array {
            $this->checkTableAllowed($table);
            return $this->dbGenericLog->select($table, $columns, $where, $orderBy, $limit, $offset);
    }
    
    /**
     * Allgemeine INSERT-Methode
     */
    public function insert(string $table, array $data): int
    {
        $this->checkTableAllowed($table);
        return $this->dbGenericLog->insert($table, $data);
    }
    
    /**
     * Allgemeine UPDATE-Methode
     */
    public function update(string $table, array $data, array $where): int
    {
        $this->checkTableAllowed($table);
        return $this->dbGenericLog->update($table, $data, $where);
    }
    
    /**
     * Allgemeine DELETE-Methode
     */
    public function delete(string $table, array $where): int
    {
        $this->checkTableAllowed($table);
        return $this->dbGenericLog->delete($table, $where);
    }
    
    /**
     * Benutzer-Daten nach Benutzer-ID holen
     */
    public function getUserDataById(string $fdId): ?array
    {
        $result = $this->select('fv_ben_dat', ['*'], ['fd_id' => $fdId]);
        return $result[0] ?? null;
    }
    
    /**
     * Neue Benutzerdaten erstellen (INSERT in fv_ben_dat)
     * 
     * @param array $data Benutzerdaten (z.B. fd_name, fd_vname, fd_email, etc.)
     * @return int Insert ID (fd_id)
     */
    public function createUserData(array $data): int
    {
        return $this->insert('fv_ben_dat', $data);
    }
    
    /**
     * Benutzerdaten aktualisieren (UPDATE in fv_ben_dat)
     * 
     * @param string $fdId Benutzer-ID (fd_id)
     * @param array $data Zu aktualisierende Daten
     * @return int Anzahl der betroffenen Zeilen
     */
    public function updateUserData(string $fdId, array $data): int
    {
        return $this->update('fv_ben_dat', $data, ['fd_id' => $fdId]);
    }
    
    /**
     * Benutzer-Daten nach Benutzer-ID holen
     */
    public function getRoleById(string $frId, string $beId): ?array
    {
        $result = $this->select('fv_rolle', ['*'], ['fr_id' => $frId, 'be_id' => $beId]);
        return $result[0] ?? null;
    }
    
    /**
     * Neue Rolle erstellen (INSERT in fv_rolle)
     *
     * @param array $data Benutzerdaten (z.B. fd_name, fd_vname, fd_email, etc.)
     * @return int Insert ID (fd_id)
     */
    public function insertRoleData(array $data): int
    {
        return $this->insert('fv_rolle', $data);
    }
    
    /**
     * Rollendaten aktualisieren (UPDATE in fv_ben_dat)
     *
     * @param string $fdId Benutzer-ID (fd_id)
     * @param array $data Zu aktualisierende Daten
     * @return int Anzahl der betroffenen Zeilen
     */
    public function updateRoleById(string $frId, array $data): int
    {
        return $this->update('fv_rolle', $data, ['fr_id' => $frId]);
    }
    
    /**
     * Rolle einlesen
     */
    /*
    public function getRoleById(int $frId): array
    {
        $tblRolle = $this->table('fv_rolle');
        $tblBeschr = $this->table('fv_rollen_beschr');
        
        $sql = "SELECT r.fr_id, r.be_id, r.fl_id, r.fr_aktiv, b.fl_Beschreibung, b.fl_modules
                FROM `{$tblRolle}` r
                INNER JOIN `{$tblBeschr}` b ON b.fl_id = r.fl_id
                WHERE r.fr_id = :fr_id
                ORDER BY r.fr_id ASC";
        $result = $this->query($sql, ['fr_id' => $frId])->fetch();
        return $result === false ? [] : $result;
    }
    */
    /**
     * Rollen- Beschreibung  Einträge holen
     * @param int $flId
     * @return array|null
     */
    public function getRoleDescrAll(): ?array
    {
        return $this->select('fv_rollen_beschr');
    }
    
    /** Gibt es diesen Login-Benutzer (fv_benutzer)? */
    public function userExists(int $beId): bool
    {
        return $beId > 0 && $this->select('fv_benutzer', ['be_id'], ['be_id' => $beId]) !== [];
    }

    /** Anzeigename (Vorname Name) aus fv_ben_dat zu einer be_id, '' wenn keiner vorhanden. */
    public function getUserNameByBeId(int $beId): string
    {
        $row = $this->select('fv_ben_dat', ['fd_vname', 'fd_name'], ['be_id' => $beId])[0] ?? null;

        return $row === null ? '' : trim((string) ($row['fd_vname'] ?? '') . ' ' . (string) ($row['fd_name'] ?? ''));
    }

    /** Profil-Nr (fd_id in fv_ben_dat, Schlüssel von BenEdit.php) zu einer be_id, 0 wenn keine vorhanden. */
    public function getProfileIdByBeId(int $beId): int
    {
        $row = $this->select('fv_ben_dat', ['fd_id'], ['be_id' => $beId])[0] ?? null;

        return (int) ($row['fd_id'] ?? 0);
    }

    /** Rollen-Beschreibung (fl_id, fl_beschreibung, fl_modules) zu einer fl_id. */
    public function getRoleDescrById(int $flId): ?array
    {
        return $this->select('fv_rollen_beschr', ['*'], ['fl_id' => $flId])[0] ?? null;
    }

    // ---------------------------------------------------------------
    // Mandanten-Erlaubnisse (fv_benutzer_mandant: fm_id, be_id, ei_id, fm_aktiv, fm_created_*, fm_changed_*)
    // ---------------------------------------------------------------

    public function getMandantPermById(int $fmId, int $beId): ?array
    {
        return $this->select('fv_benutzer_mandant', ['*'], ['fm_id' => $fmId, 'be_id' => $beId])[0] ?? null;
    }

    public function insertMandantPerm(array $data): int
    {
        return $this->insert('fv_benutzer_mandant', $data);
    }

    public function updateMandantPermById(int $fmId, array $data): int
    {
        return $this->update('fv_benutzer_mandant', $data, ['fm_id' => $fmId]);
    }

    /** Ist der Mandant dem Benutzer schon zugeordnet (außer in der Zuordnung $exceptFmId)? */
    public function mandantPermExists(int $beId, int $eiId, int $exceptFmId = 0): bool
    {
        foreach ($this->select('fv_benutzer_mandant', ['fm_id'], ['be_id' => $beId, 'ei_id' => $eiId]) as $row) {
            if ((int) ($row['fm_id'] ?? 0) !== $exceptFmId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alle Mandanten als [ei_id => Anzeigename] (aus ma_mandanten). Der Name wird defensiv aus ei_name/ei_vname
     * gebildet, fehlen die Spalten, steht "Mandant <Nr>" da.
     *
     * @return array<int,string>
     */
    public function getMandantenAll(): array
    {
        $rows = $this->dbGenericLog->getPDO()->query('SELECT * FROM ma_mandanten ORDER BY ei_id')->fetchAll(\PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $row) {
            $id = (int) ($row['ei_id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $name = trim((string) ($row['ei_name'] ?? '') . ' ' . (string) ($row['ei_vname'] ?? ''));
            $out[$id] = $name !== '' ? $name : 'Mandant ' . $id;
        }

        return $out;
    }

    // Weitere Methoden wie login, loadPermissions etc. kannst du hier ergänzen,
    // dabei kannst du ebenfalls auf $this->dbGenericLog für DB-Operationen zurückgreifen.
}

/*
Hinweise
Die Klasse DB_Benutzer erwartet im Konstruktor eine Instanz von DB_GenericLog.
Die Whitelist-Prüfung bleibt erhalten, um nur erlaubte Tabellen zu verwenden.
Alle CRUD-Methoden (select, insert, update, delete) werden an DB_GenericLog delegiert.
Die Methode getUserDataById ist ein Beispiel, wie du mit der neuen select-Methode arbeiten kannst.
Du kannst weitere Methoden (z.B. login, loadPermissions) analog anpassen und DB_GenericLog nutzen.
Wenn du möchtest, kann ich dir auch helfen, die Übergabe der DB_GenericLog-Instanz zu organisieren oder weitere Methoden umzuschreiben.

*/





