<?php
declare(strict_types=1);

namespace Fharch\Core\Database;

use PDO;

final class DB_AllgVerw
{
        
    /** Interne Whitelist der erlaubten Tabellen */
    private array $allowedTables = [
        'fv_abk',
        'fv_adm_mail',
        'fv_archivord',
        'fv_bundesld',
        'fv_firmen',
        'fv_ord_local',
        'fv_proj_config',
        'fv_staaten',
        'fv_sammlung',
        // weitere Tabellen können hier ergänzt werden
    ];
    
    private DB_GenericLog $dbGenericLog;
    private PDO $pdo;
    
    public function __construct(DB_GenericLog $dbGenericLog)
    {
        $this->dbGenericLog = $dbGenericLog;
        $this->pdo = $dbGenericLog->getPdo(); // getPdo() muss in DB_GenericLog existieren
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
    
    // -------------------------
    // Neue Tabellen-spezifische Methoden
    // -------------------------
    
    // fv_adm_mail (Admin E-Mail Tabelle)
    
    /**
     * Admin-Mail Eintrag anlegen
     * @param array $data ['be_ids'=>int, 'em_mail_grp'=>string, 'em_active'=>string, 'em_new_uid'=>int, 'em_changed_uid'=>string]
     * @return int Insert ID
     */
    public function createAdminMail(array $data): int
    {
        return $this->insert('fv_adm_mail', $data);
    }
    
    /**
     * Admin-Mail Eintrag aktualisieren
     * @param int $emId
     * @param array $data
     * @return int affected rows
     */
    public function updateAdminMail(int $emId, array $data): int
    {
        return $this->update('fv_adm_mail', $data, ['em_id' => $emId]);
    }
    
    /**
     * Admin-Mail Eintrag nach ID holen
     * @param int $emId
     * @return array|null
     */
    public function getAdminMailById(int $emId): ?array
    {
        return $this->selectOne('fv_adm_mail', ['em_id' => $emId]);
    }
    
    /**
     * Holt Benutzer-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Aktiv', 'InAktiv', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getAdminMailByIdBE(int $emId, int $beId): array {
        // SQL mit JOIN auf fv_ben_dat (Alias b) und fv_adm_mail (Alias a)
        $sql = "
        SELECT
            a.em_id,
            a.be_ids,
            a.em_active,
            a.em_mail_grp,
            b.fd_name,
            b.fd_email
        FROM fv_adm_mail a
        LEFT JOIN fv_ben_dat b ON b.be_id = a.be_ids
    ";
        
        $where = [];
        $params = [];
        
        // Filter auf emId, falls angegeben (größer 0)
        if ($emId > 0) {
            $where[] = "a.em_id = :emId";
            $params[':emId'] = $emId;
        }
        
        // Filter auf beId, falls angegeben (größer 0)
        if ($beId > 0) {
            $where[] = "b.be_id = :beId";
            $params[':beId'] = $beId;
        }
        
        // Falls WHERE-Bedingungen existieren, anfügen
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // ORDER BY anfügen
        $sql .= " ORDER BY b.fd_id";
        
        // Vorbereitung und Ausführung des Statements
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        // Ergebnis als Array zurückgeben (alle Treffer)
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    /**
     * Admin-Mail Einträge nach Gruppe und Status filtern
     * @param string|null $group
     * @param string|null $status 'a'|'i'|'' oder null für alle
     * @return array
     */
    public function getAdminMails(?string $group = null, ?string $status = null): array
    {
        $where = [];
        if ($group !== null) {
            $where['em_mail_grp'] = $group;
        }
        if ($status !== null) {
            $where['em_active'] = $status;
        }
        return $this->select('fv_adm_mail', $where);
    }
    
    //  --- Firmen- Verwaltung  ---
    
    /**
     * Firmen Eintrag anlegen
     * @param array $data ['be_ids'=>int, 'em_mail_grp'=>string, 'em_active'=>string, 'em_new_uid'=>int, 'em_changed_uid'=>string]
     * @return int Insert ID
     */
    public function createFirmen(array $data): int
    {
        return $this->insert('fv_firmen', $data);
    }
    
    /**
     * Firmen Eintrag aktualisieren
     * @param int $emId
     * @param array $data
     * @return int affected rows
     */
    public function updateFirmen(int $fiId, array $data): int
    {
        return $this->update('fv_firmen', $data, ['fi_id' => $fiId]);
    }
    
    /**
     * Firmen Eintrag nach ID holen
     * @param int $emId
     * @return array|null
     */
    
    public function getFirmenById(int $id): ?array
    {
        return $this->select('fv_firmen', ['*'], ['fi_id' => $id]);
    }
    
    /**
     * Firmen Einträge nach Funktion filtern
     * @param string|null $group
     * @param string|null $status 'a'|'i'|'' oder null für alle
     * @return array
     */
    public function getFirmenByFunkt(?string $funkt = null ): array
    {
        $where = [];
        if ($funkt !== null) {
            $where['fi_funkt'] = $funkt;
        }
        
        return $this->select('fv_firmen', $where);
    }
       
    //  --- Abkürzungen- Verwaltung  ---
    
    /**
     * Abkürzung  Eintrag anlegen
     * @param array $data ['be_ids'=>int, 'em_mail_grp'=>string, 'em_active'=>string, 'em_new_uid'=>int, 'em_changed_uid'=>string]
     * @return int Insert ID
     */
    public function createAbkuerz(array $data): int
    {
        return $this->insert('fv_abk', $data);
    }
    
    /**
     * Abkürzung   Eintrag aktualisieren
     * @param int $emId
     * @param array $data
     * @return int affected rows
     */
    public function updateAbkuerz(int $abId, array $data): int
    {
        return $this->update('fv_abk', $data, ['ab_id' => $abId]);
    }
    
    /**
     * Abkürzung  Eintrag nach ID holen
     * @param int $emId
     * @return array|null
     */
    
    public function getAbkuerzById(int $id): ?array
    {
        return $this->select('fv_abk',['*'], ['ab_id' => $id]);
    }
    
    //  --- Config- Verwaltung  ---
    
    /**
     * Config  Eintrag anlegen
     * @param array $data
     * @return int Insert ID
     */
    public function createConfig(array $data): int
    {
        return $this->insert('fv_proj_config', $data);
    }
    
    /**
     * Config   Eintrag aktualisieren
     * @param int $Id
     * @param array $data
     * @return int affected rows
     */
    public function updateConfig(int $Id, array $data): int
    {
        return $this->update('fv_proj_config', $data, ['c_flnr' => $Id]);
    }
    
    
    /**
     * Admin-EMailr-Daten nach Benutzer-ID holen
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
    public function createRoleData(array $data): int
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





