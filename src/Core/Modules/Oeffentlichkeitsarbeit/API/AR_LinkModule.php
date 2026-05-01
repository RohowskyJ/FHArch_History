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

class AR_LinkModule
{
    private DB_GenericLog $db;
    
    // Tabellensuffixe (ohne Prefix)
    private const TABLE_LINKS = 'fv_falinks';
    
    public function __construct(DB_GenericLog $db)
    {
        $this->db = $db;
        #$this->prefix = $db->getPrefix();
    }
    
    // --- Linkser (fv_Linkser) ---
    
    public function createLinks(array $data): int
    {
        return $this->db->insert(self::TABLE_LINKS, $data);
    }
    
    public function updateLinks(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_LINKS, $data, ['fa_id' => $id]);
    }
    
    public function deleteLinks(int $id): int
    {
        return $this->db->delete(self::TABLE_LINKS, ['fa_id' => $id]);
    }
    
    public function getLinksById(int $id): ?array
    {
        return $this->db->select(self::TABLE_LINKS, ['*'], ['fa_id' => $id]);
    }
    
    public function findLinks(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_LINKS, ['*'], $where, $orderBy, $limit, $offset);
    }
    

}

