<?php
declare(strict_types=1);

namespace FWHist\Modules\Inventar\API;

use FWHist\Database\DB_GenericLog;
use InvalidArgumentException;

// Shutdown-Funktion für Fatal Errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        $message = "[" . date('Y-m-d H:i:s') . "] Shutdown error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . PHP_EOL;
        @file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/Invent_reposi_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../../login/AOrd_Verz/Logging/Invent_reposi_php-error.log.txt');

final class DB_Inventar
{
    private DB_GenericLog $db;
    private int $ei_id;
    private array $allowedTables;
    
    public function __construct(?DB_GenericLog $db = null, int $ei_id = 0)
    {
        #$message = __LINE__ . " ei_id $ei_id \n";
        #@file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/Invent_reposi_fatal_error.log', $message, FILE_APPEND);
        $this->db = $db ?? DB_GenericLog::getInstance();
        $this->ei_id = $ei_id;
        
        $prefix = (string)$this->ei_id;
        // Alle erlaubten Tabellen mit Prefix initialisieren
        $this->allowedTables = [
            $prefix . '_inventar',
            $prefix . '_invent_verleih',
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
        if (
            $table === $this->urh_id . '_inventar'
            || $table === $this->urh_id . '_invent_verleih'
            ) {
                InventarTableDefinitions::ensureTenantTable($this->db->getPDO(), $this->urh_id, $table);
            }
        # $message = __LINE__ . " Table $table Data ". json_encode($data) ." \n";
        #  @file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/Invent_reposi_fatal_error.log', $message, FILE_APPEND);
        $this->checkTableAllowed($table);
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
    
    /** Einlesen Inventar- Record */
    public function getInvById(int $id): ?array
    {
        $tableName = $this->ei_id . '_inventar';
        # $message = __LINE__ . " TableName $tableName \n";
        # @file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/Invent_reposi_fatal_error.log', $message, FILE_APPEND);
        $results = $this->select($tableName, ['*'], ['in_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen Inventar- Daten */
    public function getInvData(array $where = []): array
    {
        $tableName = $this->ei_id . '_inventar';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in Inventar- Daten */
    public function insertInv(array $data): int
    {
        $message = __LINE__ . " Data ". json_encode($data) ." \n";
        @file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/Invent_reposi_fatal_error.log', $message, FILE_APPEND);
        $tableName = $this->ei_id . '_inventar';
        return $this->insert($tableName, $data);
    }
    
    /** Update eines Records */
    public function updateInv(array $data, array $where): int
    {
        $tableName = $this->ei_id . '_inventar';
        return $this->update($tableName, $data, $where);
    }
    
    /** Einlesen eines Inventar-Verleih- Records */
    public function getInvVerlById(int $id): ?array
    {
        $tableName = $this->ei_id . '_invent_verleih';
        $results = $this->select($tableName, ['*'], ['ei_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen Inventar-Verleih- Daten */
    public function getInvVerl(array $where = []): array
    {
        $tableName = $this->ei_id . '_invent_verleih';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in Inventar-Verleih- Daten
     public function insertInvVerl(array $data): int
     {
     $tableName = $this->ei_id . '_invent_verleih';
     return $this->insert($tableName, $data);
     }
     
     /** Update eines Records */
    public function updateInvVerl(array $data, array $where): int
    {
        $tableName = $this->ei_id . '_invent_verleih';
        return $this->update($tableName, $data, $where);
    }
    
   
    
}

/**
 Wichtig:
 
 $allowedTables wird im Konstruktor mit dem Prefix aus $ei_id initialisiert.
 Zugriff auf $this->ei_id ist korrekt (nicht $this->$ei_id).
 Tabellen, die in Methoden verwendet werden (z.B. 'ma_mandanten'), müssen auch in $allowedTables stehen.
 Fehlerbehandlung und Pfad-Angaben sind verbessert.
 validateIdentifier wird nur aufgerufen, wenn vorhanden.
 So ist die Klasse korrekt und sicher für die dynamische Nutzung von Tabellen mit Prefix.
 */


    

    

    
 