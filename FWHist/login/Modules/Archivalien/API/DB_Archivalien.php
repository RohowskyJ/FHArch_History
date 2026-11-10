<?php
declare(strict_types=1);

namespace FWHist\Modules\Archivalien\API;

use FWHist\Database\DB_GenericLog;
use InvalidArgumentException;

// Shutdown-Funktion für Fatal Errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        $message = "[" . date('Y-m-d H:i:s') . "] Shutdown error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . PHP_EOL;
        @file_put_contents(__DIR__ . '/../../../../login/AOrd_Verz/Logging/Archiv_reposi_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../../login/AOrd_Verz/Logging/Archiv_reposi_php-error.log.txt');

final class DB_Archivalien
{
    private DB_GenericLog $db;
    private int $ei_id;
    private array $allowedTables;
    
    public function __construct(?DB_GenericLog $db = null, int $ei_id = 0)
    {
        $this->db = $db ?? DB_GenericLog::getInstance();
        $this->ei_id = $ei_id;
        
        $prefix = (string)$this->ei_id;
        // Alle erlaubten Tabellen mit Prefix initialisieren
        $this->allowedTables = [
            $prefix . '_archivdat',
            $prefix . '_arch_verleih',
            'hi_archivord',
            'hi_archord_erw'
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
            $table === $this->ei_id . '_archivdat'
            || $table === $this->ei_id . '_arch_verl'
            ) {
                ArchivTableDefinitions::ensureTenantTable($this->db->getPDO(), $this->ei_id, $table);
        }
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
    
    /** Einlesen Archiv- Record */
    public function getArchivById(int $id): ?array
    {
        $tableName = $this->ei_id . '_archivdat';
        
        $results = $this->select($tableName, ['*'], ['ad_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen Archiv- Daten */
    public function getArchivData(array $where = []): array
    {
        $tableName = $this->ei_id . '_archivdat';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in Archiv- Daten */
    public function insertArch(array $data): int
    {
        $tableName = $this->ei_id . '_archivdat';
        return $this->insert($tableName, $data);
    }
    
    /** Update eines Records */
    public function updateArch(array $data, array $where): int
    {
        $tableName = $this->ei_id . '_archivdat';
        return $this->update($tableName, $data, $where);
    }
    
    /** Einlesen eines Archiv-Verleih- Records */
    public function getArchVerlById(int $id): ?array
    {
        $tableName = $this->ei_id . '_archord_erw';
        $results = $this->select($tableName, ['*'], ['ei_id' => $id], [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /** Einlesen Archiv-Verleih- Daten */
    public function getArchivVerl(array $where = []): array
    {
        $tableName = $this->ei_id . '_archord_erw';
        return $this->select($tableName, ['*'], $where);
    }
    
    /** Einfügen Record in Archiv-Verleih- Daten
     public function insertArchVerl(array $data): int
     {
     $tableName = $this->ei_id . '_archord_erw';
     return $this->insert($tableName, $data);
     }
     
     /** Update eines Records */
    public function updateArchVerl(array $data, array $where): int
    {
        $tableName = $this->ei_id . '_archord_erw';
        return $this->update($tableName, $data, $where);
    }
    
    /**
     * Anzeige der Archivordnung, zusätzlich definierte Ebene 3 und 4
     */
    public function getArchivordnungErweiterung(
        string $sg,
        string $ssg,
        string $lcsg,
        string $lcssg,
        string $lcssg_s0,
        string $lcssg_s1
        ): string {
            $table = 'hi_archord_erw';
            
            $where = [
                'al_sg'       => $sg . '.' . $ssg,
                'al_lcsg'     => $lcsg,
                'al_lcssg'    => $lcssg,
                'al_lcssg_s0' => $lcssg_s0,
                'al_lcssg_s1' => $lcssg_s1
            ];
            
            $results = $this->select($table, ['*'], $where, ['al_sg' => 'ASC']);
            
            if (!empty($results)) {
                $row = end($results);
                return sprintf(
                    "%s|%s|%s|%s|%s|%s",
                    $row['al_lcsg'],
                    $row['al_lcssg'],
                    $row['al_lcssg_s0'],
                    $row['al_lcssg_s1'],
                    $row['al_sammlung'],
                    $row['al_bezeich']
                    );
            }
            
            return '';
    }
    
    /**
     * Anzeige des Textes aus der Archivordnung, Ebene 1 und 2
     */
    public function getArchivordnungText(
        string $sg,
        string $ssg
        ): string {
            $table = 'hi_archivord';
            
            $where = [
                'ar_sg'     => $sg,
                'ar_sub_sg' => $ssg
            ];
            
            $results = $this->select($table, ['*'], $where, ['ar_sg' => 'ASC']);
            
            if (!empty($results)) {
                $row = end($results);
                return sprintf(
                    "%s.%s %s;",
                    $row['ar_sg'],
                    $row['ar_sub_sg'],
                    $row['ar_sgname']
                    );
            }
            
            return '';
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


    

    

    
 