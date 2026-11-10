<?php
declare(strict_types=1);

namespace FWHist\Modules\FahrzeugeGeraete\API;

use FWHist\Database\DB_GenericLog;
use InvalidArgumentException;

// Shutdown-Funktion für Fatal Errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        $message = "[" . date('Y-m-d H:i:s') . "] Shutdown error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . PHP_EOL;
        @file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/FazgGer_reposi_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../../login/AOrd_Verz/Logging/FazgGer_reposi_php-error.log.txt');

final class DB_FzgGer
{
    private DB_GenericLog $db;
    private int $urh_id;
    private array $allowedTables;
    
    public function __construct(?DB_GenericLog $db = null, int $urh_id = 0)
    {
        $this->db = $db ?? DB_GenericLog::getInstance();
        $this->urh_id = $urh_id;
        
        $prefix = (string)$this->urh_id;
        // Alle erlaubten Tabellen mit Prefix initialisieren
        $this->allowedTables = [
            $prefix . '_fza_eigner',   
            $prefix . '_fza_dokumente',
            $prefix . '_fza_geraet',
            $prefix . '_fza_fahrzeug',
            $prefix . '_fzm_fahrzeug',
            $prefix . '_fzm_geraet',
            // Beispiel, da unten verwendet
        ];
    }
    
    private function checkTableAllowed(string $table): void
    {
        if (!in_array($table, $this->allowedTables, true)) {
            throw new InvalidArgumentException("Zugriff verweigert: Tabelle '{$table}' ist nicht erlaubt.");
        }
        if (method_exists($this->db, 'validateIdentifier')) {
            $this->db->validateIdentifier($table);
        }
    }
    
    public function select(string $table, array $columns = ['*'], array $where = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        $this->checkTableAllowed($table);
        return $this->db->select($table, $columns, $where, $orderBy, $limit, $offset);
    }
    
    public function insert(string $table, array $data): int
    {
        $this->checkTableAllowed($table);
        if (
            $table === $this->urh_id . '_fza_eigner'
            || $table === $this->urh_id . '_fza_dokumente'
            || $table === $this->urh_id . '_fza_fahrzeug'
            || $table === $this->urh_id . '_fzm_fahrzeug'
            || $table === $this->urh_id . '_fzm_geraet'
        ) {
            FzgGerTableDefinitions::ensureTenantTable($this->db->getPDO(), $this->urh_id, $table);
        }
        return (int)$this->db->insert($table, $data);
    }
    
    public function update(string $table, array $data, array $where): int
    {
        $this->checkTableAllowed($table);
        return (int)$this->db->update($table, $data, $where);
    }
    
    public function delete(string $table, array $where): int
    {
        $this->checkTableAllowed($table);
        return (int)$this->db->delete($table, $where);
    }
    
