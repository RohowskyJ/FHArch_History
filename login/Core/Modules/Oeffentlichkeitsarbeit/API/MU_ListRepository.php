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
class MU_ListRepository {
    private PDO $pdo;
    protected static string $logFile = 'MU_ListRepository_debug.log.txt';
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Holt Mitglieder-Daten basierend auf dem Listentyp und optionalen Suchparametern
     * @param string $listType z.B. 'Alle', 'Mitgl', 'BezL', ...
     * @param string|null $search optionaler Suchstring
     * @return array
     */
    public function getMuseen(string $listType, ?string $search = null): array {
        $sql = "SELECT * FROM oe_museen ";
        $where = [];
        $params = [];
        /*
        "Alle" => "Alle bekannten Museen anzeigen",
        "Staat" => "Auswahl nach Staat",
        "Bundld" => "Auswahl nach Bundesland",
        "MTyp" => "Auswahl nach Museumstyp"
        */
        switch ($listType) {
            case 'Staat':
                $where = [];
                $orderBy = "ORDER BY mu_id";
                break;
            case 'Bundld':
                $where = [];
                $orderBy = "ORDER BY mu_id";
                break;
            case 'MTyp':
                $where = [];
                $orderBy = "ORDER BY mu_id";
                break;
            case 'Alle':
            default:
                # $where[] = "mi_name != ''";
                $orderBy = "ORDER BY mu_id";
        }
        
        if ($search !== null && trim($search) !== '') {
            $where[] = "mu_name LIKE :search";
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
        // $json = json_encode($row);
        // $this->log("modifyRow wurde aufgerufen, row $json");
        $muTyp = [
            "0" => "Sammlung, Depot",
            "1"  => "Schausammlung",
            "2" => "Traditionsraum",
            "3" => "Schauraum, Museum"
        ];
        $mu_id = $row['mu_id']; // ?? 0;
        
        if (!stripos(".",$tabTyp) >= 2) {
            $row['action'] = "<a href='MuseenEdit.php?ID={$mu_id}'>Edit</a>";
        }
        /* mu_name
        mu_name mu_adresse mu_plz mu_ort mu_mustyp
        */
        $row['mu_name'] = "<b>" . $row['mu_name'] . "</b>";
        $row['mu_name'] .= "<br>" . $row['mu_adresse_a'] ;
        $row['mu_name'] .= "<br>" . $row['mu_plz_a'] ." " . $row['mu_ort_a'];
        $row['mu_name'] .= "<br>" . $muTyp[$row['mu_mustyp']] ;
        /* Info:
         * öffnungszeiten, auskunft
         */
        $d_path = "../../../../login/AOrd_Verz/Museen/";
        if ($row['mu_bild_1'] != "") {
            $bild1 = $row['mu_bild_1'];
            $ext = strtolower(pathinfo($bild1, PATHINFO_EXTENSION));
            
            $graffile = false;
            foreach (GrafFiles as $key => $val ) {
                if ($val == $ext ) {
                    $graffile = true;
                    break;
                }
            }
            
            if ($graffile) {
                $row['mu_bild_1'] = "<a href='$d_path$bild1' target='_blank'><img src='$d_path$bild1' alt='$bild1' height='200' ></a>";
            } else {
                $row['mu_bild_1'] = "<a href='$d_path$bild1' target='_blank'>$bild1</a>";
            }
        }
        
        
        unset($row['mu_url_chkd']);
        unset($row['mu_url_obsolete']);
        unset($row['mu_changed-id']);
        unset($row['mu_changed_at']);
        
 
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