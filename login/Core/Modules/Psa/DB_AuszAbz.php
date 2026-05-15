<?php
declare(strict_types=1);

namespace Fharch\Core\Database;

use FSArch\ore\Database\DB_GenericLog;

final class DB_AuszAbz
{
    private DB_GenericLog $db;
    
    /** Interne Whitelist der erlaubten Tabellen */
    private array $allowedTables = [
        'aw_aermel_abz',
        'aw_ff_wappen',
        'aw_ort_ref',
        'aw_ort_wappen',
        'az_adetail',
        'az_auszeich',
        'az_auszeich.ctif',
        'az_auszeich_ve',
        'az_beschreibg',
        // weitere Tabellen können hier ergänzt werden
    ];
    
    public function __construct(?DB_GenericLog $db = null)
    {
        $this->db = $db ?? DB_GenericLog::getInstance();
    }
    
    /**
     * Prüft, ob Tabelle erlaubt ist
     */
    private function checkTableAllowed(string $table): void
    {
        if (!in_array($table, $this->allowedTables, true)) {
            throw new \InvalidArgumentException("Tabelle nicht erlaubt: {$table}");
        }
        $this->db->validateIdentifier($table);
    }
    
    /**
     * Allgemeine SELECT-Methode für erlaubte Tabellen
     * @param string $table Tabellenname (ohne Prefix)
     * @param array $columns Spalten, default ['*']
     * @param array $where assoziatives Array für WHERE (Spalte => Wert)
     * @param array $orderBy assoziativ Spalte => ASC|DESC
     * @param int|null $limit
     * @param int|null $offset
     * @return array Ergebniszeilen
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
            return $this->db->select($table, $columns, $where, $orderBy, $limit, $offset);
    }
    
    /**
     * Allgemeine INSERT-Methode
     * @param string $table
     * @param array $data Spalte => Wert
     * @return int ID des eingefügten Datensatzes
     */
    public function insert(string $table, array $data): int
    {
        $this->checkTableAllowed($table);
        return $this->db->insert($table, $data);
    }
    
    /**
     * Allgemeine UPDATE-Methode
     * @param string $table
     * @param array $data Spalte => Wert (neu)
     * @param array $where Bedingungen
     * @return int Anzahl betroffener Zeilen
     */
    public function update(string $table, array $data, array $where): int
    {
        $this->checkTableAllowed($table);
        return $this->db->update($table, $data, $where);
    }
    
    /**
     * Allgemeine DELETE-Methode
     * @param string $table
     * @param array $where Bedingungen
     * @return int Anzahl gelöschter Zeilen
     */
    public function delete(string $table, array $where): int
    {
        $this->checkTableAllowed($table);
        return $this->db->delete($table, $where);
    }
    
    /**
     * Beispiel: Hole Datensatz aus aw_aermel_abz nach fo_id
     */
    public function getAermelAbzById(int $id): ?array
    {
        return $this->select('aw_aermel_abz', ['*'], ['fo_id' => $id], [], 1)[0] ?? null;
    }
    
    /**
     * Beispiel: Füge neuen Datensatz in aw_ff_wappen ein
     */
    public function insertFfWappen(array $data): int
    {
        return $this->insert('aw_ff_wappen', $data);
    }
    
    // Weitere spezifische Methoden für einzelne Tabellen können hier ergänzt werden
}

/* Nutzung: 
 * 
 */
use FSArch\Login\Basis\DB_GenericLog;
use FSArch\Login\Tables\DB_AuszAbz;

$db = DB_GenericLog::getInstance();
$aw = new AW_Tables($db);

// Beispiel: Datensatz aus aw_aermel_abz holen
$aermel = $aw->getAermelAbzById(123);

// Beispiel: Neuer Datensatz in aw_ff_wappen
$newId = $aw->insertFfWappen([
    'fo_fw_id' => 1,
    'fo_ff_wappen' => 'bild.png',
    'fo_ff_w_sort' => 10,
    'fo_ff_w_komm' => 'Beschreibung',
    'fo_changed_id' => 5,
]);

var_dump($aermel, $newId);
/*
Hinweise:
Die Klasse nutzt intern die generische FS_Database (ohne Prefix).
Die Whitelist ist intern in der Klasse, kann aber auch ausgelagert werden, wenn gewünscht.
Für jede Tabelle kannst du hier eigene Methoden ergänzen, die auf die generischen CRUD-Methoden aufsetzen.
Spaltennamen und Tabellen werden vor der Nutzung validiert.
Die Klasse ist flexibel und kann erweitert werden, z.B. mit komplexeren Queries oder Joins.
Wenn du möchtest, kann ich dir auch für einzelne Tabellen eigene spezialisierte Klassen mit typisierten Methoden erstellen.


*/