    /** Einlesen FaA- Record */
    public function getFaA(int $id): ?array
    {
        $tableName = $this->urh_id . '_fza_dokumente';
        
        $results = $this->select($tableName, ['*'], ['fb_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen FaA- Daten */
    public function getFaAData(array $where = []): array
    {
        $tableName = $this->urh_id . '_fza_dokumente';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in FaA- Daten */
    public function insertFaAF(array $data): int
    {
        $tableName = $this->urh_id . '_fza_dokumente';
        return $this->insert($tableName, $data);
    }
    
    /** Update eines FaA Records */
    public function updateFaA(array $data, array $where): int
    {
        $tableName = $this->urh_id . '_fza_dokumente';
        return $this->update($tableName, $data, $where);
    }
    
    /** Einlesen FaF- Record */
    public function getFaFById(int $id): ?array
    {
        $tableName = $this->urh_id . '_fza_fahrzeug';
        
        $results = $this->select($tableName, ['*'], ['fz_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen FaF- Daten */
    public function getFaFData(array $where = []): array
    {
        $tableName = $this->urh_id . '_fza_fahrzeug';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in FaF- Daten */
    public function insertFaF(array $data): int
    {
        $tableName = $this->urh_id . '_fza_fahrzeug';
        return $this->insert($tableName, $data);
    }
    
    /** Update eines FaF Records */
    public function updateFaF(array $data, array $where): int
    {
        $tableName = $this->urh_id . '_fza_fahrzeug';
        return $this->update($tableName, $data, $where);
    }
    
    /** Einlesen FaE- Record */
    public function getFaE(int $id): ?array
    {
        $tableName = $this->urh_id . '_fza_eigner';
        
        $results = $this->select($tableName, ['*'], ['fa_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen FaF- Record */
    public function getFaEById(int $id): ?array
    {
        $tableName = $this->urh_id . '_fza_eigner';
        
        $results = $this->select($tableName, ['*'], ['fz_eign_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    
    /** EinlesenFaE- Daten */
    public function getFaEData(array $where = []): array
    {
        $tableName = $this->urh_id . '_fza_eigner';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in FaE- Daten */
    public function insertFaE(array $data): int
    {
        $tableName = $this->urh_id . '_fza_eigner';
        return $this->insert($tableName, $data);
    }

    /** Einfügen eines Fahrzeug-Dokuments */
    public function insertFaADokument(array $data): int
    {
        $tableName = $this->urh_id . '_fza_dokumente';
        return $this->insert($tableName, $data);
    }
    
    /** Update eines FaE Records */
    public function updateFaE(array $data, array $where): int
    {
        $tableName = $this->urh_id . '_fza_eigner';
        return $this->update($tableName, $data, $where);
    }
    
    /** Einlesen FaG- Record */
    public function getFaG(int $id): ?array
    {
        $tableName = $this->urh_id . '_fza_geraet';
        
        $results = $this->select($tableName, ['*'], ['ge_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen Fag- Daten */
    public function getFaGData(array $where = []): array
    {
        $tableName = $this->urh_id . '_fza_geraet';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in FaG- Daten */
    public function insertFaG(array $data): int
    {
        $tableName = $this->urh_id . '_fza_geraet';
        return $this->insert($tableName, $data);
    }
    
    /** Update eines FaGRecords */
    public function updateFaG(array $data, array $where): int
    {
        $tableName = $this->urh_id . '_fza_geraet';
        return $this->update($tableName, $data, $where);
    }
    
    /** Einlesen FmF- Record */
    public function getFmF(int $id): ?array
    {
        $tableName = $this->urh_id . '_fzm_fahrzeug';
        
        $results = $this->select($tableName, ['*'], ['fm_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen FmF- Daten */
    public function getFmFData(array $where = []): array
    {
        $tableName = $this->urh_id . '_fzm_fahrzeug';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in FmF- Daten */
    public function insertFmF(array $data): int
    {
        $tableName = $this->urh_id . '_fzm_fahrzeug';
        return $this->insert($tableName, $data);
    }
    
    /** Update eines FmF Records */
    public function updateFmF(array $data, array $where): int
    {
        $tableName = $this->urh_id . '_fzm_fahrzeug';
        return $this->update($tableName, $data, $where);
    }
    
    /** Einlesen FmG- Record */
    public function getFmG(int $id): ?array
    {
        $tableName = $this->urh_id . '_fzm_geraet';
        
        $results = $this->select($tableName, ['*'], ['mg_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen FmG- Daten */
    public function getFmGData(array $where = []): array
    {
        $tableName = $this->urh_id . '_fzm_geraet';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in FmG- Daten */
    public function insertFmG(array $data): int
    {
        $tableName = $this->urh_id . '_fzm_geraet';
        return $this->insert($tableName, $data);
    }
    
    /** Update eines FmG Records */
    public function updateFmG(array $data, array $where): int
    {
        $tableName = $this->urh_id . '_fzm_geraet';
        return $this->update($tableName, $data, $where);
    }
    
    
    
    
}

/**
 Wichtig:
 
 $allowedTables wird im Konstruktor mit dem Prefix aus $urh_id initialisiert.
 Zugriff auf $this->urh_id ist korrekt (nicht $this->$urh_id).
 Tabellen, die in Methoden verwendet werden (z.B. 'ma_mandanten'), müssen auch in $allowedTables stehen.
 Fehlerbehandlung und Pfad-Angaben sind verbessert.
 validateIdentifier wird nur aufgerufen, wenn vorhanden.
 So ist die Klasse korrekt und sicher für die dynamische Nutzung von Tabellen mit Prefix.
 */


    

    

    
 