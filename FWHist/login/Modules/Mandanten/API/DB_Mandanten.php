<?php
declare(strict_types=1);

namespace FWHist\Modules\Mandanten\API;

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '../../../../login/AOrd_Verz/Logging/Mand_Reposi_fatal_error.log', $message, FILE_APPEND);
    }
});
    
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '../../../../login/AOrd_Verz/Logging/Mand_Reposi_php-error.log.txt');
    

use FWHist\Database\DB_GenericLog;

final class DB_Mandanten
{
    private DB_GenericLog $db;
    
    /** Interne Whitelist der erlaubten Tabellen */
    private array $allowedTables = [
        'ma_mandanten',
        'ma_mand_erlauben',
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
    public function getMandById(int $id): ?array
    {
        return $this->select('ma_mandanten', ['*'], ['ei_id' => $id], [], 1)[0] ?? null;
    }
    
    /**
     * Beispiel: Füge neuen Datensatz in aw_ff_wappen ein
     */
    public function insertMand(array $data): int
    {
        return $this->insert('ma_mandanten', $data);
    }
    
    /**
     * Beispiel: Ändern Datensatz in aw_ff_wappen ein
     */
    public function updateMand( array $data, array $where ): int
    {
        return $this->update('ma_mandanten', $data, $where);
    }
    
    /**
     * Liefert die Daten des ausgesuchten Eigentümers als assoziatives Array zurück.
     *
     * @param string $eigentmr Eigentümer-Nummer
     * @return array|null Array mit Eigentümerdaten oder null, wenn nicht gefunden
     *
     * @global boolean $debug Anzeige von Debug-Informationen
     * @global mysqli $db Datenbank-Handle
     * @global string $module Modul-Name (optional, falls benötigt)
     * @global string $sub_mod Submodul-Name (optional, falls benötigt)
     */
    public function getEigentuemerData(int $eigentmr): ?array
    {
        // Tabelle muss erlaubt sein
        $this->checkTableAllowed('ma_mandanten');
        
        // Datenbankabfrage über select-Methode
        $rows = $this->select('ma_mandanten', ['*'], ['ei_id' => $eigentmr], [], 1);
        
        if (empty($rows)) {
            return null;
        }
        
        $row = $rows[0];
        
        // Ermittlung des Leihnamens basierend auf ei_org_typ
        $leihname = '';
        switch ($row['ei_org_typ']) {
            case "Privat":
                $leihname = '';
                break;
            case "Fa.":
                $leihname = "Fa. " . $row['ei_org_name'];
                break;
            case "FM":
                $leihname = "Feuerwehrmuseum " . $row['ei_org_name'];
                break;
            case "FF":
                $leihname = "Feuerwehr " . $row['ei_org_name'];
                break;
            case "V":
                $leihname = "Verein " . $row['ei_org_name'];
                break;
            case "AFKDO":
                $leihname = "AFKDO " . $row['ei_org_name'];
                break;
            case "BFKDO":
                $leihname = "BFKDO " . $row['ei_org_name'];
                break;
            case "LFKDO":
                $leihname = "LFKDO " . $row['ei_org_name'];
                break;
            case "GV":
                $leihname = $row['ei_org_name'];
                break;
            default:
                $leihname = $row['ei_org_name'] ?? '';
                break;
        }
        
        $eigData = [];
        
        $eigData['eig_eigner'] = $eigentmr;
        $eigData['eig_org'] = $leihname;
        $eigData['eig_name'] = $leihname;
        $eigData['eig_verant'] = trim(($row['ei_titel'] ?? '') . ' ' . ($row['ei_vname'] ?? '') . ' ' . ($row['ei_name'] ?? '') . ' ' . ($row['ei_dgr'] ?? ''));
        $eigData['eig_vopriv'] = $row['ei_vopriv'] ?? '';
        
        if (($row['ei_org_typ'] ?? '') === "Privat") {
            $eigData['eig_verant'] = "";
            $eigData['eig_staat'] = "";
            $eigData['eig_adresse'] = "";
            $eigData['eig_ort'] = "";
            $eigData['eig_name'] = trim(($row['ei_titel'] ?? '') . ' ' . ($row['ei_vname'] ?? '') . ' ' . ($row['ei_name'] ?? '') . ' ' . ($row['ei_dgr'] ?? ''));
            $eigData['eig_urhname'] = trim(($row['ei_vname'] ?? '') . ' ' . ($row['ei_name'] ?? ''));
        } else {
            $eigData['eig_staat'] = $row['ei_staat'] ?? '';
            $eigData['eig_adresse'] = $row['ei_adresse'] ?? '';
            $eigData['eig_ort'] = trim(($row['ei_plz'] ?? '') . ' ' . ($row['ei_ort'] ?? ''));
            $eigData['eig_urhname'] = $leihname;
        }
        
        return $eigData;
    }

    
    // Weitere spezifische Methoden für einzelne Tabellen können hier ergänzt werden
}

