<?php
declare(strict_types=1);

namespace Fharch\Core\Modules\Oeffentlichkeitsarbeit\API;

use \PDO;

use Fharch\Core\Database\DB_GenericLog;

/**
 * Beispiel einer modularen DB-Klasse für die Tabellen fv_links
 * mit Dependency Injection der Basis-Datenbankklasse VF_Database.
 *
 * Die Klasse kapselt CRUD-Methoden für alle drei Tabellen in einer Klasse.
 */

class DB_Oeffentlich
{
    private DB_GenericLog $db;
    
    // Tabellensuffixe (ohne Prefix)
    private const TABLE_BUECHER = 'oe_buecher';
    private const TABLE_DOKUS = 'oe_dokumente';
    private const TABLE_LINKS = 'fv_falinks';
    private const TABLE_MARKTPL = 'oe_marktplatz';
    private const TABLE_MUSEEN = 'oe_museen';
    private const TABLE_PRESSE = 'oe_presse';
    private const TABLE_TERMINE = 'oe_va_termine';
    private const TABLE_ZEITUNG = 'oe_zeitungen';
    private const TABLE_ZTINHALT = 'oe_zeitung_';
    
    /** Daten von Genereller DB- Klasse */
    public function __construct(DB_GenericLog $db)
    {
        $this->db = $db;
        #$this->prefix = $db->getPrefix();
    }
    
    /** Methoden für Archiv- Bibliotheken- Links */
    
    /** neu anlegen von Record */
    public function createLinks(array $data): int
    {
        return $this->db->insert(self::TABLE_LINKS, $data);
    }
    
