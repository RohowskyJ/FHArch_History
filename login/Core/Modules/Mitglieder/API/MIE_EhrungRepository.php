<?php

namespace Fharch\Core\Modules\Mitglieder\API;

use \PDO;

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '1');

class MIE_EhrungRepository {
    private \PDO $pdo;
    protected static string $logFile = 'MIE_Repository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Ehrungen basierend auf Listentyp und optionaler Suche (mi_id)
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring (hier als mi_id interpretiert)
     * @return array
     */
    public function getEhrungen(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM fv_mi_ehrung";
        $where = [];
        $params = [];
        $orderBy = "ORDER BY me_id"; // Default order
        
        // Filter by mi_id if $search is numeric
        if ($search !== null && is_numeric($search)) {
            $where[] = "mi_id = :mi_id";
            $params[':mi_id'] = (int)$search;
        }
        
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " $orderBy";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Modify rows before returning
            foreach ($rows as &$row) {
                $this->modifyRow($row, $listType);
            }
            
            return $rows;
            
        } catch (\PDOException $e) {
            // Log error and return empty array or handle as needed
            self::log("Database error in getEhrungen: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Modifiziert einzelne Datenzeile vor Rückgabe
     * @param array $row
     * @param string $tabTyp
     * @return bool
     */
    protected function modifyRow(array &$row, string $tabTyp): bool {
        /*
        $json = json_encode($row);
        self::log("modifyRow called, row: $json");
        */
        $me_id = $row['me_id'] ?? 0;
        $row['action'] = "<a href='MitglEhrgEdit.php?ID={$me_id}'>Edit</a>";
        
        if (!empty($row['me_bild1'])) {
            $me_bild1 = $row['me_bild1'];
            
            // Absoluter Dateisystempfad zum Bild (für PHP-Funktionen)
            $filePath = $_SERVER['DOCUMENT_ROOT'].'/FHArch_neu/login/AOrd_Verz/1/MITGL/' . $me_bild1;
            
            // Webpfad zum Bild (für HTML src und href)
            $webPath = '/fharch_neu/login/AOrd_Verz/1/MITGL/' . $me_bild1;
            
            // Prüfen, ob die Datei existiert
            if (is_file($filePath)) {
                $row['me_bild1'] = "<a href='$webPath' target='Bild 1'>
                <img src='$webPath' alt='$me_bild1' width='150px'><br>$me_bild1</a>";
            } else {
                // Kein Text als URL ausgeben, sondern leer oder Platzhalter
                $row['me_bild1'] = ''; // oder mit Platzhalterbild
            }
        }
        
        return true;
    }
    /**
     * Schreibt Log-Einträge in Datei
     * @param string $message
     * @return void
     */
    protected static function log(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}
