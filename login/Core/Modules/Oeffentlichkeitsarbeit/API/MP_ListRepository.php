<?php
namespace Fharch\Core\Modules\Oeffentlichkeitsarbeit\API;

use \PDO;
use Fharch\Core\Services\TableColumnMetadata;

const GrafFiles = array("gif","ico","jpeg","jpg","png","tiff"); 

/**
 * Liest Daten aus der Mitgliederdatei aus, Auswahl entsprechend der Listentype (alle, nur aktive, Adressliste, ..)
 * @author josef
 *
 */
class MP_ListRepository {
    private PDO $pdo;
    protected static string $logFile = 'MP_ListRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getLinks(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM oe_marktplatz ";
        $where = [];
        $params = [];
        
        switch ($listType) {
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY bs_id";
        }
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "mi_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " $orderBy";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll();
        
        // Daten vor der Rückgabe anpassen
        foreach ($rows as &$row) {
            $this->modifyRow($row, $listType);
        }
        
        return $rows;
    }
    
    protected function modifyRow(array &$row, $tabTyp)
    {
        /** Ausschluss- Kriterien :  Enddatum > Aktuelles Datum */
     
        $today = date('Y-m-d');
        echo __LINE__ . " today $today <br>";
        
        if ($row['bs_enddatum'] <= $today) {
            unset($row);
            return true;
        }
        
        
        /** ab hier sollen die Daten ausgegeben werden */
        // $json = json_encode( );
        // $this->log("modifyRow wurde aufgerufen, row $json");
        $bs_id = $row['bs_id']; // ?? 0;
        
        if ($tabTyp != "Extern") {
            $row['action'] = "<a href='MarktplEdit.php?ID={$bs_id}'>Edit</a>";
        }
        
        $MailSet = [];
        if ($row['bs_email_1'] != "") {
            $MailSet[] = trim($row['bs_email_1']);
        }
        if ($row['bs_email_2'] != "") {
            
        }
        
        $row['bs_email_1'] = implode(", ", $MailSet);
        var_dump($_SERVER);
        $srv = $_SERVER['SCRIPT_FILENAME'];
        
        $d_path = "../../../../login/AOrd_Verz/Biete_Suche/";
        if ($row['bs_bild_1'] != "") {
            $bild1 = $row['bs_bild_1'];
            $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION)); 
            
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['bs_bild_1'] = "<a href='$d_path$bild1' target='_blank'><img src='$d_path$bild1' alt='$bild1' height='200' ></a>"; 
            } else {
                $row['bs_bild_1'] = "<a href='$d_path$bild1' target='_blank'>$bild1</a>";
            }
        }
        
        if ($row['bs_bild_2'] != "") {
            $bild2 = $row['bs_bild_2'];
            $ext = strtolower(pathinfo($bild2, PATHINFO_EXTENSION));
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['bs_bild_2'] = "<a href='$d_path$bild2' target='_blank'><img src='$d_path$bild2' alt='$bild2' height='200' ></a>";
            } else {
                $row['bs_bild_2'] = "<a href='$d_path$bild2' target='_blank'>$bild2</a>";
            }
        }
        
        unset($row['bs_email_2']);
        unset($row['bs_bild_3']);
        unset($row['bs_bild_4']);
        unset($row['bs_changed_id']);
        unset($row['bs_changed_at']);
        
        return true;
    }
    
    /** Funktion zum schreiben von Log- Eintägen der Klasse */
    protected static function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }
}