    /** update von bestehendem Record */
    public function updateLinks(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_LINKS, $data, ['fa_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deleteLinks(int $id): int
    {
        return $this->db->delete(self::TABLE_LINKS, ['fa_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getLinksById(int $id): ?array
    {
        return $this->db->select(self::TABLE_LINKS, ['*'], ['fa_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findLinks(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_LINKS, ['*'], $where, $orderBy, $limit, $offset);
    }
    
    /** Methoden für Marktplatz */
    
    /** neu anlegen von Record */
    public function createMarktpl(array $data): int
    {
        return $this->db->insert(self::TABLE_MARKTPL, $data);
    }
    
    /** update von bestehendem Record */
    public function updateMarktpl(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_MARKTPL, $data, ['bs_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deleteMarktpl(int $id): int
    {
        return $this->db->delete(self::TABLE_MARKTPL, ['bs_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getMarktplById(int $id): ?array
    {
        return $this->db->select(self::TABLE_MARKTPL, ['*'], ['bs_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findMarktpl(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_MARKTPL, ['*'], $where, $orderBy, $limit, $offset);
    }
    
    /** Methoden für Buecher */
    
    /** neu anlegen von Record */
    public function createBuecher(array $data): int
    {
        return $this->db->insert(self::TABLE_BUECHER, $data);
    }
    
    /** update von bestehendem Record */
    public function updateBuecher(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_BUECHER, $data, ['bu_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deleteBuecher(int $id): int
    {
        return $this->db->delete(self::TABLE_BUECHER, ['bu_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getBuecherById(int $id): ?array
    {
        return $this->db->select(self::TABLE_BUECHER, ['*'], ['bu_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findBuecher(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_BUECHER, ['*'], $where, $orderBy, $limit, $offset);
    }
    
    /** Methoden für Tdokumente */
    
    /** neu anlegen von Record */
    public function createDokus(array $data): int
    {
        return $this->db->insert(self::TABLE_DOKUS, $data);
    }
    
    /** update von bestehendem Record */
    public function updateDokus(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_DOKUS, $data, ['dk_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deleteDokus(int $id): int
    {
        return $this->db->delete(self::TABLE_DOKUS, ['dk_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getDokusById(int $id): ?array
    {
        return $this->db->select(self::TABLE_DOKUS, ['*'], ['dk_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findDokus(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_DOKUS, ['*'], $where, $orderBy, $limit, $offset);
    }
    
    /** Methoden für Museen */
    
    /** neu anlegen von Record */
    public function createMuseen(array $data): int
    {
        return $this->db->insert(self::TABLE_MUSEEN, $data);
    }
    
    /** update von bestehendem Record */
    public function updateMuseen(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_MUSEEN, $data, ['mu_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deleteMuseen(int $id): int
    {
        return $this->db->delete(self::TABLE_MUSEEN, ['mu_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getMuseenById(int $id): ?array
    {
        return $this->db->select(self::TABLE_MUSEEN, ['*'], ['mu_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findMuseen(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_MUSEEN, ['*'], $where, $orderBy, $limit, $offset);
    }
    
    /** Methoden für Pressespiegel */
    
    /** neu anlegen von Record */
    public function createPresse(array $data): int
    {
        return $this->db->insert(self::TABLE_PRESSE, $data);
    }
    
    /** update von bestehendem Record */
    public function updatePresse(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_PRESSE, $data, ['pr_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deletePresse(int $id): int
    {
        return $this->db->delete(self::TABLE_PRESSE, ['pr_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getPresseById(int $id): ?array
    {
        return $this->db->select(self::TABLE_PRESSE, ['*'], ['pr_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findPresse(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_PRESSE, ['*'], $where, $orderBy, $limit, $offset);
    }
    
    /** Methoden für Terminplan */
    
    /** neu anlegen von Record */
    public function createTermine(array $data): int
    {
        return $this->db->insert(self::TABLE_TERMINE, $data);
    }
    
    /** update von bestehendem Record */
    public function updateTermine(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_TERMINE, $data, ['va_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deleteTermine(int $id): int
    {
        return $this->db->delete(self::TABLE_TERMINE, ['va_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getTermineById(int $id): ?array
    {
        return $this->db->select(self::TABLE_TERMINE, ['*'], ['va_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findTermine(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_TERMINE, ['*'], $where, $orderBy, $limit, $offset);
    }
    
    /** Methoden für Zeitungen */
    
    /** neu anlegen von Record */
    public function createZeitung(array $data): int
    {
        return $this->db->insert(self::TABLE_ZEITUNG, $data);
    }
    
    /** update von bestehendem Record */
    public function updateZeitung(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_ZEITUNG, $data, ['zt_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deleteZeitung(int $id): int
    {
        return $this->db->delete(self::TABLE_ZEITUNG, ['zt_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getZeitungById(int $id): ?array
    {
        return $this->db->select(self::TABLE_ZEITUNG, ['*'], ['zt_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findZeitung(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_ZEITUNG, ['*'], $where, $orderBy, $limit, $offset);
    }
    
    /** Methoden für Zt Inhalte */
    
    /** neu anlegen von Record */
    public function createZInhalte(array $data, int $ztId, int $ih_id): int
    {
        return $this->db->insert(self::TABLE_ZTINHALT . $ztId, $data);
    }
    
    /** update von bestehendem Record */
    public function updateZInhalte(int $id, array $data, int $ztId): int
    {
        return $this->db->update(self::TABLE_ZTINHALT . $ztId, $data, ['ih_id' => $id]);
    }
    
    /** Löschen eines Records */
    public function deleteZInhalte(int $id, int $ztId): int
    {
        return $this->db->delete(self::TABLE_ZTINHALT . $ztId, ['ih_id' => $id]);
    }
    
    /** einlesen Record mit ID */
    public function getZInhalteById(int $id, int $ztId): ?array
    {
        return $this->db->select(self::TABLE_ZTINHALT . $ztId, ['*'], ['ih_id' => $id]);
    }
    
    /** einlesen mhererer Records mit Parametern */
    public function findZInhalte(int $ztId, int $ih_id, array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_ZTINHALT . $ztId, ['*'], $where, $orderBy, $limit, $offset);
    }
    
}

