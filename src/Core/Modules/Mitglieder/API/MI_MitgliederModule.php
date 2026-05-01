<?php
declare(strict_types=1);

namespace Fharch\Core\Modules\Mitglieder\API; 


use Fharch\Core\Database\DB_GenericLog;

/**
 * Beispiel einer modularen DB-Klasse für die drei Tabellen fv_mitglieder, fv_mi_bez, fv_mi_ehrung
 * mit Dependency Injection der Basis-Datenbankklasse VF_Database.
 *
 * Die Klasse kapselt CRUD-Methoden für alle drei Tabellen in einer Klasse.
 */

class MI_MitgliederModule
{
    private DB_GenericLog $db;
    # private string $prefix;
    
    // Tabellensuffixe (ohne Prefix)
    private const TABLE_MITGLIEDER = 'fv_mitglieder';
    private const TABLE_MI_BEZ = 'fv_mi_bez';
    private const TABLE_MI_EHRUNG = 'fv_mi_ehrung';
    private const TABLE_MI_ANMELD = 'fv_mi_anmeld';
    private const TABLE_UNTERST = 'fv_unterst';
    
    public function __construct(DB_GenericLog $db)
    {
        $this->db = $db;
        #$this->prefix = $db->getPrefix();
    }
    
    // --- Mitglieder (fv_mitglieder) ---
    
    public function createMitglied(array $data): int
    {
        return $this->db->insert(self::TABLE_MITGLIEDER, $data);
    }
    
    public function updateMitglied(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_MITGLIEDER, $data, ['mi_id' => $id]);
    }
    
    public function deleteMitglied(int $id): int
    {
        return $this->db->delete(self::TABLE_MITGLIEDER, ['mi_id' => $id]);
    }
    
    public function getMitgliedById(int $id): ?array
    {
        return $this->db->select(self::TABLE_MITGLIEDER, ['*'], ['mi_id' => $id]);
    }
    
    public function findMitglieder(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_MITGLIEDER, $where, ['*'], $orderBy, $limit, $offset);
    }
    
    /** ist E-Mail  Adresse schon Vorhanden ? */
    public function emailExists(string $email): bool
    {
        $result = $this->db->select(self::TABLE_MITGLIEDER, ['*'], ['mi_email' => $email]);  // , ['mi_id']
        return !empty($result);
    }
    
    // --- Mitglieder-Beiträge (fv_mi_bez) ---
    
    public function createMiBez(array $data): int
    {
        return $this->db->insert(self::TABLE_MI_BEZ, $data);
    }
    
    public function updateMiBez(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_MI_BEZ, $data, ['mb_id' => $id]);
    }
    
    public function deleteMiBez(int $id): int
    {
        return $this->db->delete(self::TABLE_MI_BEZ, ['mb_id' => $id]);
    }
    
    public function getMiBezById(int $id): ?array
    {
        return $this->db->select(self::TABLE_MI_BEZ, ['*'], ['mb_id' => $id]);
    }
    
    public function findMiBez(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_MI_BEZ, $where, ['*'], $orderBy, $limit, $offset);
    }
    
    // --- Mitglieder-Ehrungen (fv_mi_ehrung) ---
    
    public function createMiEhrung(array $data): int
    {
        return $this->db->insert(self::TABLE_MI_EHRUNG, $data);
    }
    
    public function updateMiEhrung(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_MI_EHRUNG, $data, ['me_id' => $id]);
    }
    
    public function deleteMiEhrung(int $id): int
    {
        return $this->db->delete(self::TABLE_MI_EHRUNG, ['me_id' => $id]);
    }
    
    public function getMiEhrungById(int $id): ?array
    {
        return $this->db->select(self::TABLE_MI_EHRUNG, ['*'], ['me_id' => $id]);
    }
    
    public function findMiEhrungen(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_MI_EHRUNG, $where, ['*'], $orderBy, $limit, $offset);
    }
    
    //  --- Mitgliedder- Neu- Anmeldung  (fv_mi_anmeld) ---
    
    public function createMiAnmeldg(array $data): int
    {
        return $this->db->insert(self::TABLE_MI_ANMELD, $data);
    }
    
    public function getMiAnmeldgById(int $id): ?array
    {
        return $this->db->select(self::TABLE_MI_ANMELD, ['*'], ['me_id' => $id]);
    }
    
    // --- Unterstützer fv_unterst) ---
    
    public function createUnterst(array $data): int
    {
        return $this->db->insert(self::TABLE_UNTERST, $data);
    }
    
    public function updateUnterst(int $id, array $data): int
    {
        return $this->db->update(self::TABLE_UNTERST, $data, ['fu_id' => $id]);
    }
    
    public function deleteUnterst(int $id): int
    {
        return $this->db->delete(self::TABLE_UNTERST, ['fu_id' => $id]);
    }
    
    public function getUnterstById(int $id): ?array
    {
        return $this->db->select(self::TABLE_UNTERST, ['*'],['fu_id' => $id]);
    }
    
    public function findUnterst(array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->db->select(self::TABLE_UNTERST, $where, ['*'], $orderBy, $limit, $offset);
    }
    
}

/**
 * Beispiel zur Nutzung:
 */
/*
 $db = VF_Database::getInstance();
 $mitgliederModule = new MI_MitgliederModule($db);
 
 // Neues Mitglied anlegen
 $newId = $mitgliederModule->createMitglied([
 'mi_mtyp' => 'M',
 'mi_org_typ' => 'Firma',
 'mi_org_name' => 'Beispiel GmbH',
 'mi_name' => 'Mustermann',
 'mi_vname' => 'Max',
 // ... weitere Felder
 ]);
 
 // Mitglied lesen
 $mitglied = $mitgliederModule->getMitgliedById($newId);
 
 // Mitglied aktualisieren
 $mitgliederModule->updateMitglied($newId, ['mi_name' => 'Musterfrau']);
 
 // Mitglied löschen
 $mitgliederModule->deleteMitglied($newId);
 */
/*
Hinweise zur Implementierung:
Die Klasse DB_MitgliederModule kapselt alle drei Tabellen mit klar getrennten Methoden.
Die Tabellen-Suffixe (mitglieder, mi_bez, mi_ehrung) werden an die Basis-DB-Klasse übergeben, die den Prefix ergänzt und validiert.
Die Basis-Klasse VF_Database stellt Methoden insert(), update(), delete(), select(), selectOne() bereit, die hier genutzt werden.
Die Methoden akzeptieren und liefern assoziative Arrays mit den Spaltenwerten.
Optional können Filter ($where), Sortierung ($orderBy), Limit und Offset übergeben werden.
So bleibt die DB-Logik modular, testbar und übersichtlich.
Falls gewünscht, kann man noch weitere spezifische Methoden (z.B. Joins, komplexe Filter) ergänzen.
*/
